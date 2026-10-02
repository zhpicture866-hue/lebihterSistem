@extends('tablar::page')

@section('content')
    <div class="page-header d-print-none mb-4">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col d-flex align-items-center">
                    <a href="{{ route('journals.index') }}" class="btn btn-dark d-flex align-items-center">
                        <i class="ti ti-arrow-left"></i>
                    </a>      
                        <h2 class="page-title mb-0">Tambah Jurnal</h2> 
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
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <strong>Terjadi Kesalahan!</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('journals.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                    <div class="row mb-3 align-items-center">
                                        {{-- @if(auth()->user()->hasRole('Super-Admin'))
                                            <div class="col-md-4 mb-3">
                                                <label for="license_id" class="form-label required">Filter Lisensi</label>
                                                <select name="license_id" id="license_id" class="form-select select2" required>
                                                    <option value="">-- Pilih Lisensi --</option>
                                                    {{-- @foreach ($licenses as $license)
                                                        <option value="{{ $license->id }}" 
                                                            {{ $activeLicenseId == $license->id ? 'selected' : '' }}>
                                                            {{ $license->name }}
                                                        </option>
                                                    @endforeach 
                                                </select>
                                            </div>
                                        @else
                                            
                                            <input type="hidden" name="license_id" value="{{ $activeLicenseId }}">
                                        @endif --}}
                                        {{-- <input type="hidden" 
                                            name="license_id" 
                                            id="activeLicenseId" 
                                            value="{{ $activeLicenseId }}"> --}}
                                        <div class="col-md-4 mb-3">
                                            <label for="journal_code" class="required">No Transaksi</label>
                                            <input type="text" id="journal_code" name="journal_code" 
                                                class="form-control" value="{{ $journalCode }}" readonly>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="transaction_date" class="required">Tanggal Transaksi</label>
                                            <input type="date" name="transaction_date" id="transaction_date" class="form-control" required>
                                            <small id="period-warning" class="text-danger d-none">
                                                ⚠️ Periode sudah ditutup
                                            </small>
                                        </div>
                                    </div>
                        
                                <h5>Detail Akun</h5>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="width:20%">Akun</th>
                                                <th style="width:20%">Deskripsi</th>
                                                <th style="width:20%">User</th>
                                                <th style="width:10%">Debit</th>
                                                <th style="width:10%">Kredit</th>
                                                <th style="width:5%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detail-rows">
                                            <tr>
                                                <td>
                                                    <select name="details[0][account_id]" class="form-select select2 account-select" data-row="0" required>
                                                        <option value="">-- Pilih Akun --</option>
                                                            @foreach ($accounts as $account)
                                                                <option value="{{ $account->id }}"
                                                                    data-code="{{ $account->account_code }}"
                                                                    data-name="{{ $account->account_name }}"
                                                                    data-person-type="{{ $account->person_type }}">
                                                                    {{ $account->account_code }} - {{ $account->account_name }}
                                                                </option>
                                                            @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="text" name="details[0][description]" class="form-control"></td>
                                                <td>
                                                    <select name="details[0][person]" class="form-select select2 user-select" data-row="0">
                                                        <option value="">-- Pilih User --</option>
                                                    </select>
                                                </td>
                                                <td><input type="text" name="details[0][debit]" class="form-control debit-input"></td>
                                                <td><input type="text" name="details[0][credit]" class="form-control credit-input"></td>
                                                <td><button type="button" class="btn btn-sm btn-dark remove-row" title="Hapus">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="6"><button type="button" id="add-row" class="btn btn-sm btn-dark text-white">Tambah Baris</button></td>
                                            </tr>
                                            <tr>
                                                <th colspan="3">Subtotal</th>
                                                <th id="subtotal-debit">0</th>
                                                <th id="subtotal-credit">0</th>
                                                <th colspan="3"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    @php
                                        $isBalanced = $journal->details->sum('debit') == $journal->details->sum('credit');
                                    @endphp
                                    <div id="balance-status" 
                                        class="mt-2 fw-bold {{ $isBalanced ? 'text-success' : 'text-danger' }}">
                                        {{ $isBalanced ? '✅ Seimbang' : '❌ Tidak Seimbang' }}
                                    </div>

                                <div class="col-md-4 mb-3">
                                    <label for="description">Keterangan</label>
                                    <textarea name="description" class="form-control"></textarea>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Lampiran</label>

                                    <input
                                        type="file"
                                        name="enclosure[]"
                                        id="enclosure"
                                        class="form-control"
                                        multiple
                                        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx"
                                    >

                                    <div id="previewContainer" class="row mt-3"></div>

                                    @error('enclosure')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                    @error('enclosure.*')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                </div>
                                
                                <div class="text-end mt-5">
                                    <button type="submit" class="btn btn-dark px-4">
                                        <i class="ti ti-device-floppy me-1"></i> Simpan Data
                                    </button>
                                </div>

                                {{-- @if(!auth()->user()->hasRole('Super-Admin'))
                                    <input type="hidden" id="activeLicenseId" value="{{ $activeLicenseId }}">
                                @endif --}}

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

    $('.select2').select2({ placeholder: "-- Pilih --", width: '100%' });

    let accountsData = [];

    function loadAccounts() {
        $.get(`/get-accounts`, function (data) {
            accountsData = data;

            $('.account-select').each(function () {
                renderAccountOptions($(this));
            });
        });

        $('#journal_code').val('Loading...');
        $.get(`/ajax/journals/next-code`)
            .done(function (res) {
                $('#journal_code').val(res.next_code);
            })
            .fail(function () {
                $('#journal_code').val('');
                alert('Gagal mengambil kode jurnal');
            });
    }

    function renderAccountOptions($select) {
        $select.empty().append('<option value="">-- Pilih Akun --</option>');

        $.each(accountsData, function (_, account) {
            $select.append(`
                <option value="${account.id}" 
                        data-code="${account.account_code}" 
                        data-person-type="${account.person_type}">
                    ${account.account_code} - ${account.account_name}
                </option>
            `);
        });

        $select.select2({
            placeholder: "-- Pilih Akun --",
            width: '100%',

            templateSelection: function (data) {

                if (!data.text) return data.text;

                return data.text.length > 50
                    ? data.text.substring(0, 50) + '...'
                    : data.text;
            }
        });
    }

    const userCache = {};

    function renderUserOptions($select, type) {

        if ($select.hasClass("select2-hidden-accessible")) {
            $select.select2("destroy");
        }

        $select.empty().append('<option value="">-- Pilih User --</option>');

        let url = '';
        if (type === "employee") url = '/get-employees';
        else if (type === "customer") url = '/get-customers';
        else if (type === "worker") url = '/get-workers';
        else if (type === "license") url = '/get-licenses';

        if (url) {
            if (userCache[type]) {
                appendUsers($select, userCache[type]);
                initSelect2WithCreate($select);
            } else {
                $.get(url, function (data) {
                    userCache[type] = data;
                    appendUsers($select, data);
                    initSelect2WithCreate($select);
                });
            }
        } else {
            initSelect2WithCreate($select);
        }
    }

    function appendUsers($select, data) {
        $.each(data, function (_, user) {
            $select.append(`<option value="${user.id}">${user.name}</option>`);
        });
    }

    function initSelect2WithCreate($select) {
        if ($select.hasClass("select2-hidden-accessible")) return;

        $select.select2({
            placeholder: "-- Input manual jika tidak ada User --",
            width: '100%',
            tags: true,
        });
    }

    loadAccounts();

    $('#add-row').click(function () {
        const rowCount = $('#detail-rows tr').length;

        const newRow = `
            <tr>
                <td>
                    <select name="details[${rowCount}][account_id]" 
                            class="form-select account-select" required></select>
                </td>
                <td><input type="text" name="details[${rowCount}][description]" class="form-control"></td>
                <td>
                    <select name="details[${rowCount}][person]" 
                            class="form-select user-select"></select>
                </td>
                <td><input type="text" name="details[${rowCount}][debit]" class="form-control debit-input"></td>
                <td><input type="text" name="details[${rowCount}][credit]" class="form-control credit-input"></td>
                <td>
                    <button type="button" class="btn btn-sm btn-dark remove-row">
                        <i class="ti ti-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#detail-rows').append(newRow);

        renderAccountOptions($('#detail-rows tr:last .account-select'));
        initSelect2WithCreate($('#detail-rows tr:last .user-select'));
    });

    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        calculateSubtotals();
    });

    $(document).on('change', '.account-select', function () {
        const $row = $(this).closest('tr');

        const selected = $(this).find(':selected');
        const personType = selected.data('person-type');
        const accountCode = String(selected.data('code') || "");

        const $debit  = $row.find('.debit-input');
        const $credit = $row.find('.credit-input');

        // reset
        $debit.prop('disabled', false).val('');
        $credit.prop('disabled', false).val('');

        // render user
        renderUserOptions($row.find('.user-select'), personType);

        const firstDigit = accountCode.charAt(0);

        if (firstDigit === "5" || firstDigit === "6") {
            // Aset + Beban → normal di DEBIT
            $credit.prop('disabled', true).val('');
        } 
        else if (firstDigit === "2" || firstDigit === "3" || firstDigit === "4") {
            // Hutang + Modal + Pendapatan → normal di KREDIT
            $debit.prop('disabled', true).val('');
        }

        if ($credit.is(':disabled')) {
            $credit.addClass('bg-light');
        } else {
            $credit.removeClass('bg-light');
        }

        if ($debit.is(':disabled')) {
            $debit.addClass('bg-light');
        } else {
            $debit.removeClass('bg-light');
        }
    });
    function formatRupiah(value) {

        value = value.replace(/[^\d]/g, '');

        return new Intl.NumberFormat('id-ID').format(value);
    }

    function parseRupiah(value) {

        return parseFloat(
            String(value).replace(/\./g, '')
        ) || 0;
    }

    function calculateSubtotals() {
        let totalDebit = 0;
        let totalCredit = 0;

        $('#detail-rows tr').each(function () {
            totalDebit += parseRupiah($(this).find('.debit-input').val());
            totalCredit += parseRupiah($(this).find('.credit-input').val());
        });

        $('#subtotal-debit').text(totalDebit.toLocaleString('id-ID'));
        $('#subtotal-credit').text(totalCredit.toLocaleString('id-ID'));

        const isBalanced = totalDebit === totalCredit;

        $('#balance-status')
            .text(isBalanced ? '✅ Seimbang' : '❌ Tidak Seimbang')
            .toggleClass('text-success', isBalanced)
            .toggleClass('text-danger', !isBalanced);
    }

    $(document).on('input', '.debit-input, .credit-input', function () {

        let raw = $(this).val().replace(/[^\d]/g, '');

        $(this).val(formatRupiah(raw));

        if ($(this).hasClass('debit-input')) {
            $(this).closest('tr').find('.credit-input').val('');
        } else {
            $(this).closest('tr').find('.debit-input').val('');
        }

        calculateSubtotals();
    });

    $('form').on('submit', function (e) {

        let totalDebit = 0;
        let totalCredit = 0;

        $('#detail-rows tr').each(function () {

            totalDebit += parseRupiah(
                $(this).find('.debit-input').val()
            );

            totalCredit += parseRupiah(
                $(this).find('.credit-input').val()
            );
        });

        // Validasi balance
        if (totalDebit !== totalCredit) {

            e.preventDefault();

            alert('Transaksi tidak seimbang!');

            return false;
        }

        // Convert rupiah -> numeric sebelum submit
        $('.debit-input, .credit-input').each(function () {

            let numeric = parseRupiah($(this).val());

            $(this).val(numeric);
        });
    });

});
</script>
<script>
$('#transaction_date').on('change', function () {

    let selected = $(this).val();

    if (!selected) return;

    $.get('/check-period', { date: selected }, function(res) {

        if (res.closed) {
            $('#period-warning').removeClass('d-none');
            $('button[type="submit"]').prop('disabled', true);
        } else {
            $('#period-warning').addClass('d-none');
            $('button[type="submit"]').prop('disabled', false);
        }

    });

});
</script>
<script>
document.getElementById('enclosure').addEventListener('change', function(e){

    const container = document.getElementById('previewContainer');
    container.innerHTML = '';

    [...e.target.files].forEach(file => {

        const ext = file.name.split('.').pop().toLowerCase();

        const col = document.createElement('div');
        col.className = 'col-md-4 mb-3';

        let html = '';

        // Image
        if (file.type.startsWith('image/')) {

            html = `
                <div class="card">
                    <img src="${URL.createObjectURL(file)}"
                        class="card-img-top"
                        style="height:180px;object-fit:cover">

                    <div class="card-body p-2">
                        <small>${file.name}</small>
                    </div>
                </div>
            `;

        }

        // PDF
        else if(ext === 'pdf'){

            html = `
                <div class="card">
                    <embed
                        src="${URL.createObjectURL(file)}"
                        type="application/pdf"
                        width="100%"
                        height="180px">

                    <div class="card-body p-2">
                        <small>${file.name}</small>
                    </div>
                </div>
            `;
        }

        // Word
        else if(['doc','docx'].includes(ext)){

            html = `
                <div class="card text-center p-4">
                    <i class="ti ti-file-word text-primary"
                       style="font-size:60px"></i>

                    <small class="mt-2">${file.name}</small>
                </div>
            `;
        }

        // Excel
        else if(['xls','xlsx'].includes(ext)){

            html = `
                <div class="card text-center p-4">
                    <i class="ti ti-file-spreadsheet text-success"
                       style="font-size:60px"></i>

                    <small class="mt-2">${file.name}</small>
                </div>
            `;
        }

        // lainnya
        else{

            html = `
                <div class="card text-center p-4">
                    <i class="ti ti-file"
                       style="font-size:60px"></i>

                    <small class="mt-2">${file.name}</small>
                </div>
            `;
        }

        col.innerHTML = html;
        container.appendChild(col);

    });

});
</script>
@endpush