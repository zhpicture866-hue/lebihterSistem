@extends('tablar::page')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                
                <!-- Page title actions -->
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                @can('tambah jurnal')       
                        <a href="{{ route("journals.create") }}" class="btn btn-dark">
                            <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Tambah Jurnal
                        </a>
                 @endcan
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-center mb-4">
                                    Daftar Jurnal
                            </h2>
                        </div>
                        <div class="table-responsive">

                            <table id="journals-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>No. Transaksi</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>PIC</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @can('tambah jurnal')
    <a href="{{ route('journals.create') }}"
    class="mobile-fab d-md-none">

        <svg xmlns="http://www.w3.org/2000/svg"
            width="26"
            height="26"
            viewBox="0 0 24 24"
            stroke-width="2"
            stroke="currentColor"
            fill="none"
            stroke-linecap="round"
            stroke-linejoin="round">

            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>

        </svg>

    </a>
    @endcan
@endsection

@push('js')
<script>
$(function () {
    const isMobile = window.innerWidth < 576;
    const table = $('#journals-table').DataTable({
        scrollY: '500px',
        scrollX: true,
        scrollCollapse: true,
        fixedColumns: !isMobile ? {
            leftColumns: 0
        } : false,
        processing: true,
        serverSide: true,
        responsive: false,
        ajax: '{{ route("journals.index") }}',
        order: [[2, 'desc']],
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            // { data: 'license_type', name: 'licenses.license_type' },
            // { data: 'license_name', name: 'licenses.name' },
            { data: 'journal_code', name: 'accounting_journals.journal_code' },
            { data: 'transaction_date', name: 'accounting_journals.transaction_date' },
            { data: 'creator_name', name: 'users.fullname' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
                    language: {
                search: "",
                searchPlaceholder: "Cari jurnal...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "›",
                    previous: "‹"
                }
            },

            initComplete: function () {
                const input = $('.dt-search input');
                input.removeClass('form-control-sm')
                    .addClass('form-control');
            }
    });

        $('#journals-table').on('click', '.delete-journal', function () {
            const journalId = $(this).data('id');

            Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data akan hilang secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'

            }).then((result) => {

                if (result.isConfirmed) {
                    $.ajax({

                        url: `/journals/${journalId}`,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },

                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Jurnal berhasil dihapus.',
                                    timer: 2000,
                                    showConfirmButton: false
                            });

                        table.ajax.reload(null, false); // refresh datatable
                        } else {

                            Swal.fire('Gagal', response.message || 'Tidak bisa menghapus data.', 'error');
                        }
                        },

                    error: function () {

                    Swal.fire('Error', 'Terjadi kesalahan saat menghapus.', 'error');
                    }

                    });
                }
            });
        });
});
</script>

@if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: '{{ session('success') }}',
            timer: 2000,
            showConfirmButton: false
        });
    </script>
    @endif
@endpush