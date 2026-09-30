<div class=n"card shadow-sm border-0 mb-4">
    <div class="card-body px-5 py-4">
    <h3 class="mb-4 fw-bold">Edit Data Proyek</h3>
        <form id="project-edit-form"
            action="{{ route('projects.update', $project->id) }}"
            method="POST">

            @csrf
            @method('PUT')

            <div class="row g-4">

                <div class="col-md-4">
                    <label class="form-label">Nama Proyek</label>
                    <input type="text" name="project_name" class="form-control"
                        value="{{ $project->project_name }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label required">Jenis Proyek</label>
                    @php
                        // Sudah punya level = sudah pernah generateLevels() -> tipe tidak boleh diubah lagi
                        $isLocked = $project && $project->levels->isNotEmpty();
                    @endphp
                    <select name="project_type"
                            class="form-select select2 @error('project_type') is-invalid @enderror"
                            @disabled($isLocked)
                            required>
                        <option value="">-- Pilih --</option>
                        @foreach($projectTypes as $type)
                            <option value="{{ $type->id }}"
                                {{ old('project_type', $project?->project_type ?? '') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($isLocked)
                        {{-- disabled select tidak ikut ter-submit, jadi kirim value asli lewat hidden input --}}
                        <input type="hidden" name="project_type" value="{{ $project->project_type }}">
                        {{-- <small class="text-muted">Jenis proyek tidak bisa diubah setelah proyek dibuat.</small> --}}
                    @endif
                    @error('project_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label required">Tanggal Mulai Proyek</label>

                    <input
                        type="text"
                        name="start_date"
                        id="start_date_edit"
                        class="form-control"
                        required
                        value="{{ old('start_date', $project->start_date) }}"
                        placeholder="DD-MM-YYYY"
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tanggal Akhir Proyek (Estimasi)</label>

                    <input
                        type="text"
                        name="end_date"
                        id="end_date_edit"
                        class="form-control"
                        value="{{ old('end_date', $project->end_date) }}"
                        placeholder="DD-MM-YYYY"
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Customer</label>
                    <input type="text" class="form-control"
                        value="{{ $project->customer->display_name }}" readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Karyawan</label>
                    <input type="text" class="form-control"
                        value="{{ $project->employee->display_name }}" readonly>
                </div>
                <div class="col-12">
                    <label class="form-label">Base URL</label>
                    <input
                        type="url"
                        name="base_url"
                        id="base_url"
                        class="form-control @error('base_url') is-invalid @enderror"
                        value="{{ old('base_url', $project->base_url) }}"
                        placeholder="https://example.com"
                        maxlength="255"
                    >
                    @error('project_location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 mt-3">
                    <label class="form-label">Alamat Lokasi</label>
                    <textarea name="project_location" class="form-control" rows="3">{{ $project->project_location }}</textarea>
                </div>

                <div class="row mb-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label required">Provinsi</label>
                        <select id="edit_province" name="province_id" class="form-select select2">
                            <option value="">-- Pilih Provinsi --</option>
                            @foreach ($provinces as $prov)
                                <option value="{{ $prov->id }}" {{ $prov->id == $project->province_id ? 'selected' : '' }}>
                                    {{ $prov->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Kabupaten/Kota</label>
                            <select id="edit_city" name="city_id" class="form-select select2">
                                <option value="">-- Pilih Kota --</option>
                            </select>
                    </div>
                </div>

                <div class="row mb-3 mt-2">
                    <div class="col-md-5">
                        <label class="form-label required">Kecamatan</label>
                            <select id="edit_district" name="district_id" class="form-select select2">
                                <option value="">-- Pilih Kecamatan --</option>
                            </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label required">Kelurahan</label>
                            <select id="edit_sub_district" name="sub_district_id" class="form-select select2"></select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label required">Kode Pos</label>
                            <select id="edit_postal_code" name="postal_code_id" class="form-select select2"></select>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        flatpickr('#start_date_edit', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd-m-Y',
        });

        flatpickr('#end_date_edit', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd-m-Y',
        });

    });
</script>
<script>
$(document).ready(function () {
    @if(isset($project))
        let existingProvince = "{{ $project->province_id }}";
        let existingCity = "{{ $project->city_id }}";
        let existingDistrict = "{{ $project->district_id }}";
        let existingSubDistrict = "{{ $project->sub_district_id }}";
        let existingPostal = "{{ $project->postal_code_id }}";
    @else
        let existingProvince = "";
        let existingCity = "";
        let existingDistrict = "";
        let existingSubDistrict = "";
        let existingPostal = "";
    @endif

    // 1. province → city
    $('#edit_province').on('change', function () {
        let id = $(this).val();
        $('#edit_city').empty().append('<option value="">Loading...</option>');
        $('#edit_district').empty();
        $('#edit_sub_district').empty();
        $('#edit_postal_code').empty();

        if (!id) return;

        $.get(`/api/cities/${id}`, function (data) {
            $('#edit_city').empty().append('<option value="">-- Pilih Kota --</option>');
            data.forEach(item => {
                $('#edit_city').append(new Option(item.name, item.id));
            });

            if (existingCity) {
                $('#edit_city').val(existingCity).trigger('change');
                existingCity = null;
            }
        });
    });

    // 2. city → district
    $('#edit_city').on('change', function () {
        let id = $(this).val();
        $('#edit_district').empty().append('<option>Loading...</option>');
        $('#edit_sub_district').empty();
        $('#edit_postal_code').empty();

        if (!id) return;

        $.get(`/api/districts/${id}`, function (data) {
            $('#edit_district').empty().append('<option>-- Pilih Kecamatan --</option>');
            data.forEach(item => {
                $('#edit_district').append(new Option(item.name, item.id));
            });

            if (existingDistrict) {
                $('#edit_district').val(existingDistrict).trigger('change');
                existingDistrict = null;
            }
        });
    });

    // 3. district → sub_district
    $('#edit_district').on('change', function () {
        let id = $(this).val();
        $('#edit_sub_district').empty().append('<option>Loading...</option>');
        $('#edit_postal_code').empty();

        if (!id) return;

        $.get(`/api/sub_districts/${id}`, function (data) {
            $('#edit_sub_district').empty().append('<option>-- Pilih Kelurahan --</option>');
            data.forEach(item => {
                $('#edit_sub_district').append(new Option(item.name, item.id));
            });

            if (existingSubDistrict) {
                $('#edit_sub_district').val(existingSubDistrict).trigger('change');
                existingSubDistrict = null;
            }
        });
    });

    // 4. sub_district → postal_code
    $('#edit_sub_district').on('change', function () {
        let id = $(this).val();
        $('#edit_postal_code').empty().append('<option>Loading...</option>');

        if (!id) return;

        $.get(`/api/postal_codes/${id}`, function (data) {
            $('#edit_postal_code').empty().append('<option>-- Pilih Kode Pos --</option>');
            data.forEach(item => {
                $('#edit_postal_code').append(new Option(item.postal_code, item.id));
            });

            if (existingPostal) {
                $('#edit_postal_code').val(existingPostal).trigger('change');
                existingPostal = null;
            }
        });
    });

    // 🔥 AUTO-LOAD saat halaman edit dibuka
    if (existingProvince) {
        $('#edit_province').trigger('change');
    }
});
</script>
@endpush