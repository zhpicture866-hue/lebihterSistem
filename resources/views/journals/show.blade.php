@extends('tablar::page')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('journals.index') }}" class="btn btn-dark d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>      
                    <h2 class="page-title mb-0">Jurnal</h2> 
            </div>
        </div>
    </div>
</div>


<div class="page-body">
    <div class="container-xl">
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @php
                            $ext = $journal->enclosure 
                                ? strtolower(pathinfo($journal->enclosure, PATHINFO_EXTENSION)) 
                                : null;
                        @endphp
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        {{-- HEADER INFO --}}
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h3 class="mb-0 fw-bold">Detail Jurnal</h3>
                                <div class="text-muted small">Informasi transaksi</div>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('journals.export', $journal->id) }}" class="btn btn-dark">
                                    <i class="ti ti-file-export"></i> Ekspor
                                </a>
                                <a href="{{ route('journals.print', $journal->id) }}" target="_blank" class="btn btn-outline-dark">
                                    <i class="ti ti-printer"></i> Cetak
                                </a>
                            </div>
                        </div>

                        {{-- INFO BOX --}}
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="text-muted small">No. Transaksi</div>
                                        <div class="fw-bold">{{ $journal->journal_code }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="text-muted small">Tanggal</div>
                                        <div class="fw-bold">
                                            {{ \Carbon\Carbon::parse($journal->transaction_date)->format('d M Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle jurnal-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode Akun</th>
                                        <th>Nama Akun</th>
                                        <th>Deskripsi</th>
                                        <th>User</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Kredit</th>
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
                                            <td title="{{ $detail->account->account_name ?? '-' }}">
                                                {{ Str::limit($detail->account->account_name ?? '-', 45) }}
                                            </td>

                                            <td title="{{ $detail->description ?? '-' }}">
                                                {{ Str::limit($detail->description ?? '-', 35) }}
                                            </td>
                                            <td>{{ $detail->person_name ?? '-' }}</td>
                                            <td class="text-end text-success">
                                                Rp {{ number_format($detail->debit, 0, ',', '.') }}
                                            </td>
                                            <td class="text-end text-danger">
                                                Rp {{ number_format($detail->credit, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                                <tfoot class="fw-bold border-top">
                                    <tr>
                                        <td colspan="4">Total</td>
                                        <td class="text-end text-success">
                                            Rp {{ number_format($totalDebit, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end text-danger">
                                            Rp {{ number_format($totalCredit, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Keterangan</label>
                            <div class="border rounded p-3 bg-light">
                                {{ $journal->description ?? '-' }}
                            </div>
                        </div>

                        {{-- LAMPIRAN (FULL WIDTH DI BAWAH) --}}
                        @if($journal->enclosures->isNotEmpty())
                            <div class="mb-4">
                                <label class="form-label fw-bold">Lampiran Jurnal</label>

                                <div class="row">
                                    @foreach($journal->enclosures as $enclosure)

                                        @php
                                            $ext = strtolower(pathinfo($enclosure->file_name, PATHINFO_EXTENSION));
                                        @endphp

                                        <div class="col-md-4 mb-3">
                                            <div class="card shadow-sm h-100">
                                                <div class="card-body text-center">

                                                    @if(in_array($ext, ['jpg','jpeg','png','gif','webp']))
                                                    <img src="{{ asset('storage/'.$enclosure->file_name) }}"
                                                        class="img-fluid rounded"
                                                        style="max-height:200px;cursor:pointer"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#previewModal"
                                                        data-type="image"
                                                        data-file="{{ asset('storage/'.$enclosure->file_name) }}">

                                                    @elseif($ext == 'pdf')

                                                        <i class="ti ti-file-type-pdf text-danger"
                                                            style="font-size:60px"></i>

                                                        <p class="mt-2 mb-2">
                                                            {{ basename($enclosure->file_name) }}
                                                        </p>

                                                        <button class="btn btn-primary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#previewModal"
                                                            data-type="pdf"
                                                            data-file="{{ asset('storage/'.$enclosure->file_name) }}">
                                                        Lihat PDF
                                                    </button>

                                                    @elseif(in_array($ext,['doc','docx']))

                                                        <i class="ti ti-file-type-doc text-primary"
                                                            style="font-size:60px"></i>

                                                        <p class="mt-2">
                                                            {{ basename($enclosure->file_name) }}
                                                        </p>

                                                        <a href="{{ asset('storage/'.$enclosure->file_name) }}"
                                                        target="_blank"
                                                        class="btn btn-primary btn-sm">
                                                            Download
                                                        </a>

                                                    @elseif(in_array($ext,['xls','xlsx']))

                                                        <i class="ti ti-file-type-xls text-success"
                                                            style="font-size:60px"></i>

                                                        <p class="mt-2">
                                                            {{ basename($enclosure->file_name) }}
                                                        </p>

                                                        <a href="{{ asset('storage/'.$enclosure->file_name) }}"
                                                        target="_blank"
                                                        class="btn btn-primary btn-sm">
                                                            Download
                                                        </a>

                                                    @else

                                                        <i class="ti ti-file"
                                                            style="font-size:60px"></i>

                                                        <p class="mt-2">
                                                            {{ basename($enclosure->file_name) }}
                                                        </p>

                                                        <a href="{{ asset('storage/'.$enclosure->file_name) }}"
                                                        target="_blank"
                                                        class="btn btn-secondary btn-sm">
                                                            Download
                                                        </a>

                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- ACTION --}}
                        <div class="d-flex gap-2">
                            <a href="{{ route('journals.edit', $journal->id) }}" class="btn btn-dark">
                                <i class="ti ti-pencil"></i> Ubah
                            </a>
                        </div>

                        {{-- FOOTER --}}
                        <div class="text-end text-muted small mt-4">
                            Terakhir diubah oleh 
                            <strong>{{ $journal->creator->fullname ?? 'Sistem' }}</strong> 
                            pada {{ \Carbon\Carbon::parse($journal->updated_at)->translatedFormat('d F Y H:i') }} GMT+7
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Preview Lampiran</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>

            <div class="modal-body text-center" id="previewContent">
                <!-- Konten preview akan dimasukkan lewat JavaScript -->
            </div>

        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const previewModal = document.getElementById('previewModal');
    const previewContent = document.getElementById('previewContent');

    previewModal.addEventListener('show.bs.modal', function (event) {

        const button = event.relatedTarget;

        const type = button.getAttribute('data-type');
        const file = button.getAttribute('data-file');

        previewContent.innerHTML = '';

        if (!file) {
            previewContent.innerHTML = `
                <div class="alert alert-danger">
                    File lampiran tidak ditemukan.
                </div>
            `;
            return;
        }

        if (type === 'image') {

            previewContent.innerHTML = `
                <img src="${file}"
                     class="img-fluid rounded"
                     style="max-height: 75vh;"
                     alt="Preview Lampiran">
            `;

        } else if (type === 'pdf') {

            previewContent.innerHTML = `
                <embed src="${file}"
                       type="application/pdf"
                       width="100%"
                       height="700px">
            `;

        } else {

            previewContent.innerHTML = `
                <div class="py-5">
                    <i class="ti ti-file"
                       style="font-size: 70px;"></i>

                    <p class="mt-3">
                        File ini tidak dapat dipreview.
                    </p>

                    <a href="${file}"
                       target="_blank"
                       class="btn btn-primary">
                        Buka File
                    </a>
                </div>
            `;
        }
    });

    previewModal.addEventListener('hidden.bs.modal', function () {
        previewContent.innerHTML = '';
    });

});
</script>
@endsection