<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Buku Besar</title>
    <style>
        body {
            font-family: Poppins, sans-serif;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        table th, table td {
            border: 1px solid #444;
            padding: 6px;
            word-wrap: break-word;
        }
        table th {
            background-color: #f5f5f5;
        }
        h3, p {
            text-align: center;
            margin-bottom: 10px;
        }

        table th:nth-child(1),
        table td:nth-child(1) { width: 80px; }   /* Tanggal */

        table th:nth-child(2),
        table td:nth-child(2) { width: 100px; }  /* Transaksi */

        table th:nth-child(3),
        table td:nth-child(3) { width: 40%; }    /* Deskripsi (fleksibel) */

        table th:nth-child(4),
        table td:nth-child(4) { width: 100px; text-align: right; } /* Debit */

        table th:nth-child(5),
        table td:nth-child(5) { width: 100px; text-align: right; } /* Kredit */

        table th:nth-child(6),
        table td:nth-child(6) { width: 120px; text-align: right; }
    </style>
</head>

<body>
    <div class="header">
        <h2 style="text-align:center;">Lebih Tersistem</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    @foreach($ledger as $accountId => $data)
        <h4>{{ $data['account']->account_code }} - {{ $data['account']->account_name }}</h4>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Transaksi</th>
                    <th>Deskripsi</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Kredit</th>
                    <th class="text-end">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @php $lastJournal = null; @endphp
                @foreach($data['rows'] as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row['transaction_date'])->format('d/m/Y') }}</td>
                        <td>
                            @if($lastJournal !== $row['journal_code'])
                                ({{ $row['journal_code'] }})
                                @php $lastJournal = $row['journal_code']; @endphp
                            @endif
                        </td>
                        <td>{{ $row['description'] }}</td>
                        <td align="right">Rp {{ number_format($row['debit'], 2, ',', '.') }}</td>
                        <td align="right">Rp {{ number_format($row['credit'], 2, ',', '.') }}</td>
                        <td align="right">Rp {{ number_format($row['balance'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
    @endforeach
</body>
</html>

