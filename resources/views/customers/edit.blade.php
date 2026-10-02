@extends('tablar::page')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('customers.index') }}" class="btn btn-dark d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>
                
                    <h2 class="page-title mb-0">Edit Data Customer</h2>
                
            </div>
        </div>
    </div>
</div>


<div class="page-body">
    <div class="container-xl">
        <div class="card shadow-sm border-0">
            <div class="card-body px-5 py-4">
                <form action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data">
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
                                <div class="col-md-2">
                                    <label class="form-label">Title</label>
                                    <select name="title" class="form-select select2">
                                        <option value="">-- Pilih --</option>
                                        <option value="1" {{ old('title', $user->title) == 1 ? 'selected' : '' }}>Bapak</option>
                                        <option value="2" {{ old('title', $user->title) == 2 ? 'selected' : '' }}>Ibu</option>
                                        <option value="3" {{ old('title', $user->title) == 3 ? 'selected' : '' }}>Kak</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label required">Nama Lengkap</label>
                                    <input type="text" name="fullname" class="form-control @error('fullname') is-invalid @enderror"  value="{{ old('fullname', $user->fullname) }}" required>
                                    @error('fullname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
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
                                    <select name="religion_id" class="form-select select2">
                                        <option value="">-- Pilih Agama --</option>
                                        @foreach($religions as $religion)
                                            <option value="{{ $religion->id }}" {{ old('religion_id', $user->religion_id) == $religion->id ? 'selected' : '' }}>
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
                                    <input type="text" name="identity_number" maxlength="16" inputmode="numeric" pattern="\d{16}" class="form-control @error('identity_number') is-invalid @enderror" value="{{ old('identity_number', $user->identity_number) }}">
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
                            </div>
                        </div>

                        <div class="section-block mb-5">
                            <h3 class="fw-semibold mb-3 border-bottom pb-2">📞 Kontak & Alamat</h3>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
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
                                        <select name="user_province_id" id="user_province" class="form-select select2">
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
                                                <select name="user_city_id" id="user_city" class="form-select select2">
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
                                                <select name="user_district_id" id="user_district" class="form-select select2">
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
                                                <select name="user_sub_district_id" id="user_sub_district" class="form-select select2">
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
                                                <select name="user_postal_code_id" id="user_postal_code" class="form-select select2">
                                                    <option value="">-- Pilih kelurahan --</option>
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
                                        <select id="bank_id" name="bank_id" class="form-select"
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
                        <h3 class="fw-semibold mb-3 border-bottom pb-2">Data Pelanggan</h3>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label required">Nomor Identitas Customer (NIC)</label>
                                <input type="text" id="nic" name="nic" class="form-control" value="{{ old('nic', $customer->nic) }}" readonly>
                                @error('nic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Kategori</label>
                                <select name="category" class="form-select select2">
                                    <option value="">-- Pilih --</option>
                                    <option value="1" {{ old('category', $customer->category) == 1 ? 'selected' : '' }}>Individu</option>
                                    <option value="2" {{ old('category', $customer->category) == 2 ? 'selected' : '' }}>Perusahaan</option>
                                    <option value="3" {{ old('category', $customer->category) == 3 ? 'selected' : '' }}>Instansi</option>
                                    <option value="4" {{ old('category', $customer->category) == 4 ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Loyalty Level</label>
                                <select name="loyalty_level" class="form-select select2">
                                    <option value="1" {{ old('loyalty_level', $customer->loyalty_level) == 1 ? 'selected' : '' }}>Lead</option>
                                    <option value="2" {{ old('loyalty_level', $customer->loyalty_level) == 2 ? 'selected' : '' }}>New Customer</option>
                                    <option value="3" {{ old('loyalty_level', $customer->loyalty_level) == 3 ? 'selected' : '' }}>Silver</option>
                                    <option value="4" {{ old('loyalty_level', $customer->loyalty_level) == 4 ? 'selected' : '' }}>Gold</option>
                                    <option value="5" {{ old('loyalty_level', $customer->loyalty_level) == 5 ? 'selected' : '' }}>Platinum</option>
                                </select>
                            </div>
                            
                            <div class="col-md-10">
                                <label class="form-label" for="role">Role:</label>
                                        <select class="form-control select2" name="role[]" multiple>
                                            @foreach (config('eksternal_roles.roles') as $role)
                                                <option value="{{ $role }}" 
                                                    {{ in_array($role, $selectedRoles ?? []) ? 'selected' : '' }}>
                                                    {{ ucfirst($role) }}
                                                </option>
                                            @endforeach
                                        </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="same_address" name="same_address"
                            @if(
                                $customer->province_id == $user->province_id &&
                                $customer->city_id == $user->city_id &&
                                $customer->district_id == $user->district_id &&
                                $customer->sub_district_id == $user->sub_district_id &&
                                $customer->postal_code_id == $user->postal_code_id
                            ) checked @endif>
                        <label class="form-check-label fw-semibold" for="same_address">
                            Alamat pengiriman sama dengan domisili pengguna
                        </label>
                    </div>

                    <div class="section-block mb-5">
                        <h3 class="fw-semibold mb-3 border-bottom pb-2">Alamat Pengiriman</h3>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Nama Penerima</label>
                                <input type="text" name="shipping_name" class="form-control @error('shipping_name') is-invalid @enderror" value="{{ old('shipping_name', $customer->shipping_name) }}">
                                @error('shiping_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Handphone</label>
                                <input type="number" name="shipping_phone" class="form-control @error('shipping_phone') is-invalid @enderror" value="{{ old('shipping_phone', $customer->shipping_phone) }}">
                                @error('shipping_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea name="shipping_address" rows="2" class="form-control @error('shipping_address') is-invalid @enderror">{{ old('shipping_address', $customer->shipping_address) }}</textarea>
                                @error('shipping_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                                    <option value="">-- Pilih kelurahan --</option>
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
$(document).ready(function() {

    function lockShippingFields(lock = true) {
        const fields = $('#province, #city, #district, #sub_district, #postal_code, [name="shipping_address"], [name="shipping_name"], [name="shipping_phone"]');
        if (lock) {
            fields.attr('readonly', true).addClass('bg-light text-muted');
        } else {
            fields.attr('readonly', false).removeClass('bg-light text-muted');
        }
    }

    // Saat checkbox diubah
    $('#same_address').on('change', function() {
        if ($(this).is(':checked')) {

            // Ambil data domisili user
            let province = $('#user_province').val();
            let city = $('#user_city').val();
            let district = $('#user_district').val();
            let subdistrict = $('#user_sub_district').val();
            let postal = $('#user_postal_code').val();

            let provinceText = $('#user_province option:selected').text();
            let cityText = $('#user_city option:selected').text();
            let districtText = $('#user_district option:selected').text();
            let subdistrictText = $('#user_sub_district option:selected').text();
            let postalText = $('#user_postal_code option:selected').text();

            // Isi select2 pengiriman langsung
            $('#province').append(new Option(provinceText, province, true, true)).trigger('change.select2');
            setTimeout(() => { $('#city').append(new Option(cityText, city, true, true)).trigger('change.select2'); }, 400);
            setTimeout(() => { $('#district').append(new Option(districtText, district, true, true)).trigger('change.select2'); }, 800);
            setTimeout(() => { $('#sub_district').append(new Option(subdistrictText, subdistrict, true, true)).trigger('change.select2'); }, 1200);
            setTimeout(() => { $('#postal_code').append(new Option(postalText, postal, true, true)).trigger('change.select2'); }, 1500);

            // Copy field teks
            $('[name="shipping_address"]').val($('[name="address"]').val());
            $('[name="shipping_name"]').val($('[name="fullname"]').val());
            $('[name="shipping_phone"]').val($('[name="phone"]').val());

            lockShippingFields(true);

        } else {
            lockShippingFields(false);
        }
    });

    // Saat halaman pertama kali dimuat (mode edit)
    if ($('#same_address').is(':checked')) {
        lockShippingFields(true);
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
                                    @endpush

                                    


