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
                        Edit Sistem
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

            {{-- Success Message --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <div class="d-flex">
                        <i class="ti ti-check me-2"></i>

                        <div>
                            {{ session('success') }}
                        </div>
                    </div>

                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            {{-- Validation Error --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <div class="d-flex">
                        <i class="ti ti-alert-circle me-2"></i>

                        <div>
                            <h4 class="alert-title">
                                Terjadi kesalahan
                            </h4>

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

            <div class="row row-cards">

                {{-- Informasi Sistem --}}
                <div class="col-lg-7">
                    <form
                        action="{{ route('systems.update', $system->id) }}"
                        method="POST"
                    >
                        @csrf
                        @method('PUT')

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    Informasi Sistem
                                </h3>
                            </div>

                            <div class="card-body">
                                <div class="row">

                                    {{-- Nama --}}
                                    <div class="col-12 mb-3">
                                        <label for="name" class="form-label required">
                                            Nama Sistem
                                        </label>

                                        <input
                                            type="text"
                                            name="name"
                                            id="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name', $system->name) }}"
                                            maxlength="255"
                                            required
                                        >

                                        @error('name')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- Slug --}}
                                    <div class="col-12 mb-3">
                                        <label for="slug" class="form-label">
                                            Slug
                                        </label>

                                        <div class="input-group">
                                            <input
                                                type="text"
                                                id="slug"
                                                class="form-control"
                                                value="{{ $system->slug }}"
                                                readonly
                                            >

                                            <span class="input-group-text">
                                                <i class="ti ti-lock"></i>
                                            </span>
                                        </div>

                                        <small class="form-hint">
                                            Slug merupakan identifier sistem dan tidak diubah
                                            otomatis ketika nama sistem berubah.
                                        </small>
                                    </div>

                                    {{-- Base URL --}}
                                    <div class="col-12 mb-3">
                                        <label for="base_url" class="form-label">
                                            Base URL
                                        </label>

                                        <input
                                            type="url"
                                            name="base_url"
                                            id="base_url"
                                            class="form-control @error('base_url') is-invalid @enderror"
                                            value="{{ old('base_url', $system->base_url) }}"
                                            maxlength="255"
                                            placeholder="https://example.com"
                                        >

                                        @error('base_url')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                        <small class="form-hint">
                                            URL utama sistem.
                                        </small>
                                    </div>

                                    {{-- Status --}}
                                    <div class="col-12">
                                        <div class="form-label">
                                            Status Sistem
                                        </div>

                                        <label class="form-check form-switch">
                                            <input
                                                type="checkbox"
                                                name="is_active"
                                                value="1"
                                                class="form-check-input"
                                                {{ old('is_active', $system->is_active) ? 'checked' : '' }}
                                            >

                                            <span class="form-check-label">
                                                Sistem Aktif
                                            </span>
                                        </label>

                                        <small class="form-hint">
                                            Nonaktifkan jika sistem sementara tidak digunakan.
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
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Informasi Plan --}}
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    Plan Sistem
                                </h3>

                                <div class="text-secondary small">
                                    Daftar plan yang tersedia untuk sistem ini.
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            @if ($system->plans->count())
                                <div class="list-group list-group-flush">
                                    @foreach ($system->plans as $plan)
                                        <div class="list-group-item">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="fw-semibold">
                                                        {{ $plan->name }}
                                                    </div>

                                                    <div class="text-secondary small">
                                                        Rp {{ number_format($plan->price, 0, ',', '.') }}
                                                        &bull; {{ $plan->duration_days }} hari
                                                    </div>
                                                </div>

                                                <div class="ms-auto d-flex align-items-center gap-2">
                                                    @if ($plan->is_active)
                                                        <span class="badge bg-success-lt">
                                                            Aktif
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary-lt">
                                                            Nonaktif
                                                        </span>
                                                    @endif

                                                    <a
                                                        href="{{ route('systems.plans.edit', [$system->id, $plan->id]) }}"
                                                        class="btn btn-icon btn-sm btn-outline-secondary"
                                                    >
                                                        <i class="ti ti-edit"></i>
                                                    </a>

                                                    <button
                                                        type="button"
                                                        data-id="{{ $plan->id }}"
                                                        class="btn btn-icon btn-sm btn-outline-danger delete-plan"
                                                    >
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="ti ti-package-off"></i>
                                    </div>

                                    <p class="empty-title">
                                        Belum ada plan
                                    </p>

                                    <p class="empty-subtitle text-secondary">
                                        Tambahkan minimal satu plan untuk sistem ini.
                                    </p>

                                    <div class="empty-action">
                                        <a
                                            href="{{ route('systems.plans.create', $system->id) }}"
                                            class="btn btn-primary"
                                        >
                                            <i class="ti ti-plus me-1"></i>
                                            Tambah Plan
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($system->plans->count())
                            <div class="card-footer">
                                <a
                                    href="{{ route('systems.plans.create', $system->id) }}"
                                    class="btn btn-outline-primary w-100"
                                >
                                    <i class="ti ti-plus me-1"></i>
                                    Tambah Plan
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection

@push('js')
<script>
    $(document).ready(function () {

        /*
         * Delete Plan
         */
        $(document).on('click', '.delete-plan', function () {

            const id = $(this).data('id');

            Swal.fire({
                title: 'Hapus Plan?',
                text: 'Data plan akan dihapus. Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ url('systems/' . $system->id . '/plans') }}/" + id,
                    type: 'DELETE',

                    data: {
                        _token: "{{ csrf_token() }}"
                    },

                    success: function (response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message ?? 'Plan berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },

                    error: function (xhr) {

                        let message = 'Terjadi kesalahan saat menghapus plan.';

                        if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: message
                        });
                    }
                });

            });
        });

    });
</script>
@endpush