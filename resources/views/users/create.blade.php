@extends('tablar::page')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('users.index') }}" class="btn btn-dark d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>
                
                    <h2 class="page-title mb-0">Tambah Data Pengguna</h2>
                
            </div>
        </div>
    </div>
</div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card shadow-sm border-0">
                <div class="card-body px-5 py-4">
                    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
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
                                            <select name="title" class="form-select select2">
                                                <option value="">-- Pilih --</option>
                                                <option value="1" {{ old('title') == '1' ? 'selected' : '' }}>Bapak</option>
                                                <option value="2" {{ old('title') == '2' ? 'selected' : '' }}>Ibu</option>
                                                <option value="3" {{ old('title') == '3' ? 'selected' : '' }}>Kak</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label required" for="fullname">Nama Lengkap:</label>
                                            <input type="text" class="form-control @error('fullname') is-invalid @enderror" id="fullname" name="fullname" value="{{ old('fullname') }}" required>
                                            @error('fullname')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-3">
                                                <label class="form-label">Nama Panggilan</label>
                                                <input type="text" class="form-control @error('nickname') is-invalid @enderror" id="nickname" name="nickname" value="{{ old('nickname') }}">
                                                @error('nickname')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label">Jenis Kelamin</label>
                                            <select name="gender" class="form-select select2">
                                                <option value="">-- Pilih --</option>
                                                <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>Laki-laki</option>
                                                <option value="2" {{ old('gender') == '2' ? 'selected' : '' }}>Perempuan</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Tempat Lahir</label>
                                            <input type="text" class="form-control @error('birth_place') is-invalid @enderror" id="birth_place" name="birth_place" value="{{ old('birth_place') }}">
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
                                <div class="section-block mb-5">
                                    <h3 class="fw-semibold mb-3 border-bottom pb-2">📞 Kontak & Alamat</h3>
                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <label class="form-label">Telepon</label>
                                            <div class="input-group">
                                                <input type="text" name="phone"
                                                    class="form-control @error('phone') is-invalid @enderror"
                                                    value="{{ old('phone') }}"
                                                    placeholder="08xx atau +65 9123 4567"
                                                    inputmode="numeric" required>
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Nomor luar negeri awali dengan + dan kode negara.</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label required" for="email">Email</label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"name="email" value="{{ old('email') }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label required" for="password">Kata sandi </label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" value="{{ old('password') }}" placeholder="Kata sandi min. 8 karakter" required>
                                            @error('password')
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
                                        <div class="row g-4 mt-2">
                                            <div class="col-md-6">
                                                <label class="form-label">Provinsi</label>
                                                <select name="province_id" id="province" 
                                                        class="form-select select2 @error('province_id') is-invalid @enderror" 
                                                        >
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
                                                <select name="city_id" id="city" class="form-select select2">
                                                    <option value="">-- Pilih Kota --</option>       
                                                </select>
                                                @error('city_id')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">Kecamatan</label>
                                                <select name="district_id" id="district" class="form-select select2">
                                                    <option value="">-- Pilih Kecamatan --</option>  
                                                </select>
                                                @error('district_id')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">Kelurahan</label>
                                                <select name="sub_district_id" id="sub_district" class="form-select select2">
                                                    <option value="">-- Pilih Kelurahan --</option> 
                                                </select>
                                                @error('sub_district_id')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Kode Pos</label>
                                                <select name="postal_code_id" id="postal_code" class="form-select select2">
                                                    <option value="">-- Pilih Kode Pos --</option>       
                                                </select>
                                                @error('postal_code_id')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                    {{-- <div class="row mb-4">

                                        <div class="col-md-6 mb-3">
                                            <label class="required" for="role">Role:</label>
                                            <select class="form-select select2" name="role[]" multiple required>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div> --}}

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
$('.select2').select2({
            width: '100%'
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