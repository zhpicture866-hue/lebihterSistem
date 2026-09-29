@php
    $type = [
        '1'     => 'Jasa Konstruksi',
        '2'     => 'Klinik',
        '3'     => 'Toko',
        '4'     => 'Event',
        '5'     => 'Company Profile',
    ];
    $status = [
        '1'     => 'Proses',
        '2'     => 'Revisi',
        '3'     => 'Butuh Persetujuan',
        '4'     => 'Selesai',
    ];
@endphp

@can('lihat data proyek')
@if(isset($project))
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header fw-bold">
        Informasi Proyek
    </div>
    <div class="card-body">

        <div class="row g-4">

            <div class="col-md-4">
                <label class="fw-semibold">Nama Proyek</label>
                <input type="text" class="form-control" value="{{ $project->project_name }}" readonly>
            </div>

            <div class="col-md-2">
                <label class="fw-semibold">Jenis Proyek</label>
                <input type="text" class="form-control" value="{{ $type[$project->project_type] }}" readonly>
            </div>

            <div class="col-md-3">
                <label class="fw-semibold">Tanggal Mulai Proyek</label>
                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') }}" readonly>
            </div>

            <div class="col-md-3">
                <label class="fw-semibold">Tanggal Akhir Proyek(Estimasi)</label>
                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') }}" readonly>
            </div>

            {{-- <div class="col-md-2">
                <label class="fw-semibold">Status Proyek</label>
                <input type="text" class="form-control" value="{{ $status[$project->project_status] }}" readonly>
            </div> --}}

            <div class="col-md-4">
                <label class="fw-semibold">Customer</label>
                <input type="text" class="form-control" value="{{ $project->customer_name }}" readonly>
            </div>

            <div class="col-md-4">
                <label class="fw-semibold">Karyawan</label>
                <input type="text" class="form-control" value="{{ $project->employee_name }}" readonly>
            </div>

            <div class="col-12 mt-3">
                <label class="fw-semibold">Alamat Lokasi</label>
                <textarea id="project_location" class="form-control" rows="3" readonly>{{ $project->project_location }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="fw-semibold">Provinsi</label>
                <input id="province" type="text" class="form-control" value="{{ $project?->province?->name ?? '-' }}" readonly>
            </div>
            <div class="col-md-6">
                <label class="fw-semibold">Kabupaten/Kota</label>
                <input id="city" type="text" class="form-control" value="{{ $project?->city?->name ?? '-' }}" readonly>
            </div>
            <div class="col-md-5">
                <label class="fw-semibold">Kecamatan</label>
                <input id="district" type="text" class="form-control" value="{{ $project?->district?->name ?? '-' }}" readonly>
            </div>
            <div class="col-md-5">
                <label class="fw-semibold">Kelurahan</label>
                <input id="sub_district" type="text" class="form-control" value="{{ $project?->subDistrict?->name ?? '-' }}" readonly>
            </div>
            <div class="col-md-2">
                <label class="fw-semibold">Kode Pos</label>
                <input id="postal_code" type="text" class="form-control" value="{{ $project?->postalCode?->postal_code ?? '-' }}" readonly>
            </div>
            @if($project->description)
            <div class="col-12 mt-3">
                <label class="fw-semibold">Ringkasan Kegiatan</label>
                <textarea id="description" class="form-control" rows="3" readonly>{{ $project->description }}</textarea>
            </div>
            @endif
        </div>
    </div>
    
</div>
@endif
@endcan