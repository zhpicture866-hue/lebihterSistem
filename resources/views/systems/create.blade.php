@extends('tablar::page')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Manajemen Sistem
                    </div>

                    <h2 class="page-title">
                        Tambah Sistem
                    </h2>
                </div>

                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('systems.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            {{-- Validation Error --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <div class="d-flex">
                        <i class="ti ti-alert-circle me-2"></i>

                        <div>
                            <h4 class="alert-title">Terjadi kesalahan</h4>

                            <div class="text-secondary">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            <form action="{{ route('systems.store') }}" method="POST">
                @csrf

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            Informasi Sistem
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            {{-- Nama Sistem --}}
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label required">
                                    Nama Sistem
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}"
                                    placeholder="Contoh: SIM Antosa Architect"
                                    maxlength="255"
                                    required
                                    autofocus
                                >

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <small class="form-hint">
                                    Nama sistem yang akan ditampilkan di dalam manajemen sistem.
                                </small>
                            </div>

                            {{-- Base URL --}}
                            <div class="col-md-12 mb-3">
                                <label for="base_url" class="form-label">
                                    Base URL
                                </label>

                                <input
                                    type="url"
                                    name="base_url"
                                    id="base_url"
                                    class="form-control @error('base_url') is-invalid @enderror"
                                    value="{{ old('base_url') }}"
                                    placeholder="https://example.com"
                                    maxlength="255"
                                >

                                @error('base_url')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <small class="form-hint">
                                    URL utama sistem. Contoh:
                                    <code>https://sim.antosaarchitect.com</code>
                                </small>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-12">
                                <div class="form-label">
                                    Status Sistem
                                </div>

                                <label class="form-check form-switch">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        class="form-check-input"
                                        {{ old('is_active', true) ? 'checked' : '' }}
                                    >

                                    <span class="form-check-label">
                                        Sistem Aktif
                                    </span>
                                </label>

                                <small class="form-hint">
                                    Sistem yang aktif dapat digunakan dan ditampilkan pada bagian terkait.
                                </small>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a
                            href="{{ route('systems.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            Batal
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Sistem
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection