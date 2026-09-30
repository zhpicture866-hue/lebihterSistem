@extends('tablar::page')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col d-flex align-items-center">
                    <a href="{{ route('projects.index') }}" class="btn btn-dark d-flex align-items-center">
                        <i class="ti ti-arrow-left"></i>
                    </a>      
                        <h2 class="page-title mb-0">Tambah Proyek</h2> 
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @include('projects.components.timeline-horizontal')
            @if($activeStep == 1)
            <div id="project" class="step-section">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body px-5 py-4">
                        <h3 class="mb-4 fw-bold">Buat Proyek Baru</h3>
                        @include('projects.steps.create-project')
                    </div>
                </div>
            </div>
            @endif
            @if($activeStep >= 2)
                    <x-collapse-card title="1. Proyek" target="project-body">
                        <x-slot:actions>
                            @can('ubah data proyek')
                            <div class="btn-group">
                                <button type="button"
                                    class="btn btn-sm btn-dark me-2 btn-toggle-view-edit"
                                    data-view="project-view"
                                    data-edit="project-edit"
                                    title="Edit Data">
                                    <i class="ti ti-edit"></i>
                                </button>
                            </div>
                            @endcan
                        </x-slot:actions>
                        <div id="project-view">
                            @include('projects.details.project')
                        </div>
                        <div id="project-edit" style="display:none;">
                            @include('projects.edit.project-form')

                            <div class="d-flex justify-content-between align-items-center mt-4">

                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary btn-cancel-view-edit"
                                        data-view="project-view"
                                        data-edit="project-edit">
                                    <i class="ti ti-x me-1"></i>
                                    Batal
                                </button>

                                <button type="submit"
                                        form="project-edit-form"
                                        class="btn btn-dark">
                                    <i class="ti ti-device-floppy me-1"></i>
                                    Simpan Perubahan Data
                                </button>

                            </div>
                        </div>
                    </x-collapse-card>

                @php
                    // Peta nama level -> partial. 'create' = form kosong (dipakai step yg belum ada
                    // datanya sama sekali), 'edit' = form dibuka lewat toggle "Edit" dari mode ringkas,
                    // 'detail' = ringkasan (null = step ini gak butuh mode ringkas terpisah, partial
                    // 'create'-nya sendiri sudah pintar menampilkan status akhir, misal step Invoice).
                    // Nambah project_type baru dengan step yang SAMA otomatis kepakai.
                    $stepViews = [
                        'Penawaran Harga' => [
                            'create'  => 'projects.steps.rab-process',
                            'edit'    => 'projects.edit.rab-process',
                            'detail'  => 'projects.details.rab-process',
                            'hasData' => (bool) ($project->rab && $project->rab->items()->exists()),
                            'action'  => '<button type="submit" form="rabForm" class="btn btn-dark" title="Simpan RAB"><i class="ti ti-device-floppy me-1"></i></button>',
                        ],
                        'Penawaran Harga' => [
                            'create'  => 'projects.steps.rab-process',
                            'edit'    => 'projects.edit.rab-process',
                            'detail'  => 'projects.details.rab-process',
                            'hasData' => (bool) ($project->rab && $project->rab->items()->exists()),
                            'formId'  => 'rab-edit-form',
                            'action'  => '<button type="submit" form="rabForm" class="btn btn-dark" title="Simpan RAB">
                                            <i class="ti ti-device-floppy me-1"></i>
                                        </button>',
                            'updateText' => 'Simpan Perubahan RAB',
                        ],
                        'Setting Termin' => [
                            'create'  => 'projects.steps.build-termin',
                            'edit'    => 'projects.edit.build-termin-form',
                            'detail'  => 'projects.details.build-termins',
                            // FIX: tadinya hardcode `false` -> gak akan pernah nampilin ringkasan+edit
                            // walau termin udah pernah diisi. Sekarang dihitung beneran dari data.
                            'hasData' => (bool) $project->buildTermins()->exists(),
                            'action'  => null,
                        ],
                        'Setting Termin' => [
                            'create'  => 'projects.steps.build-termin',
                            'edit'    => 'projects.edit.build-termin-form',
                            'detail'  => 'projects.details.build-termins',
                            'hasData' => (bool) $project->buildTermins()->exists(),
                            'formId'  => 'build-termin-edit-form',
                            'action'  => null,
                            'updateText' => 'Simpan Perubahan Termin',
                        ],
                    ];
                @endphp

                @foreach($project->levels->sortBy('level_order') as $level)
                    @continue($activeStep < $level->level_order + 1)
                    {{-- Step Invoice dihapus: tombol invoice sekarang ada di tabel step Setting Termin --}}
                    @continue($level->level_name === 'Invoice')

                    @php
                        $stepTitle = ($level->level_order + 1) . '. ' . $level->level_name;
                        $slug = \Illuminate\Support\Str::slug($level->level_name);
                    @endphp

                    <div id="step-{{ $slug }}" class="step-section">


                            @php $config = $stepViews[$level->level_name] ?? null; @endphp

                            @if(! $config)
                                <x-collapse-card :title="$stepTitle" target="{{ $slug }}-body">
                                    <div class="alert alert-warning mb-0">
                                        Belum ada tampilan untuk step "<strong>{{ $level->level_name }}</strong>".
                                        Hubungi developer untuk menambahkan partial view-nya.
                                    </div>
                                </x-collapse-card>

                            @elseif($config['detail'] && $config['hasData'])
                                <x-collapse-card :title="$stepTitle" target="{{ $slug }}-body">
                                    <x-slot:actions>
                                        @can('ubah data proyek')
                                            <button type="button"
                                                    class="btn btn-sm btn-dark me-2 btn-toggle-view-edit"
                                                    data-view="{{ $slug }}-view"
                                                    data-edit="{{ $slug }}-edit"
                                                    title="Edit Data">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                        @endcan
                                    </x-slot:actions>
                                    <div id="{{ $slug }}-view">
                                        @include($config['detail'])
                                    </div>
                                    <div id="{{ $slug }}-edit" style="display:none;">

                                        @include($config['edit'])

                                        <div class="d-flex justify-content-between align-items-center mt-4">

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary btn-cancel-view-edit"
                                                    data-view="{{ $slug }}-view"
                                                    data-edit="{{ $slug }}-edit">
                                                <i class="ti ti-x me-1"></i>
                                                Batal
                                            </button>

                                            @can('ubah data proyek')
                                                @if(!empty($config['formId']))
                                                    <button type="submit"
                                                            form="{{ $config['formId'] }}"
                                                            class="btn btn-dark">
                                                        <i class="ti ti-device-floppy me-1"></i>
                                                        {{ $config['updateText'] ?? 'Simpan Perubahan' }}
                                                    </button>
                                                @endif
                                            @endcan

                                        </div>
                                    </div>
                                </x-collapse-card>

                            @else
                                <x-collapse-card :title="$stepTitle" target="{{ $slug }}-body" :sticky="false">
                                    @if($config['action'])
                                        <x-slot:actions>
                                            {!! $config['action'] !!}
                                        </x-slot:actions>
                                    @endif
                                    @include($config['create'])
                                </x-collapse-card>
                            @endif

                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });
    });
</script>

<script>
$('#province').change(function () {
var id = $(this).val();
$('#city').html('<option>Loading...</option>');
$('#district').html('<option value="">-- Pilih kecamatan --</option>');
$('#sub_district').html('<option value="">-- Pilih kelurahan --</option>');

if (id) {
$.get('/api/cities/' + id, function (data) {
$('#city').empty().append('<option value="">-- Pilih city --</option>');
$.each(data, function (i, city) {
    $('#city').append('<option value="' + city.id + '">' + city.name + '</option>');
        });
    });
    }
});

$('#city').change(function () {
var id = $(this).val();
$('#district').html('<option>Loading...</option>');
$('#sub_district').html('<option value="">-- Pilih kelurahan --</option>');

if (id) {
    $.get('/api/districts/' + id, function (data) {
        $('#district').empty().append('<option value="">-- Pilih kecamatan --</option>');
        $.each(data, function (i, district) {
            $('#district').append('<option value="' + district.id + '">' + district.name + '</option>');
                });
            });
        }
    });

$('#district').change(function () {
var id = $(this).val();
$('#sub_district').html('<option>Loading...</option>');

    if (id) {
        $.get('/api/sub_districts/' + id, function (data) {
            $('#sub_district').empty().append('<option value="">-- Pilih kelurahan --</option>');
            $.each(data, function (i, sub_district) {
                $('#sub_district').append('<option value="' + sub_district.id + '">' + sub_district.name + '</option>');
            });
        });
    }
});

$('#sub_district').change(function () {
var id = $(this).val();
$('#postal_code').html('<option>Loading...</option>');

if (id) {
    $.get('/api/postal_codes/' + id, function (data) {
        $('#postal_code').empty().append('<option value="">-- Pilih kode pos --</option>');
        $.each(data, function (i, postal_code) {
            $('#postal_code').append('<option value="' + postal_code.id + '">' + postal_code.postal_code + '</option>');
        });
    });
    }
});
</script>
<script>
function initLocationCascade(config) {
    const {
        prefix = '',
        oldProvince,
        oldCity,
        oldDistrict,
        oldSub,
        oldPostal
    } = config;

    const $province = $('#' + prefix + 'province');
    const $city = $('#' + prefix + 'city');
    const $district = $('#' + prefix + 'district');
    const $sub = $('#' + prefix + 'sub_district');
    const $postal = $('#' + prefix + 'postal_code');

    function loadCities(provinceId, selected = null, callback = null) {
        if (!provinceId) return;
        $.get(`/api/cities/${provinceId}`, function (data) {
            $city.empty().append('<option value="">-- Pilih Kota --</option>');
            data.forEach(item => {
                $city.append(`<option value="${item.id}" ${selected == item.id ? 'selected' : ''}>${item.name}</option>`);
            });
            $city.trigger('change.select2');
            if (callback) callback();
        });
    }

    function loadDistricts(cityId, selected = null, callback = null) {
        if (!cityId) return;
        $.get(`/api/districts/${cityId}`, function (data) {
            $district.empty().append('<option value="">-- Pilih Kecamatan --</option>');
            data.forEach(item => {
                $district.append(`<option value="${item.id}" ${selected == item.id ? 'selected' : ''}>${item.name}</option>`);
            });
            $district.trigger('change.select2');
            if (callback) callback();
        });
    }

    function loadSubDistricts(districtId, selected = null, callback = null) {
        if (!districtId) return;
        $.get(`/api/sub_districts/${districtId}`, function (data) {
            $sub.empty().append('<option value="">-- Pilih Kelurahan --</option>');
            data.forEach(item => {
                $sub.append(`<option value="${item.id}" ${selected == item.id ? 'selected' : ''}>${item.name}</option>`);
            });
            $sub.trigger('change.select2');
            if (callback) callback();
        });
    }

    function loadPostalCodes(subId, selected = null) {
        if (!subId) return;
        $.get(`/api/postal_codes/${subId}`, function (data) {
            $postal.empty().append('<option value="">-- Pilih Kode Pos --</option>');
            data.forEach(item => {
                $postal.append(`<option value="${item.id}" ${selected == item.id ? 'selected' : ''}>${item.postal_code}</option>`);
            });
            $postal.trigger('change.select2');
        });
    }
    $province.on('change', function () {
        loadCities(this.value);
    });

    $city.on('change', function () {
        loadDistricts(this.value);
    });

    $district.on('change', function () {
        loadSubDistricts(this.value);
    });

    $sub.on('change', function () {
        loadPostalCodes(this.value);
    });

    // Restore old()
    if (oldProvince) {
        loadCities(oldProvince, oldCity, () => {
            loadDistricts(oldCity, oldDistrict, () => {
                loadSubDistricts(oldDistrict, oldSub, () => {
                    loadPostalCodes(oldSub, oldPostal);
                });
            });
        });
    }
}
</script>


<script>
document.addEventListener('DOMContentLoaded', () => {

    const fileStores = new Map();

    document.querySelectorAll('.image-input').forEach(input => {
        fileStores.set(input, new DataTransfer());

        input.addEventListener('change', () => {
            const previewEl = document.getElementById(input.dataset.preview);
            const store     = fileStores.get(input);

            const selectedFiles = Array.from(input.files);
            input.value = '';

            selectedFiles.forEach(file => {
                store.items.add(file);

                if (file.type.startsWith("image/")) {

                    const reader = new FileReader();

                    reader.onload = e => {

                        const div = document.createElement('div');
                        div.className = 'position-relative';

                        div.innerHTML = `
                            <img src="${e.target.result}"
                                class="rounded border"
                                style="width:120px;height:120px;object-fit:cover">

                            <button type="button"
                                    class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-image">
                                ×
                            </button>
                        `;

                        div.querySelector('.remove-image').addEventListener('click', () => {
                            const index = Array.from(store.files).findIndex(
                                f => f.name === file.name && f.size === file.size
                            );

                            if (index > -1) {
                                store.items.remove(index);
                                input.files = store.files;
                            }

                            div.remove();
                        });

                        previewEl.appendChild(div);
                    };

                    reader.readAsDataURL(file);

                } else if (file.type === "application/pdf") {

                    const div = document.createElement('div');
                    div.className = 'position-relative';

                    div.innerHTML = `
                        <div class="border rounded d-flex align-items-center justify-content-center"
                            style="width:120px;height:120px;">
                            📄 PDF
                        </div>

                        <button type="button"
                                class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-image">
                            ×
                        </button>
                    `;

                    div.querySelector('.remove-image').addEventListener('click', () => {
                        const index = Array.from(store.files).findIndex(
                            f => f.name === file.name && f.size === file.size
                        );

                        if (index > -1) {
                            store.items.remove(index);
                            input.files = store.files;
                        }

                        div.remove();
                    });

                    previewEl.appendChild(div);
                }
            });

            input.files = store.files;
        });
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll(".btn-toggle-view-edit").forEach(btn => {

        btn.addEventListener("click", () => {

            const view = document.getElementById(btn.dataset.view);
            const edit = document.getElementById(btn.dataset.edit);

            if (!view || !edit) return;

            view.style.display = "none";
            edit.style.display = "block";
        });

    });

    document.querySelectorAll(".btn-cancel-view-edit").forEach(btn => {

        btn.addEventListener("click", () => {

            const view = document.getElementById(btn.dataset.view);
            const edit = document.getElementById(btn.dataset.edit);

            if (!view || !edit) return;

            edit.style.display = "none";
            view.style.display = "block";
        });

    });

    let rabEditLoaded = false;

    const rabId = @json($project?->rab?->id);
    const rabEdit = document.getElementById("penawaran-harga-edit");

    if (!rabId || !rabEdit) return;


    const btnRabEdit = document.querySelector(
        '[data-edit="penawaran-harga-edit"]'
    );

    if (!btnRabEdit) return;


    btnRabEdit.addEventListener("click", async () => {

        if (rabEditLoaded) {

            setTimeout(() => {
                initRabEdit();
            }, 100);

            return;
        }

        try {

            const response = await fetch(
                `/rab/${rabId}/structure`
            );

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            loadExistingRab(data);

            setTimeout(() => {
                initRabEdit();
            }, 100);

            rabEditLoaded = true;

        } catch (error) {

            console.error("Gagal memuat RAB:", error);

            Swal.fire({
                icon: "error",
                title: "Gagal Memuat RAB",
                text: "Data RAB gagal dimuat."
            });

        }

    });

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.approve-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: form.dataset.title || 'Apakah Anda yakin?',
                text: form.dataset.text || 'Proses ini akan dilanjutkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#212529',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    form.submit();
                }
            });
        });
    });
});
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {

        document.querySelectorAll(".btn-toggle-card").forEach(btn => {

            btn.addEventListener("click", function () {

                const target = document.querySelector(
                    this.dataset.target
                );

                if(!target) return;

                // TOGGLE
                target.classList.toggle("d-none");

                // ICON
                const icon = this.querySelector("i");

                if(target.classList.contains("d-none")){

                    icon.classList.remove("ti-chevron-up");
                    icon.classList.add("ti-chevron-down");

                }else{

                    icon.classList.remove("ti-chevron-down");
                    icon.classList.add("ti-chevron-up");

                }

            });

        });

    });
</script>
@endpush