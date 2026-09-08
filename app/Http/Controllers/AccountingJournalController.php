<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountingJournalRequest;
use App\Http\Requests\UpdateAccountingJournalRequest;
use App\Models\AccountingJournal;
use App\Models\AccountingJournalDetail;
use App\Models\AccountingAccount;
use App\Models\AccountingPeriod;
use App\Models\License;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Worker;
use App\Models\User;
use App\Models\AccountingJournalEnclosure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\BalanceSheetService;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;
use DB;


class AccountingJournalController extends Controller
{
    public function index(Request $request)
{
    if ($request->ajax()) {

        $licenseId = config('app.license_id');

        $journals = AccountingJournal::query()
            ->select(
                'accounting_journals.id',
                'accounting_journals.journal_code',
                'accounting_journals.transaction_date',
                'global.users.fullname as creator_name'
            )
            ->leftJoin('global.users', 'accounting_journals.created_by', '=', 'global.users.id')
            ->where('accounting_journals.license_id', $licenseId);
        return DataTables::of($journals)
            ->addIndexColumn()

            ->editColumn('transaction_date', function ($row) {
                return \Carbon\Carbon::parse($row->transaction_date)->format('d/m/Y');
            })

            ->editColumn('journal_code', function ($row) {
                return '<a href="'.route('journals.show',$row->id).'"
                        class="fw-bold text-primary">'
                        .$row->journal_code.
                        '</a>';
            })

            ->editColumn('creator_name', function ($row) {
                return $row->creator_name
                    ?: '<small class="fst-italic text-muted">dibuat oleh sistem</small>';
            })

            ->addColumn('action', function ($journal) {
                $buttons = '';

                if (auth()->user()->can('ubah jurnal')) {
                    $buttons .= '<a href="' . route('journals.edit', $journal->id) . '" class="btn btn-sm btn-dark me-1">
                                    <i class="ti ti-edit"></i>
                                </a>';
                }

                if (auth()->user()->can('lihat jurnal')) {
                    $buttons .= '<a href="' . route('journals.show', $journal->id) . '" class="btn btn-sm btn-dark me-1">
                                    <i class="ti ti-eye"></i>
                                </a>';
                }

                if (auth()->user()->can('hapus jurnal')) {
                    $buttons .= '<button data-id="' . $journal->id . '" class="btn btn-sm btn-dark delete-journal">
                                    <i class="ti ti-trash"></i>
                                </button>';
                }

                return $buttons;
            })

            ->rawColumns(['journal_code', 'creator', 'action'])
            ->make(true);
    }

    return view('journals.index');
}

public function create(AccountingJournal $journal)
{
    $licenseId = config('app.license_id');

    $accounts = AccountingAccount::query()
        ->where('license_id', $licenseId)
        ->where('is_parent', false)
        ->where('is_active', true)
        ->orderBy('account_code')
        ->get();

    $employees = User::select('id', 'fullname as name')->get();
    $customers = User::select('id', 'fullname as name')->get();
    $workers = User::select('id', 'fullname as name')->get();

    $journalCode = $this->generateNextJournalCode();
    $lastClosedDate = DB::table('lebihtersistem.accounting_periods')
        ->where('license_id', $licenseId)
        ->where('is_closed', true)
        ->max('end_date');

    return view('journals.create', [
        'journal'        => $journal,
        'accounts'       => $accounts,
        'employees'      => $employees,
        'customers'      => $customers,
        'workers'        => $workers,
        'journalCode'    => $journalCode,
        'lastClosedDate' => $lastClosedDate,
    ]);
}

private function generateNextJournalCode()
{
    $licenseId = config('app.license_id');

    $lastJournalNumber = AccountingJournal::where('license_id', $licenseId)
        ->where('journal_code', 'ILIKE', 'IJ-%')
        ->selectRaw("
            MAX(
                CAST(
                    REGEXP_REPLACE(journal_code, '^.*-', '') AS INTEGER
                )
            ) as last_number
        ")
        ->value('last_number');

    $lastJournalNumber = $lastJournalNumber ?? 0;

    do {
        $nextNumber = str_pad($lastJournalNumber + 1, 4, '0', STR_PAD_LEFT);
        $journalCode = 'IJ-' . $nextNumber;

        $exists = AccountingJournal::where('journal_code', $journalCode)->exists();
        $lastJournalNumber++;
    } while ($exists);

    return $journalCode;
}

public function getNextCode()
{
    $nextCode = $this->generateNextJournalCode();

    return response()->json([
        'next_code' => $nextCode,
    ]);
}

public function store(StoreAccountingJournalRequest $request)
{
    $user = Auth::user();
    $licenseId = config('app.license_id');
    $year = date('Y', strtotime($request->transaction_date));

    $isClosed = DB::table('lebihtersistem.accounting_periods')
        ->where('license_id', $licenseId)
        ->where('year', $year)
        ->orderByDesc('id')
        ->value('is_closed');

    if ($isClosed) {
        return back()->withErrors([
            'transaction_date' => "Tahun $year sudah ditutup"
        ]);
    }

    // ✅ Validasi balance
    $totalDebit  = collect($request->details)->sum('debit');
    $totalCredit = collect($request->details)->sum('credit');

    if ($totalDebit != $totalCredit) {
        return back()->withErrors('Debit dan Credit harus balance.');
    }

    // ✅ Pakai auto generate (tidak dari request)
    $journalCode = $this->generateNextJournalCode();

    // Simpan jurnal
    $journal = AccountingJournal::create([
        'license_id'       => $licenseId,
        'journal_code'     => $journalCode,
        'transaction_date' => $request->transaction_date,
        'description'      => $request->description,
        'created_by'       => $user->id,
    ]);
    if ($request->hasFile('enclosure')) {
        foreach ($request->file('enclosure') as $file) {

            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

            $filePath = $file->storeAs(
                'attachments',
                $fileName,
                'public'
            );

            AccountingJournalEnclosure::create([
                'journal_id' => $journal->id,
                'file_name'  => $filePath,
            ]);
        }
    }
    foreach ($request->details as $detail) {
        AccountingJournalDetail::create([
            'journal_id'  => $journal->id,
            'account_id'  => $detail['account_id'],
            'person'      => $detail['person'] ?? null,
            'debit'       => $detail['debit'] ?? 0,
            'credit'      => $detail['credit'] ?? 0,
            'description' => $detail['description'] ?? null,
        ]);
    }

    return redirect()->route('journals.index')
        ->with('success', 'Jurnal berhasil dibuat.');
}
public function checkPeriod(Request $request)
{
    $licenseId = config('app.license_id');

    $isClosed = DB::table('lebihtersistem.accounting_periods')
        ->where('license_id', $licenseId)
        ->whereDate('start_date', '<=', $request->date)
        ->whereDate('end_date', '>=', $request->date)
        ->value('is_closed');

    return response()->json([
        'closed' => (bool) $isClosed
    ]);
}

public function show(AccountingJournal $journal)
{
    if ($journal->license_id !== config('app.license_id')) {
        abort(403);
    }

    $journal->load(['details.account', 'creator']);

    return view('journals.show', compact('journal'));
}

public function edit(AccountingJournal $journal)
{
    if ($journal->license_id !== config('app.license_id')) {
        abort(403);
    }

    $accounts = AccountingAccount::where('is_active', true)
        ->orderBy('account_code')
        ->get();

    $employees = Employee::with('user')
        ->get()
        ->map(fn($emp) => [
            'id'   => $emp->id,
            'name' => $emp->user?->fullname ?? '-',
        ]);

    $customers = Customer::with('user')
        ->get()
        ->map(fn($cus) => [
            'id'   => $cus->id,
            'name' => $cus->user?->fullname ?? '-',
        ]);

    $workers = Worker::with('user')
        ->get()
        ->map(fn($work) => [
            'id'   => $work->id,
            'name' => $work->user?->fullname ?? '-',
        ]);

    $journal->load(['details.account']);

    return view('journals.edit', compact('journal', 'accounts', 'employees', 'customers', 'workers'));
}


public function update(UpdateAccountingJournalRequest $request, AccountingJournal $journal)
{
    if ($journal->license_id !== config('app.license_id')) {
        abort(403, 'Akses tidak valid.');
    }

    $totalDebit = collect($request->details)->sum('debit');
    $totalCredit = collect($request->details)->sum('credit');

    if ($totalDebit != $totalCredit) {
        return back()->withErrors([
            'details' => 'Debit dan kredit harus seimbang.'
        ]);
    }

    if ($request->filled('remove_enclosures')) {
        $files = AccountingJournalEnclosure::whereIn(
            'id',
            $request->remove_enclosures
        )->get();
        foreach ($files as $file) {
            Storage::disk('public')->delete($file->file_name);
            $file->delete();
        }
    }
    if ($request->hasFile('enclosure')) {
        foreach ($request->file('enclosure') as $file) {
            $path = $file->storeAs(
                'attachments',
                Str::uuid().'.'.$file->getClientOriginalExtension(),
                'public'
            );
            $journal->enclosures()->create([
                'file_name' => $path,
            ]);
        }
    }

    $journal->update([
        'transaction_date' => $request->transaction_date,
        'description' => $request->description,
    ]);

    // 🔹 Reset detail
    $journal->details()->delete();

    foreach ($request->details as $detail) {
        $journal->details()->create([
            'account_id' => $detail['account_id'],
            'person' => $detail['person'] ?? null,
            'debit' => $detail['debit'] ?? 0,
            'credit' => $detail['credit'] ?? 0,
            'description' => $detail['description'] ?? null,
        ]);
    }

return redirect()
    ->route('journals.show', $journal)
    ->with('success', 'Jurnal berhasil diperbarui.');
}

    public function destroy($id)
    {
        $journal = AccountingJournal::findOrFail($id);
         if ($journal->enclosure && Storage::disk('public')->exists($journal->enclosure)) {
            Storage::disk('public')->delete($journal->enclosure);
        }
        $journal->details()->delete();
        $journal->delete();

        return response()->json([
            'status' => 'success'
        ]);
    }


    public function report23(Request $request)
{
    $user = Auth::user();
    
    $accountId = $request->input('account_id');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    // $licenseFilterId = $request->input('license_id');

    
    // $licenses = collect();
    // if ($user->hasRole('Super-Admin')) {
    //     $licenses = License::all();
    // } elseif ($user->hasRole('Pemilik Lisensi')) {
    //     $licenses = $user->licenses ?? collect();
    //     if ($licenses->isEmpty()) {
    //         abort(403, 'Lisensi tidak ditemukan.');
    //     }
    // } elseif ($user->hasRole('Akuntan')) {
    //     $licenses = $user->employee?->licenses ?? collect();
    //     if ($licenses->isEmpty()) {
    //         abort(403, 'Lisensi tidak ditemukan.');
    //     }
    // } else {
    //     abort(403, 'Role tidak diizinkan.');
    // }

    // Filter akun
    $accountsQuery = AccountingAccount::where('is_parent', false)->where('is_active', true);
    // if ($licenseFilterId) {
    //     $accountsQuery->where('license_id', $licenseFilterId);
    // } else {
    //     $accountsQuery->whereIn('license_id', $licenses->pluck('id'));
    // }
    $accounts = $accountsQuery->orderBy('account_code')->get();

    $journalsQuery = AccountingJournal::query()
        ->with([
            'creator',
            'details' => function ($q) use ($accountId) {
                $q->select('id','account_id','journal_id','debit','credit','description');

                if ($accountId) {
                    $q->where('account_id', $accountId);
                }

                $q->with('account:id,account_code,account_name');
            }
        ])
        ->when($startDate, fn($q) => $q->whereDate('transaction_date', '>=', $startDate))
        ->when($endDate, fn($q) => $q->whereDate('transaction_date', '<=', $endDate))
        ->when($accountId, fn($q) => $q->whereHas('details', fn($q2) => $q2->where('account_id', $accountId)));


    // if ($licenseFilterId) {
    //     if (
    //         $user->hasRole('Super-Admin') ||
    //         ($user->hasRole('Pemilik Lisensi') && $licenses->pluck('id')->contains($licenseFilterId)) ||
    //         ($user->hasRole('Akuntan') && $licenses->pluck('id')->contains($licenseFilterId))
    //     ) {
    //         $journalsQuery->where('license_id', $licenseFilterId);
    //     } else {
    //         abort(403, 'Lisensi tidak valid.');
    //     }
    // } else {
    //     $journalsQuery->whereIn('license_id', $licenses->pluck('id'));
    // }

    $journals = $journalsQuery->orderBy('transaction_date')->paginate(20);

    return view('journals.report', compact(
        'accounts',
        'journals',
        'accountId',
        'startDate',
        'endDate'
    ));
}
    public function report(Request $request)
{
    $user = Auth::user();
    
    $accountId = $request->input('account_id');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    // $licenseFilterId = $request->input('license_id');

    
    // $licenses = collect();
    // if ($user->hasRole('Super-Admin')) {
    //     $licenses = License::all();
    // } elseif ($user->hasRole('Pemilik Lisensi')) {
    //     $licenses = $user->licenses ?? collect();
    //     if ($licenses->isEmpty()) {
    //         abort(403, 'Lisensi tidak ditemukan.');
    //     }
    // } elseif ($user->hasRole('Akuntan')) {
    //     $licenses = $user->employee?->licenses ?? collect();
    //     if ($licenses->isEmpty()) {
    //         abort(403, 'Lisensi tidak ditemukan.');
    //     }
    // } else {
    //     abort(403, 'Role tidak diizinkan.');
    // }

    // Filter akun
    $accountsQuery = AccountingAccount::where('is_parent', false)->where('is_active', true);
    // if ($licenseFilterId) {
    //     $accountsQuery->where('license_id', $licenseFilterId);
    // } else {
    //     $accountsQuery->whereIn('license_id', $licenses->pluck('id'));
    // }
    $accounts = $accountsQuery->orderBy('account_code')->get();

    $journalsQuery = AccountingJournal::query()
        ->with('creator:id,fullname')
        ->with(['details' => function ($q) use ($accountId) {
            if ($accountId) {
                $q->where('account_id', $accountId)->with('account');
            } else {
                $q->with('account');
            }
        }])
        ->when($startDate, fn($q) => $q->whereDate('transaction_date', '>=', $startDate))
        ->when($endDate, fn($q) => $q->whereDate('transaction_date', '<=', $endDate))
        // pastikan jurnal memiliki detail yang sesuai (biar jurnal tanpa akun itu tidak muncul)
        ->when($accountId, fn($q) => $q->whereHas('details', fn($q2) => $q2->where('account_id', $accountId)));


    // if ($licenseFilterId) {
    //     if (
    //         $user->hasRole('Super-Admin') ||
    //         ($user->hasRole('Pemilik Lisensi') && $licenses->pluck('id')->contains($licenseFilterId)) ||
    //         ($user->hasRole('Akuntan') && $licenses->pluck('id')->contains($licenseFilterId))
    //     ) {
    //         $journalsQuery->where('license_id', $licenseFilterId);
    //     } else {
    //         abort(403, 'Lisensi tidak valid.');
    //     }
    // } else {
    //     $journalsQuery->whereIn('license_id', $licenses->pluck('id'));
    // }

    $journals = $journalsQuery->orderBy('transaction_date')->paginate(20);

    return view('journals.report', compact(
        'accounts',
        'journals',
        'accountId',
        'startDate',
        'endDate'
    ));
}
   public function generalJournal(Request $request)
{
    $user = Auth::user();

    // 🔹 Default filter tanggal: bulan berjalan
    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->endOfMonth()->toDateString();

    // 🔹 Base query
    $journals = AccountingJournal::with(['details.account']);

    // 🔹 Filter role user
    // if ($user->hasRole('Super-Admin')) {
    //     $licenses = License::all();
    // } else {     
    //     $licenses = $user->hasRole('Pemilik Lisensi')
    //         ? $user->licenses
    //         : $user->employee?->licenses;

    //     abort_if(!$licenses || $licenses->isEmpty(), 403, 'Lisensi tidak ditemukan.');
    // }

    // $activeLicenseId = $request->get('license_id') ?? session('active_license_id');

    // if ($activeLicenseId) {
    //     $journals->where('license_id', $activeLicenseId);
    // } else {
    //     $journals->whereIn('license_id', $licenses->pluck('id'));
    // }

    // 🔹 Filter tanggal
    $journals = $journals->whereBetween('transaction_date', [$startDate, $endDate])
        ->orderBy('transaction_date')
        ->get();

    $totalDebit = 0;
    $totalCredit = 0;

    foreach ($journals as $journal) {
        foreach ($journal->details as $detail) {
            $totalDebit += $detail->debit;
            $totalCredit += $detail->credit;
        }
    }

    return view('journals.general', compact('journals', 'startDate', 'endDate', 'totalDebit', 'totalCredit'));
}

public function exportPDF(Request $request)
{
    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate = $request->end_date ?? now()->endOfMonth()->toDateString();
    $activeLicenseId = $request->license_id ?? auth()->user()->license_id;

    $journals = AccountingJournal::with(['details.account'])
        ->whereBetween('transaction_date', [$startDate, $endDate])
        ->orderBy('transaction_date', 'asc')
        ->get();

    $totalDebit = $journals->sum(fn($j) => $j->details->sum('debit'));
    $totalCredit = $journals->sum(fn($j) => $j->details->sum('credit'));

    // Load view khusus PDF
    $pdf = Pdf::loadView('journals.export-pdf', compact(
        'journals',
        'startDate',
        'endDate',
        'totalDebit',
        'totalCredit'
    ))
    ->setPaper('a4', 'portrait');

    return $pdf->stream('Jurnal Umum '.$startDate.'_to_'.$endDate.'.pdf');
}

public function ledger(Request $request)
{
    $user = Auth::user();

    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->endOfMonth()->toDateString();

    [$ledger] = $this->getLedgerData($startDate, $endDate);

    return view('journals.ledger', compact(
        'ledger',
        'startDate',
        'endDate'
    ));
}

private function getLedgerData($startDate, $endDate)
{
    $query = AccountingJournalDetail::with(['journal', 'account']);
    $user = Auth::user();

    $query->whereHas('journal', function ($q) use ($startDate, $endDate) {
        $q->whereBetween('transaction_date', [$startDate, $endDate]);
    });

    $details = $query
        ->join(
            'lebihtersistem.accounting_accounts as accounts',
            'accounts.id',
            '=',
            'lebihtersistem.accounting_journal_details.account_id'
        )
        ->orderByRaw("
            SPLIT_PART(accounts.account_code, '-', 1)::INT,
            SPLIT_PART(accounts.account_code, '-', 2)::INT,
            SPLIT_PART(accounts.account_code, '-', 3)::INT
        ")
        ->orderBy(
            AccountingJournal::select('transaction_date')
                ->whereColumn(
                    'id',
                    'lebihtersistem.accounting_journal_details.journal_id'
                )
        )
        ->select('lebihtersistem.accounting_journal_details.*')
        ->get();

    $ledger = [];

    foreach ($details->groupBy('account_id') as $accountId => $items) {
        $balance = 0;
        $rows = [];

        foreach ($items as $detail) {
            $balance += ($detail->debit - $detail->credit);

            $rows[] = [
                'journal_id'       => $detail->journal_id,
                'transaction_date' => $detail->journal->transaction_date,
                'journal_code'     => $detail->journal->journal_code,
                'description'      => $detail->description,
                'debit'            => $detail->debit,
                'credit'           => $detail->credit,
                'balance'          => $balance,
            ];
        }

        $ledger[$accountId] = [
            'account' => $items->first()->account,
            'rows'    => $rows,
        ];
    }

    return [$ledger];
}

public function exportLedgerPdf(Request $request)
{
    $startDate = $request->get('start_date');
    $endDate   = $request->get('end_date');

    $user = auth()->user();

    // 🔹 Ambil data ledger sesuai filter
    [$ledger] = $this->getLedgerData(
        $startDate,
        $endDate,
    );

    // 🔹 Load view PDF
    $pdf = Pdf::loadView('journals.ledgerpdf', compact(
        'ledger', 'startDate', 'endDate'
    ))->setPaper('a4', 'portrait');

    return $pdf->stream('Buku Besar '.$startDate.'_to_'.$endDate.'.pdf');
}

public function trialBalance(Request $request)
{
    $user = auth()->user();

    // Default periode (bulan berjalan)
    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->endOfMonth()->toDateString();

    // 🔹 Filter lisensi sesuai role
    // if ($user->hasRole('Super-Admin')) {
    //     $licenses = License::all();
    // } elseif ($user->hasRole('Pemilik Lisensi')) {
    //     $licenses = $user->licenses;
    // } else {
    //     $licenses = $user->employee?->licenses ?? collect();
    // }

    // 🔹 Lisensi aktif → default ambil yang pertama
    // $activeLicenseId = $request->get('license_id') ?? session('active_license_id');

    // 🔹 Ambil akun yang sudah dikelompokkan
    $groupedAccounts = $this->getTrialBalanceAccounts($startDate, $endDate);

    // 🔹 Hitung total debit & kredit
    $totalDebit  = collect($groupedAccounts)->sum(fn($cat) => collect($cat)->sum(fn($sub) => $sub['subtotalDebit']));
    $totalCredit = collect($groupedAccounts)->sum(fn($cat) => collect($cat)->sum(fn($sub) => $sub['subtotalCredit']));

    return view('journals.trialbalance', compact(
        'groupedAccounts',
        'totalDebit',
        'totalCredit',
        'startDate',
        'endDate',
        'licenses',
    ));
}

private function buildGroupedAccounts(\Closure $journalFilter)
{
    $accounts = AccountingAccount::where('is_parent', false)
        ->get()
        ->map(function ($account) use ($journalFilter) {

            $debit = AccountingJournalDetail::query()
                ->where('account_id', $account->id)
                ->whereHas('journal', $journalFilter)
                ->sum('debit');

            $credit = AccountingJournalDetail::query()
                ->where('account_id', $account->id)
                ->whereHas('journal', $journalFilter)
                ->sum('credit');

            switch ($account->category) {

                case 'AKTIVA':
                case 'BEBAN':
                    $balance = $debit - $credit;
                    break;

                case 'KEWAJIBAN':
                case 'EKUITAS':
                case 'PENDAPATAN':
                    $balance = $credit - $debit;
                    break;

                default:
                    $balance = $debit - $credit;
            }

            return [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'category'     => $account->category,
                'sub_category' => $account->sub_category,
                'debit'        => $debit,
                'credit'       => $credit,
                'balance'      => $balance,
            ];
        });

    return $accounts
        ->groupBy('category')
        ->map(function ($catGroup) {

            return $catGroup->groupBy('sub_category')->map(function ($subGroup) {

                return [
                    'accounts'         => $subGroup,
                    'subtotalDebit'    => $subGroup->sum('debit'),
                    'subtotalCredit'   => $subGroup->sum('credit'),
                    'subtotalBalance'  => $subGroup->sum('balance'),
                ];

            });

        });
}
private function getBalanceSheetAccounts($startDate, $endDate)
{
    return $this->buildGroupedAccounts(function ($query) use ($startDate, $endDate) {

        $query->whereBetween('transaction_date', [$startDate, $endDate]);

    });
}
private function getTrialBalanceAccounts($startDate, $endDate)
{
    return $this->buildGroupedAccounts(function ($query) use ($startDate, $endDate) {

        $query->whereBetween('transaction_date', [$startDate, $endDate]);

    });
}
public function exportTrial(Request $request)
{
    $user = auth()->user();

    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->endOfMonth()->toDateString();

    // 🔹 Filter lisensi sesuai role → sama seperti trialBalance()
    if ($user->can('tambah jurnal')) {

        // Boleh melihat semua parent account
        $parentAccounts = AccountingAccount::where('is_parent', true)
            ->orderBy('account_code')
            ->get();

    } elseif ($user->can('tambah jurnal lisensi')) {

        $activeLicenseId = session('active_license_id');

        if (!$activeLicenseId) {
            abort(403, 'Silakan pilih lisensi aktif.');
        }

        // Hanya parent account milik lisensi aktif
        $parentAccounts = AccountingAccount::where('is_parent', true)
            ->where('license_id', $activeLicenseId)
            ->orderBy('account_code')
            ->get();

    } else {

        abort(403, 'Tidak memiliki akses.');

    }

//    $activeLicenseId = $request->get('license_id') ?? session('active_license_id');

    // 🔹 Ambil data yang sama seperti di view trial balance
    $groupedAccounts = $this->getTrialBalanceAccounts($startDate, $endDate);
  
    $totalDebit  = collect($groupedAccounts)->sum(fn($cat) => collect($cat)->sum(fn($sub) => $sub['subtotalDebit']));
    $totalCredit = collect($groupedAccounts)->sum(fn($cat) => collect($cat)->sum(fn($sub) => $sub['subtotalCredit']));

    // 🔹 Generate PDF
    $pdf = Pdf::loadView('journals.trialbalance-pdf', [
        'groupedAccounts' => $groupedAccounts,
        'startDate'       => $startDate,
        'endDate'         => $endDate,
        'licenses'        => $licenses,
        'totalDebit'      => $totalDebit,
        'totalCredit'     => $totalCredit,
        'request'         => $request,
    ]);

    return $pdf->stream('trial-balance.pdf');
}

public function print(AccountingJournal $journal)
{
    $pdf = Pdf::loadView('journals.print', compact('journal'))
        ->setPaper('a4', 'landscape'); // bisa juga landscape

    return $pdf->stream('Jurnal Harian ' . $journal->journal_code . '.pdf');
}

public function balanceSheet(Request $request)
{
    $user = auth()->user();

    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->endOfMonth()->toDateString();

    // if ($user->hasRole('Super-Admin')) {
    //     $licenses = License::all();
    // } elseif ($user->hasRole('Pemilik Lisensi')) {
    //     $licenses = $user->licenses;
    // } else {
    //     $licenses = $user->employee?->licenses ?? collect();
    // }

    // $activeLicenseId = $request->get('license_id') ?? session('active_license_id');

    $viewType = $request->get('view', 'default'); // 🔹 default | skontro

    $groupedAccounts = $this->getBalanceSheetAccounts($startDate, $endDate);

    $totals = BalanceSheetService::calculate($groupedAccounts);
    $totalDebit  = collect($groupedAccounts)->sum(fn($cat) => collect($cat)->sum(fn($sub) => $sub['subtotalDebit']));
    $totalCredit = collect($groupedAccounts)->sum(fn($cat) => collect($cat)->sum(fn($sub) => $sub['subtotalCredit']));
    
    return view('reports.balance_sheet', array_merge([
        'startDate'       => $startDate,
        'endDate'         => $endDate,
        'groupedAccounts' => $groupedAccounts,
        'viewType'        => $viewType,
        'totalDebit'      => $totalDebit,
        'totalCredit'     => $totalCredit,
    ], $totals));
}

}