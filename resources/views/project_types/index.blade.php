@extends('tablar::page')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-auto ms-auto d-print-none">
                <div class="btn-list">
                {{-- @can('tambah data karyawan')        --}}
                <span class="d-none d-sm-inline">
                    <a href="{{ route("project_types.create") }}" class="btn btn-dark d-none d-sm-inline-block" >
                        <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Tambah Data Jenis Proyek
                    </a>
                </span>
                {{-- @endcan --}}
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
                        <p class="text-center mb-4" style="font-size: 1.5rem; font-weight: 400; font-family: 'Poppins', sans-serif;">
                                Daftar Jenis Proyek
                        </p>
                    </div>
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table id="table-project-types" class="table table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Kode</th>
                                    <th>Status</th>
                                    <th>Jumlah Project</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>                        
</div>
@can('tambah data jenis')
<a href="{{ route('project_types.create') }}"
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
    const table = $('#table-project-types').DataTable({
                serverSide: true,
                processing: true,
                responsive: false,
        ajax: '{{ route('project_types.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code' },
            { data: 'is_active', name: 'is_active', orderable: false, searchable: false },
            { data: 'projects_count', name: 'projects_count', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
                language: {
                    search: "",
                    searchPlaceholder: "Cari jenis...",
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

    // Delete via AJAX (tombol dirender dari controller: class="delete-project-type" data-id="...")
    $(document).on('click', '.delete-project-type', function () {
        const id = $(this).data('id');

        if (!confirm('Yakin mau hapus jenis proyek ini?')) {
            return;
        }

        $.ajax({
            url: `/project_types/${id}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                alert(res.message);
                table.ajax.reload();
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message ?? 'Gagal menghapus jenis proyek.';
                alert(msg);
            },
        });
    });
});
</script>
@endpush