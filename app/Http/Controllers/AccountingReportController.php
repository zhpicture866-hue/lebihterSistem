<?php

namespace App\Http\Controllers;

use App\Models\AccountingAccount;
use App\Models\License;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountingReportController extends Controller
{
public function incomeStatement(Request $request)
{
    $user = Auth::user();

    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->endOfMonth()->toDateString();

    $accounts = AccountingAccount::query()
        ->from('lebihtersistem.accounting_accounts as accounts')

        ->select(
            'accounts.id',
            'accounts.account_code',
            'accounts.account_name',
            'accounts.category',
            'accounts.sub_category',
            'accounts.is_parent',

            DB::raw("
                SUM(
                    CASE
                        WHEN accounts.category = 'PENDAPATAN'
                            THEN details.credit - details.debit
                        ELSE details.debit - details.credit
                    END
                ) as balance
            ")
        )

        ->leftJoin(
            'lebihtersistem.accounting_journal_details as details',
            'details.account_id',
            '=',
            'accounts.id'
        )

        ->leftJoin(
            'lebihtersistem.accounting_journals as journals',
            'journals.id',
            '=',
            'details.journal_id'
        )

        ->whereIn(
            'accounts.category',
            ['PENDAPATAN', 'BEBAN']
        )

        ->whereBetween(
            'journals.transaction_date',
            [$startDate, $endDate]
        )

        ->groupBy(
            'accounts.id',
            'accounts.account_code',
            'accounts.account_name',
            'accounts.category',
            'accounts.sub_category',
            'accounts.is_parent'
        )

        ->orderBy('accounts.account_code')

        ->get()

        // Skip akun induk
        ->reject(fn ($acc) => $acc->is_parent);

    // 🔹 Grouping by category & sub_category
    $grouped = $accounts
        ->groupBy('category')
        ->map(function ($catGroup) {

            return $catGroup
                ->groupBy('sub_category')
                ->map(function ($subGroup) {

                    return [
                        'accounts' => $subGroup
                            ->sortBy('account_code')
                            ->values(),

                        'subtotal' => $subGroup->sum('balance'),
                    ];
                });
        });

    $totalIncome = $grouped
        ->get('PENDAPATAN', collect())
        ->sum('subtotal');

    $totalExpense = $grouped
        ->get('BEBAN', collect())
        ->sum('subtotal');

    $netIncome = $totalIncome - $totalExpense;

    return view('reports.income_statement', compact(
        'startDate',
        'endDate',
        'grouped',
        'totalIncome',
        'totalExpense',
        'netIncome'
    ));
}

public function exportPdf(Request $request)
{
    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->endOfMonth()->toDateString();
    $activeLicenseId = $request->license_id ?? session('active_license_id');

    $query = DB::table('lebihtersistem.accounting_accounts as accounts')
        ->select(
            'accounts.account_code',
            'accounts.account_name',
            'accounts.category',
            'accounts.sub_category',
            DB::raw("
                SUM(
                    CASE
                        WHEN accounts.category = 'PENDAPATAN'
                            THEN details.credit - details.debit
                        ELSE details.debit - details.credit
                    END
                ) as balance
            ")
        )
        ->leftJoin(
            'lebihtersistem.accounting_journal_details as details',
            'details.account_id',
            '=',
            'accounts.id'
        )
        ->leftJoin(
            'lebihtersistem.accounting_journals as journals',
            'journals.id',
            '=',
            'details.journal_id'
        )
        ->whereIn('accounts.category', [
            'PENDAPATAN',
            'BEBAN'
        ])
        ->whereBetween(
            'journals.transaction_date',
            [$startDate, $endDate]
        );

    if ($activeLicenseId) {
        $query->where(
            'accounts.license_id',
            $activeLicenseId
        );
    }

    $accounts = $query
        ->groupBy(
            'accounts.account_code',
            'accounts.account_name',
            'accounts.category',
            'accounts.sub_category'
        )
        ->orderBy(
            'accounts.account_code',
            'asc'
        )
        ->get();

    $totalIncome = $accounts
        ->where('category', 'PENDAPATAN')
        ->sum('balance');

    $totalExpense = $accounts
        ->where('category', 'BEBAN')
        ->sum('balance');

    $netIncome = $totalIncome - $totalExpense;

    $pdf = Pdf::loadView(
        'reports.income_statement_pdf',
        [
            'accounts'      => $accounts,
            'startDate'     => $startDate,
            'endDate'       => $endDate,
            'totalIncome'   => $totalIncome,
            'totalExpense'  => $totalExpense,
            'netIncome'     => $netIncome,
        ]
    )->setPaper('a4', 'portrait');

    return $pdf->stream(
        "Laporan_Laba_Rugi_{$startDate}_sd_{$endDate}.pdf"
    );
}

public function exportExcel(Request $request)
{
    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->endOfMonth()->toDateString();
    $activeLicenseId = $request->license_id ?? session('active_license_id');

    $query = DB::table('lebihtersistem.accounting_accounts as accounts')
        ->select(
            'accounts.account_code',
            'accounts.account_name',
            'accounts.category',
            'accounts.sub_category',
            DB::raw("
                SUM(
                    CASE
                        WHEN accounts.category = 'PENDAPATAN'
                            THEN details.credit - details.debit
                        ELSE details.debit - details.credit
                    END
                ) as balance
            ")
        )
        ->leftJoin(
            'lebihtersistem.accounting_journal_details as details',
            'details.account_id',
            '=',
            'accounts.id'
        )
        ->leftJoin(
            'lebihtersistem.accounting_journals as journals',
            'journals.id',
            '=',
            'details.journal_id'
        )
        ->whereIn('accounts.category', [
            'PENDAPATAN',
            'BEBAN'
        ])
        ->whereBetween(
            'journals.transaction_date',
            [$startDate, $endDate]
        );

    if ($activeLicenseId) {
        $query->where(
            'accounts.license_id',
            $activeLicenseId
        );
    }

    $accounts = $query
        ->groupBy(
            'accounts.account_code',
            'accounts.account_name',
            'accounts.category',
            'accounts.sub_category'
        )
        ->orderBy(
            'accounts.account_code',
            'asc'
        )
        ->get();

    // 🔹 Buat Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Judul
    $sheet->setCellValue('A1', 'Laporan Laba Rugi');
    $sheet->setCellValue(
        'A2',
        "Periode: $startDate s/d $endDate"
    );

    // Header tabel
    $sheet->setCellValue('A4', 'Kode Akun');
    $sheet->setCellValue('B4', 'Nama Akun');
    $sheet->setCellValue('C4', 'Kategori');
    $sheet->setCellValue('D4', 'Sub Kategori');
    $sheet->setCellValue('E4', 'Saldo');

    // Isi data
    $row = 5;

    foreach ($accounts as $acc) {
        $sheet->setCellValue(
            "A$row",
            $acc->account_code
        );

        $sheet->setCellValue(
            "B$row",
            $acc->account_name
        );

        $sheet->setCellValue(
            "C$row",
            $acc->category
        );

        $sheet->setCellValue(
            "D$row",
            $acc->sub_category
        );

        $sheet->setCellValue(
            "E$row",
            $acc->balance
        );

        $row++;
    }

    // Auto-size kolom
    foreach (range('A', 'E') as $col) {
        $sheet
            ->getColumnDimension($col)
            ->setAutoSize(true);
    }

    // Export
    $fileName =
        "Laporan_Laba_Rugi_{$startDate}_sd_{$endDate}.xlsx";

    $writer = new Xlsx($spreadsheet);

    return new StreamedResponse(
        function () use ($writer) {
            $writer->save('php://output');
        },
        200,
        [
            'Content-Type' =>
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

            'Content-Disposition' =>
                "attachment;filename=\"$fileName\"",

            'Cache-Control' => 'max-age=0',
        ]
    );
}

}