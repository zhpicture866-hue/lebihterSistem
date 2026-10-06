@extends('tablar::page')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('customers.index') }}" class="btn btn-dark d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>
                
                    <h2 class="page-title mb-0">Tambah Customer</h2>
                
            </div>
        </div>
    </div>
</div>


<div class="page-body">
    <div class="container-xl">
        <div class="card shadow-sm border-0">
            <div class="card-body px-5 py-4">
                <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data">
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

                    <div class="text-center mb-5">
                    <div class="position-relative d-inline-block">
                            <div id="previewImage"
                                 class="rounded-3 shadow-sm bg-light d-flex align-items-center justify-content-center"
                                 style="width:150px; height:150px;">
                                 <i class="ti ti-user" style="font-size: 64px; color:#aaa;"></i>
                            </div>
                        
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
                            <div class="col-md-2">
                                <label class="form-label">Title</label>
                                <select name="title" 
                                        class="form-select select2 @error('title') is-invalid @enderror">
                                    <option value="">-- Pilih --</option>
                                    <option value="1" {{ old('title') == '1' ? 'selected' : '' }}>Bapak</option>
                                    <option value="2" {{ old('title') == '2' ? 'selected' : '' }}>Ibu</option>
                                    <option value="3" {{ old('title') == '3' ? 'selected' : '' }}>Kak</option>
                                </select>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label required">Nama Lengkap</label>
                                <input type="text" name="fullname" class="form-control @error('fullname') is-invalid @enderror"  value="{{ old('fullname') }}" required>
                                @error('fullname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nama Panggilan</label>
                                <input type="text" name="nickname" class="form-control @error('nickname') is-invalid @enderror" value="{{ old('nickname') }}">
                                @error('nickname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="gender" 
                                        class="form-select select2 @error('gender') is-invalid @enderror">
                                    <option value="">-- Pilih --</option>
                                    <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="2" {{ old('gender') == '2' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" name="birth_place" class="form-control @error('birth_place') is-invalid @enderror" value="{{ old('birth_place') }}">
                                @error('birth_place')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Lahir</label>
                                            <input type="date" name="birth_date" class="form-control"
                                                value="{{ old('birth_date') }}"
                                                pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Agama</label>
                                <select name="religion_id" class="form-select select2">
                                    <option value="">-- Pilih Agama --</option>
                                    @foreach($religions as $religion)
                                        <option value="{{ $religion->id }}" {{ old('religion_id') == $religion->id ? 'selected' : '' }}>
                                            {{ $religion->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('religion_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Nomor KTP</label>
                                <input type="text" class="form-control @error('identity_number') is-invalid @enderror" name="identity_number" maxlength="16" inputmode="numeric" pattern="\d{16}" value="{{ old('identity_number') }}">
                                @error('identity_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">NPWP</label>
                                <input type="text" name="npwp" class="form-control @error('npwp') is-invalid @enderror" value="{{ old('npwp') }}">
                                @error('npwp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ========== SECTION 2: KONTAK & ALAMAT ========== --}}
                    <div class="section-block mb-5">
                        <h3 class="fw-semibold mb-3 border-bottom pb-2">📞 Kontak & Alamat</h3>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label required">Nomor Telepon</label>
                                <div class="input-group">
                                    <select name="phone_code" id="phone_code" class="form-select" style="max-width: 210px;">
                                        @foreach (config('phone_codes') as $code => $negara)
                                            <option value="{{ $code }}" @selected(old('phone_code', '62') == $code)>
                                                +{{ $code }} {{ $negara }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="phone_number" id="phone_number"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone_number') }}"
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
                                <label class="form-label required">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Provinsi</label>
                                <select name="user_province_id" id="user_province" 
                                        class="form-select select2 @error('user_province_id') is-invalid @enderror">
                                    <option value="">-- Pilih Provinsi --</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('user_province_id') == $province->id ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_province_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Kabupaten/Kota</label>
                                <select name="user_city_id" id="user_city" 
                                        class="form-select select2 @error('user_city_id') is-invalid @enderror">
                                    <option value="">-- Pilih Kota --</option>
                                </select>
                                @error('user_city_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Kecamatan</label>
                                <select name="user_district_id" id="user_district" 
                                        class="form-select select2 @error('user_district_id') is-invalid @enderror">
                                    <option value="">-- Pilih Kecamatan --</option>
                                </select>
                                @error('user_district_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Kelurahan</label>
                                <select name="user_sub_district_id" id="user_sub_district" 
                                        class="form-select select2 @error('user_sub_district_id') is-invalid @enderror">
                                    <option value="">-- Pilih Kelurahan --</option>
                                </select>
                                @error('user_sub_district_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Kode Pos</label>
                                <select name="user_postal_code_id" id="user_postal_code" 
                                        class="form-select select2 @error('user_postal_code_id') is-invalid @enderror">
                                    <option value="">-- Pilih Kode Pos --</option>
                                </select>
                                @error('user_postal_code_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <div class="section-block mb-5">
                        <h3 class="fw-semibold mb-3 border-bottom pb-2">🏦 Data Bank</h3>
                        <p class="small text-muted mb-3">Diperlukan bila terjadi pengembalian dana</p>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label" for="bank_id">Nama Bank</label>
                                <select id="bank_id" name="bank_id" class="form-select select">
                                    <option value="">Pilih Bank</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nomor Rekening</label>
                                <input type="text" id="account_number" name="account_number" class="form-control @error('account_number') is-invalid @enderror" value="{{ old('account_number') }}">
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Atas Nama</label>
                                <input type="text" id="account_holder" name="account_holder" class="form-control @error('account_holder') is-invalid @enderror" value="{{ old('account_holder') }}">
                                @error('account_holder')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ========== SECTION 4: DATA KEPEGAWAIAN ========== --}}
                    <div class="section-block mb-5">
                        <h3 class="fw-semibold mb-3 border-bottom pb-2">💼 Data Pelanggan</h3>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label required">ID Customer</label>
                                <input type="text" id="nic" name="nic" class="form-control" required readonly>
                                @error('nic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kategori Customer</label>
                                <select name="category" class="form-select select2" value="{{ old('category') }}">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="1" {{ old('category') == '1' ? 'selected' : '' }}>Individu</option>
                                    <option value="2" {{ old('category') == '2' ? 'selected' : '' }}>Perusahaan</option>
                                    <option value="3" {{ old('category') == '3' ? 'selected' : '' }}>Instansi</option>
                                    <option value="4" {{ old('category') == '4' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Loyalty Level</label>
                                <select name="loyalty_level" class="form-select select2">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="1" {{ old('loyalty_level') == '1' ? 'selected' : '' }}>Lead</option>
                                    <option value="2" {{ old('loyalty_level') == '2' ? 'selected' : '' }}>New Customer</option>
                                    <option value="3" {{ old('loyalty_level') == '3' ? 'selected' : '' }}>Silver</option>
                                    <option value="4" {{ old('loyalty_level') == '4' ? 'selected' : '' }}>Gold</option>
                                    <option value="5" {{ old('loyalty_level') == '5' ? 'selected' : '' }}>Platinum</option>
                                </select>
                            </div>
                            
                            <div class="col-md-10">
                                <label class="form-label" for="role">Posisi :</label>
                                <select class="form-select select2" name="role[]" multiple>
                                    @foreach (config('eksternal_roles.roles') as $role)
                                        <option value="{{ $role }}" 
                                            {{ in_array($role, old('role', [])) ? 'selected' : '' }}>
                                            {{ ucfirst($role) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>

                    {{-- ========== SECTION 3: OPSI ALAMAT PENGIRIMAN ========== --}}
                    <div class="section-block mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="same_address" name="same_address">
                            <label class="form-check-label fw-semibold" for="same_address">
                                Alamat pengiriman sama dengan domisili pengguna
                            </label>
                        </div>
                    </div>


                        <div class="section-block mb-5">
                            <h3 class="fw-semibold mb-3 border-bottom pb-2">Alamat Pengiriman</h3>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Penerima</label>
                                    <input type="text" name="shipping_name" class="form-control @error('shipping_name') is-invalid @enderror" value="{{ old('shipping_name') }}">
                                    @error('shiping_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nomor Handphone</label>
                                    <input type="number" name="shipping_phone" class="form-control @error('shipping_phone') is-invalid @enderror" value="{{ old('shipping_phone') }}">
                                    @error('shipping_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alamat Lengkap</label>
                                    <textarea name="shipping_address" rows="2" class="form-control @error('shipping_address') is-invalid @enderror">{{ old('shipping_address') }}</textarea>
                                    @error('shipping_address')
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
                                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kabupaten/Kota</label>
                                    <select name="city_id" id="city" class="form-select select2" value="{{ old('city') }}">
                                        <option value="">-- Pilih Kota --</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Kecamatan</label>
                                    <select name="district_id" id="district" class="form-select select2" value="{{ old('district') }}">
                                        <option value="">-- Pilih Kecamatan --</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Kelurahan</label>
                                    <select name="sub_district_id" id="sub_district" class="form-select select2" value="{{ old('sub_district') }}">
                                        <option value="">-- Pilih Kelurahan --</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Kode Pos</label>
                                    <select name="postal_code_id" id="postal_code" class="form-select select2" value="{{ old('postal_code') }}">
                                        <option value="">-- Pilih Kode Pos --</option>
                                    </select>
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
    document.addEventListener('DOMContentLoaded', () => {
        const nicField = document.getElementById('nic');

        // Pastikan elemen NIC ada dulu
        if (!nicField) return;

        fetch('{{ route('customers.generateNic') }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.nic) {
                nicField.value = data.nic;
            }
        })
        .catch(error => console.error('❌ Error fetching NIC:', error));
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

    

<script>
    $('#user_province').change(function () {
    var id = $(this).val();
    $('#user_city').html('<option>Loading...</option>');
    $('#user_district').html('<option value="">-- Pilih kecamatan --</option>');
    $('#user_sub_district').html('<option value="">-- Pilih kelurahan --</option>');

    if (id) {
    $.get('/api/cities/' + id, function (data) {
    $('#user_city').empty().append('<option value="">-- Pilih city --</option>');
    $.each(data, function (i, city) {
        $('#user_city').append('<option value="' + city.id + '">' + city.name + '</option>');
            });
        });
        }
    });

    $('#user_city').change(function () {
    var id = $(this).val();
    $('#user_district').html('<option>Loading...</option>');
    $('#user_sub_district').html('<option value="">-- Pilih kelurahan --</option>');

    if (id) {
        $.get('/api/districts/' + id, function (data) {
            $('#user_district').empty().append('<option value="">-- Pilih kecamatan --</option>');
            $.each(data, function (i, district) {
                $('#user_district').append('<option value="' + district.id + '">' + district.name + '</option>');
                    });
                });
            }
        });

    $('#user_district').change(function () {
    var id = $(this).val();
    $('#user_sub_district').html('<option>Loading...</option>');

        if (id) {
            $.get('/api/sub_districts/' + id, function (data) {
                $('#user_sub_district').empty().append('<option value="">-- Pilih kelurahan --</option>');
                $.each(data, function (i, sub_district) {
                    $('#user_sub_district').append('<option value="' + sub_district.id + '">' + sub_district.name + '</option>');
                });
            });
        }
    });

    $('#user_sub_district').change(function () {
    var id = $(this).val();
    $('#user_postal_code').html('<option>Loading...</option>');

    if (id) {
        $.get('/api/postal_codes/' + id, function (data) {
            $('#user_postal_code').empty().append('<option value="">-- Pilih kode pos --</option>');
            $.each(data, function (i, postal_code) {
                $('#user_postal_code').append('<option value="' + postal_code.id + '">' + postal_code.postal_code + '</option>');
            });
        });
        }
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
$(document).ready(function() {
    $('#same_address').on('change', function() {
        if ($(this).is(':checked')) {

            // Ambil data dari wilayah user
            let province = $('#user_province').val();
            let city = $('#user_city').val();
            let district = $('#user_district').val();
            let subdistrict = $('#user_sub_district').val();
            let postal = $('#user_postal_code').val();

            // Ambil data teks (untuk select2 append manual)
            let provinceText = $('#user_province option:selected').text();
            let cityText = $('#user_city option:selected').text();
            let districtText = $('#user_district option:selected').text();
            let subdistrictText = $('#user_sub_district option:selected').text();
            let postalText = $('#user_postal_code option:selected').text();

            // 1️⃣ Province
            $('#province').append(new Option(provinceText, province, true, true)).trigger('change.select2');

            // 2️⃣ Tunggu AJAX kota selesai, lalu isi City
            setTimeout(() => {
                $('#city').append(new Option(cityText, city, true, true)).trigger('change.select2');
            }, 400);

            // 3️⃣ Isi District
            setTimeout(() => {
                $('#district').append(new Option(districtText, district, true, true)).trigger('change.select2');
            }, 800);

            // 4️⃣ Isi SubDistrict
            setTimeout(() => {
                $('#sub_district').append(new Option(subdistrictText, subdistrict, true, true)).trigger('change.select2');
            }, 1200);

            // 5️⃣ Isi Postal Code
            setTimeout(() => {
                $('#postal_code').append(new Option(postalText, postal, true, true)).trigger('change.select2');
            }, 1500);

            // Copy alamat, nama, dan telepon
            $('[name="shipping_address"]').val($('[name="address"]').val());
            $('[name="shipping_name"]').val($('[name="fullname"]').val());
            $('[name="shipping_phone"]').val($('[name="phone"]').val());

            // Disable field
            $('#province, #city, #district, #sub_district, #postal_code, [name="shipping_address"], [name="shipping_name"], [name="shipping_phone"]')
                .attr('readonly', true)
                .addClass('bg-light text-muted');

        } else {
            // Aktifkan kembali jika user batal
            $('#province, #city, #district, #sub_district, #postal_code, [name="shipping_address"], [name="shipping_name"], [name="shipping_phone"]')
                .attr('readonly', false)
                .removeClass('bg-light text-muted');
        }
    });
});
</script>

<script>
    $(document).ready(function () {
    // 🟢 Ambil nilai lama (old) dari Blade
    let oldProvince  = "{{ old('user_province_id') }}";
    let oldCity      = "{{ old('user_city_id') }}";
    let oldDistrict  = "{{ old('user_district_id') }}";
    let oldSub       = "{{ old('user_sub_district_id') }}";
    let oldPostal    = "{{ old('user_postal_code_id') }}";

    if (oldProvince) {
    setTimeout(() => {
        loadCities(oldProvince, oldCity, function () {
            if (oldCity) loadDistricts(oldCity, oldDistrict, function () {
                if (oldDistrict) loadSubDistricts(oldDistrict, oldSub, function () {
                    if (oldSub) loadPostalCodes(oldSub, oldPostal);
                });
            });
        });
    }, 200); // tunggu 200ms
}


    console.log({
    oldProvince,
    oldCity,
    oldDistrict,
    oldSub,
    oldPostal
});


    // 🔹 Event saat provinsi berubah
    $('#user_province').on('change', function () {
        loadCities(this.value);
    });

    // 🔹 Event saat kota berubah
    $('#user_city').on('change', function () {
        loadDistricts(this.value);
    });

    // 🔹 Event saat kecamatan berubah
    $('#user_district').on('change', function () {
        loadSubDistricts(this.value);
    });

    // 🔹 Event saat kelurahan berubah
    $('#user_sub_district').on('change', function () {
        loadPostalCodes(this.value);
    });

    // ==============================
// Fungsi AJAX versi sesuai route kamu
// ==============================
function loadCities(provinceId, selected = null, callback = null) {
    if (!provinceId) return;
    $.get(`/api/cities/${provinceId}`, function (data) {
        $('#user_city').empty().append('<option value="">-- Pilih Kota --</option>');
        $.each(data, function (i, city) {
            $('#user_city').append(
                `<option value="${city.id}" ${selected == city.id ? 'selected' : ''}>${city.name}</option>`
            );
        });
        if (callback) callback();
    });
}

function loadDistricts(cityId, selected = null, callback = null) {
    if (!cityId) return;
    $.get(`/api/districts/${cityId}`, function (data) {
        $('#user_district').empty().append('<option value="">-- Pilih Kecamatan --</option>');
        $.each(data, function (i, district) {
            $('#user_district').append(
                `<option value="${district.id}" ${selected == district.id ? 'selected' : ''}>${district.name}</option>`
            );
        });
        if (callback) callback();
    });
}

function loadSubDistricts(districtId, selected = null, callback = null) {
    if (!districtId) return;
    $.get(`/api/sub_districts/${districtId}`, function (data) {
        $('#user_sub_district').empty().append('<option value="">-- Pilih Kelurahan --</option>');
        $.each(data, function (i, sub) {
            $('#user_sub_district').append(
                `<option value="${sub.id}" ${selected == sub.id ? 'selected' : ''}>${sub.name}</option>`
            );
        });
        if (callback) callback();
    });
}

function loadPostalCodes(subId, selected = null) {
    if (!subId) return;
    $.get(`/api/postal_codes/${subId}`, function (data) {
        $('#user_postal_code').empty().append('<option value="">-- Pilih Kode Pos --</option>');
        $.each(data, function (i, code) {
            $('#user_postal_code').append(
                `<option value="${code.id}" ${selected == code.id ? 'selected' : ''}>${code.postal_code}</option>`
            );
        });
    });
}

});
</script>

<script>
    $(document).ready(function () {
    // 🟢 Ambil nilai lama (old) dari Blade
    let oldProvince  = "{{ old('province_id') }}";
    let oldCity      = "{{ old('city_id') }}";
    let oldDistrict  = "{{ old('district_id') }}";
    let oldSub       = "{{ old('sub_district_id') }}";
    let oldPostal    = "{{ old('postal_code_id') }}";

    if (oldProvince) {
    setTimeout(() => {
        loadCities(oldProvince, oldCity, function () {
            if (oldCity) loadDistricts(oldCity, oldDistrict, function () {
                if (oldDistrict) loadSubDistricts(oldDistrict, oldSub, function () {
                    if (oldSub) loadPostalCodes(oldSub, oldPostal);
                });
            });
        });
    }, 200); // tunggu 200ms
}


    console.log({
    oldProvince,
    oldCity,
    oldDistrict,
    oldSub,
    oldPostal
});


    // 🔹 Event saat provinsi berubah
    $('#province').on('change', function () {
        loadCities(this.value);
    });

    // 🔹 Event saat kota berubah
    $('#city').on('change', function () {
        loadDistricts(this.value);
    });

    // 🔹 Event saat kecamatan berubah
    $('#district').on('change', function () {
        loadSubDistricts(this.value);
    });

    // 🔹 Event saat kelurahan berubah
    $('#sub_district').on('change', function () {
        loadPostalCodes(this.value);
    });

    // ==============================
// Fungsi AJAX versi sesuai route kamu
// ==============================
function loadCities(provinceId, selected = null, callback = null) {
    if (!provinceId) return;
    $.get(`/api/cities/${provinceId}`, function (data) {
        $('#city').empty().append('<option value="">-- Pilih Kota --</option>');
        $.each(data, function (i, city) {
            $('#city').append(
                `<option value="${city.id}" ${selected == city.id ? 'selected' : ''}>${city.name}</option>`
            );
        });
        if (callback) callback();
    });
}

function loadDistricts(cityId, selected = null, callback = null) {
    if (!cityId) return;
    $.get(`/api/districts/${cityId}`, function (data) {
        $('#district').empty().append('<option value="">-- Pilih Kecamatan --</option>');
        $.each(data, function (i, district) {
            $('#district').append(
                `<option value="${district.id}" ${selected == district.id ? 'selected' : ''}>${district.name}</option>`
            );
        });
        if (callback) callback();
    });
}

function loadSubDistricts(districtId, selected = null, callback = null) {
    if (!districtId) return;
    $.get(`/api/sub_districts/${districtId}`, function (data) {
        $('#sub_district').empty().append('<option value="">-- Pilih Kelurahan --</option>');
        $.each(data, function (i, sub) {
            $('#sub_district').append(
                `<option value="${sub.id}" ${selected == sub.id ? 'selected' : ''}>${sub.name}</option>`
            );
        });
        if (callback) callback();
    });
}

function loadPostalCodes(subId, selected = null) {
    if (!subId) return;
    $.get(`/api/postal_codes/${subId}`, function (data) {
        $('#postal_code').empty().append('<option value="">-- Pilih Kode Pos --</option>');
        $.each(data, function (i, code) {
            $('#postal_code').append(
                `<option value="${code.id}" ${selected == code.id ? 'selected' : ''}>${code.postal_code}</option>`
            );
        });
    });
}

});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    // Ambil old value dari Blade (jika ada)
    const oldBankId = "{{ old('bank_id') }}";

    fetch(`${window.location.origin}/api/banks`)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('bank_id');
            data.forEach(bank => {
                const option = document.createElement('option');
                option.value = bank.id;
                option.text = `${bank.name} (${bank.code})`;

                // Cek apakah bank.id sama dengan oldBankId
                if (bank.id == oldBankId) {
                    option.selected = true;
                }

                select.appendChild(option);

                $('#bank_id').val(oldBankId).trigger('change');
            });
        });
});
</script>
<script>
$('#phone_number').on('input', function () {
    let v = $(this).val().replace(/\D/g, '').replace(/^0+/, ''); // hanya angka, buang 0 di depan
    if ($('#phone_code').val() === '62' && v.startsWith('62')) v = v.slice(2); // paste +62...
    $(this).val(v);
});

$('#phone_code').on('change', function () {
    $('#phone_number').trigger('input');
});
</script>
@endpush