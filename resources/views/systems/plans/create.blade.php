@extends('tablar::page')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Manajemen Sistem &bull; {{ $system->name }}
                    </div>

                    <h2 class="page-title">
                        Tambah Plan
                    </h2>
                </div>

                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('systems.edit', $system->id) }}" class="btn btn-outline-secondary">
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

            <form action="{{ route('systems.plans.store', $system->id) }}" method="POST">
                @csrf

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            Informasi Plan
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            {{-- Nama Plan --}}
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label required">
                                    Nama Plan
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}"
                                    placeholder="Contoh: Bulanan, Tahunan"
                                    maxlength="255"
                                    required
                                    autofocus
                                >

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Harga --}}
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label required">
                                    Harga
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>

                                    <input
                                        type="number"
                                        name="price"
                                        id="price"
                                        class="form-control @error('price') is-invalid @enderror"
                                        value="{{ old('price') }}"
                                        placeholder="150000"
                                        min="0"
                                        step="1"
                                        required
                                    >

                                    @error('price')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <small class="form-hint">
                                    Harga per satu kali periode langganan.
                                </small>
                            </div>

                            {{-- Durasi --}}
                            <div class="col-md-6 mb-3">
                                <label for="duration_days" class="form-label required">
                                    Durasi (hari)
                                </label>

                                <input
                                    type="number"
                                    name="duration_days"
                                    id="duration_days"
                                    class="form-control @error('duration_days') is-invalid @enderror"
                                    value="{{ old('duration_days') }}"
                                    placeholder="30"
                                    min="1"
                                    required
                                >

                                @error('duration_days')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <small class="form-hint">
                                    Contoh: 30 untuk bulanan, 365 untuk tahunan.
                                </small>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-12">
                                <div class="form-label">
                                    Status Plan
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
                                        Plan Aktif
                                    </span>
                                </label>

                                <small class="form-hint">
                                    Plan yang aktif bisa dipilih saat customer berlangganan/perpanjang.
                                </small>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a
                            href="{{ route('systems.edit', $system->id) }}"
                            class="btn btn-outline-secondary"
                        >
                            Batal
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Plan
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection