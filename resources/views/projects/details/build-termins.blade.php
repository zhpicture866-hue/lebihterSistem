@php
    $termins = $project->buildTermins->sortBy('termin_no')->values();

    $user = auth()->user();

    $canManage  = $user->can('ubah data proyek');
    $isCustomer = $user->id === $project->customer?->user?->id;
    $canDecide  = $canManage || $isCustomer;

    // Invoice termin (sama seperti di komponen invoice & bukti pembayaran)
    $invoiceOf = fn ($t) => $project->invoicebuilds->where('termin', $t->termin_no)->first();

    // Lunas = invoice sudah di-approve
    $isPaid = function ($t) use ($invoiceOf) {
        $inv = $invoiceOf($t);

        return $inv && $inv->status === 'approved';
    };

    $subscriptionStatus = $project->subscription_status; // not_started | active | stopped
    $stoppedTermin = $termins->firstWhere('renewal_decision', 'stopped');

    $billedTotal = $termins->sum('amount');
    $paidTotal   = $termins->filter($isPaid)->sum('amount');

    $statusBadge = [
        'not_started' => ['bg-secondary-lt', 'Belum Dimulai'],
        'active'      => ['bg-green-lt', 'Berlangganan Aktif'],
        'stopped'     => ['bg-red-lt', 'Berhenti Berlangganan'],
    ][$subscriptionStatus];
@endphp

<div id="termin-subscription-root">


@if ($errors->has('termin'))
    <div class="alert alert-danger">
        {{ $errors->first('termin') }}
    </div>
@endif

@if ($stoppedTermin)
    <div class="alert alert-secondary">
        <i class="ti ti-player-stop me-1"></i>
        Langganan dihentikan setelah termin ke-{{ $stoppedTermin->termin_no }}{{ $stoppedTermin->decided_at ? ' pada ' . $stoppedTermin->decided_at->translatedFormat('d F Y') : '' }}.
        Tidak ada termin baru yang akan dibuat.
    </div>
@endif

<div class="mb-4">

    <div class="row g-3">

        <div class="col-md-3">
            <label class="form-label text-muted">
                Total Penawaran Harga
            </label>

            <div class="fw-semibold">
                Rp {{ number_format($project->rab?->grand_total ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label text-muted">
                Status Langganan
            </label>

            <div>
                <span class="badge {{ $statusBadge[0] }}">
                    {{ $statusBadge[1] }}
                </span>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label text-muted">
                Total Terbayar
            </label>

            <div class="fw-semibold">
                Rp {{ number_format($paidTotal, 0, ',', '.') }}
            </div>
        </div>

        <div class="col-md-3 text-md-end">
            <label class="form-label text-muted">
                Total Termin
            </label>

            <div class="fw-semibold">
                {{ $termins->count() }} Termin
            </div>
        </div>

    </div>

</div>

<div class="table-responsive">

    <table class="table table-bordered align-middle mb-0">

        <thead>

            <tr>

                <th width="70" class="text-center">
                    Termin
                </th>

                <th class="text-center" style="min-width: 200px;">
                    Keterangan &amp; Periode
                </th>

                <th class="text-center" style="min-width: 100px;">
                    Nominal
                </th>

                <th class="text-center">
                    Tanggal Penagihan
                </th>

                <th class="text-center">
                    Status
                </th>

                <th class="text-center" style="min-width: 200px;">
                    Invoice
                </th>

                <th class="text-center">
                    Bukti Pembayaran
                </th>

                <th class="text-center" width="210">
                    Langganan
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($termins as $index => $termin)

                @php
                    $inv  = $invoiceOf($termin);
                    $paid = $inv && $inv->status === 'approved';

                    // Bukti sudah diupload, tapi invoice belum di-approve
                    $awaitingApproval = $inv && $inv->bukti_pembayaran && ! $paid;

                    // [warna badge, ikon, keterangan (tooltip)]
                    if ($paid) {
                        $statusIcon = ['bg-success', 'ti-check', 'Lunas'];
                    } elseif ($awaitingApproval) {
                        $statusIcon = ['bg-info', 'ti-hourglass', 'Menunggu Persetujuan'];
                    } elseif ($inv) {
                        $statusIcon = ['bg-warning', 'ti-clock', 'Menunggu Pembayaran'];
                    } else {
                        $statusIcon = ['bg-secondary', 'ti-minus', 'Belum Ditagih'];
                    }
                @endphp

                <tr>

                    <td class="text-center">
                        {{ $termin->termin_no }}
                    </td>

                    <td>
                        <div>{{ $termin->description ?: '-' }}</div>

                        @if($termin->period_start && $termin->period_end)
                            <div class="text-muted small">
                                {{ $termin->billing_period_label }}:
                                {{ $termin->period_start->translatedFormat('d M Y') }}
                                &ndash;
                                {{ $termin->period_end->translatedFormat('d M Y') }}
                            </div>
                        @endif
                    </td>

                    <td class="text-end text-nowrap">
                        Rp {{ number_format($termin->amount, 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ $termin->billing_date->translatedFormat('d F Y') }}
                    </td>

                    <td class="text-center">
                        <span class="badge rounded-circle d-inline-flex align-items-center justify-content-center {{ $statusIcon[0] }}"
                              style="width: 28px; height: 28px;"
                              data-bs-tooltip="true"
                              title="{{ $statusIcon[2] }}"
                              aria-label="{{ $statusIcon[2] }}">
                            <i class="ti {{ $statusIcon[1] }} text-white"></i>
                        </span>

                        @if($paid && $inv->approved_at)
                            <div class="text-muted small mt-1">
                                {{ $inv->approved_at->translatedFormat('d M Y') }}
                            </div>
                        @endif
                    </td>

                    <td class="text-center">
                        @include('projects.components.termin-invoice-actions', [
                            'project' => $project,
                            'termins' => $termins,
                            'termin'  => $termin,
                            'index'   => $index,
                        ])
                    </td>

                    <td class="text-center">
                        {{-- Kwitansi muncul di sini setelah invoice di-approve --}}
                        @include('projects.components.termin-bukti-pembayaran', [
                            'project' => $project,
                            'termin'  => $termin,
                        ])
                    </td>

                    <td class="text-center">

                        @if(! $paid)

                            <span class="text-muted small">
                                {{ $awaitingApproval ? 'Menunggu persetujuan' : 'Menunggu pembayaran' }}
                            </span>

                        @elseif($termin->renewal_decision === null)

                            {{-- Setelah kwitansi tersedia: pilih lanjut atau stop --}}
                            @if($canDecide)
                                <div class="d-flex gap-2 justify-content-center">

                                    <form
                                        method="POST"
                                        class="decision-form"
                                        action="{{ route('projects.termins.continue', [$project->id, $termin->id]) }}"
                                        data-title="Lanjut berlangganan?"
                                        data-text="Termin berikutnya akan dibuat otomatis."
                                        data-icon="question"
                                        data-confirm-text="Ya, Lanjutkan"
                                        data-confirm-color="#212529"
                                    >
                                        @csrf
                                        <button type="submit"
                                                class="badge rounded-circle d-inline-flex align-items-center justify-content-center bg-success border-0"
                                                style="width: 28px; height: 28px; cursor: pointer;"
                                                data-bs-tooltip="true"
                                                title="Lanjut berlangganan"
                                                aria-label="Lanjut berlangganan">
                                            <i class="ti ti-repeat text-white"></i>
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        class="decision-form"
                                        action="{{ route('projects.termins.stop', [$project->id, $termin->id]) }}"
                                        data-title="Berhenti berlangganan?"
                                        data-text="Tindakan ini tidak dapat dibatalkan."
                                        data-icon="warning"
                                        data-confirm-text="Ya, Berhenti"
                                        data-confirm-color="#d63939"
                                    >
                                        @csrf
                                        <button type="submit"
                                                class="badge rounded-circle d-inline-flex align-items-center justify-content-center bg-danger border-0"
                                                style="width: 28px; height: 28px; cursor: pointer;"
                                                data-bs-tooltip="true"
                                                title="Berhenti berlangganan"
                                                aria-label="Berhenti berlangganan">
                                            <i class="ti ti-player-stop text-white"></i>
                                        </button>
                                    </form>

                                </div>
                            @else
                                <span class="text-muted small">Menunggu keputusan customer</span>
                            @endif

                        @elseif($termin->renewal_decision === 'continued')

                            <span class="badge rounded-circle d-inline-flex align-items-center justify-content-center bg-success"
                                  style="width: 28px; height: 28px;"
                                  data-bs-tooltip="true"
                                  title="Lanjut berlangganan"
                                  aria-label="Lanjut berlangganan">
                                <i class="ti ti-repeat text-white"></i>
                            </span>

                        @elseif($termin->renewal_decision === 'stopped')

                            <span class="badge rounded-circle d-inline-flex align-items-center justify-content-center bg-danger"
                                  style="width: 28px; height: 28px;"
                                  data-bs-tooltip="true"
                                  title="Berhenti berlangganan"
                                  aria-label="Berhenti berlangganan">
                                <i class="ti ti-player-stop text-white"></i>
                            </span>

                        @endif

                    </td>

                </tr>

            @endforeach

        </tbody>

        <tfoot>

            <tr>
                <th colspan="2" class="text-end">
                    Total Tagihan
                </th>

                <th class="text-end text-nowrap">
                    Rp {{ number_format($billedTotal, 0, ',', '.') }}
                </th>

                <th colspan="5"></th>
            </tr>

            <tr>
                <th colspan="2" class="text-end">
                    Total Terbayar
                </th>

                <th class="text-end text-nowrap">
                    Rp {{ number_format($paidTotal, 0, ',', '.') }}
                </th>

                <th colspan="5"></th>
            </tr>

        </tfoot>

    </table>

</div>

</div>{{-- /#termin-subscription-root --}}

@push('js')
<script>
// Ingat status buka/tutup card "Setting Termin" per proyek (selama tab masih sama),
// supaya setelah halaman dimuat ulang (Lanjut, Stop, approve, upload bukti, dll.)
// card langsung terbuka dan tidak perlu dibuka manual lagi.
// Memakai event "load" supaya berjalan setelah listener tombol card di blade induk terpasang.
window.addEventListener('load', function () {

    const root = document.getElementById('termin-subscription-root');

    if (!root) return;

    const target = root.closest('[id$="-body"]');

    if (!target) return;

    const STORAGE_KEY = 'card-open:{{ $project->id }}:' + target.id;

    const isOpen = function () {
        return window.getComputedStyle(target).display !== 'none'
            && !target.classList.contains('d-none');
    };

    // Simpan status saat halaman ditinggalkan (submit form, reload, dst.)
    window.addEventListener('pagehide', function () {
        sessionStorage.setItem(STORAGE_KEY, isOpen() ? '1' : '0');
    });

    // Pulihkan: buka card jika sebelumnya terbuka
    if (sessionStorage.getItem(STORAGE_KEY) !== '1' || isOpen()) return;

    const selector = [
        '[data-bs-target="#' + target.id + '"]',
        '[data-target="#' + target.id + '"]',
        '[href="#' + target.id + '"]'
    ].join(',');

    const toggle = document.querySelector(selector);

    if (toggle) {
        toggle.click(); // pakai mekanisme buka-tutup bawaan komponen
    } else {
        target.classList.remove('d-none');
        target.classList.add('show');
        target.style.display = '';
    }

    const card = target.closest('.card') || target;
    card.style.scrollMarginTop = '110px'; // supaya tidak tertutup header

    setTimeout(function () {
        card.scrollIntoView({ block: 'start' });
    }, 350);
});
</script>
@endpush

@push('js')
<script>
// Setelah "Download Invoice" diklik (invoice dibuka di tab baru), segarkan tabel termin
// di latar belakang supaya tombol upload bukti, status, dan tombol termin berikutnya
// langsung muncul tanpa reload halaman.
document.addEventListener('DOMContentLoaded', function () {

    const root = document.getElementById('termin-subscription-root');

    if (!root) return;

    const REFRESH_URL = @json(route('projects.termins.refresh', $project->id));
    const POLL_INTERVAL = 1500;   // ms
    const MAX_TRIES = 20;         // ~30 detik

    let polling = false;

    // Konfirmasi approve (sama seperti di blade induk) untuk form hasil penyegaran
    function bindApproveForms(scope) {
        if (!window.Swal) return;

        scope.querySelectorAll('.approve-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: form.dataset.title || 'Apakah Anda yakin?',
                    text: form.dataset.text || 'Proses ini akan dilanjutkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#212529',
                }).then(function (result) {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: function () { Swal.showLoading(); }
                        });
                        form.submit();
                    }
                });
            });
        });
    }

    function swapTable(html) {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const fresh = doc.getElementById('termin-subscription-root');

        if (!fresh) return;

        root.innerHTML = fresh.innerHTML;

        if (window.bootstrap) {
            root
                .querySelectorAll('[data-bs-toggle="tooltip"], [data-bs-tooltip="true"]')
                .forEach(function (el) {
                    bootstrap.Tooltip.getOrCreateInstance(el);
                });
        }

        bindApproveForms(root);
    }

    function pollUntilReady(terminNo) {
        if (polling) return;

        polling = true;

        let tries = 0;

        const tick = async function () {
            tries++;

            try {
                const response = await fetch(REFRESH_URL + '?termin=' + encodeURIComponent(terminNo), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    const data = await response.json();

                    if (data.ready) {
                        swapTable(data.html);
                        polling = false;
                        return;
                    }
                }
            } catch (error) {
                // abaikan, coba lagi pada putaran berikutnya
            }

            if (tries < MAX_TRIES) {
                setTimeout(tick, POLL_INTERVAL);
            } else {
                polling = false; // menyerah; tombol akan muncul setelah reload seperti biasa
            }
        };

        setTimeout(tick, 1000);
    }

    // Delegasi event: tetap berfungsi setelah isi tabel diganti
    root.addEventListener('click', function (event) {
        const link = event.target.closest('a[data-invoice-download]');

        if (!link) return;

        // Sudah pernah di-download -> tidak ada yang berubah, tidak perlu menyegarkan
        if (link.dataset.invoiceDownloaded === '1') return;

        pollUntilReady(link.dataset.invoiceDownload);
    });

    // Konfirmasi SweetAlert untuk Lanjut / Stop.
    // Memakai delegasi event pada root, jadi tetap berfungsi setelah tabel disegarkan.
    root.addEventListener('submit', function (event) {
        const form = event.target.closest('form.decision-form');

        if (!form) return;

        // Cadangan jika SweetAlert tidak termuat
        if (!window.Swal) {
            if (!confirm(form.dataset.text || 'Lanjutkan?')) {
                event.preventDefault();
            }

            return;
        }

        event.preventDefault();

        // Tutup tooltip tombol supaya tidak "menempel" di belakang dialog
        const button = form.querySelector('button[type="submit"]');

        if (button && window.bootstrap) {
            const tooltip = bootstrap.Tooltip.getInstance(button);

            if (tooltip) tooltip.hide();
        }

        Swal.fire({
            title: form.dataset.title || 'Apakah Anda yakin?',
            text: form.dataset.text || '',
            icon: form.dataset.icon || 'warning',
            showCancelButton: true,
            confirmButtonText: form.dataset.confirmText || 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: form.dataset.confirmColor || '#212529',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: function () { Swal.showLoading(); }
            });

            form.submit(); // submit() tidak memicu event "submit" lagi, jadi tidak berulang
        });
    });
});
</script>
@endpush