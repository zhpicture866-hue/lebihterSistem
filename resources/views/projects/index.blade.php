@extends('tablar::page')

@section('content')
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                
                <!-- Page title actions -->
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                @can('tambah data proyek')       
                        <a href="{{ route("projects.create") }}" class="btn btn-dark" >
                            <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Tambah Data Proyek
                        </a>
                 @endcan
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-center mb-4">
                                 Daftar Proyek
                            </h2>
                        </div>

                        <div class="table-responsive">
                            <table id="tableProjects" class="table card-table table-vcenter text-nowrap">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        {{-- <th>Kode Proyek</th> --}}
                                        <th>Nama Proyek</th>
                                        <th>Jenis Proyek</th>
                                        <th>Customer</th>
                                        <th>Karyawan</th>
                                        {{-- <th>Affiliator</th> --}}
                                        <th>Tanggal</th>
                                        {{-- <th>Lokasi</th> --}}
                                        {{-- <th>Provinsi</th>
                                        <th>Kabupaten/Kota</th>
                                        <th>Kecamatan</th>
                                        <th>Kelurahan</th>
                                        <th>Kode Pos</th> --}}
                                        <th>Tahapan</th>
                                        {{-- <th>Status</th> --}}
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
@can('tambah data proyek')
<a href="{{ route('projects.create') }}"
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
            const table = $('#tableProjects').DataTable({
                scrollY: '500px',
                scrollX: true,
                scrollCollapse: true,
                fixedColumns: !isMobile ? {
                    leftColumns: 4
                } : false,
                serverSide: true,
                processing: true,
                responsive: false,
                ajax: '{{ route("projects.index") }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    // { data: 'project_code', name: 'project_code' },
                    { data: 'project_name', name: 'project_name' },
                    { data: 'project_type', name: 'project_type' },
                    { data: 'customer', name: 'customer.user.fullname' },
                    { data: 'employee', name: 'employee.user.fullname' },
                    // { data: 'affiliator', name: 'affiliator.user.fullname' },
                    { data: 'start_date', name: 'start_date' },
                    // { data: 'project_location', name: 'project_location' },
                    // { data: 'province_name', name: 'province.name' },
                    // { data: 'city_name', name: 'city.name'},
                    // { data: 'district_name', name: 'district.name' },
                    // { data: 'sub_district_name', name: 'sub_district_name' },
                    // { data: 'postal_code', name: 'postal_code' },
                    { data: 'current_level', name: 'current_level' },
                    // { data: 'project_status', name: 'project_status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Cari proyek...",
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
            $('table').on('click', '.delete-projects', function () {
            const projectId = $(this).data('id');

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

                        url: `/projects/${projectId}`,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },

                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Data Proyek telah dihapus.',
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