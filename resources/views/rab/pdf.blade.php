<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $offer->offer_number }} {{ $project->projectType->name }} {{ $project->project_name }}</title>
    <style>
        @page { margin: 150px 0 90px 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #000; margin: 0; }

        .header { position: fixed; top: -150px; left: 0; right: 0; }
        .footer { position: fixed; bottom: -90px; left: 0; right: 0; }
        .header img, .footer img { width: 100%; display: block; }

        .content { padding: 20px 50px 0 50px; }
        .meta { line-height: 1.4; }
        .section-title { font-weight: bold; font-size: 11px; margin: 28px 0 10px 0; }

        table.info { width: 100%; border-collapse: collapse; margin-left: 25px; }
        table.info td { border: none; padding: 1px 0; vertical-align: top; }
        table.info td.label { width: 55px; font-style: italic; }
        table.info td.sep { width: 12px; }

        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items thead th { background: #000; color: #fff; padding: 5px 6px; font-size: 10px; text-align: center; border: none; }
        table.items td { padding: 6px; vertical-align: top; border: none; }
        table.items tr.item-row td { border-bottom: 1px solid #000; }
        table.items tr.empty-row td { border-bottom: 1px solid #000; height: 20px; }

        .desc p  { margin: 0 0 2px 0; padding: 0; }
        .desc ul, .desc ol { margin: 2px 0 4px 0; padding-left: 14px; }
        .desc-titled > p:first-child { font-weight: 400; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }

        table.summary { width: 100%; border-collapse: collapse; margin-top: 12px; border-top: 2px solid #000; }
        table.summary td { padding: 4px 6px; border-bottom: 1px solid #000; }
        table.summary td.lbl { text-align: right; font-weight: bold; width: 79%; }
        table.summary tr.total td.val { background: #000; color: #fff; font-weight: bold; }
        table.summary tr.total td.lbl { border-bottom: none; }

        .notes { margin-top: 18px; line-height: 1.5; }

        /* ==== Tambahan untuk Lampiran 2 ==== */
        .page-break { page-break-before: always; }

        table.payment { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.payment thead th { background: #000; color: #fff; padding: 6px; font-size: 10px; text-align: center; border: none; }
        table.payment td { padding: 8px 6px; vertical-align: top; border: none; }
        table.payment tr.payment-row td { border-bottom: 1px solid #000; }
        table.payment tr.empty-row td { border-bottom: 1px solid #000; height: 22px; }

        .keterangan { margin-top: 10px; }
        .keterangan ol { margin: 0; padding-left: 18px; line-height: 1.6; }

        .rekening { margin-top: 16px; }
        .rekening .bank-block { margin-bottom: 10px; }
        .rekening .bank-name { font-weight: bold; }
        .rekening .bank-number { font-weight: bold; font-size: 13px; }

        .closing { margin-top: 18px; line-height: 1.5; }

        .ttd { margin-top: 24px; }
        .ttd img.signature { height: 70px; margin: 6px 0; display: block; }
        .ttd .signer-name { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>

@php
    $rp = fn ($n) => 'Rp. ' . number_format((float) $n, 0, ',', '.');
    $diskon = (float) $offer->discount;

    // Fallback data kalau controller belum kirim $terms / $bankAccounts / payments relation
    $terms = $terms ?? [
        'Penawaran harga ini <strong>berlaku hingga 7 hari</strong> dari tanggal penawaran.',
        'Metode pembayaran akan dijelaskan pada bagian <strong>detail pembayaran.</strong>',
        'Harga belum termasuk pajak (apabila ada) dan Pajak ditanggung oleh pihak klien.',
        'Booking tanggal setelah proses pembayaran DP / pembayaran pertama.',
        'Mohon untuk segera konfirmasi kepada kami, Apabila telah melakukan pembayaran.',
        'Pastikan untuk melakukan pembayaran pada nomor rekening yang telah disebutkan.',
        'DP dan pembayaran yang telah dibayarkan tidak bisa dikembalikan dengan alasan apapun.',
        'ZH Picture berhak atas dokumentasi untuk kebutuhan sosial media maupun promosi.',
        'ZH Picture berusaha semaksimal mungkin untuk menjaga privasi hasil dokumentasi terutama akhwat.',
        'Proses editing kurang lebih 14 – 28 hari kerja setelah hari H proses syuting selesai.',
    ];

    $bankAccounts = $bankAccounts ?? [
        ['bank' => 'Bank Mandiri', 'number' => '141 001 378 428 5', 'holder' => 'Achmad Zulkifli Nur Rochim'],
        ['bank' => 'Bank BTN', 'number' => '00113 01 50 004049 4', 'holder' => 'Achmad Zulkifli Nur Rochim'],
    ];

    // Ambil dari relasi payments kalau ada, kalau tidak fallback 1 baris = grand_total
    $payments = $payments ?? $project->buildTermins()
        ->orderBy('termin_no')
        ->get();
@endphp

<div class="header">
    <img src="{{ public_path('images/header-penawaran.png') }}">
</div>
<div class="footer">
    <img src="{{ public_path('images/footer-penawaran.png') }}">
</div>

{{-- ======================= LAMPIRAN 1 ======================= --}}
<div class="content">

    <div class="meta">
        No. {{ $offer->offer_number ?? '-' }}<br>
        {{ $offer->offer_date ? \Carbon\Carbon::parse($offer->offer_date)->translatedFormat('d F Y') : '-' }}<br>
        Lampiran 1<br>
        Penawaran Harga
    </div>

    <div class="section-title">Detail Proyek</div>
    <table class="info">
        <tr><td class="label">Project</td><td class="sep">:</td><td>{{ $project->project_name }}</td></tr>
        <tr><td class="label">Client</td><td class="sep">:</td><td>{{ $project->customer->user->readable_title ?? '-' }} {{ $offer->contact_name }}</td></tr>
        <tr><td class="label">Address</td><td class="sep">:</td><td>{{ $project->customer->user->address ?? '-' }}</td></tr>
        <tr><td class="label">Phone</td><td class="sep">:</td><td>{{ $project->customer->user->phone ?? '-' }}</td></tr>
        <tr><td class="label">Event Date</td><td class="sep">:</td><td>{{ $project->start_date->translatedFormat('d F Y') }}</td></tr>
        <tr><td class="label">Location</td><td class="sep">:</td><td>{{ $project->project_location ?? '-' }}</td></tr>
    </table>

    <div class="section-title">Detail Penawaran</div>
    <table class="items">
        <thead>
            <tr>
                <th style="width:50%">Nama Produk</th>
                <th style="width:9%">Periode</th>
                <th style="width:10%">Qty</th>
                <th style="width:11%">Harga</th>
                <th style="width:20%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($offer->items as $item)
                <tr class="item-row">
                    <td>
                        @if($item->category_name)
                            <div class="bold">
                                {{ $item->category_name }}
                                @if($item->floor_name) ({{ $item->floor_name }}) @endif
                            </div>
                        @endif

                        @if($item->description)
                            @php
                                $desc = strip_tags($item->description, '<p><br><ul><ol><li><strong><b><em><i><u>');
                            @endphp
                            <div class="desc {{ $item->category_name ? '' : 'desc-titled' }}">
                                {!! $desc !!}
                            </div>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->billing_period_label }}</td>
                    <td class="text-center">{{ rtrim(rtrim(number_format($item->volume, 5, '.', ''), '0'), '.') }}</td>
                    <td class="text-right">{{ $rp($item->price) }}</td>
                    <td class="text-right">{{ $rp($item->total) }}</td>
                </tr>
            @endforeach

            @for($i = $offer->items->count(); $i < 4; $i++)
                <tr class="empty-row"><td colspan="5">&nbsp;</td></tr>
            @endfor
        </tbody>
    </table>
    @php
        $rounded = floor((float) $offer->grand_total / 100000) * 100000;
    @endphp

    <table class="summary">
        <tr>
            <td class="lbl">SUBTOTAL</td>
            <td class="text-right">{{ $rp($offer->subtotal) }}</td>
        </tr>
        <tr>
            <td class="lbl">DISCOUNT</td>
            <td class="text-right">- {{ $rp($offer->discount) }}</td>
        </tr>
        <tr>
            <td class="lbl">SUBTOTAL AFTER DISCOUNT</td>
            <td class="text-right">{{ $rp($offer->subtotal_after_discount) }}</td>
        </tr>
        <tr>
            <td class="lbl">TAX RATE</td>
            <td class="text-right">{{ rtrim(rtrim(number_format($offer->tax_rate, 2, ',', '.'), '0'), ',') }}%</td>
        </tr>
        <tr>
            <td class="lbl">TOTAL TAX</td>
            <td class="text-right">{{ $rp($offer->tax_total) }}</td>
        </tr>
        <tr>
            <td class="lbl">SHIPPING / HANDLING</td>
            <td class="text-right">{{ $rp($offer->shipping) }}</td>
        </tr>
        <tr>
            <td class="lbl">GRAND TOTAL</td>
            <td class="text-right bold">{{ $rp($offer->grand_total) }}</td>
        </tr>
        <tr class="total">
            <td class="lbl">DIBULATKAN</td>
            <td class="val text-right">{{ $rp($rounded) }}</td>
        </tr>
    </table>

</div>

{{-- ======================= LAMPIRAN 2 ======================= --}}
<div class="content page-break">

    <div class="meta">
        No. {{ $offer->offer_number ?? '-' }}<br>
        {{ $offer->offer_date ? \Carbon\Carbon::parse($offer->offer_date)->translatedFormat('d F Y') : '-' }}<br>
        Lampiran 2<br>
        Penawaran Harga
    </div>
    <div class="section-title">Detail Pembayaran</div>

    <table class="payment">
        <thead>
            <tr>
                {{-- <th style="width:12%">Termin</th> --}}
                <th style="width:28%">Pembayaran</th>
                <th style="width:20%">Nominal</th>
                <th style="width:25%">Waktu</th>
                <th style="width:15%">Ket</th>
            </tr>
        </thead>

        <tbody>
            @forelse($payments as $payment)
                <tr class="payment-row">
                    {{-- <td class="text-center">
                        Termin {{ $payment->termin_no }}
                    </td> --}}

                    <td>
                        {{ $payment->description ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ $rp($payment->amount) }}
                    </td>

                    <td class="text-center">
                        {{ $payment->billing_date
                            ? \Carbon\Carbon::parse($payment->billing_date)->translatedFormat('d F Y')
                            : '-'
                        }}
                    </td>

                    <td class="text-center">
                        {{ $payment->percentage }}%
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">
                        Belum ada pengaturan termin pembayaran.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{-- <div class="section-title">Detail Pembayaran</div>
    <table class="payment">
        <thead>
            <tr>
                <th style="width:22%">Pembayaran</th>
                <th style="width:22%">Nominal</th>
                <th style="width:41%">Waktu</th>
                <th style="width:15%">Ket</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr class="payment-row">
                    <td>{{ $payment->label }}</td>
                    <td class="text-center">{{ $rp($payment->nominal) }}</td>
                    <td class="text-center">{{ $payment->waktu }}</td>
                    <td class="text-center">{{ $payment->ket ?? '' }}</td>
                </tr>
            @endforeach

            @for($i = count($payments); $i < 3; $i++)
                <tr class="empty-row"><td colspan="4">&nbsp;</td></tr>
            @endfor
        </tbody>
    </table> --}}

    <div class="section-title">Keterangan</div>
    <div class="keterangan">
        <ol>
            @foreach($terms as $term)
                <li>{!! $term !!}</li>
            @endforeach
        </ol>
    </div>

    <div class="rekening">
        <span class="bold">Pilihan Nomor Rekening :</span>
        @foreach($bankAccounts as $acc)
            <div class="bank-block">
                <div class="bank-name">{{ $acc['bank'] }}</div>
                <div class="bank-number">{{ $acc['number'] }}</div>
                <div>a.n. <span class="bold">{{ $acc['holder'] }}</span></div>
            </div>
        @endforeach
    </div>

    <div class="closing">
        Demikian penawaran harga kami sampaikan, besar harapan kami, Bapak/Ibu berminat dengan harga yang kami
        tawarkan. Atas perhatian Bapak/Ibu, kami ucapkan terima kasih.
    </div>

    <div class="ttd">
        Hormat kami,<br>
        ZH Picture<br>

        <img class="signature" src="{{ public_path('images/ttd-zhpicture.png') }}">

        <div class="signer-name">{{ $company->director_name ?? 'Achmad Zulkifli Nur Rochim, S.Psi.' }}</div>
        <div>{{ $company->director_title ?? 'Direktur' }}</div>
    </div>

</div>

</body>
</html>