<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lebih Tersistem</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* biar lebar otomatis */
        }

        table th, table td {
            border: 1px solid #444;
            padding: 5px;
            text-align: left;
            font-size: 9px;
            vertical-align: top;
            white-space: normal !important;   /* teks bisa turun ke bawah */
            word-break: break-word !important; /* pecah kata panjang */
            word-wrap: break-word !important;
            overflow: visible !important;
        }

        table th {
            background: #f2f2f2;
        }

        /* Set minimal lebar agar tidak hilang */
        table th:nth-child(1), table td:nth-child(1) { width: 65px; }   /* Tanggal */
        table th:nth-child(2), table td:nth-child(2) { width: 80px; }   /* No Jurnal */
        table th:nth-child(3), table td:nth-child(3) { width: 170px; }  /* Deskripsi */
        table th:nth-child(4), table td:nth-child(4) { width: 55px; }   /* No. Akun */
        table th:nth-child(5), table td:nth-child(5) { width: 120px; }  /* Nama Akun */
        table th:nth-child(6), table td:nth-child(6) { width: 85px; text-align: right; } /* Debit */
        table th:nth-child(7), table td:nth-child(7) { width: 85px; text-align: right; } /* Kredit */

        tfoot td {
            font-weight: bold;
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <h2>Laporan Jurnal umum</h2>

    <p><strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    {{-- <p><strong>Lisensi:</strong> {{ auth()->user()->license->name ?? '-' }}</p> --}}

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No Jurnal</th>
                <th>Deskripsi</th>
                <th>No. Akun</th>
                <th>Nama Akun</th>
                <th>Debit</th>
                <th>Kredit</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDebit = 0;
                $totalCredit = 0;
            @endphp
            @foreach($journals as $journal)
                @foreach($journal->details as $detail)
                    @php
                        $totalDebit += $detail->debit;
                        $totalCredit += $detail->credit;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($journal->transaction_date)->format('d/m/Y') }}</td>
                        <td>{{ $journal->journal_code }}</td>
                        <td>{{ $detail->description ?? '-' }}</td>
                        <td>{{ $detail->account->account_code ?? '-' }}</td>
                        <td>{{ $detail->account->account_name ?? '-' }}</td>
                        <td>Rp {{ number_format($detail->debit, 2, ',', '.') }}</td>
                        <td>Rp {{ number_format($detail->credit, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right;">Total</td>
                <td style="text-align: right;">Rp {{ number_format($totalDebit, 2, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totalCredit, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

