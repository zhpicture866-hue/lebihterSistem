@extends('tablar::page')

@section('content')
<div class="container-xl" style="padding-top:80px">

    <div class="row align-items-center" style="padding-bottom:20px">
        <div class="col d-flex align-items-center">
            <a href="{{ route('employees.index') }}" class="btn btn-dark d-flex align-items-center">
                <i class="ti ti-arrow-left"></i>
            </a>
                <h2 class="page-title">Detail Karyawan</h2>
        </div>
    </div>
    {{-- <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">Detail Karyawan</h2>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left"></i> Kembali
        </a>
    </div> --}}
    <div class="tabs-mobile-wrapper">
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-personal" role="tab">
                    <i class="ti ti-user"></i> Detail Pribadi
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-hr" role="tab">
                    <i class="ti ti-id-badge"></i> Informasi Kepegawaian
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-employment" role="tab">
                    <i class="ti ti-briefcase"></i> Riwayat Penggajian
                </a>
            </li>
        </ul>
    </div>
    <div class="tab-content">

        {{-- ======================= TAB 1: PERSONAL DETAILS ======================= --}}
        <div class="tab-pane fade show active" id="tab-personal" role="tabpanel">
            
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Informasi Personal</h3>
                    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-outline-dark btn-sm" data-bs-toggle="tooltip" title="Ubah Detail">
                        <i class="ti ti-edit"></i>
                    </a>
                </div>

                <div class="card-body">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-3 text-center">
                            @if ($employee->user->photo)
                            <img id="previewImage" src="{{ asset('storage/'.$employee->user->photo) }}" alt="Profile" 
                                 class="rounded-3 shadow-sm border" width="150" height="150"
                                 style="object-fit: cover;">
                        @else
                            <div id="previewImage"
                                 class="rounded-3 shadow-sm bg-light d-flex align-items-center justify-content-center"
                                 style="width:150px; height:150px;">
                                 <i class="ti ti-user" style="font-size: 64px; color:#aaa;"></i>
                            </div>
                        @endif
                        </div>
                        <div class="col-md-9">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="text-muted small">Nama Lengkap</div>
                                    <div class="fw-bold">{{ $employee->user->fullname ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Email</div>
                                    <div class="fw-bold">{{ $employee->user->email ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Telepon</div>
                                    <div class="fw-bold">+{{ $employee->user->phone ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small mt-2">Tanggal Lahir</div>
                                    <div class="fw-bold">{{ $employee->user->birth_date_formatted ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small mt-2">Jenis Kelamin</div>
                                    <div class="fw-bold">{{ $employee->user->readable_gender ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small mt-2">Posisi / Jabatan</div>
                                    <div class="fw-bold">{{ $employee->user->roles->pluck('name')->implode(', ') ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section: Address --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title mb-0">Informasi Alamat</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="text-muted small">Alamat Lengkap</div>
                            <div class="fw-bold">{{ $employee->user->address ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Kelurahan</div>
                            <div class="fw-bold">{{ $employee->user->subDistrict->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Kecamatan</div>
                            <div class="fw-bold">{{ $employee->user->district->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Kabupaten/Kota</div>
                            <div class="fw-bold">{{ $employee->user->city->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Provinsi</div>
                            <div class="fw-bold">{{ $employee->user->province->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Kode Pos</div>
                            <div class="fw-bold">{{ $employee->user->postalCode->postal_code ?? '-' }}</div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Section: Bank Information (ganti HR section lama) --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title mb-0">Informasi Bank</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted small">Nama Bank</div>
                            <div class="fw-bold">{{ $employee->user->bank->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Nomor Rekening</div>
                            <div class="fw-bold">{{ $employee->user->account_number ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Atas Nama</div>
                            <div class="fw-bold">{{ $employee->user->account_holder ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div> {{-- end tab personal --}}

        {{-- ======================= TAB 2: HUMAN RESOURCE INFO ======================= --}}
        <div class="tab-pane fade" id="tab-hr" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title mb-0">Informasi Kepegawaian</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted small">NIK</div>
                            <div class="fw-bold">{{ $employee->nik }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Tanggal Gabung</div>
                            <div class="fw-bold">{{ $employee->start_date ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Status</div>
                            <div class="fw-bold">{{ $employee->employment_status ?? '-' }}</div>
                        </div>

                        <div class="col-md-4 mt-3">
                            <div class="text-muted small">Gaji Pokok</div>
                            <div class="fw-bold">Rp {{ number_format($employee->salary, 0, ',', '.') }}</div>
                        </div>

                        <div class="col-md-4 mt-3">
                            <div class="text-muted small">Tanggal Berakhir Kontrak</div>
                            <div class="fw-bold">{{ $employee->end_date ?? '-' }}</div>
                        </div>

                        <div class="col-md-4 mt-3">
                            <div class="text-muted small">Dokumen Kontrak Kerja</div>
                            @if ($employee->contract_letter_file)
                                <a href="{{ asset('storage/' . $employee->contract_letter_file) }}"
                                   target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                   <i class="ti ti-file-text"></i> Lihat Dokumen
                                </a>
                            @else
                                <div>-</div>
                            @endif
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-muted small">Foto KTP</div>

                            @if($employee->user->identity_photo)

                                @php
                                    $ext = strtolower(pathinfo($employee->user->identity_photo, PATHINFO_EXTENSION));
                                @endphp

                                @if(in_array($ext, ['jpg', 'jpeg', 'png']))
                                    <a href="{{ asset('storage/'.$employee->user->identity_photo) }}"
                                    target="_blank">
                                        <img src="{{ asset('storage/'.$employee->user->identity_photo) }}"
                                            class="img-fluid rounded border mt-1"
                                            style="max-height:180px; object-fit:cover;">
                                    </a>
                                @else
                                    <a href="{{ asset('storage/'.$employee->user->identity_photo) }}"
                                    target="_blank"
                                    class="btn btn-outline-danger mt-1">
                                        <i class="ti ti-file-type-pdf"></i>
                                        Lihat PDF KTP
                                    </a>
                                @endif

                            @else
                                <div>-</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================= TAB 3: EMPLOYMENT HISTORY ======================= --}}
        <div class="tab-pane fade" id="tab-employment" role="tabpanel">
            <div class="card">
                <div class="card-body text-center text-muted">
                    <em>Belum ada data riwayat pekerjaan.</em>
                </div>
            </div>
        </div>

    </div> {{-- tab-content end --}}
</div>
@endsection

@push('css')
    <style>
    .nav nav-tabs .nav-item .nav-link {
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
}

    .nav-link i {
    align-items: center;
    gap: 10px;
    margin: 0 10px;
}
</style>
@endpush