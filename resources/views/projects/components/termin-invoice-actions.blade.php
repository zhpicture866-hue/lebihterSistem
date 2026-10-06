@php
    $t = $termin->termin_no;

    $inv = $project->invoicebuilds->where('termin', $t)->first();

    $prevInv = $index > 0
        ? $project->invoicebuilds->where('termin', $termins[$index - 1]->termin_no)->first()
        : null;

    // Termin pertama selalu bisa di-download; berikutnya harus menunggu
    // invoice termin sebelumnya sudah di-download.
    $canDownload = $index == 0 || ($prevInv && $prevInv->downloaded_at);

    // Approve baru bisa dilakukan kalau invoice sudah di-download DAN
    // bukti pembayaran sudah diunggah.
    $canApprove = $inv
        && $inv->downloaded_at
        && $inv->bukti_pembayaran
        && ! $inv->approved_at
        && ($index == 0 || optional($prevInv)->approved_at);

    $canSeeApprove = auth()->user()->hasAnyRole(['Super-Admin', 'Manager Finance']);
@endphp

<div class="d-flex flex-column align-items-center gap-1">
    <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">

        @if($inv)
            <span class="badge rounded-circle d-inline-flex align-items-center justify-content-center
                @if($inv->status == 'approved') bg-success @else bg-warning @endif"
                style="width: 28px; height: 28px;"
                title="{{ $inv->status == 'approved' ? 'Approved' : 'Menunggu Approval' }}">
                <i class="ti {{ $inv->status == 'approved' ? 'ti-check' : 'ti-clock' }} text-white"></i>
            </span>
        @endif

        @if($canDownload)

            <a href="{{ route('projects.invoice.build', ['project' => $project->id, 'termin' => $t]) }}"
            data-invoice-download="{{ $t }}"
            data-invoice-downloaded="{{ $inv && $inv->downloaded_at ? 1 : 0 }}"
            class="btn btn-dark btn-sm"
            target="_blank"
            data-bs-toggle="tooltip"
            title="{{ $inv && $inv->downloaded_at ? 'Lihat Invoice' : 'Download Invoice Termin' }}">
                <i class="ti ti-download"></i>
            </a>

            {{-- Bukti Pembayaran: hanya tersedia setelah invoice di-download --}}
            @if($inv && $inv->downloaded_at)

                {{-- @if($inv->bukti_pembayaran)
                    <a href="{{ Storage::url($inv->bukti_pembayaran) }}"
                    class="btn btn-outline-dark btn-sm"
                    target="_blank"
                    title="Lihat Bukti Pembayaran">
                        <i class="ti ti-file-check"></i>
                    </a>
                @endif --}}

                @if(! $inv->approved_at)
                    <button type="button"
                            class="btn btn-secondary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-tooltip="true"
                            data-bs-target="#modal-bukti-pembayaran-{{ $inv->id }}"
                            title="{{ $inv->bukti_pembayaran ? 'Ganti Bukti Pembayaran' : 'Upload Bukti Pembayaran' }}">
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
                                            Upload Bukti Pembayaran Termin {{ $t }}
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

            @if($canApprove && $canSeeApprove)
                <form action="{{ route('projects.invoice.build.approve', [$project->id, $inv->id]) }}"
                    method="POST"
                    class="approve-form d-inline"
                    data-title="Approve Termin {{ $t }}?"
                    data-text="Invoice termin {{ $t }} akan disetujui.">
                    @csrf
                    <button class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Approve Termin {{ $t }}">
                        <i class="ti ti-check"></i>
                    </button>
                </form>
            @endif

        @else
            <span class="text-muted">Belum tersedia</span>
        @endif

    </div>
    @if($inv && $inv->approved_at)
        <div class="small text-muted text-center">
            Disetujui oleh <strong>{{ $inv->approve_by_name ?: '-' }}</strong>,
            <br>
            {{ $inv->approved_at->format('Y-m-d H:i:s') }}
        </div>
    @endif
</div>