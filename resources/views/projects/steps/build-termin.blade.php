@php
    $rab = $project->rab;
    $offerTotal = (float) ($rab?->grand_total ?? 0);

    // Periode default: ikuti penawaran jika semua item memakai periode yang sama
    $itemPeriods = $rab ? $rab->items->pluck('billing_period')->filter()->unique()->values() : collect();
    $defaultPeriod = $itemPeriods->count() === 1 ? $itemPeriods->first() : 'monthly';

    // Saran nominal: jumlah harga item untuk 1 periode (hanya jika periodenya seragam)
    $suggestedAmount = ($rab && $itemPeriods->count() === 1)
        ? (float) $rab->items->sum('price')
        : null;

    $oldAmount = (int) old('amount', 0);
@endphp

<form
    action="{{ route('projects.build-termin.store', $project->id) }}"
    method="POST"
    id="build-termin-form"
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
            Total penawaran harga belum tersedia. Lengkapi penawaran terlebih dahulu
            sebelum membuat termin.
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

                    @if ($suggestedAmount)
                        <div class="text-muted small mt-1">
                            Harga untuk 1 periode ({{ $defaultPeriod === 'annual' ? 'tahunan' : 'bulanan' }}):
                            <strong>Rp {{ number_format($suggestedAmount, 0, ',', '.') }}</strong>
                            (belum termasuk diskon dan pajak)
                        </div>
                    @endif
                </div>

                <div class="avatar avatar-lg bg-white shadow-sm">
                    <i class="ti ti-receipt-2 fs-2"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <h3 class="mb-1 fw-bold">
            Buat Termin Pertama
        </h3>

        <div class="text-muted">
            Setiap termin adalah satu periode langganan. Setelah termin dibayar dan
            customer memilih lanjut, termin berikutnya dibuat otomatis dengan nominal
            dan periode yang sama.
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Nominal Tagihan <span class="text-danger">*</span>
                    </label>

                    {{-- Tampilan berformat Rp, tanpa name --}}
                    <input
                        type="text"
                        id="termin-amount-display"
                        class="form-control fw-bold"
                        placeholder="Rp 0"
                        inputmode="numeric"
                        autocomplete="off"
                        value="{{ $oldAmount > 0 ? 'Rp ' . number_format($oldAmount, 0, ',', '.') : '' }}"
                    >

                    {{-- Angka murni yang dikirim ke server --}}
                    <input
                        type="hidden"
                        name="amount"
                        id="termin-amount"
                        value="{{ $oldAmount > 0 ? $oldAmount : '' }}"
                    >

                    @if ($suggestedAmount)
                        <button
                            type="button"
                            id="btn-use-offer-amount"
                            class="btn btn-link btn-sm p-0 mt-1"
                            data-amount="{{ round($suggestedAmount) }}"
                        >
                            Gunakan harga per periode dari penawaran
                        </button>
                    @endif
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        Periode Berlangganan <span class="text-danger">*</span>
                    </label>

                    <select name="billing_period" class="form-select" required>
                        <option value="monthly" {{ old('billing_period', $defaultPeriod) === 'monthly' ? 'selected' : '' }}>
                            Bulanan (Monthly)
                        </option>
                        <option value="annual" {{ old('billing_period', $defaultPeriod) === 'annual' ? 'selected' : '' }}>
                            Tahunan (Annual)
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        Keterangan
                    </label>

                    <input
                        type="text"
                        name="termin_description"
                        class="form-control"
                        maxlength="255"
                        placeholder="Contoh: Langganan Sistem"
                        value="{{ old('termin_description') }}"
                    >
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        Tanggal Penagihan <span class="text-danger">*</span>
                    </label>

                    <input
                        type="date"
                        name="billing_date"
                        class="form-control"
                        required
                        value="{{ old('billing_date', now()->format('Y-m-d')) }}"
                    >
                </div>

            </div>

            <div class="alert alert-info mt-4 mb-0">
                <i class="ti ti-info-circle me-1"></i>
                Masa layanan termin ke-1 dimulai dari tanggal penagihan. Termin berikutnya
                mengikuti periode yang dipilih (bulan atau tahun berikutnya).
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button
            type="submit"
            class="btn btn-dark px-4"
            id="btn-save-termin"
            {{ $offerTotal <= 0 ? 'disabled' : '' }}
        >
            <i class="ti ti-device-floppy me-1"></i>
            Buat Termin
        </button>
    </div>

</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('build-termin-form');
    const display = document.getElementById('termin-amount-display');
    const hidden = document.getElementById('termin-amount');
    const useOfferButton = document.getElementById('btn-use-offer-amount');

    if (!form || !display || !hidden) {
        return;
    }

    function formatRupiah(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0
        }).format(Number(value) || 0);
    }

    function setAmount(value) {
        const digits = String(value).replace(/\D/g, '');

        hidden.value = digits;
        display.value = digits ? formatRupiah(digits) : '';
    }

    display.addEventListener('input', function () {
        setAmount(display.value);
    });

    if (useOfferButton) {
        useOfferButton.addEventListener('click', function () {
            setAmount(useOfferButton.dataset.amount);
            display.focus();
        });
    }

    form.addEventListener('submit', function (event) {
        if (!hidden.value || Number(hidden.value) <= 0) {
            event.preventDefault();

            alert('Nominal tagihan wajib diisi.');
            display.focus();
        }
    });
});
</script>
@endpush