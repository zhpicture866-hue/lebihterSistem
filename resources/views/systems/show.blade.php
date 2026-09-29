@extends('tablar::page')

@section('content')
<div class="container-xl" style="padding-top:80px">

    <div class="row align-items-center" style="padding-bottom:20px">
        <div class="col d-flex align-items-center">
            <a href="{{ route('contractors.index') }}" class="btn btn-dark d-flex align-items-center">
                <i class="ti ti-arrow-left"></i>
            </a>
                <h2 class="page-title">Detail Kontraktor</h2>
        </div>
    </div>
    {{-- <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">Detail </h2>
        <a href="{{ route('.index') }}" class="btn btn-outline-secondary">
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
                <a class="nav-link" data-bs-toggle="tab" href="#tab-loyalty" role="tab">
                    <i class="ti ti-map-pin"></i> Detail Usaha
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-employment" role="tab">
                    <i class="ti ti-briefcase"></i> Riwayat Pembayaran
                </a>
            </li>
        </ul>
    </div>
    <div class="tab-content">

        {{-- ================= TAB 1: PERSONAL ================= --}}
        <div class="tab-pane fade show active" id="tab-personal" role="tabpanel">

            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Informasi Personal</h3>
                    <a href="{{ route('contractors.edit', $contractor->id) }}" class="btn btn-outline-dark btn-sm" data-bs-toggle="tooltip" title="Ubah Detail">
                        <i class="ti ti-edit"></i>
                    </a>
                </div>

                <div class="card-body">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-3 text-center">
                            @if ($user->photo)
                            <img id="previewImage" src="{{ asset('storage/photos/'.$user->photo) }}" alt="Profile" 
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
                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-md">
                                    <div class="text-muted small">Nama Lengkap</div>
                                    <div class="fw-bold">{{ $user->fullname ?? '-' }}</div>
                                </div>
                                <div class="col-md">
                                    <div class="text-muted small">Email</div>
                                    <div class="fw-bold">{{ $user->email ?? '-' }}</div>
                                </div>
                                {{-- <div class="col-md-6">
                                    <div class="text-muted small mt-2">Kategori contractor</div>
                                    <div class="fw-bold">{{ $contractor->readable_category ?? '-' }}</div>
                                </div> --}}
                                <div class="col-md">
                                    <div class="text-muted small">Telepon</div>
                                    <div class="fw-bold">{{ $user->phone ?? '-' }}</div>
                                </div>
                                
                                {{-- <div class="col-md-4">
                                    <div class="text-muted small mt-2">Status</div>
                                    <span class="badge {{ $contractor->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $contractor->status_text }}
                                    </span>
                                </div> --}}
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
                            <div class="fw-bold">{{ $user->address ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Kelurahan</div>
                            <div class="fw-bold">{{ $user->subDistrict->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Kecamatan</div>
                            <div class="fw-bold">{{ $user->district->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Kabupaten/Kota</div>
                            <div class="fw-bold">{{ $user->city->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Provinsi</div>
                            <div class="fw-bold">{{ $user->province->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Kode Pos</div>
                            <div class="fw-bold">{{ $user->postalCode->postal_code ?? '-' }}</div>
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
                            <div class="fw-bold">{{ $user->bank->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Nomor Rekening</div>
                            <div class="fw-bold">{{ $user->account_number ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Atas Nama</div>
                            <div class="fw-bold">{{ $user->account_holder ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= TAB 2: LOYALTY ================= --}}
        {{-- <div class="tab-pane fade" id="tab-loyalty" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title mb-0">Membership / Loyalty Program</h3>
                </div>
                <div class="card-body text-center">
                    @php
                        $color = [
                            'silver' => 'secondary',
                            'gold' => 'warning',
                            'platinum' => 'info',
                        ][$contractor->loyalty_level] ?? 'secondary';
                    @endphp

                    <div class="mb-3">
                        <span class="avatar avatar-lg rounded-circle bg-{{ $color }}-lt">
                            <i class="ti ti-star text-{{ $color }}"></i>
                        </span>
                    </div>

                    {{-- <h3 class="fw-bold text-capitalize">{{ $contractor->readable_loyalty_level }}</h3>
                    <p class="text-muted">
                        Tingkatan membership menentukan keuntungan & promo khusus pelanggan.
                    </p> 
                </div>
            </div>
        </div> --}}

        {{-- ================= TAB 3: SHIPPING ================= --}}
        <div class="tab-pane fade" id="tab-loyalty" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title mb-0">Detail Usaha</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-muted small">Nama Usaha</div>
                            <div class="fw-bold">{{ $contractor->contractor_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Nomor HP Usaha</div>
                            <div class="fw-bold">{{ $contractor->contractor_phone ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Alamat Lengkap Usaha</div>
                            <div class="fw-bold">{{ $contractor->contractor_address ?? '-' }}</div>
                        </div>

                        <div class="col-md-3">
                            <div class="text-muted small">Kelurahan</div>
                            <div class="fw-bold">{{ $contractor->subDistrict->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Kecamatan</div>
                            <div class="fw-bold">{{ $contractor->district->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Kabupaten/Kota</div>
                            <div class="fw-bold">{{ $contractor->city->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Provinsi</div>
                            <div class="fw-bold">{{ $contractor->province->name ?? '-' }}</div>
                        </div>

                        <div class="col-md-3">
                            <div class="text-muted small">Kode Pos</div>
                            <div class="fw-bold">{{ $contractor->postalCode->postal_code ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Catatan</div>
                            <div class="fw-bold">{{ $contractor->notes ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="tab-employment" role="tabpanel">
            <div class="card">
                <div class="card-body text-center text-muted">
                    <em>Belum ada data riwayat pembayaran.</em>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('css')
    <style>
    .nav nav-tabs .nav-item .nav-link {
    align-items: center;
    gap: 10px;
    margin: 0 10px;
}

    .nav-link i {
    align-items: center;
    gap: 10px;
    margin: 0 10px;
}
</style>
@endpush
