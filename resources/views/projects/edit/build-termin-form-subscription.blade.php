@php
    $termins = $project->buildTermins->sortBy('termin_no')->values();
    $latest  = $termins->last();

    $isSubscription = $latest && $latest->period_start;

    $latestInvoice = $latest
        ? $project->invoicebuilds->where('termin', $latest->termin_no)->first()
        : null;

    $hasInvoice   = (bool) $latestInvoice;
    $isSuperAdmin = auth()->user()?->hasRole('Super-Admin');

    $invoicePaid = $latestInvoice?->status === \App\Models\InvoiceBuild::STATUS_PAID;
    $invoiceApproved = $latestInvoice?->status === \App\Models\InvoiceBuild::STATUS_APPROVED;

    // Super-Admin boleh membuka form selama invoice belum dibayar
    $superAdminEdit = $hasInvoice && $isSuperAdmin && ! $invoicePaid;

    // Sudah disetujui -> hanya keterangan
    $lockFinancial = $superAdminEdit && $invoiceApproved;

    $isOnlyTermin = $termins->count() === 1;
    $offerTotal   = (float) ($project->rab?->grand_total ?? 0);
    $oldAmount    = $latest ? (int) old('amount', (int) $latest->amount) : 0;
@endphp

@if(! $latest)

    <div class="alert alert-info mb-0">
        Belum ada termin yang bisa diedit.
    </div>

@elseif(! $isSubscription)

    <div class="alert alert-warning mb-0">
        Termin ini dibuat dengan sistem lama (pembagian persentase) dan tidak dapat
        diedit di sini.
    </div>

@elseif($hasInvoice && ! $superAdminEdit)

    <div class="alert alert-info mb-0">
        <i class="ti ti-info-circle me-1"></i>
        Termin ke-{{ $latest->termin_no }} sudah ditagih (invoice sudah dibuat), jadi
        tidak dapat diubah. Termin berikutnya bisa diubah setelah dibuat melalui
        <strong>Lanjut Berlangganan</strong>, selama invoicenya belum dibuat.
    </div>

@else

    <form
        action="{{ route('projects.build-termin.update', $project->id) }}"
        method="POST"
        id="build-termin-edit-form"
    >
        @csrf
        @method('PUT')

        <input type="hidden" name="termin_no" value="{{ $latest->termin_no }}">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-3">
            <h3 class="mb-1 fw-bold">
                Edit Termin ke-{{ $latest->termin_no }}
            </h3>

            <div class="text-muted">
                Hanya termin terakhir yang belum ditagih yang dapat diubah.
                Total penawaran: <strong>Rp {{ number_format($offerTotal, 0, ',', '.') }}</strong>.
            </div>
        </div>
        @if($superAdminEdit)
            <div class="alert alert-warning mt-2 mb-0">
                @if($lockFinancial)
                    Invoice termin ini sudah disetujui, jadi hanya <strong>Keterangan</strong> yang bisa diubah.
                @else
                    Invoice termin ini sudah dibuat. Perubahan nominal akan ikut memperbarui invoicenya.
                @endif
                Termin sebelumnya tidak terpengaruh.
            </div>
        @endif
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
                            id="termin-edit-amount-display"
                            class="form-control fw-bold"
                            placeholder="Rp 0"
                            inputmode="numeric"
                            autocomplete="off"
                            {{ $lockFinancial ? 'readonly' : '' }}
                            value="{{ $oldAmount > 0 ? 'Rp ' . number_format($oldAmount, 0, ',', '.') : '' }}"
                        >

                        {{-- Angka murni yang dikirim ke server --}}
                        <input
                            type="hidden"
                            name="amount"
                            id="termin-edit-amount"
                            value="{{ $oldAmount > 0 ? $oldAmount : '' }}"
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Keterangan
                        </label>

                        <input
                            type="text"
                            name="termin_description"
                            class="form-control"
                            maxlength="255"
                            placeholder="Contoh: Langganan Sistem"
                            value="{{ old('termin_description', $latest->description) }}"
                        >
                    </div>

                    @if($isOnlyTermin && ! $hasInvoice)

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Periode Berlangganan <span class="text-danger">*</span>
                            </label>

                            <select name="billing_period" class="form-select" required>
                                <option value="monthly" {{ old('billing_period', $latest->billing_period) === 'monthly' ? 'selected' : '' }}>
                                    Bulanan (Monthly)
                                </option>
                                <option value="annual" {{ old('billing_period', $latest->billing_period) === 'annual' ? 'selected' : '' }}>
                                    Tahunan (Annual)
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Tanggal Penagihan <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="billing_date"
                                class="form-control"
                                required
                                value="{{ old('billing_date', $latest->billing_date?->format('Y-m-d')) }}"
                            >
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <div class="text-muted small">
                                Periode dan tanggal hanya bisa diubah selama baru ada satu termin,
                                karena menjadi patokan masa layanan termin berikutnya.
                            </div>
                        </div>

                    @else
                        @if($superAdminEdit)
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tanggal Penagihan</label>
                                <input
                                    type="date"
                                    name="billing_date"
                                    class="form-control"
                                    {{ $lockFinancial ? 'disabled' : 'required' }}
                                    value="{{ old('billing_date', $latest->billing_date?->format('Y-m-d')) }}"
                                >
                            </div>
                        @endif
                        <div class="col-12">
                            <div class="text-muted small">
                                Masa layanan:
                                <strong>
                                    {{ $latest->billing_period_label }},
                                    {{ $latest->period_start->translatedFormat('d M Y') }}
                                    &ndash;
                                    {{ $latest->period_end->translatedFormat('d M Y') }}
                                </strong>
                                (tidak dapat diubah).
                            </div>
                        </div>

                    @endif

                </div>

                <div class="alert alert-info mt-4 mb-0">
                    <i class="ti ti-info-circle me-1"></i>
                    Nominal dan keterangan ini juga menjadi dasar termin berikutnya
                    (disalin otomatis saat <strong>Lanjut Berlangganan</strong>).
                </div>
            </div>
        </div>

    </form>

@endif

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('build-termin-edit-form');

    // Tidak ada yang bisa diedit -> sembunyikan tombol simpan di footer blade induk
    if (!form) {
        document
            .querySelectorAll('button[form="build-termin-edit-form"]')
            .forEach(function (button) {
                button.classList.add('d-none');
            });

        return;
    }

    const display = document.getElementById('termin-edit-amount-display');
    const hidden = document.getElementById('termin-edit-amount');

    function formatRupiah(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0
        }).format(Number(value) || 0);
    }

    display.addEventListener('input', function () {
        const digits = display.value.replace(/\D/g, '');

        hidden.value = digits;
        display.value = digits ? formatRupiah(digits) : '';
    });

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