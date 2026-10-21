@php
    $offerTotal = (float) ($project->rab?->grand_total ?? 0);

    // Kalau validasi gagal, tampilkan input terakhir user (old()).
    // Kalau tidak, tampilkan termin yang tersimpan di database.
    if (old('percentage') !== null) {

        $oldPercentages  = (array) old('percentage');
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

    } else {

        $initialRows = $project->buildTermins
            ->sortBy('termin_no')
            ->values()
            ->map(fn ($termin) => [
                'percentage'   => (float) $termin->percentage,
                'amount'       => (float) $termin->amount,
                'description'  => $termin->description,
                'billing_date' => $termin->billing_date?->format('Y-m-d'),
            ])
            ->all();
    }
@endphp

<form
    action="{{ route('projects.build-termin.update', $project->id) }}"
    method="POST"
    id="build-termin-edit-form"
>
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
        id="build-edit-offer-total"
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
            id="btn-add-edit-termin"
            class="btn btn-dark"
            title="Tambah Termin"
        >
            <i class="ti ti-plus me-1"></i>
        </button>
    </div>

    {{-- Baris termin dirender oleh JavaScript dari template di bawah --}}
    <div id="termin-edit-container"></div>

    <template id="termin-edit-row-template">
        <div class="card border-0 shadow-sm mb-3 termin-card termin-row">
            <div class="card-body p-3">
                <div class="row g-3 align-items-end">

                    {{-- NOMOR --}}
                    <div class="col-md-1">
                        <label class="form-label text-muted small">
                            Termin
                        </label>

                        <div class="termin-number">
                            <span class="termin-no">1</span>
                        </div>
                    </div>

                    {{-- PERSENTASE --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">
                            Persentase
                        </label>

                        <div class="input-group">
                            <input
                                type="number"
                                name="percentage[]"
                                class="form-control termin-percentage"
                                min="0.01"
                                max="100"
                                step="0.0001"
                                placeholder="30"
                                required
                            >

                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    {{-- NOMINAL --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">
                            Nominal Pembayaran
                        </label>

                        {{-- Input tampilan (berformat Rp), tanpa name --}}
                        <input
                            type="text"
                            class="form-control termin-amount fw-bold"
                            placeholder="Rp 0"
                            inputmode="numeric"
                            autocomplete="off"
                        >

                        {{-- Nilai angka murni yang dikirim ke server --}}
                        <input
                            type="hidden"
                            name="amount[]"
                            class="termin-amount-value"
                            value=""
                        >
                    </div>

                    {{-- KETERANGAN --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">
                            Keterangan
                        </label>

                        <input
                            type="text"
                            name="termin_description[]"
                            class="form-control termin-description"
                            placeholder="Contoh: DP / Tahap 1 / Pelunasan"
                        >
                    </div>

                    {{-- TANGGAL PENAGIHAN --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">
                            Tanggal Penagihan
                        </label>

                        <input
                            type="date"
                            name="billing_date[]"
                            class="form-control termin-billing-date"
                        >
                    </div>

                    {{-- HAPUS --}}
                    <div class="col-md-1">
                        <button
                            type="button"
                            class="btn btn-dark btn-icon btn-remove-termin"
                            title="Hapus Termin"
                        >
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>

                </div>
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
                                id="total-termin-percentage-edit"
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
                                id="total-termin-amount-edit"
                                class="fs-3 fw-bold"
                            >
                                Rp 0
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div
                id="termin-warning-edit"
                class="alert alert-warning mt-4 mb-0 d-none"
            >
                <div class="d-flex align-items-center">
                    <i class="ti ti-alert-triangle me-2 fs-2"></i>

                    <div>
                        <div class="fw-bold">
                            Nominal melebihi total penawaran
                        </div>

                        <div class="small">
                            Total nominal termin selain termin terakhir sudah melebihi total penawaran.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- <div class="d-flex justify-content-end mt-4 gap-2">

        <button type="submit" class="btn btn-dark" id="btn-update-termin">
            <i class="ti ti-device-floppy"></i>
            Simpan Perubahan
        </button>
        <button type="button" class="btn btn-secondary btn-cancel">
            <i class="ti ti-x"></i> Batal
        </button>
    </div> --}}

</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('build-termin-edit-form');
    const container = document.getElementById('termin-edit-container');
    const template = document.getElementById('termin-edit-row-template');

    if (!form || !container || !template) {
        return;
    }

    const addButton = document.getElementById('btn-add-edit-termin');

    // Tombol simpan bisa berada di luar <form> (misalnya footer modal).
    const saveButton = document.getElementById('btn-update-termin');

    const warningElement = document.getElementById('termin-warning-edit');
    const totalPercentageElement = document.getElementById('total-termin-percentage-edit');
    const totalAmountElement = document.getElementById('total-termin-amount-edit');

    const offerTotal = parseFloat(
        document.getElementById('build-edit-offer-total')?.value || 0
    ) || 0;

    const initialRows = @json($initialRows);

    /* ---------- Helper ---------- */

    function formatRupiah(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0
        }).format(Math.round(Number(value) || 0));
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
            ? ((amount / offerTotal) * 100).toFixed(4)
            : '';
    }

    // Termin TERAKHIR selalu menyerap sisa dari termin-termin lain, supaya
    // total nominal selalu pas sama dengan total penawaran (tanpa sisa
    // pembulatan) -- tidak peduli apakah user mengedit lewat kolom
    // persentase atau nominal. Karena itu baris terakhir dikunci read-only
    // (lihat updateAutoLastRow()).
    function applyLastRowRemainder() {
        const rows = Array.from(getRows());

        if (rows.length === 0) {
            return false;
        }

        const lastRow = rows[rows.length - 1];
        const otherRows = rows.slice(0, -1);

        const sumOthers = otherRows.reduce(function (sum, row) {
            return sum + (Number(fields(row).amountValue.value) || 0);
        }, 0);

        const remainder = Math.max(offerTotal - sumOthers, 0);

        setAmount(lastRow, remainder);

        const f = fields(lastRow);
        f.percentage.value = offerTotal > 0
            ? ((remainder / offerTotal) * 100).toFixed(4)
            : '';

        // true kalau nominal termin-termin lain sudah melebihi total penawaran.
        return sumOthers > offerTotal;
    }

    // Baris terakhir dikunci (read-only) karena nilainya otomatis mengikuti
    // sisa dari baris-baris lain -- lihat applyLastRowRemainder().
    function updateAutoLastRow() {
        const rows = Array.from(getRows());

        rows.forEach(function (row, index) {
            const isLast = index === rows.length - 1;
            const f = rows.length ? fields(row) : null;

            if (!f) {
                return;
            }

            f.percentage.readOnly = isLast;
            f.amountDisplay.readOnly = isLast;
            f.percentage.title = isLast ? 'Dihitung otomatis dari sisa termin lain' : '';
            f.amountDisplay.title = f.percentage.title;
            row.classList.toggle('termin-row-auto', isLast);
        });
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
        // Baris terakhir dipaksa jadi remainder DULU, baru totalnya dihitung
        // -- jadi total yang tampil selalu pas, dan yang tersimpan (amount[])
        // selalu sama dengan yang tampil di layar.
        const exceeded = applyLastRowRemainder();

        let totalPercentage = 0;
        let totalAmount = 0;

        getRows().forEach(function (row) {
            const f = fields(row);

            totalPercentage += parseFloat(f.percentage.value) || 0;
            totalAmount += Number(f.amountValue.value) || 0;
        });

        totalPercentageElement.textContent = totalPercentage.toFixed(2) + '%';
        totalAmountElement.textContent = formatRupiah(totalAmount);

        // Warning sekarang hanya untuk kasus nominal termin lain melebihi
        // total penawaran (bikin termin terakhir jadi 0) -- bukan lagi soal
        // "belum 100%", karena baris terakhir selalu otomatis menutup sisanya.
        warningElement.classList.toggle('d-none', !exceeded);

        const isValid = !exceeded && offerTotal > 0;

        if (saveButton) {
            saveButton.disabled = !isValid;
        }

        return isValid;
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

        // Nominal yang sudah tersimpan ditampilkan apa adanya (termin terakhir
        // bisa berisi sisa pembulatan), tidak dihitung ulang dari persentase.
        const storedAmount = Number(data.amount);
        const hasStoredAmount =
            data.amount !== undefined &&
            data.amount !== null &&
            data.amount !== '' &&
            Number.isFinite(storedAmount);

        if (hasStoredAmount && f.percentage.value !== '') {
            setAmount(row, storedAmount);
        } else if (f.percentage.value !== '') {
            syncAmountFromPercentage(row);
        } else if (hasStoredAmount && storedAmount > 0) {
            setAmount(row, storedAmount);
            syncPercentageFromAmount(row);
        }

        container.appendChild(row);

        updateTerminNumbers();
        updateAutoLastRow();
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
        updateAutoLastRow();
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

            alert('Total nominal termin selain termin terakhir melebihi total penawaran.');
        }
    });

    /* ---------- Inisialisasi ---------- */

    (initialRows.length ? initialRows : [{}]).forEach(addRow);
});
</script>
@endpush