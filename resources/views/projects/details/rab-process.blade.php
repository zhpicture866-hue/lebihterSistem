@php
$rab = $project->rab()->with('items')->first();
    $canEdit = auth()->user()->can('lihat daftar proyek');
    $ReadOnly = !$canEdit;
@endphp

@can('lihat data proyek')
@if($rab)
<div class="card shadow-sm border-0 mb-4">

    <div class="card-body">

        <div class="row g-4">
            <div class="col-md-4">
                <label class="fw-semibold">Nomor Penawaran</label>
                <input type="text" class="form-control" readonly
                       value="{{ $rab->offer_number }}">
            </div>
            <div class="col-md-4">
                <label class="fw-semibold">Tanggal Penawaran</label>
                <input type="text" class="form-control" readonly
                    value="{{ $rab->offer_date?->format('d/m/Y') ?? '-' }}">
            </div>
            <div class="col-md-4">
                <label class="fw-semibold">Nama Customer</label>
                <input type="text" class="form-control" readonly
                       value="{{ $rab->contact_name }}">
            </div>
        </div>

        <h5 class="fw-bold mt-5 mb-3">Rincian Pekerjaan</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle" style="width: 100%;">
                    <thead>
                        <tr>
                            <th width="5%" style="text-align: center;">NO</th>
                            <th width="45%" style="text-align: center;">NAMA PRODUK</th>
                            <th width="10%" style="text-align: center;">PERIODE</th>
                            <th width="5%" style="text-align: center;">QTY</th>
                            <th width="17.5%" style="text-align: center;">HARGA</th>
                            <th width="17.5%" style="text-align: center;">JUMLAH</th>
                        </tr>
                    </thead>

                    <tbody>

                        @php
                            $items = $rab->items->sortBy('order_no')->values();
                        @endphp

                        @foreach($items as $item)

                            <tr>
                                <td class="text-center">
                                    {{ $loop->iteration }}
                                </td>
                                <td>
                                    {!! function_exists('clean')
                                        ? clean($item->description)
                                        : strip_tags((string) $item->description, '<p><br><strong><em><u><ol><ul><li>') !!}
                                </td>
                                <td class="text-center">
                                    {{ $item->billing_period_label }}
                                </td>
                                <td class="text-center">
                                    {{ rtrim(rtrim(number_format($item->volume, 5, '.', ''), '0'), '.') }}
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format($item->price, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format($item->total, 2, ',', '.') }}
                                </td>
                            </tr>

                        @endforeach

                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">SUBTOTAL</th>
                            <th class="text-end">Rp {{ number_format($rab->subtotal, 2, ',', '.') }}</th>
                        </tr>

                        <tr>
                            <th colspan="5" class="text-end">DISCOUNT</th>
                            <th class="text-end">Rp {{ number_format($rab->discount, 2, ',', '.') }}</th>
                        </tr>

                        <tr>
                            <th colspan="5" class="text-end">SUBTOTAL AFTER DISCOUNT</th>
                            <th class="text-end">Rp {{ number_format($rab->subtotal_after_discount, 2, ',', '.') }}</th>
                        </tr>

                        <tr>
                            <th colspan="5" class="text-end">TAX RATE</th>
                            <th class="text-end">{{ $rab->tax_rate }}%</th>
                        </tr>

                        <tr>
                            <th colspan="5" class="text-end">TOTAL TAX</th>
                            <th class="text-end">Rp {{ number_format($rab->tax_total, 2, ',', '.') }}</th>
                        </tr>

                        <tr>
                            <th colspan="5" class="text-end">SHIPPING / HANDLING</th>
                            <th class="text-end">Rp {{ number_format($rab->shipping, 2, ',', '.') }}</th>
                        </tr>

                        <tr>
                            <th colspan="5" class="text-end fw-bold">GRAND TOTAL</th>
                            <th class="text-end fw-bold">
                                Rp {{ number_format($rab->grand_total, 2, ',', '.') }}
                            </th>
                        </tr>
                        @if($rab->grand_total >= 100000)
                        <tr>
                            <th colspan="5" class="text-end fw-bold">DIBULATKAN</th>
                            <th class="text-end fw-bold">
                                Rp {{ number_format(floor($rab->grand_total / 100000) * 100000, 0, ',', '.') }}
                            </th>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>

        @if($rab->notes)
            <div class="mt-4">
                <h5 class="fw-bold">Keterangan</h5>
                <div class="border p-3" style="white-space: pre-line;">{{ $rab->notes }}</div>
            </div>
        @endif
        <div class="d-flex align-items-center gap-2 mt-4">
            @if($rab->id)
                
            <a href="{{ route('projects.rab.pdf', $project->id) }}"
                class="btn btn-dark"
                target="_blank"
                title="Download PDF">
                    <i class="ti ti-download"></i>Download PDF
            </a>
                
            @endif
        </div>
        @if(!$ReadOnly)
            <div class="card mt-3">
                <div class="card-body text-muted small">
                    <div>Dibuat oleh: {{ $rab->creator?->fullname ?? '-' }}</div>
                    <div>Dibuat pada: {{ $rab->created_at?->format('d M Y H:i') }}</div>
                    <div>Terakhir diubah: {{ $rab->updated_at?->format('d M Y H:i') }}</div>
                    <div>Diubah oleh: {{ $rab->editor?->fullname ?? '-' }}</div>
                </div>
            </div>
        @endif
    </div>
</div>
@endif
@endcan