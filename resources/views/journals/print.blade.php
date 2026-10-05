<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lebih Tersistem</title>
    <style>
        body { font-family: Poppins, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
 
        table th, table td { 
            border: 1px solid #444; 
            padding: 6px; 
            word-wrap: break-word; 
            font-size: 10px; 
            white-space: normal; 
            vertical-align: top;
        }

        table th { background-color: #f5f5f5; }
        h2 { text-align: center; }
        
        table th:nth-child(1),
        table td:nth-child(1) { width: 10%; } 

        table th:nth-child(2),
        table td:nth-child(2) { width: 20%; }

        table th:nth-child(3),
        table td:nth-child(3) { width: 30%; }  

        table th:nth-child(4),
        table td:nth-child(4) { width: 15%; }

        table th:nth-child(5),
        table td:nth-child(5) { width: 12.5%; text-align: right; } /* Debit */

        table th:nth-child(6),
        table td:nth-child(6) { width: 12.5%; text-align: right; } /* Kredit */
    </style>
</head>
<body>
    <h2>Jurnal Transaksi</h2>

    <p><strong>No. Transaksi:</strong> {{ $journal->journal_code }}</p>
    <p><strong>Tanggal Transaksi:</strong> {{ \Carbon\Carbon::parse($journal->transaction_date)->format('d/m/Y') }}</p>
    {{-- <p><strong>Lisensi:</strong> {{ $journal->license->name ?? '-' }}</p> --}}

    <table>
        <thead>
            <tr>
                <th>No. Akun</th>
                <th>Nama Akun</th>
                <th>Deskripsi</th>
                <th>User</th>
                <th>Debit</th>
                <th>Kredit</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDebit = 0;
                $totalCredit = 0;
            @endphp
            @foreach($journal->details as $detail)
                @php
                    $totalDebit += $detail->debit;
                    $totalCredit += $detail->credit;
                @endphp
                <tr>
                    <td>{{ $detail->account->account_code ?? '-' }}</td>
                    <td>{{ $detail->account->account_name ?? '-' }}</td>
                    <td>{{ $detail->description ?? '-' }}</td>
                    <td>{{ $detail->person_name ?? '-' }}</td>
                    <td>Rp. {{ number_format($detail->debit, 2, ',', '.') }}</td>
                    <td>Rp. {{ number_format($detail->credit, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"><strong>Total</strong></td>
                <td><strong>Rp. {{ number_format($totalDebit, 2, ',', '.') }}</strong></td>
                <td><strong>Rp. {{ number_format($totalCredit, 2, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <p><strong>Keterangan:</strong> {{ $journal->description }}</p>
</body>
</html>
