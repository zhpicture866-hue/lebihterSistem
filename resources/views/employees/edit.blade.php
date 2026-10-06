@extends('tablar::page')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('employees.index') }}" class="btn btn-dark d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>
                
                    <h2 class="page-title mb-0">Edit Data Karyawan</h2>
                
            </div>
        </div>
    </div>
</div>


<div class="page-body">
    <div class="container-xl">
        <div class="card shadow-sm border-0">
            <div class="card-body px-5 py-4">
                <form action="{{ route('employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                    <div class="text-center mb-5">
                        <div class="position-relative d-inline-block">
                            @if ($user->photo)
                                <img id="previewImage" src="{{ asset('storage/'.$user->photo) }}" alt="Profile" 
                                    class="rounded-3 shadow-sm border" width="150" height="150"
                                    style="object-fit: cover;">
                            @else
                                <div id="previewImage"
                                    class="rounded-3 shadow-sm bg-light d-flex align-items-center justify-content-center"
                                    style="width:150px; height:150px;">
                                    <i class="ti ti-user" style="font-size: 64px; color:#aaa;"></i>
                                </div>
                            @endif
                            <label for="photo"
                                class="btn btn-sm btn-dark position-absolute bottom-0 end-0 translate-middle rounded-circle"
                                title="Ganti Foto">
                                <i class="ti ti-camera"></i>
                            </label>
                        </div>
                        <input type="file" id="photo" name="photo" class="d-none" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <small class="text-danger fw-semibold">
                            * : Wajib diisi
                        </small>
                    </div>

                    
                        <div class="section-block mb-5">
                            <h3 class="fw-semibold mb-3 border-bottom pb-2">🧍 Informasi Pribadi</h3>
                            <div class="row g-4">
                                <div class="col-md-5">
                                    <label class="form-label required">Nama Lengkap</label>
                                    <input type="text" name="fullname" class="form-control @error('fullname') is-invalid @enderror"  value="{{ old('fullname', $user->fullname) }}" required>
                                    @error('fullname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Nama Panggilan</label>
                                    <input type="text" name="nickname" class="form-control @error('nickname') is-invalid @enderror" value="{{ old('nickname', $user->nickname) }}">
                                    @error('nickname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select name="gender" class="form-select select2">
                                        <option value="">-- Pilih --</option>
                                        <option value="1" {{ old('gender', $user->gender) == 1 ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="2" {{ old('gender', $user->gender) == 2 ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tempat Lahir</label>
                                    <input type="text" name="birth_place" class="form-control @error('birth_place') is-invalid @enderror" value="{{ old('birth_place', $user->birth_place) }}">
                                    @error('birth_place')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Lahir</label>
                                                <input type="date" name="birth_date" class="form-control"
                                                    value="{{ old('birth_date', $user->birth_date) }}"
                                                    pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Agama</label>
                                    <select name="religion_id"
                                        class="form-select select2 @error('religion_id') is-invalid @enderror">
                                        <option value="">-- Pilih Agama --</option>
                                        @foreach($religions as $religion)
                                            <option value="{{ $religion->id }}" {{ old('religion_id', $user->religion_id) == $religion->id ? 'selected' : '' }}>
                                                {{ $religion->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('religion_id')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                {{-- {{ dd($user->religion_id) }} --}}
                                <div class="col-md-4">
                                    <label class="form-label">Nomor KTP</label>
                                    <input type="text" class="form-control @error('identity_number') is-invalid @enderror" name="identity_number" maxlength="16" inputmode="numeric" pattern="[0-9]*" value="{{ old('identity_number', $user->identity_number) }}">
                                    @error('identity_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">NPWP</label>
                                    <input type="text" name="npwp" class="form-control @error('npwp') is-invalid @enderror" value="{{ old('npwp', $user->npwp) }}">
                                    @error('npwp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label>Status Pernikahan :</label>
                                    <select name="marital_status" class="form-select select2">
                                        <option value="">-- Pilih Status --</option>
                                        <option value="1" {{ $employee->marital_status == 1 ? 'selected' : '' }}>Lajang</option>
                                        <option value="2" {{ $employee->marital_status == 2 ? 'selected' : '' }}>Menikah</option>
                                        <option value="3" {{ $employee->marital_status == 3 ? 'selected' : '' }}>Duda</option>
                                        <option value="4" {{ $employee->marital_status == 4 ? 'selected' : '' }}>Janda</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="section-block mb-5">
                            <h3 class="fw-semibold mb-3 border-bottom pb-2">📞 Kontak & Alamat</h3>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Telepon</label>
                                    <div class="input-group">
                                        <select name="phone_code" id="phone_code" class="form-select" style="max-width: 210px;">
                                            @foreach (config('phone_codes') as $code => $negara)
                                                <option value="{{ $code }}" @selected(old('phone_code', $user->phone_code ?? '62') == $code)>
                                                    +{{ $code }} {{ $negara }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="phone_number" id="phone_number"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            value="{{ old('phone_number', $user->phone_number) }}"
                                            placeholder="85655xxxxxxx" inputmode="numeric" required>
                                    </div>
                                    @error('phone')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    @error('phone_code')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alamat Lengkap</label>
                                    <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row g-4 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label">Provinsi</label>
                                        <select name="province_id" id="province" class="form-select select2">
                                            <option value="">-- Pilih Provinsi --</option>
                                            @foreach($provinces as $province)
                                                <option value="{{ $province->id }}"
                                                    {{ $user->province_id == $province->id ? 'selected' : '' }}>
                                                    {{ $province->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kabupaten/Kota</label>
                                                <select name="city_id" id="city" class="form-select select2">
                                                    <option value="">-- Pilih Kota --</option>
                                                    @foreach($cities as $city)
                                                        <option value="{{ $city->id }}"
                                                            {{ $user->city_id == $city->id ? 'selected' : '' }}>
                                                            {{ $city->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Kecamatan</label>
                                                <select name="district_id" id="district" class="form-select select2">
                                                    <option value="">-- Pilih Kecamatan --</option>
                                                    @foreach($districts as $district)
                                                        <option value="{{ $district->id }}"
                                                            {{ $user->district_id == $district->id ? 'selected' : '' }}>
                                                            {{ $district->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Kelurahan</label>
                                                <select name="sub_district_id" id="sub_district" class="form-select select2">
                                                    <option value="">-- Pilih kelurahan --</option>
                                                    @foreach($subDistricts as $sub_district)
                                                        <option value="{{ $sub_district->id }}"
                                                            {{ $user->sub_district_id == $sub_district->id ? 'selected' : '' }}>
                                                            {{ $sub_district->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Kode Pos</label>
                                                <select name="postal_code_id" id="postal_code" class="form-select select2">
                                                    <option value="">-- Pilih Kode Pos --</option>
                                                    @foreach($postalCodes as $postal_code)
                                                        <option value="{{ $postal_code->id }}"
                                                            {{ $user->postal_code_id == $postal_code->id ? 'selected' : '' }}>
                                                            {{ $postal_code->postal_code }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                </div>
                            </div>
                        </div>

                        <div class="section-block mb-5">
                            <h3 class="fw-semibold mb-3 border-bottom pb-2">🏦 Data Bank</h3>
                            <p class="small text-muted mb-3">Diperlukan bila terjadi pengembalian dana</p>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label" for="bank_id">Nama Bank</label>
                                        <select id="bank_id" name="bank_id" class="form-select select2"
                                        value="{{ old('bank_id', auth()->user()->bank_id) }}">
                                            <option value="">Pilih Bank</option>
                                        </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nomor Rekening</label>
                                    <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror" value="{{ old('account_number', $user->account_number) }}">
                                    @error('account_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Atas Nama</label>
                                    <input type="text" name="account_holder" class="form-control @error('account_holder') is-invalid @enderror" value="{{ old('account_holder', $user->account_holder) }}">
                                    @error('account_holder')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    

                    <div class="section-block mb-5">
                        <h3 class="fw-semibold mb-3 border-bottom pb-2">💼 Data Kepegawaian</h3>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">NIK</label>
                                <input type="text" id="nik" name="nik" class="form-control" value="{{ old('nik', $employee->nik) }}" readonly>
                                @error('nik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Status Karyawan</label>
                                <select name="employment_status" class="form-select select2" value="{{ old('employment_status', $employee->employment_status) }}">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="Tetap" {{ $employee->employment_status == "Tetap" ? 'selected' : '' }}>Tetap</option>
                                    <option value="Kontrak" {{ $employee->employment_status == "Kontrak" ? 'selected' : '' }}>Kontrak</option>
                                    <option value="Harian" {{ $employee->employment_status == "Harian" ? 'selected' : '' }}>Harian</option>
                                    <option value="Honorer" {{ $employee->employment_status == "Honorer" ? 'selected' : '' }}>Honorer</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                            <label class="form-label">Tanggal Mulai Kerja :</label>
                                            <input type="date" name="start_date" class="form-control" 
                                                value="{{ old('start_date', $employee->start_date) }}"
                                                pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD">
                                        </div>
                            <div class="col-md-12">
                                <label class="form-label required" for="role">Role:</label>

                                <select class="form-control select2" name="role[]" multiple required>

                                    @foreach ($internalRoles as $role)

                                        <option value="{{ $role }}"
                                            {{ in_array($role, $selectedRoles ?? []) ? 'selected' : '' }}>
                                            {{ $role }}
                                        </option>

                                    @endforeach

                                </select>
                            </div>

                            <div class="section-block mb-5">
                                <h3 class="fw-semibold mb-3 border-bottom pb-2">📎 Dokumen Karyawan</h3>
                                <div class="row g-4">

                                    <!-- PDF Surat Perjanjian Kerja -->
                                    <div class="col-md-6">
                                        <label>Surat Perjanjian Kerja Saat Ini:</label><br>
                                        @if ($employee->contract_letter_file)
                                            <a href="{{ asset('storage/' . $employee->contract_letter_file) }}"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            Lihat / Unduh PDF
                                            </a>
                                        @else
                                            <p class="text-muted mb-0">Belum ada surat perjanjian kerja.</p>
                                        @endif
                                    </div>

                                    <div class="col-md-6">
                                        <label for="contract_letter_file" class="form-label">Upload Surat Perjanjian Kerja Baru (PDF)</label>
                                        <input type="file" name="contract_letter_file" class="form-control" accept="application/pdf">
                                        <small class="text-muted">Kosongkan jika tidak ingin mengubah file.</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Sertifikat Pelatihan Saat Ini:</label><br>
                                        @if ($employee->training_certificate)
                                            <a href="{{ asset('storage/' . $employee->training_certificate) }}"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            Lihat / Unduh PDF
                                            </a>
                                        @else
                                            <p class="text-muted mb-0">Belum ada sertifikat pelatihan.</p>
                                        @endif
                                    </div>

                                    <div class="col-md-6">
                                        <label for="training_certificate" class="form-label">Upload Sertifikat Baru (PDF)</label>
                                        <input type="file" name="training_certificate" class="form-control" accept="application/pdf">
                                        <small class="text-muted">Kosongkan jika tidak ingin mengubah file.</small>
                                    </div>
                                     <div class="col-md-6">
                                        <label>Foto KTP:</label><br>
                                        @if ($user->identity_photo)
                                            <a href="{{ asset('storage/' . $user->identity_photo) }}"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            Lihat / Unduh KTP
                                            </a>
                                        @else
                                            <p class="text-muted mb-0">Belum ada foto KTP.</p>
                                        @endif
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Upload Foto KTP (PDF/Gambar)</label>
                                        <input type="file" name="identity_photo" class="form-control" accept="application/pdf,image/*">
                                        <small class="text-muted">Kosongkan jika tidak ingin mengubah file.</small>
                                    </div>
                                </div>
                    </div>
                        </div>
                    </div>

                    {{-- ========== SECTION 5: PENGHASILAN ========== --}}
                    <div class="section-block mb-5">
                        <h3 class="fw-semibold mb-3 border-bottom pb-2">💰 Data Penghasilan</h3>
                        <div class="row g-4">
                            <div class="col-md">
                                <label class="form-label">Gaji Pokok</label>
                                <input type="number" id="basic_salary" name="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror" value="{{ old('basic_salary', $employee->basic_salary) }}">
                                @error('basic_salary')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md">
                                <label class="form-label">Tunjangan</label>
                                <input type="number" name="allowance" class="form-control" value="{{ old('allowance', $employee->allowance) }}">
                            </div>
                            <div class="col-md">
                                <label class="form-label">Potongan</label>
                                <input type="number" name="deduction" class="form-control" value="{{ old('deduction', $employee->deduction) }}">
                            </div>
                            <div class="col-md">
                                <label class="form-label">Bonus</label>
                                <input type="number" name="bonus" class="form-control" value="{{ old('bonus', $employee->bonus) }}">
                            </div>
                            <div class="col-md">
                                <label class="form-label">THR</label>
                                <input type="number" name="thr" class="form-control" value="{{ old('thr', $employee->thr) }}">
                            </div>
                        </div>
                    </div>

                    {{-- SUBMIT --}}
                    <div class="text-end mt-5">
                        <button type="submit" class="btn btn-dark px-4">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Data
                        </button>
                    </div>
                </form>

            </div>
        </div>
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
                                        $('#sub_district').html('<option value="">-- Pilih Kelurahan --</option>');

                                            if (id) {
                                            $.get('/api/cities/' + id, function (data) {
                                            $('#city').empty().append('<option value="">-- Pilih Kabupaten --</option>');
                                            $.each(data, function (i, city) {
                                            $('#city').append('<option value="' + city.id + '">' + city.name + '</option>');
                                                        });
                                                    });
                                                }
                                            });

                                            $('#city').change(function () {
                                                var id = $(this).val();
                                                $('#district').html('<option>Loading...</option>');
                                                $('#sub_district').html('<option value="">-- Pilih Kelurahan --</option>');

                                                if (id) {
                                                    $.get('/api/districts/' + id, function (data) {
                                                        $('#district').empty().append('<option value="">-- Pilih Kecamatan --</option>');
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
                                                        $('#sub_district').empty().append('<option value="">-- Pilih Kelurahan --</option>');
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
                                                        $('#postal_code').empty().append('<option value="">-- Pilih Kode Pos --</option>');
                                                        $.each(data, function (i, postal_code) {
                                                            $('#postal_code').append('<option value="' + postal_code.id + '">' + postal_code.postal_code + '</option>');
                                                        });
                                                    });
                                                }
                                            });
                                        </script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('bank_id');
    const selectedBankId = "{{ $user->bank_id ?? '' }}";

    fetch('/api/banks')
        .then(response => response.json())
        .then(data => {
            data.forEach(bank => {
                const option = document.createElement('option');
                option.value = bank.id;
                option.text = `${bank.name} (${bank.code})`;
                if (bank.id === selectedBankId) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        });
});
</script>
<script>
$('#phone_number').on('input', function () {
    let v = $(this).val().replace(/\D/g, '').replace(/^0+/, '');
    if ($('#phone_code').val() === '62' && v.startsWith('62')) v = v.slice(2);
    $(this).val(v);
});

$('#phone_code').on('change', function () {
    $('#phone_number').trigger('input');
});
</script>

    <script>
        document.getElementById('photo').addEventListener('change', function (event) {
        const input = event.target;
        const file = input.files[0];
        const previewContainer = document.getElementById('previewImage');

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                // Jika sebelumnya preview berupa ikon <div>, ganti jadi <img>
                if (previewContainer.tagName.toLowerCase() === 'div') {
                    const img = document.createElement('img');
                    img.id = 'previewImage';
                    img.src = e.target.result;
                    img.className = 'border rounded-3 shadow-sm';
                    img.width = 150;
                    img.height = 150;
                    img.style.objectFit = 'cover';
                    previewContainer.replaceWith(img);
                } else {
                    previewContainer.src = e.target.result;
                }
            };
            reader.readAsDataURL(file);
        }
    });
    </script>
@endpush