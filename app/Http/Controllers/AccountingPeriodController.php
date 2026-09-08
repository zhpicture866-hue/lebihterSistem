<?php

namespace App\Http\Controllers;

use App\Models\AccountingAccount;
use App\Models\AccountingClosingBalance;
use App\Models\AccountingJournalDetail;
use App\Models\AccountingPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AccountingPeriodController extends Controller
{
    public function index()
    {
        $periods = DB::table('lebihtersistem.accounting_periods')
            ->orderByDesc('year')
            ->get();

        return view('accounting.periods.index', compact('periods'));
    }

    public function close(Request $request)
    {
        $request->validate([
            'year' => 'required|integer'
        ]);

        $year = $request->year;

        $period = DB::table('accounting_periods')
            ->where('year', $year)
            ->first();

        if (!$period) {
            return back()->with('error', 'Periode tidak ditemukan');
        }

        if ($period->is_closed) {
            return back()->with('error', 'Periode sudah ditutup');
        }

        DB::transaction(function () use ($year) {

            $balances = DB::table('accounting_journal_details as d')
                ->join('accounting_journals as j', 'j.id', '=', 'd.journal_id')
                ->join('accounting_accounts as a', 'a.id', '=', 'd.account_id')
                ->select(
                    'd.account_id',
                    DB::raw('SUM(d.debit) as total_debit'),
                    DB::raw('SUM(d.credit) as total_credit')
                )
                ->whereYear('j.transaction_date', $year)
                ->whereNotIn('a.category', ['PENDAPATAN', 'BEBAN'])
                ->groupBy('d.account_id')
                ->get();

            $nextYear = $year + 1;

            foreach ($balances as $b) {

                $net = $b->total_debit - $b->total_credit;

                DB::table('opening_balances')->updateOrInsert(
                    [
                        'account_id' => $b->account_id,
                        'year'       => $nextYear,
                    ],
                    [

                        'debit'  => $net > 0 ? $net : 0,
                        'credit' => $net < 0 ? abs($net) : 0,
                        'updated_at' => now(),
                    ]
                );
            }

            DB::table('accounting_periods')
                ->where('year', $year)
                ->update([
                    'is_closed' => true,
                    'closed_at' => now(),
                    'closed_by' => auth()->id()
                ]);

            DB::table('accounting_periods')->updateOrInsert(
                [
                    'year' => $nextYear
                ],
                [
                    'id' => Str::uuid(),
                    'start_date' => "$nextYear-01-01",
                    'end_date' => "$nextYear-12-31",
                    'is_closed' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        });

        return back()->with('success', "Tahun $year berhasil ditutup");
    }

    public function reopen(Request $request)
    {
        $request->validate([
            'year' => 'required|integer'
        ]);

        DB::table('accounting_periods')
            ->where('year', $request->year)
            ->update([
                'is_closed' => false,
                'closed_at' => null,
                'closed_by' => null
            ]);

        return back()->with('success', "Tahun {$request->year} dibuka kembali");
    }
    public function destroy($id)
{
    $period = DB::table('accounting_periods')->where('id', $id)->first();

    if (!$period) {
        return back()->with('error', 'Periode tidak ditemukan');
    }
    if ($period->is_closed) {
        return back()->with('error', 'Periode yang sudah closed tidak boleh dihapus');
    }

    // contoh validasi keamanan
    $hasJournal = DB::table('accounting_journals')
        ->whereYear('transaction_date', $period->year)
        ->exists();

    if ($hasJournal) {
        return back()->with('error', 'Periode tidak bisa dihapus karena sudah ada transaksi');
    }

    DB::transaction(function () use ($period) {

        // hapus periode
        DB::table('accounting_periods')
            ->where('id', $period->id)
            ->delete();
    });

    return back()->with('success', 'Periode berhasil dihapus');
}
}
