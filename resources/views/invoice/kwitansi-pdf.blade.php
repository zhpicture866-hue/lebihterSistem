<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kwitansi Pembayaran Tahap {{ $invoice->termin }}</title>

<style>
@page {
    margin: 120px 30px 40px 30px;
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    line-height: 1.5;
}

/* HEADER */
.header {
    position: fixed;
    top: -100px;
    left: 0;
    right: 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    page-break-inside: avoid;
}
th, td {
    border: 1px solid #333;
    padding: 6px;
}
.no-border td {
    border: none;
    padding: 0px 0;
}

th {
    background: #000;
    color: #fff;
}

.text-right { text-align: right; }
.text-center { text-align: center; }
.bold { font-weight: bold; }
p {
    margin: 0 0 6px 0;
}

.lunas-stamp {
    display: inline-block;
    border: 3px solid #2fb344;
    color: #2fb344;
    font-weight: bold;
    font-size: 16px;
    padding: 4px 14px;
    transform: rotate(-8deg);
    margin-left: 10px;
}
</style>
</head>

<body>

{{-- HEADER (sama seperti invoice) --}}
<div class="header">
    <img src="{{ public_path('images/header-penawaran.png') }}" style="width:100%;">
</div>

<div style="height:20px;"></div>

<table width="100%" class="no-border" style="margin-top:15px;">
<tr>
    <!-- KIRI -->
    <td width="60%" valign="top">
        <table class="no-border">
            <tr><td>CP</td><td>: +62838 7424 3693</td></tr>
            <tr><td>Email</td><td>: zoelhapner@gmail.com</td></tr>
        </table>
    </td>

    <!-- KANAN -->
    <td width="40%" valign="top" align="right">
        <table class="no-border" align="right">
            <tr>
                <td style="padding-right:10px;">No. Kwitansi</td>
                <td><strong>{{ $number }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>{{ optional($invoice->approved_at)->format('d F Y') }}</td>
            </tr>
        </table>
    </td>
</tr>
</table>

<table width="100%" class="no-border" style="margin-top:15px;">
<tr>
    <!-- KIRI -->
    <td width="100%" valign="top">
        <p class="bold">
            Diterima Dari
            @if($isFinalPayment)
                <span class="lunas-stamp">LUNAS</span>
            @endif
        </p>
        <p>
            <strong>{{ optional($project->customer->user)->readable_title }} {{ $offer->contact_name }}</strong><br>
            {{ optional($project->customer->user)->address }}<br>
            Telp: {{ optional($project->customer->user)->phone }}
        </p>
    </td>
</tr>
</table>

<br>

{{-- TABEL --}}
<table>
<thead>
<tr>
    <th>Deskripsi</th>
    <th>%</th>
    <th>Total Harga Proyek (Rp)</th>
    <th>Total Diterima (Rp)</th>
</tr>
</thead>
@php
    $buildTermin = $project->buildTermins->firstWhere('termin_no', $invoice->termin);
    $terminLabel = $buildTermin->description ?? ('Pembayaran Termin ' . $invoice->termin);
@endphp
<tbody>
<tr>
    <td>
Pembayaran {{ $terminLabel }}
Proyek {{ $project->project_name }}
    </td>

    <td class="text-center">{{ $invoice->payment_percentage }}%</td>
    <td class="text-right">{{ number_format($grandTotal,0,',','.') }}</td>
    <td class="text-right">{{ number_format($invoice->amount,0,',','.') }}</td>
</tr>
</tbody>

<tfoot>
    @if(isset($total_price))
    <tr>
        <th colspan="3" class="text-right">SUBTOTAL</th>
        <th class="text-right">
            {{ number_format($offer->total_price,0,',','.') }}
        </th>
    </tr>
    @endif

    <tr>
        <th colspan="3" class="text-right bold">TOTAL PEMBAYARAN {{ $terminLabel }}
        ({{ $invoice->payment_percentage }}%)</th>
        <th class="text-right bold">
            {{ number_format($invoice->amount,0,',','.') }}
        </th>
    </tr>
</tfoot>
</table>

<br>
<p><strong>Terbilang :</strong><br>
{{ ucwords(terbilang($invoice->amount)) }} Rupiah
</p>

<p class="bold">Keterangan :</p>

<p>
Kwitansi ini merupakan bukti sah penerimaan pembayaran atas Invoice
No. <strong>{{ $invoice->invoice_number }}</strong>,
yang telah disetujui pada
{{ optional($invoice->approved_at)->format('d F Y, H:i') }} WIB.
</p>

<ul>
@foreach($project->invoicebuilds->sortBy('termin') as $inv)
    @php
        $buildTermin = $project->buildTermins->firstWhere('termin_no', $inv->termin);
    @endphp
<li>
Pembayaran {{ $buildTermin->description ?? ('Pembayaran Termin ' . $inv->termin) }}
sebesar {{ $inv->payment_percentage }}%
x Rp {{ number_format($grandTotal,0,',','.') }}
=
Rp {{ number_format($inv->amount,0,',','.') }}
@if($inv->status === 'approved')
    (Lunas
    @if($inv->bukti_pembayaran_uploaded_at)
        , {{ $inv->bukti_pembayaran_uploaded_at->format('d F Y') }}
    @endif
    )
@else
    (Belum Lunas)
@endif
</li>
@endforeach
</ul>

<div style="
    page-break-inside: avoid;
    page-break-before: avoid;
    margin-top:15px;
">

    <p>Hormat Kami,<br>
       ZH Picture
    </p>
    <div style="height:90px;">
        <img src="{{ public_path('images/ttd-zhpicture.png') }}"
             style="height:100px;">
    </div>
    <p>
        <strong><u>Achmad Zulkifli Nur Rochim, S.Psi.</u></strong><br>
        Direktur
    </p>

</div>
</body>
</html>

{{-- ?regenerate=1 --}}