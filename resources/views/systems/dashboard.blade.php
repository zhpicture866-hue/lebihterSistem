@extends('tablar::page')

@section('title', 'Welcome - Guest Dashboard')

@section('content')

    <div class="container py-5">

        {{-- Header --}}
        <div class="mb-5 text-center">
            <h2 class="fw-bold mb-2">Selamat Datang, {{ auth()->user()->fullname }} 👋</h2>
            <p class="text-muted mb-0">Anda login sebagai <strong>Customer</strong>.</p>
        </div>

        {{-- Ringkasan Proyek --}}
        <div class="row justify-content-center mb-5">
            <div class="col-md-10">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body py-4">
                        <h5 class="fw-semibold mb-4"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Ringkasan Proyek</h5>
                        <div class="row text-center">
                            <div class="col-md-4 border-end">
                                <h3 class="fw-bold text-primary mb-0">3</h3>
                                <p class="text-muted mb-0 small">Proyek Aktif</p>
                            </div>
                            <div class="col-md-4 border-end">
                                <h3 class="fw-bold text-success mb-0">1</h3>
                                <p class="text-muted mb-0 small">Proyek Selesai</p>
                            </div>
                            <div class="col-md-4">
                                <h3 class="fw-bold text-warning mb-0">0</h3>
                                <p class="text-muted mb-0 small">Dalam Revisi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fitur Utama --}}
        <div class="row justify-content-center g-4 mb-5">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center rounded-4">
                    <div class="card-body py-4">
                        <i class="bi bi-folder-plus display-5 text-primary mb-3"></i>
                        <h6 class="fw-semibold mb-2">Ajukan Proyek Baru</h6>
                        <p class="text-muted small mb-3">Kirim detail proyek baru ke tim Antosa Architect.</p>
                        <a href="{{ route('project.create') }}" class="btn btn-primary btn-sm px-3">Ajukan</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center rounded-4">
                    <div class="card-body py-4">
                        <i class="bi bi-clock-history display-5 text-success mb-3"></i>
                        <h6 class="fw-semibold mb-2">Riwayat Proyek</h6>
                        <p class="text-muted small mb-3">Lihat daftar proyek Anda yang sedang atau sudah selesai.</p>
                        <a href="{{ route('project.index') }}" class="btn btn-success btn-sm px-3">Lihat</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center rounded-4">
                    <div class="card-body py-4">
                        <i class="bi bi-chat-dots display-5 text-info mb-3"></i>
                        <h6 class="fw-semibold mb-2">Kontak Tim Antosa</h6>
                        <p class="text-muted small mb-3">Butuh bantuan? Hubungi tim Antosa Architect.</p>
                        <a href="{{ route('support.contact') }}" class="btn btn-info btn-sm px-3 text-white">Hubungi</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lengkapi Profil --}}
        <div class="text-center">
            <a href="{{ route('guest.complete-profile') }}" class="btn btn-outline-success px-4">
                <i class="bi bi-person-lines-fill me-2"></i> Lengkapi Profil
            </a>
        </div>

    </div>
@endsection

