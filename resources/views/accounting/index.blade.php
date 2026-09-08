@extends('tablar::page')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                
                <!-- Page title actions -->
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                        @can('tambah akun-akuntansi')       
                        <span class="d-none d-sm-inline">
                                <a href="{{ route("accounting.create") }}" class="btn btn-dark d-none d-sm-inline-block" >
                                    <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <line x1="12" y1="5" x2="12" y2="19"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    Tambah Akun
                                </a>
                            </span>
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
                                    Daftar Akun
                            </h2>
                        </div>
                        <div class="table-responsive">
                            <table id="tableAccounts" class="table card-table table-vcenter text-nowrap" >
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Kode Akun</th>
                                        <th>Nama Akun</th>
                                        <th>Kategori</th>
                                        <th>Sub Kategori</th>
                                        <th>Saldo Awal</th>
                                        <th>Akun Induk</th>
                                        <th>Apakah Akun Induk?</th>
                                        <th>Status</th>
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
    @can('tambah akun-akuntansi')
        <a href="{{ route('accounting.create') }}"
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
    $(function() {
        const isMobile = window.innerWidth < 576;
        const table = $('#tableAccounts').DataTable({
            scrollY: '500px',
            scrollX: true,
            scrollCollapse: true,
                fixedColumns: !isMobile ? {
                    leftColumns: 3
                } : false,
            serverSide: true,
            processing: true,
            responsive: false,
            ajax: '{{ route("accounting.index") }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'account_code', name: 'account_code' },
                { data: 'account_name', name: 'account_name' },
                { data: 'category', name: 'category' },
                { data: 'sub_category', name: 'sub_category' },
                { data: 'initial_balance', name: 'initial_balance' },
                { data: 'parent_name', name: 'parent.account_name', defaultContent: '-' },
                { data: 'is_parent', name: 'is_parent' },
                { data: 'status', name: 'is_active' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
            ],
            language: {
                search: "",
                searchPlaceholder: "Cari akun...",
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

        // Delete user functionally
        $('table').on('click', '.delete-accounts', function () {
        const accountId = $(this).data('id');

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

                    url: `/accounting/${accountId}`,
                    method: 'DELETE',
                    data: {
                            _token: '{{ csrf_token() }}',
                        },

                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Data Akun telah dihapus.',
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
@endpush