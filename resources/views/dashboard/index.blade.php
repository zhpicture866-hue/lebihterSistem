@extends('tablar::page')

@section('content')

@php
    // Format rupiah, angka negatif tampil sebagai -Rp 6.200.000
    $rp = fn ($n) => ($n < 0 ? '-' : '') . 'Rp ' . number_format(abs($n), 0, ',', '.');
    $tone = fn ($n) => $n < 0 ? 'text-danger' : 'text-success';
@endphp

<style>
    .dash-stat {
        display: block;
        height: 100%;
        padding: 1rem 1.25rem;
        border: 1px solid var(--tblr-border-color);
        border-radius: .75rem;
        background: var(--tblr-bg-surface);
        color: inherit;
        text-decoration: none;
    }
    a.dash-stat:hover { border-color: var(--tblr-primary); color: inherit; }
    .dash-stat-label { font-size: .8125rem; color: var(--tblr-secondary); }
    .dash-stat-value { margin-top: .25rem; font-size: 1.375rem; font-weight: 700; line-height: 1.2; }

    .dash-total {
        height: 100%;
        padding: 1.25rem 1.5rem;
        border-radius: .75rem;
        background: var(--tblr-primary-lt);
    }
    .dash-total-value { margin-top: .25rem; font-size: 1.875rem; font-weight: 700; line-height: 1.2; }

    .dash-account {
        display: flex;
        align-items: center;
        gap: .875rem;
        height: 100%;
        padding: .875rem 1rem;
        border: 1px solid var(--tblr-border-color);
        border-radius: .75rem;
    }
    .dash-account-icon {
        flex: none;
        display: grid;
        place-items: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: .625rem;
        background: var(--tblr-bg-surface-secondary);
        font-size: 1.25rem;
    }
    .dash-account-name { font-size: .875rem; color: var(--tblr-secondary); overflow-wrap: anywhere; }
    .dash-account-balance { font-weight: 600; white-space: nowrap; }

    .footer.footer-transparent {
        display: flex;
        margin-left: 0;
        flex-direction: column;
        padding: 20px 20px 20px 16px;
        transition: all .3s ease;
    }
    .sidebar-collapsed .footer.footer-transparent {
        padding-left: 20px;
        padding-right: 18px;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
</style>

<div class="page-body">
    <div class="container-xl dashboard-container">
        <div class="row g-4">

            @can('lihat akun-akuntansi')
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">💰 Finance</h5>
                        <span class="text-secondary">
                            {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                        </span>
                    </div>

                    <div class="card-body p-4">

                        {{-- Ringkasan: total kas & bank + angka bulan ini --}}
                        <div class="row g-3">
                            <div class="col-lg-4">
                                <div class="dash-total">
                                    <div class="text-secondary">Total Kas &amp; Bank</div>
                                    <div class="dash-total-value {{ $totalCashBank < 0 ? 'text-danger' : 'text-primary' }}">
                                        {{ $rp($totalCashBank) }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="row g-3 h-100">
                                    <div class="col-sm-4">
                                        <a href="{{ route('journals.general') }}" class="dash-stat">
                                            <div class="dash-stat-label">📈 Pendapatan</div>
                                            <div class="dash-stat-value {{ $tone($monthlyRevenue) }}">
                                                {{ $rp($monthlyRevenue) }}
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="dash-stat">
                                            <div class="dash-stat-label">📥 Kas Masuk</div>
                                            <div class="dash-stat-value text-success">
                                                {{ $rp($cashInThisMonth) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="dash-stat">
                                            <div class="dash-stat-label">📤 Kas Keluar</div>
                                            <div class="dash-stat-value text-danger">
                                                {{ $rp($cashOutThisMonth) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Saldo per akun --}}
                        <h6 class="mt-4 mb-3">Saldo per akun</h6>

                        <div class="row g-3">
                            @forelse($cashAccounts as $account)
                                <div class="col-sm-6 col-xl-4">
                                    <div class="dash-account">
                                        <div class="dash-account-icon">
                                            {{ str_contains(strtolower($account['account_name']), 'bank') ? '🏦' : '💵' }}
                                        </div>
                                        <div class="flex-fill">
                                            <div class="dash-account-name">{{ $account['account_name'] }}</div>
                                            <div class="dash-account-balance {{ $account['balance'] < 0 ? 'text-danger' : '' }}">
                                                {{ $rp($account['balance']) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-secondary">Belum ada akun kas atau bank.</div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
            @endcan

            {{--
            ================= Blok Project (nonaktif) =================
            Hapus tanda komentar ini kalau mau diaktifkan lagi.
            Perlu variabel: $totalProject, $runningBuild, $completedBuild,
            $totalDesign, $totalRab, $totalBuild, $topBuildProjects

            @can('lihat daftar proyek')
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header">
                        <h5 class="mb-0">📁 Project</h5>
                    </div>
                    <div class="card-body p-4">

                        <div class="row g-3 mb-4">
                            @foreach([
                                ['📁', 'Total',             $totalProject,   route('projects.index'),                 ''],
                                ['🚧', 'Sedang Dikerjakan', $runningBuild,   null,                                    'text-primary'],
                                ['✅', 'Sudah Selesai',     $completedBuild, null,                                    'text-success'],
                                ['🎨', 'Desain',            $totalDesign,    route('projects.index', ['type' => 1]), 'text-info'],
                                ['📑', 'RAB',               $totalRab,       route('projects.index', ['type' => 2]), 'text-warning'],
                                ['🏗', 'Build',             $totalBuild,     route('projects.index', ['type' => 3]), 'text-success'],
                            ] as [$icon, $label, $value, $url, $color])
                                <div class="col-6 col-md-4 col-xl-2">
                                    <{{ $url ? 'a' : 'div' }} @if($url) href="{{ $url }}" @endif class="dash-stat text-center">
                                        <div class="fs-2">{{ $icon }}</div>
                                        <div class="dash-stat-label">{{ $label }}</div>
                                        <div class="dash-stat-value {{ $color }}">{{ $value }}</div>
                                    </{{ $url ? 'a' : 'div' }}>
                                </div>
                            @endforeach
                        </div>

                        <h6 class="mb-3">🏗 Progress Tertinggi</h6>
                        @foreach($topBuildProjects as $project)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>{{ $project->project_name }}</span>
                                    <span>{{ number_format($project->progress, 0) }}%</span>
                                </div>
                                <div class="progress mt-1" style="height:8px;">
                                    <div class="progress-bar" style="width: {{ $project->progress }}%"></div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
            @endcan
            --}}

        </div>
    </div>
</div>
@endsection