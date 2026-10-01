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

                <th class="text-center">
                    Keterangan &amp; Periode
                </th>

                <th width="180" class="text-center">
                    Nominal
                </th>

                <th class="text-center">
                    Tanggal Penagihan
                </th>

                <th class="text-center">
                    Status
                </th>

                <th class="text-center">
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

                    <td class="text-end">
                        Rp {{ number_format($termin->amount, 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ $termin->billing_date->translatedFormat('d F Y') }}
                    </td>

                    <td class="text-center">
                        @if($paid)
                            <span class="badge bg-green-lt">Lunas</span>
                            @if($inv->approved_at)
                                <div class="text-muted small mt-1">
                                    {{ $inv->approved_at->translatedFormat('d M Y') }}
                                </div>
                            @endif
                        @elseif($inv)
                            <span class="badge bg-yellow-lt">Menunggu Pembayaran</span>
                        @else
                            <span class="badge bg-secondary-lt">Belum Ditagih</span>
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

                            <span class="text-muted small">Menunggu pembayaran</span>

                        @elseif($termin->renewal_decision === null)

                            {{-- Setelah kwitansi tersedia: pilih lanjut atau stop --}}
                            @if($canDecide)
                                <div class="d-flex gap-2 justify-content-center">

                                    <form
                                        method="POST"
                                        action="{{ route('projects.termins.continue', [$project->id, $termin->id]) }}"
                                        onsubmit="return confirm('Lanjut berlangganan? Termin berikutnya akan dibuat otomatis.')"
                                    >
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-dark">
                                            Lanjut Berlangganan
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('projects.termins.stop', [$project->id, $termin->id]) }}"
                                        onsubmit="return confirm('Yakin berhenti berlangganan? Tindakan ini tidak dapat dibatalkan.')"
                                    >
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Stop
                                        </button>
                                    </form>

                                </div>
                            @else
                                <span class="text-muted small">Menunggu keputusan customer</span>
                            @endif

                        @elseif($termin->renewal_decision === 'continued')

                            <span class="badge bg-green-lt">Lanjut berlangganan</span>

                        @elseif($termin->renewal_decision === 'stopped')

                            <span class="badge bg-red-lt">Berhenti berlangganan</span>

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

                <th class="text-end">
                    Rp {{ number_format($billedTotal, 0, ',', '.') }}
                </th>

                <th colspan="5"></th>
            </tr>

            <tr>
                <th colspan="2" class="text-end">
                    Total Terbayar
                </th>

                <th class="text-end">
                    Rp {{ number_format($paidTotal, 0, ',', '.') }}
                </th>

                <th colspan="5"></th>
            </tr>

        </tfoot>

    </table>

</div>