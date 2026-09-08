@extends('tablar::page')

@section('content')
    <div class="page-header d-print-none mb-4">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col d-flex align-items-center">
                    <a href="{{ route('accounting.index') }}" class="btn btn-dark d-flex align-items-center">
                        <i class="ti ti-arrow-left"></i>
                    </a>      
                        <h2 class="page-title mb-0">Edit Akun Akuntansi</h2> 
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
                            <form action="{{ route('accounting.update', $account->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <ul class="mb-0">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kode Akun</label>
                                        <input type="text" name="account_code" value="{{ $account->account_code }}" class="form-control" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nama Akun</label>
                                        <input type="text" name="account_name" value="{{ $account->account_name }}" class="form-control" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kategori</label>
                                        <select name="category" class="form-select select2" required>
                                            <option value="">-- Pilih Kategori --</option>
                                                    @foreach ($categories as $cat)
                                                        <option value="{{ $cat }}" {{ $account->category == $cat ? 'selected' : '' }}>
                                                            {{ $cat }}
                                                        </option>
                                                    @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Sub Kategori</label>
                                        <select name="sub_category" id="sub_category" class="form-select select2">
                                            <option value="">-- Pilih Sub kategori --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Apakah Akun Induk?</label>
                                        <select name="is_parent" class="form-select select2">
                                            <option value="1" {{ $account->is_parent ? 'selected' : '' }}>
                                                Ya (Akun Induk)
                                            </option>
                                            <option value="0" {{ !$account->is_parent ? 'selected' : '' }}>
                                                Tidak (Akun Child)
                                            </option>
                                        </select>
                                    </div>
                                

                                    <div class="col-md-6 mb-3" id="parent-field">
                                        <label class="form-label">Akun Induk</label>
                                        <select name="parent_id" class="form-select select2">
                                            <option value="">-- Pilih Akun Induk --</option>
                                            @foreach ($parentAccounts as $parent)
                                                <option value="{{ $parent->id }}" {{ $account->parent_id == $parent->id ? 'selected' : '' }}>
                                                    {{ $parent->account_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Saldo Awal</label>
                                        <input type="number" step="0.01" name="initial_balance" value="{{ $account->initial_balance }}" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">User</label>
                                        <select name="person_type" class="form-select select2" value="{{ old('person_type') }}">
                                            <option value="">-- Pilih --</option>
                                            <option value="employee" {{ old('person_type', $account->person_type) == 'employee' ? 'selected' : '' }}>Karyawan</option>
                                            <option value="customer" {{ old('person_type', $account->person_type) == 'customer' ? 'selected' : '' }}>Customer</option>
                                            <option value="worker" {{ old('person_type', $account->person_type) == 'worker' ? 'selected' : '' }}>Tukang</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="text-end mt-5">
                                            <button type="submit" class="btn btn-dark px-4">
                                                <i class="ti ti-device-floppy me-1"></i>Simpan Perubahan
                                            </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>   
@endsection
@push('js')
<script>
$(document).ready(function () {

    $('.select2').select2({
        width: '100%'
    });

    const selectIsParent = $('[name="is_parent"]');

    const subCategories = @json($subCategories);

    const selectedSub = @json($account->sub_category);

    function loadSub(category) {

        let options =
            '<option value="">-- Pilih Sub kategori --</option>';

        if (subCategories[category]) {

            subCategories[category].forEach(function (item) {

                let selected =
                    item === selectedSub
                        ? 'selected'
                        : '';

                options += `
                    <option value="${item}" ${selected}>
                        ${item}
                    </option>
                `;
            });
        }

        $('#sub_category')
            .html(options)
            .val(selectedSub)
            .trigger('change');
    }


    let category = $('[name="category"]').val();

    if (category) {
        loadSub(category);
    }


    $('[name="category"]').on('change', function () {

        loadSub($(this).val());

        // Setelah kategori berubah,
        // generate ulang kode
        generateCode();
    });


    /*
    |--------------------------------------------------------------------------
    | GENERATE ACCOUNT CODE
    |--------------------------------------------------------------------------
    */

    function generateCode() {

        let category = $('[name="category"]').val();

        let parentId = $('[name="parent_id"]').val();

        let isParent = $('[name="is_parent"]').val();


        if (!category) {

            $('#account_code_preview').val('');
            $('#account_code').val('');

            return;
        }

        $('#account_code_preview').val('Generating...');

        const params = new URLSearchParams({
            category: category || '',
            parent_id: parentId || '',
            is_parent: isParent || '',
            exclude_id: @json($account->id)

        });

        fetch(`/accounting/generate-code?${params.toString()}`)

            .then(res => res.json())

            .then(data => {

                $('#account_code_preview')
                    .val(data.code);

                $('#account_code')
                    .val(data.code);

            })

            .catch(error => {

                console.error(error);

                $('#account_code_preview').val('');

                $('#account_code').val('');

            });
    }

    $('[name="category"]').on(
        'change',
        generateCode
    );

    $('[name="parent_id"]').on(
        'change',
        generateCode
    );

    $('[name="is_parent"]').on(
        'change',
        generateCode
    );

});
</script>
@endpush