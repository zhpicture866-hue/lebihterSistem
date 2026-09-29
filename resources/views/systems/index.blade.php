@extends('tablar::page')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Manajemen
                    </div>

                    <h2 class="page-title">
                        Sistem
                    </h2>
                </div>

                @can('kelola sistem')
                    <div class="col-auto ms-auto d-print-none">
                        <a href="{{ route('systems.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>
                            Tambah Sistem
                        </a>
                    </div>
                @endcan
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">
                        Daftar Sistem
                    </h3>
                </div>

                <div class="card-body border-bottom py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="text-secondary">
                                Daftar sistem yang terdaftar pada platform.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table
                        id="systemsTable"
                        class="table table-vcenter card-table table-striped table-hover"
                        width="100%"
                    >
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Sistem</th>
                                <th>Slug</th>
                                <th>Base URL</th>
                                <th class="text-center">Plans</th>
                                <th class="text-center">Subscriptions</th>
                                <th class="text-center">Status</th>
                                <th width="100" class="text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
@endsection

@push('styles')
    <style>
        #systemsTable td {
            vertical-align: middle;
        }

        #systemsTable .btn + .btn {
            margin-left: 4px;
        }
    </style>
@endpush

@push('js')
<script>
    $(document).ready(function () {

        const table = $('#systemsTable').DataTable({
            processing: true,
            serverSide: true,

            ajax: {
                url: "{{ route('systems.index') }}",
                type: "GET"
            },

            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'slug',
                    name: 'slug'
                },
                {
                    data: 'base_url',
                    name: 'base_url',
                    render: function (data) {
                        if (!data) {
                            return '<span class="text-muted">-</span>';
                        }

                        return data;
                    }
                },
                {
                    data: 'plans_count',
                    name: 'plans_count',
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'subscriptions_count',
                    name: 'subscriptions_count',
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    orderable: true,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],

            order: [
                [1, 'asc']
            ],

            pageLength: 10,

            language: {
                processing: 'Memproses...',
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                infoFiltered: '(difilter dari _MAX_ total data)',
                zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Belum ada data sistem',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: '›',
                    previous: '‹'
                }
            }
        });


        /*
         * Delete System
         */
        $(document).on('click', '.delete-system', function () {

            const id = $(this).data('id');

            Swal.fire({
                title: 'Hapus Sistem?',
                text: 'Data sistem akan dihapus. Tindakan ini tidak dapat dibatalkan.',
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
                    url: "{{ url('systems') }}/" + id,
                    type: 'DELETE',

                    data: {
                        _token: "{{ csrf_token() }}"
                    },

                    success: function (response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message ?? 'Sistem berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        table.ajax.reload(null, false);
                    },

                    error: function (xhr) {

                        let message = 'Terjadi kesalahan saat menghapus sistem.';

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