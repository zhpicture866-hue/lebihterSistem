@extends('tablar::page')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('project_types.index') }}" class="btn btn-dark d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>
                
                    <h2 class="page-title mb-0">Edit Jenis Proyek</h2>
                
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
                            <form action="{{ route('project_types.update', $projectType) }}" method="POST">
                                @csrf
                                @method('PUT')
                                @include('project_types._form')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
{{-- @extends('tablar::page')

@section('content')
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <!-- Page title actions -->
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                  
                        <a href=" {{ route("pieces.index") }} " class="btn btn-primary d-none d-sm-inline-block" >
                            Kembali
                        </a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <p class="text-center mb-4" style="font-size: 1.5rem; font-weight: 400; font-family: 'Poppins', sans-serif;">
                                Edit Data Keluarga
                            </p>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('pieces.update', $piece->id) }}" method="POST"  style="font-size: 1.5rem; font-weight: 400; font-family: 'Poppins', sans-serif;">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $piece->name) }}" required>
                                    </div>

                                    
                                </div>

                                {{-- <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jenis Kelamin</label>
                                        <select name="gender" class="form-select">
                                            <option value="">-- Pilih kelamin --</option>
                                            <option value="1" {{ $pieces->gender == 1 ? 'selected' : '' }}>Laki - Laki</option>
                                            <option value="2" {{ $pieces->gender == 2 ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                                <label>Tanggal Lahir *</label>
                                                <input type="date" name="birth_date" class="form-control" required
                                                    value="{{ old('birth_date', $piece->birth_date) }}"
                                                    pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Pekerjaan</label>
                                        <input type="text" name="job" class="form-control" value="{{ old('job', $pieces->job) }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Telepon Kantor</label>
                                        <input type="number" name="job_phone" class="form-control" value="{{ old('job_phone', $pieces->job_phone) }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                            <label for="last_education_level" class="form-label">Jenjang Pendidikan <code>*</code></label>
                                            <select name="last_education_level" class="form-select" required>
                                                <option value="">-- Pilih Jenjang --</option>
                                                <option value="SD" {{ $pieces->last_education_level == 'SD' ? 'selected' : '' }}>SD</option>
                                                <option value="SMP" {{ $pieces->last_education_level == 'SMP' ? 'selected' : '' }}>SMP</option>
                                                <option value="SMA" {{ $pieces->last_education_level == 'SMA' ? 'selected' : '' }}>SMA</option>
                                                <option value="D3" {{ $pieces->last_education_level == 'D3' ? 'selected' : '' }}>D3</option>
                                                <option value="S1" {{ $pieces->last_education_level == 'S1' ? 'selected' : '' }}>S1</option>
                                                <option value="S2" {{ $pieces->last_education_level == 'S2' ? 'selected' : '' }}>S2</option>
                                                <option value="S3" {{ $pieces->last_education_level == 'S3' ? 'selected' : '' }}>S3</option>
                                            </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                                <label class="form-label">Nama Sekolah</label>
                                                <input type="text" name="institution_name" class="form-control" value="{{ old('institution_name', $pieces->institution_name) }}">
                                    </div>
                                </div> 


                        <div class="d-flex justify-content-between">
                            <a href="{{ route('pieces.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                        </div>
                        </form>
 

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection --}}