@php
    $offerTotal = (float) ($project->rab?->grand_total ?? 0);
 
    // Data lama (setelah validasi gagal) supaya baris termin tidak hilang.
    $oldPercentages  = (array) old('percentage', ['']);
    $oldAmounts      = (array) old('amount', []);
    $oldDescriptions = (array) old('termin_description', []);
    $oldBillingDates = (array) old('billing_date', []);
 
    $initialRows = [];
 
    foreach ($oldPercentages as $i => $percentage) {
        $initialRows[] = [
            'percentage'   => $percentage,
            'amount'       => $oldAmounts[$i] ?? '',
            'description'  => $oldDescriptions[$i] ?? '',
            'billing_date' => $oldBillingDates[$i] ?? '',
        ];
    }
@endphp
@can('lihat daftar proyek')
<form
    action="{{ route('projects.build-termin.store', $project->id) }}"
    method="POST"
    id="build-termin-form"
    enctype="multipart/form-data"
>
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

    @if ($offerTotal <= 0)
        <div class="alert alert-warning">
            Total penawaran harga belum tersedia. Lengkapi RAB terlebih dahulu
            sebelum mengatur termin.
        </div>
    @endif

    <div class="card border-0 bg-light mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small mb-1">
                        TOTAL PENAWARAN HARGA
                    </div>

                    <div class="fs-2 fw-bold text-dark">
                        Rp {{ number_format($offerTotal, 0, ',', '.') }}
                    </div>

                    <div class="text-muted small mt-1">
                        Nilai ini menjadi dasar perhitungan setiap termin.
                    </div>
                </div>

                <div class="avatar avatar-lg bg-white shadow-sm">
                    <i class="ti ti-receipt-2 fs-2"></i>
                </div>
            </div>
        </div>
    </div>

    <input
        type="hidden"
        id="build-offer-total"
        value="{{ $offerTotal }}"
    >

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="mb-1 fw-bold">
                Setting Termin Pembayaran
            </h3>

            <div class="text-muted">
                Atur pembagian pembayaran berdasarkan persentase termin.
            </div>
        </div>

        <button
            type="button"
            id="btn-add-termin"
            class="btn btn-dark"
            title="Tambah Termin"
        >
            <i class="ti ti-plus me-1"></i>
        </button>
    </div>

    {{-- Baris termin dirender oleh JavaScript dari template di bawah --}}
    <div id="termin-container"></div>

    <template id="termin-row-template">
        <div class="card border-0 shadow-sm mb-3 termin-card termin-row">
            <div class="card-body p-3">
                <div class="row g-3 align-items-end">

                    {{-- NOMOR --}}
                    <div class="col-md-1">
                        <label class="form-label text-muted small">Termin</label>
                        <div class="termin-number">
                            <span class="termin-no">1</span>
                        </div>
                    </div>

                    {{-- PERSENTASE --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Persentase</label>
                        <div class="input-group">
                            <input type="number" name="percentage[]" class="form-control termin-percentage"
                                min="0" max="100" step="0.01" placeholder="30" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    {{-- NOMINAL --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Nominal Pembayaran</label>
                        <input type="text" class="form-control termin-amount fw-bold" placeholder="Rp 0"
                            inputmode="numeric" autocomplete="off">
                        <input type="hidden" name="amount[]" class="termin-amount-value" value="">
                    </div>

                    {{-- KETERANGAN --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Keterangan</label>
                        <input type="text" name="termin_description[]" class="form-control termin-description"
                            placeholder="Contoh: DP / Tahap 1 / Pelunasan">
                    </div>

                    {{-- TANGGAL PENAGIHAN --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Tanggal Penagihan</label>
                        <input type="date" name="billing_date[]" class="form-control termin-billing-date">
                    </div>

                    {{-- HAPUS --}}
                    <div class="col-md-1">
                        <button type="button" class="btn btn-dark btn-icon btn-remove-termin" title="Hapus Termin">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>

                </div>

                {{-- BARIS BARU: BUKTI PEMBAYARAN --}}
                {{-- <div class="row g-3 align-items-end mt-1">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">
                            Bukti Pembayaran
                        </label>
                        <input
                            type="file"
                            name="bukti_pembayaran[]"
                            class="form-control termin-bukti-pembayaran"
                            accept=".pdf,.jpg,.jpeg,.png"
                        >
                        <div class="form-hint mt-1 small text-muted">
                            Format: PDF, JPG, PNG. Maks. 5MB.
                        </div>
                    </div>
                </div> --}}

            </div>
        </div>
    </template>

    {{-- RINGKASAN --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4">

            <div class="row g-4">

                <div class="col-md-6">
                    <div class="summary-item">
                        <div class="summary-icon">
                            <i class="ti ti-percentage"></i>
                        </div>

                        <div>
                            <div class="text-muted small">
                                Total Persentase
                            </div>

                            <div
                                id="total-termin-percentage"
                                class="fs-3 fw-bold"
                            >
                                0%
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="summary-item">
                        <div class="summary-icon">
                            <i class="ti ti-cash"></i>
                        </div>

                        <div>
                            <div class="text-muted small">
                                Total Nominal
                            </div>

                            <div
                                id="total-termin-amount"
                                class="fs-3 fw-bold"
                            >
                                Rp 0
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div
                id="termin-warning"
                class="alert alert-warning mt-4 mb-0 d-none"
            >
                <div class="d-flex align-items-center">
                    <i class="ti ti-alert-triangle me-2 fs-2"></i>

                    <div>
                        <div class="fw-bold">
                            Persentase belum lengkap
                        </div>

                        <div class="small">
                            Total persentase termin harus tepat 100%.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button
            type="submit"
            class="btn btn-dark px-4"
            id="btn-save-termin"
        >
            <i class="ti ti-device-floppy me-1"></i>
            Simpan Setting Termin
        </button>
    </div>

</form>
@endcan
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('build-termin-form');
    const container = document.getElementById('termin-container');
    const template = document.getElementById('termin-row-template');

    if (!form || !container || !template) {
        return;
    }

    const addButton = document.getElementById('btn-add-termin');
    const saveButton = document.getElementById('btn-save-termin');
    const warningElement = document.getElementById('termin-warning');
    const totalPercentageElement = document.getElementById('total-termin-percentage');
    const totalAmountElement = document.getElementById('total-termin-amount');

    const offerTotal = parseFloat(
        document.getElementById('build-offer-total')?.value || 0
    ) || 0;

    const initialRows = @json($initialRows);

    /* ---------- Helper ---------- */

    function formatRupiah(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0
        }).format(Math.round(Number(value) || 0));
    }

    function parseDigits(value) {
        return Number(String(value || '').replace(/\D/g, '')) || 0;
    }

    function getRows() {
        return container.querySelectorAll('.termin-row');
    }

    function fields(row) {
        return {
            percentage: row.querySelector('.termin-percentage'),
            amountDisplay: row.querySelector('.termin-amount'),
            amountValue: row.querySelector('.termin-amount-value'),
            description: row.querySelector('.termin-description'),
            billingDate: row.querySelector('.termin-billing-date'),
        };
    }

    /* ---------- Sinkronisasi persentase <-> nominal ---------- */

    function clearAmount(row) {
        const f = fields(row);
        f.amountDisplay.value = '';
        f.amountValue.value = '';
    }

    function setAmount(row, amount) {
        const f = fields(row);
        f.amountDisplay.value = formatRupiah(amount);
        f.amountValue.value = String(Math.round(amount));
    }

    // Persentase diubah -> hitung nominal.
    function syncAmountFromPercentage(row) {
        const f = fields(row);

        if (f.percentage.value === '') {
            clearAmount(row);
            return;
        }

        const percentage = parseFloat(f.percentage.value) || 0;

        setAmount(row, offerTotal * (percentage / 100));
    }

    // Nominal diubah -> hitung persentase. Nominal yang diketik user
    // tidak dihitung ulang, hanya diformat.
    function syncPercentageFromAmount(row) {
        const f = fields(row);
        const digits = String(f.amountDisplay.value).replace(/\D/g, '');

        if (digits === '') {
            clearAmount(row);
            f.percentage.value = '';
            return;
        }

        const amount = Number(digits);

        f.amountDisplay.value = formatRupiah(amount);
        f.amountValue.value = String(amount);

        f.percentage.value = offerTotal > 0
            ? ((amount / offerTotal) * 100).toFixed(2)
            : '';
    }

    /* ---------- Ringkasan & validasi ---------- */

    function updateTerminNumbers() {
        getRows().forEach(function (row, index) {
            const numberElement = row.querySelector('.termin-no');

            if (numberElement) {
                numberElement.textContent = index + 1;
            }
        });
    }

    function refreshSummary() {
        let totalPercentage = 0;
        let totalAmount = 0;

        getRows().forEach(function (row) {
            const f = fields(row);

            totalPercentage += parseFloat(f.percentage.value) || 0;
            totalAmount += Number(f.amountValue.value) || 0;
        });

        totalPercentageElement.textContent = totalPercentage.toFixed(2) + '%';
        totalAmountElement.textContent = formatRupiah(totalAmount);

        // Dibandingkan dalam satuan 0,01% agar bebas error floating point.
        const percentageComplete = Math.round(totalPercentage * 100) === 10000;

        warningElement.classList.toggle('d-none', percentageComplete);
        saveButton.disabled = !percentageComplete || offerTotal <= 0;

        return percentageComplete;
    }

    /* ---------- Tambah / hapus baris ---------- */

    function addRow(data) {
        data = data || {};

        const row = template.content
            .cloneNode(true)
            .querySelector('.termin-row');

        const f = fields(row);

        f.percentage.value = data.percentage ?? '';
        f.description.value = data.description ?? '';
        f.billingDate.value = data.billing_date ?? '';

        if (f.percentage.value !== '') {
            syncAmountFromPercentage(row);
        } else if (parseDigits(data.amount) > 0) {
            f.amountDisplay.value = String(data.amount);
            syncPercentageFromAmount(row);
        }

        container.appendChild(row);

        updateTerminNumbers();
        refreshSummary();
    }

    if (addButton) {
        addButton.addEventListener('click', function () {
            addRow();
        });
    }

    container.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.btn-remove-termin');

        if (!removeButton || getRows().length <= 1) {
            return;
        }

        removeButton.closest('.termin-row')?.remove();

        updateTerminNumbers();
        refreshSummary();
    });

    container.addEventListener('input', function (event) {
        const row = event.target.closest('.termin-row');

        if (!row) {
            return;
        }

        if (event.target.classList.contains('termin-percentage')) {
            syncAmountFromPercentage(row);
            refreshSummary();
            return;
        }

        if (event.target.classList.contains('termin-amount')) {
            syncPercentageFromAmount(row);
            refreshSummary();
        }
    });

    form.addEventListener('submit', function (event) {
        if (!refreshSummary()) {
            event.preventDefault();

            alert('Total persentase termin harus tepat 100%.');
        }
    });

    /* ---------- Inisialisasi ---------- */

    (initialRows.length ? initialRows : [{}]).forEach(addRow);
});
</script>
@endpush