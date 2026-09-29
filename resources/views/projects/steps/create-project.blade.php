@can('lihat daftar proyek')
<form action="{{ route('projects.store') }}" method="POST">
    @csrf
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-3">
        <small class="text-danger fw-semibold">
            * : Wajib diisi
        </small>
    </div>
    {{-- Buat Proyek Baru --}}
    <div class="section-block mb-5"> 
        <h3 class="fw-semibold mb-3 border-bottom pb-2">Informasi Proyek</h3>
        <div class="row g-4">
            <div class="col-md-4">
                <label class="form-label required">Nama Proyek</label>
                <input type="text" name="project_name" placeholder="Sesuaikan dengan jenis proyek" class="form-control @error('project_name') is-invalid @enderror"  value="{{ old('project_name') }}" required>
                @error('project_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-2">
                <label class="form-label required">Jenis Proyek</label>
                <select name="project_type"
                        class="form-select select2 @error('project_type') is-invalid @enderror"
                        required>
                    <option value="">-- Pilih --</option>
                    @foreach($projectTypes as $type)
                        <option value="{{ $type->id }}"
                            {{ old('project_type', $project->project_type ?? '') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
                @error('project_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

<div class="col-md-3">
    <label class="form-label required">Tanggal Mulai Proyek</label>

    <input
        type="text"
        name="start_date"
        id="start_date"
        class="form-control"
        required
        value="{{ old('start_date') }}"
        placeholder="DD-MM-YYYY"
    >
</div>

<div class="col-md-3">
    <label class="form-label">Tanggal Akhir Proyek (Estimasi)</label>

    <input
        type="text"
        name="end_date"
        id="end_date"
        class="form-control"
        value="{{ old('end_date') }}"
        placeholder="DD-MM-YYYY"
    >
</div>

            <div class="col-md-4">
                <label class="form-label required">Customer</label>
                <select name="customer_id" 
                        class="form-select select2 @error('customer_id') is-invalid @enderror" 
                        required>
                    <option value="">-- Pilih Customer --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" 
                            {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->display_name }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label required">Karyawan</label>
                <select name="employee_id" 
                        class="form-select select2 @error('employee_id') is-invalid @enderror" 
                        required>
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" 
                            {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->display_name }}
                        </option>
                    @endforeach
                </select>
                @error('employee_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Affiliator (Opsional)</label>
                <select name="affiliator_id" 
                        class="form-select select2 @error('affiliator_id') is-invalid @enderror">
                    <option value="">-- Pilih Affiliator --</option>
                    @foreach($affiliators as $affiliator)
                        <option value="{{ $affiliator->id }}" 
                            {{ old('affiliator_id') == $affiliator->id ? 'selected' : '' }}>
                            {{ $affiliator->display_name }}
                        </option>
                    @endforeach
                </select>
                @error('affiliator_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Base URL</label>
                                                <input
                                    type="url"
                                    name="base_url"
                                    id="base_url"
                                    class="form-control @error('base_url') is-invalid @enderror"
                                    value="{{ old('base_url') }}"
                                    placeholder="https://example.com"
                                    maxlength="255"
                                >
                @error('project_location')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>         
    </div>

    <div class="section-block mb-5">
        <h3 class="fw-semibold mb-3 border-bottom pb-2">Lokasi Proyek</h3>
        <div class="row g-4">
            <div class="col-12">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="project_location" rows="3" class="form-control @error('project_location') is-invalid @enderror">{{ old('project_location') }} </textarea>
                @error('project_location')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="row g-4 mt-2">
            <div class="col-md-6">
                <label class="form-label">Provinsi</label>
                <select name="province_id" id="province" 
                        class="form-select select2 @error('province_id') is-invalid @enderror">
                    <option value="">-- Pilih Provinsi --</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                            {{ $province->name }}
                        </option>
                    @endforeach
                </select>
                @error('province_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Kabupaten/Kota</label>
                <select name="city_id" id="city" 
                        class="form-select select2 @error('city_id') is-invalid @enderror">
                    <option value="">-- Pilih Kota --</option>
                </select>
                @error('city_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-5">
                <label class="form-label">Kecamatan</label>
                <select name="district_id" id="district" 
                        class="form-select select2 @error('district_id') is-invalid @enderror">
                    <option value="">-- Pilih Kecamatan --</option>
                </select>
                @error('district_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-5">
                <label class="form-label">Kelurahan</label>
                <select name="sub_district_id" id="sub_district" 
                        class="form-select select2 @error('sub_district_id') is-invalid @enderror">
                    <option value="">-- Pilih Kelurahan --</option>
                </select>
                @error('sub_district_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-2">
                <label class="form-label">Kode Pos</label>
                <select name="postal_code_id" id="postal_code" 
                        class="form-select select2 @error('postal_code_id') is-invalid @enderror">
                    <option value="">-- Pilih Kode Pos --</option>
                </select>
                @error('postal_code_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </div>
    <div class="text-end mt-5">
        <button type="submit" class="btn btn-dark px-4">
            <i class="ti ti-device-floppy me-1"></i> Buat Proyek baru
        </button>
    </div>
</form>
@endcan

@push('js')

<script>
document.addEventListener('DOMContentLoaded', function () {

    flatpickr('#start_date', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd-m-Y',
    });

    flatpickr('#end_date', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd-m-Y',
    });

});
</script>
<script>
$(function () {
    initLocationCascade({
        prefix: '',
        oldProvince: "{{ old('province_id') }}",
        oldCity: "{{ old('city_id') }}",
        oldDistrict: "{{ old('district_id') }}",
        oldSub: "{{ old('sub_district_id') }}",
        oldPostal: "{{ old('postal_code_id') }}"
    });
});
</script>
@endpush