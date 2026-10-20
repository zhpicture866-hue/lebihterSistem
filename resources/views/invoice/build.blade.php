<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $invoice->invoice_number }}</title>

<style>
@page {
    margin: 120px 30px 70px 30px;
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    line-height: 1.5;
    color: #111;
}

/* HEADER & FOOTER (full bleed) */
.header {
    position: fixed;
    top: -120px;
    left: -30px;
    right: -30px;
}
.header img {
    width: 100%;
}

.footer {
    position: fixed;
    bottom: -70px;
    left: -30px;
    right: -30px;
    height: 45px;
    background: #0d0d0d;
}

table {
    width: 100%;
    border-collapse: collapse;
}

p { margin: 0; }

.bold { font-weight: bold; }
.italic { font-style: italic; }
.text-right { text-align: right; }
.text-center { text-align: center; }

/* BLOK INFO (tanpa border) */
.info td {
    border: none;
    padding: 0;
    vertical-align: top;
}
.info .label {
    font-style: italic;
    width: 60px;
}
.info .sep {
    width: 12px;
}

/* TABEL ITEM */
.items {
    table-layout: fixed;
    width: 100%;
}
.items th {
    background: #000;
    color: #fff;
    font-size: 10px;
    text-align: center;
    padding: 9px 4px;
    line-height: 1.2;
    white-space: nowrap;
    border: none;
    border-right: 1px solid #fff;
}
.items th:last-child {
    border-right: none;
}
.items td {
    padding: 10px 6px;
    font-size: 10px;
    border: none;
    border-bottom: 1.5px solid #000;
    vertical-align: middle;
}
.items tr.total td {
    border-bottom: none;
    padding: 10px 6px;
}
.items td.nowrap {
    white-space: nowrap;
}
.items td.total-value {
    white-space: nowrap;
    background: #000;
    color: #fff;
    font-weight: bold;
    text-align: right;
}

.section-title {
    font-weight: bold;
    font-size: 11px;
    margin-bottom: 8px;
}

.notes {
    font-style: italic;
    font-size: 10px;
}
</style>
</head>

<body>

@php
    $buildTermin = $project->buildTermins->firstWhere('termin_no', $invoice->termin);
    $terminLabel = $buildTermin->description ?? ('Pembayaran Termin ' . $invoice->termin);
    $amount      = number_format($invoice->amount, 0, ',', '.');
@endphp

{{-- HEADER --}}
<div class="header">
    <img src="{{ public_path('images/header-penawaran.png') }}">
</div>

{{-- FOOTER --}}
<div class="footer">
    <img src="{{ public_path('images/footer-penawaran.png') }}">
</div>

{{-- INVOICE --}}
<div style="margin-top:20px;">
    <p class="bold" style="font-size:12px;">Invoice</p>
    <p>{{ $invoice->invoice_number }}</p>
    <p>{{ $invoice->invoice_date->translatedFormat('d F Y') }}</p>
</div>

{{-- INFORMASI DETAIL PROYEK & TAGIHAN --}}
<div style="margin-top:28px;">
    <p class="section-title">Informasi Detail Proyek &amp; Tagihan :</p>

    <table class="info" style="width:70%; margin-left:20px;">
        <tr>
            <td class="label">Project</td>
            <td class="sep">:</td>
            <td>{{ $project->project_name }}</td>
        </tr>
        <tr>
            <td class="label">Client</td>
            <td class="sep">:</td>
            <td>{{ optional($project->customer->user)->readable_title }} {{ $offer->contact_name }}</td>
        </tr>
        <tr><td colspan="3" style="height:8px;"></td></tr>
        <tr>
            <td class="label">Address</td>
            <td class="sep">:</td>
            <td>{{ optional($project->customer->user)->address }}</td>
        </tr>
        <tr>
            <td class="label">Phone</td>
            <td class="sep">:</td>
            <td>{{ optional($project->customer->user)->phone }}</td>
        </tr>
    </table>
</div>

{{-- TABEL ITEM --}}
<table class="items" style="margin-top:25px;">
    <thead>
        <tr>
            <th style="width:36%;">Nama Produk</th>
            <th style="width:8%;">Qty</th>
            <th style="width:8%;">Day</th>
            <th style="width:24%;">Harga Satuan</th>
            <th style="width:24%;">Total Harga</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Pembayaran {{ $terminLabel }} Proyek {{ $project->project_name }}</td>
            <td class="text-center">1</td>
            <td class="text-center">-</td>
            <td class="text-right nowrap">Rp. {{ $amount }}</td>
            <td class="text-right nowrap">Rp. {{ $amount }}</td>
        </tr>
        <tr class="total">
            <td colspan="4" class="text-right bold">Jumlah Total</td>
            <td class="total-value">Rp. {{ $amount }}</td>
        </tr>
    </tbody>
</table>

{{-- INFORMASI PEMBAYARAN --}}
<div style="margin-top:45px; page-break-inside:avoid;">
    <p class="section-title">Informasi Pembayaran</p>

    <table class="info">
        <tr>
            <td style="width:50%; padding-left:20px;">
                Bank Mandiri<br>
                <strong>141 001 378 428 5</strong><br>
                a.n. <strong>Achmad Zulkifli Nur Rochim</strong>
            </td>
            <td class="notes" style="width:50%;">
                Mohon untuk segera konfirmasi kepada kami, Apabila telah melakukan pembayaran.
                <br><br>
                * DP dan pembayaran yang telah dibayarkan tidak bisa dikembalikan dengan alasan apapun
            </td>
        </tr>
    </table>
</div>

{{-- TANDA TANGAN --}}
<div style="margin-top:60px; page-break-inside:avoid;">
    <p style="padding-left:20px;">lebihtersistem</p>
    <div style="height:95px;">
        <img src="{{ public_path('images/ttd-lebihtersistem.png') }}" style="height:100px;">
    </div>
    <p style="padding-left:20px;">
        Achmad Zulkifli Nur Rochim, S.Psi<br>
        Direktur
    </p>
</div>

</body>
</html>