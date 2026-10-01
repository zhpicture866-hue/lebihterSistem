@php
    $inv = $project->invoicebuilds->where('termin', $termin->termin_no)->first();
    $isApproved = $inv && $inv->status === 'approved';
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-center gap-2">

    @if(! $inv || (! $inv->bukti_pembayaran && ! $isApproved))

        <span class="text-muted">Belum tersedia</span>

    @else

        @if($inv->bukti_pembayaran)
            <a href="{{ Storage::url($inv->bukti_pembayaran) }}"
               class="btn btn-outline-dark btn-sm"
               target="_blank"
               data-bs-tooltip="true"
               title="Lihat Bukti">
                <i class="ti ti-file-check"></i>
            </a>
        @endif
        @if($isApproved)
            <a href="{{ route('projects.invoice.build.kwitansi', [$project->id, $inv->id]) }}"
            class="btn btn-dark btn-sm"
            target="_blank"
            data-bs-toggle="tooltip"
            title="Download Kwitansi">
                <i class="ti ti-receipt"></i>
            </a>

            <button type="button"
                    class="btn btn-dark btn-sm btn-upload-bukti-pembayaran"
                    data-bs-toggle="modal"
                    data-bs-tooltip="true"
                    data-bs-target="#modal-bukti-pembayaran-{{ $inv->id }}"
                    title="{{ $inv->bukti_pembayaran ? 'Ganti Bukti' : 'Upload Bukti' }}">
                <i class="ti ti-upload"></i>
            </button>

            <div class="modal fade" id="modal-bukti-pembayaran-{{ $inv->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form action="{{ route('projects.invoice.build.bukti-pembayaran', [$project->id, $inv->id]) }}"
                        method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">
                                    Upload Bukti Pembayaran Termin {{ $termin->termin_no }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <input type="file"
                                    name="bukti_pembayaran"
                                    class="form-control"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    required>
                                <div class="form-hint mt-2 small text-muted">
                                    Format: PDF, JPG, PNG. Maks. 5MB.
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-dark">
                                    Simpan
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif

</div>