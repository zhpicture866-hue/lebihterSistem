@extends('tablar::page')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('journals.index') }}" class="btn btn-dark d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>      
                    <h2 class="page-title mb-0">Edit Jurnal</h2> 
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

                        <form action="{{ route('journals.update', $journal->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            
                            <div class="row mb-3 align-items-center">
                                {{-- @if(auth()->user()->hasRole('Super-Admin'))
                                    <div class="col-md-4 mb-3">
                                        <label for="license_id" class="form-label">Pilih Lisensi</label>
                                        <select name="license_id" id="license_id" class="form-select" disabled>
                                            @foreach ($licenses as $license)
                                                <option value="{{ $license->id }}" 
                                                    {{ $journal->license_id == $license->id ? 'selected' : '' }}>
                                                    {{ $license->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="license_id" value="{{ $journal->license_id }}">
                                    </div>
                                @endif --}}

                                <div class="col-md-4 mb-3">
                                    <label for="journal_code" class="required">No Transaksi</label>
                                    <input type="text" name="journal_code" 
                                        class="form-control" value="{{ old('journal_code', $journal->journal_code) }}" readonly>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="transaction_date">Tanggal Transaksi</label>
                                    <input type="date" name="transaction_date" class="form-control"
                                        value="{{ old('transaction_date', $journal->transaction_date) }}" required>
                                </div>
                            </div>

                            <h4>Detail Akun</h4>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered jurnal-table">
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
                                        @if(isset($journal) && $journal->details)
                                            @foreach ($journal->details as $i => $detail)
                                                <tr>
                                                    <td>
                                                        <select name="details[{{ $i }}][account_id]" 
                                                                class="form-select select2 account-select" 
                                                                data-row="{{ $i }}" required>
                                                            <option value="{{ $detail->account_id }}" 
                                                                data-code="{{ $detail->account->account_code }}"
                                                                data-person-type="{{ $detail->account->person_type }}" 
                                                                selected>
                                                                {{ $detail->account->account_code }} - {{ $detail->account->account_name }}
                                                            </option>
                                                        </select>
                                                    </td>

                                                    {{-- Deskripsi --}}
                                                    <td>
                                                        <input type="text" 
                                                            name="details[{{ $i }}][description]" 
                                                            class="form-control"
                                                            value="{{ old("details.$i.description", $detail->description) }}">
                                                    </td>

                                                    <td>
                                                        <select name="details[{{ $i }}][person]" 
                                                                class="form-select user-select" 
                                                                data-row="{{ $i }}" 
                                                                data-selected="{{ $detail->person ?? '' }}"
                                                                data-selected-label="{{ $detail->person_name ?? '' }}">
                                                            <option value="">-- Pilih User --</option>
                                                        </select>
                                                            @php
                                                                if ($detail->person_type === 'team') {
                                                                    $users = $teams;
                                                                } elseif ($detail->person_type === 'member') {
                                                                    $users = $members;
                                                                } elseif ($detail->person_type === 'mitra') {
                                                                    $users = $partners;
                                                                } else {
                                                                    $users = collect();
                                                                }
                                                            @endphp
                                                            @foreach ($users as $u)
                                                                <option value="{{ $u->id }}" {{ $u->id == $detail->person ? 'selected' : '' }}>
                                                                    {{ $u->fullname }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    
                                                    <td>
                                                        <input type="text"
                                                            name="details[{{ $i }}][debit]" 
                                                            class="form-control debit-input" 
                                                            value="{{ old("details.$i.debit", $detail->debit) }}"
                                                            {{ $detail->credit ? 'disabled' : '' }}>

                                                        @if($detail->credit)
                                                            <input type="hidden" name="details[{{ $i }}][debit]" value="{{ $detail->debit }}">
                                                        @endif
                                                    </td>

                                                    
                                                    <td>
                                                        <input type="text" 
                                                            name="details[{{ $i }}][credit]" 
                                                            class="form-control credit-input" 
                                                            value="{{ old("details.$i.credit", $detail->credit) }}"
                                                            {{ $detail->debit ? 'disabled' : '' }}>

                                                        @if($detail->debit)
                                                            <input type="hidden" name="details[{{ $i }}][credit]" value="{{ $detail->credit }}">
                                                        @endif
                                                    </td>
                                                    <td><button type="button" class="btn btn-sm btn-dark remove-row" title="Hapus">
                                                                <i class="ti ti-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <td colspan="6"><button type="button" id="add-row" class="btn btn-sm btn-dark text-black">Tambah Baris</button></td>
                                        </tr>
                                        <tr>
                                            <th colspan="3">Subtotal</th>
                                            <th id="subtotal-debit">{{ $journal->details->sum('debit') }}</th>
                                            <th id="subtotal-credit">{{ $journal->details->sum('credit') }}</th>
                                            <th colspan="3"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            @php
                                $isBalanced = $journal->details->sum('debit') == $journal->details->sum('credit');
                            @endphp

                            <div id="balance-status" 
                                class="mt-2 fw-bold {{ $isBalanced ? 'text-success' : 'text-danger' }}">
                                {{ $isBalanced ? '✅ Seimbang' : '❌ Tidak Seimbang' }}
                            </div>

                            <div class="col-md-6 mb-3">
                                    <label for="description">Keterangan</label>
                                    <textarea name="description" class="form-control">{{ old('description', $journal->description) }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tambah Lampiran</label>
                                <input type="file" name="enclosure[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                                @error('enclosure.*')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            @if($journal->enclosures->isNotEmpty())
                                <div class="row mt-3">
                                    @foreach($journal->enclosures as $enclosure)
                                        @php
                                            $ext = strtolower(pathinfo($enclosure->file_name, PATHINFO_EXTENSION));
                                        @endphp
                                        <div class="col-md-4 mb-3">
                                            <div class="card">
                                                <div class="card-body text-center position-relative">
                                                        <input
                                                            type="checkbox"
                                                            name="remove_enclosures[]"
                                                            value="{{ $enclosure->id }}"
                                                            class="remove-checkbox d-none">

                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-enclosure">
                                                            <i class="ti ti-x"></i>
                                                        </button>
                                                    @if(in_array($ext,['jpg','jpeg','png','gif','webp']))
                                                        <img
                                                            src="{{ asset('storage/'.$enclosure->file_name) }}"
                                                            class="img-fluid rounded"
                                                            style="height:180px;object-fit:cover">
                                                    @elseif($ext=='pdf')
                                                        <i class="ti ti-file-type-pdf text-danger"
                                                        style="font-size:60px"></i>
                                                        <p class="mt-2">
                                                            {{ basename($enclosure->file_name) }}
                                                        </p>
                                                    @elseif(in_array($ext,['doc','docx']))
                                                        <i class="ti ti-file-type-doc text-primary"
                                                        style="font-size:60px"></i>

                                                        <p class="mt-2">
                                                            {{ basename($enclosure->file_name) }}
                                                        </p>

                                                    @elseif(in_array($ext,['xls','xlsx']))

                                                        <i class="ti ti-file-type-xls text-success"
                                                        style="font-size:60px"></i>

                                                        <p class="mt-2">
                                                            {{ basename($enclosure->file_name) }}
                                                        </p>

                                                    @endif

                                                </div>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            @endif
                            <div class="text-end mt-5">
                                <button type="submit" class="btn btn-dark px-4">
                                    <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
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

                let selected = $(this).data('selected');

                renderAccountOptions($(this));

                if (selected) {
                    $(this).val(selected).trigger('change');
                }
            });
        });
    }

    function renderAccountOptions($select) {

        let selected = $select.data('selected') || $select.val();

        if ($select.hasClass("select2-hidden-accessible")) {
            $select.select2("destroy");
        }

        $select.empty().append(`
            <option value="">-- Pilih Akun --</option>
        `);

        $.each(accountsData, function (_, account) {

            $select.append(`
                <option value="${account.id}"
                        data-code="${account.account_code}"
                        data-person-type="${account.person_type}">
                    ${account.account_code} - ${account.account_name}
                </option>
            `);
        });

        $select.val(selected);

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

        let selectedText = $select.find(':selected').text();

        $select.next('.select2-container')
            .attr('title', selectedText);
    }

    function renderUserOptions($select, personType, selected = null, selectedLabel = null) {

        if ($select.hasClass("select2-hidden-accessible")) {
            $select.select2('destroy');
        }

        $select.empty().append('<option value="">-- Pilih User --</option>');

        let urlMap = {
            employee: '/get-employees',
            customer: '/get-customers',
            worker: '/get-workers',
            license: '/get-licenses'
        };

        const finishInit = (data = []) => {
            $.each(data, function (_, user) {
                $select.append(
                    `<option value="${user.id}" ${selected == user.id ? 'selected' : ''}>${user.name}</option>`
                );
            });

            const label = selectedLabel || selected;

            if (selected && !data.some(u => u.id == selected)) {
                $select.append(`<option value="${selected}" selected>${label}</option>`);
            }

            $select.select2({
                placeholder: "-- Input manual jika tidak ada User --",
                width: '100%',
                tags: true,
            });
        };

        if (!personType || !urlMap[personType]) {
            finishInit([]);
            return;
        }

        $.get(urlMap[personType], function (data) {
            finishInit(data);
        });
    }

    const debitOnly = new Set(["5","6"]);   
    const creditOnly = new Set(["2","3","4"]);  

    function applyDebitCreditRule($row, accountCode) {

        const firstDigit = accountCode.charAt(0);

        const $debit  = $row.find('.debit-input');
        const $credit = $row.find('.credit-input');

        // reset dulu
        $debit.prop('disabled', false);
        $credit.prop('disabled', false);

        // akun beban / biaya
        if (debitOnly.has(firstDigit)) {

            $credit.prop('disabled', true);

        } 
        // akun hutang / modal / pendapatan
        else if (creditOnly.has(firstDigit)) {

            $debit.prop('disabled', true);
        }

        syncHiddenInput($row);
    }

    function syncHiddenInput($row) {

        let debit = $row.find('.debit-input').val();
        let credit = $row.find('.credit-input').val();

        $row.find('.hidden-debit').remove();
        $row.find('.hidden-credit').remove();

        if ($row.find('.debit-input').prop('disabled')) {
            $row.append(`<input type="hidden" name="${$row.find('.debit-input').attr('name')}" value="${debit}" class="hidden-debit">`);
        }

        if ($row.find('.credit-input').prop('disabled')) {
            $row.append(`<input type="hidden" name="${$row.find('.credit-input').attr('name')}" value="${credit}" class="hidden-credit">`);
        }
    }

    $(document).on('change', '.account-select', function () {
        const $row = $(this).closest('tr');

        const accountCode = String($(this).find(':selected').data('code') || '');
        const personType  = $(this).find(':selected').data('person-type');

        renderUserOptions($row.find('.user-select'), personType);

        applyDebitCreditRule($row, accountCode);
        syncHiddenInput($row);
    });

    $('.account-select').each(function () {
        const $row = $(this).closest('tr');

        const accountCode = String($(this).find(':selected').data('code') || '');
        const personType  = $(this).find(':selected').data('person-type');
        const $userSelect = $row.find('.user-select');

        const selectedUser  = $userSelect.data('selected');
        const selectedLabel = $userSelect.data('selected-label'); // <-- tambahkan ini

        renderUserOptions($userSelect, personType, selectedUser, selectedLabel); // <-- tambah param ke-4

        applyDebitCreditRule($row, accountCode);
    });

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
                            class="form-select select2 user-select"></select>
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

        const $newRow = $('#detail-rows tr:last');

        renderAccountOptions($newRow.find('.account-select'));

        renderUserOptions($newRow.find('.user-select'), null);
    });

    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        calculateSubtotals();
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
        let totalDebit = 0, totalCredit = 0;

        $('#detail-rows tr').each(function() {
            totalDebit  += parseRupiah($(this).find('.debit-input').val()) || 0;
            totalCredit += parseRupiah($(this).find('.credit-input').val()) || 0;
        });

        $('#subtotal-debit').text(totalDebit.toLocaleString('id-ID'));
        $('#subtotal-credit').text(totalCredit.toLocaleString('id-ID'));

        if (totalDebit === totalCredit && totalDebit > 0) {
            $('#balance-status').text('✅ Seimbang').css('color', 'green');
        } else {
            $('#balance-status').text('❌ Tidak Seimbang').css('color', 'red');
        }
    }

    $(document).on('input', '.debit-input, .credit-input', function () {

        let $row = $(this).closest('tr');

        let raw = $(this).val().replace(/[^\d]/g, '');

        $(this).val(formatRupiah(raw));

        if ($(this).hasClass('debit-input')) {
            $row.find('.credit-input').val('');
        } else {
            $row.find('.debit-input').val('');
        }

        calculateSubtotals();
    });

    $('form').on('submit', function (e) {

        let totalDebit = 0;
        let totalCredit = 0;

        $('#detail-rows tr').each(function () {
            syncHiddenInput($(this));

            totalDebit += parseRupiah(
                $(this).find('.debit-input').val()
            );

            totalCredit += parseRupiah(
                $(this).find('.credit-input').val()
            );
        });

        if (totalDebit !== totalCredit) {

            e.preventDefault();

            alert('Transaksi tidak seimbang!');

            return false;
        }

        $('.debit-input, .credit-input').each(function () {

            let numeric = parseRupiah($(this).val());

            $(this).val(numeric);
        });
    });

    loadAccounts();

    $('.debit-input, .credit-input').each(function () {

        let value = $(this).val();

        if (value) {
            $(this).val(formatRupiah(value));
        }
    });

    calculateSubtotals();
    $(document).on('click', '.remove-enclosure', function () {

        const $card = $(this).closest('.card');

        Swal.fire({
            title: 'Hapus lampiran?',
            text: 'Lampiran akan dihapus setelah Anda menyimpan perubahan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'
        }).then((result) => {

            if (result.isConfirmed) {

                $card.find('.remove-checkbox').prop('checked', true);

                $card.fadeOut();

            }

        });

    });
});
</script>

@endpush