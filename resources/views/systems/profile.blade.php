@extends('tablar::page')

@section('content')

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card border-0 shadow rounded-4 overflow-hidden">
                    
                    {{-- Foto Profil di Atas --}}
                    


                    <div class="card-body px-4 pb-4">
                        <form action="{{ route('customer.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="text-center bg-light py-4 position-relative">
                                <div class="position-relative d-inline-block">
                                    @if ($user->photo)
                                        {{-- Kalau user punya foto --}}
                                        <img
                                            id="previewImage" 
                                            src="{{ asset('storage/'.$user->photo) }}" 
                                            alt="Profile" 
                                            class="border rounded-3 shadow-sm"
                                            width="150" height="150"
                                            style="object-fit: cover;">
                                    @else
                                        {{-- Kalau belum punya foto, tampilkan ikon --}}
                                        <div id="previewImage" 
                                            class="border rounded-3 shadow-sm d-flex align-items-center justify-content-center bg-light"
                                            style="width:150px; height:150px;">
                                            <i class="ti ti-user" style="font-size: 64px; color:#aaa;"></i>
                                        </div>
                                    @endif

                                    <label for="photo"
                                        class="btn btn-sm btn-primary position-absolute bottom-0 end-0 translate-middle rounded-circle"
                                        title="Ganti Foto">
                                        <i class="ti ti-camera"></i>
                                    </label>
                                </div>
                                <input type="file" id="photo" name="photo" class="d-none" accept="image/*">
                            </div>

                            




                            {{-- Informasi Pribadi --}}
                            <section class="pb-3 border-bottom mb-4">
                                <h3 class="fw-bold text-dark mb-3">Informasi Pribadi</h3>
                                <div class="row g-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="fullname" class="form-label">Nama Lengkap</label>
                                        <input type="text" name="fullname" id="fullname" class="form-control"
                                               value="{{ old('fullname', auth()->user()->fullname) }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nickname" class="form-label">Nama Panggilan</label>
                                        <input type="text" name="nickname" id="nickname" class="form-control"
                                               value="{{ old('nickname', auth()->user()->nickname) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                            <label class="required">Jenis Kelamin</label>
                                            <select name="gender" class="form-select" required>
                                                <option value="">-- Pilih Jenis Kelamin --</option>
                                                <option value="1" {{ $user->gender == 1 ? 'selected' : '' }}>Laki - Laki</option>
                                                <option value="2" {{ $user->gender == 2 ? 'selected' : '' }}>Perempuan</option>
                                            </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="birth_place" class="form-label">Tempat Lahir</label>
                                        <input type="text" name="birth_place" id="birth_place" class="form-control"
                                               value="{{ old('birth_place', auth()->user()->birth_place) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="required">Tanggal Lahir</label>
                                            <input type="date" name="birth_date" class="form-control" required
                                                value="{{ old('birth_date', auth()->user()->birth_date) }}"
                                                pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                            <label class="required" for="religion_id">Agama</label>
                                            <select name="religion_id" class="form-select" required>
                                                <option value="">-- Pilih Agama --</option>
                                                @foreach($religions as $religion)
                                                    <option value="{{ $religion->id }}" {{ old('religion_id', auth()->user()->religion_id) == $religion->id ? 'selected' : '' }}>
                                                        {{ $religion->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="identity_number" class="form-label">Nomor KTP</label>
                                        <input type="number" name="identity_number" id="identity_number" class="form-control"
                                               value="{{ old('identity_number', auth()->user()->identity_number) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="npwp" class="form-label">NPWP</label>
                                        <input type="number" name="npwp" id="npwp" class="form-control"
                                               value="{{ old('npwp', auth()->user()->npwp) }}">
                                    </div>
                                </div>
                            </section>

                            {{-- Kontak & Alamat --}}
                            <section class="pb-3 border-bottom mb-4">
                                <h3 class="fw-bold text-dark mb-3">Kontak & Alamat</h3>
                                <div class="row g-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Nomor Telepon</label>
                                        <input type="text" name="phone" id="phone" class="form-control"
                                               value="{{ old('phone', auth()->user()->phone) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" name="email" id="email" class="form-control"
                                               value="{{ old('email', auth()->user()->email) }}" readonly>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label for="address" class="form-label">Alamat Lengkap</label>
                                    <textarea name="address" id="address" rows="2" class="form-control">{{ old('address', auth()->user()->address) }}</textarea>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-6 mb-3">
                                        <label for="province" class="required">Provinsi</label>
                                        <select name="province_id" id="province" class="form-select select2" required>
                                                <option value="">-- Pilih Provinsi --</option>
                                                @foreach($provinces as $province)
                                                    <option value="{{ $province->id }}"
                                                        {{ $user->province_id == $province->id ? 'selected' : '' }}>
                                                        {{ $province->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="required">Kabupaten/Kota</label>
                                            <select name="city_id" id="city" class="form-select select2" required>
                                                <option value="">-- Pilih Kota --</option>
                                                @foreach($cities as $city)
                                                    <option value="{{ $city->id }}"
                                                        {{ $user->city_id == $city->id ? 'selected' : '' }}>
                                                        {{ $city->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="required">Kecamatan</label>
                                            <select name="district_id" id="district" class="form-select select2" required>
                                                <option value="">-- Pilih Kecamatan --</option>
                                                @foreach($districts as $district)
                                                    <option value="{{ $district->id }}"
                                                        {{ $user->district_id == $district->id ? 'selected' : '' }}>
                                                        {{ $district->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                       <label class="required">Kelurahan</label>
                                            <select name="sub_district_id" id="sub_district" class="form-select select2" required>
                                                <option value="">-- Pilih Desa --</option>
                                                @foreach($subDistricts as $sub_district)
                                                    <option value="{{ $sub_district->id }}"
                                                        {{ $user->sub_district_id == $sub_district->id ? 'selected' : '' }}>
                                                        {{ $sub_district->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="required">Kode Pos</label>
                                            <select name="postal_code_id" id="postal_code" class="form-select select2" required>
                                                <option value="">-- Pilih Desa --</option>
                                                @foreach($postalCodes as $postal_code)
                                                    <option value="{{ $postal_code->id }}"
                                                        {{ $user->postal_code_id == $postal_code->id ? 'selected' : '' }}>
                                                        {{ $postal_code->postal_code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                    </div>
                                </div>
                            </section>

                            {{-- Data Bank --}}
                            <section class="pb-3 mb-4">
                                <h3 class="fw-bold text-dark mb-3">Data Bank</h3>
                                <p class="small text-muted mt-2 mb-0">Diperlukan bila terjadi pengembalian dana</p>
                                <div class="row g-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="bank_id">Nama Bank</label>
                                            <select id="bank_id" name="bank_id" class="form-select">
                                                <option value="{{ old('bank_id', auth()->user()->bank_id) }}">Pilih Bank</option>
                                            </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="account_number" class="form-label">Nomor Rekening</label>
                                        <input type="number" name="account_number" id="account_number" class="form-control"
                                               value="{{ old('account_number', auth()->user()->account_number) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="account_holder" class="form-label">Atas Nama</label>
                                        <input type="text" name="account_holder" id="account_holder" class="form-control"
                                               value="{{ old('account_holder', auth()->user()->account_holder) }}">
                                    </div>
                                </div>
                            </section>

                            {{-- Tombol Aksi --}}
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-dark px-4">
                                    <i class="ti ti-check me-1"></i> Simpan & Kirim
                                </button>
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4 ms-2">
                                    <i class="ti ti-arrow-left me-1"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
$('.select2').select2({
            placeholder: "-- Pilih --",
            width: '100%'
        });
</script>

<script>
    document.getElementById('photo').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>

<style>
#previewImage {
    object-fit: cover;
    transition: 0.3s ease;
}
#previewImage:hover {
    opacity: 0.8;
    cursor: pointer;
}
</style>

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

