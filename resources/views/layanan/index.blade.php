@extends('tablar::page')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                 @can('tambah data alat')       
                        <a href="{{ route("equipment_costs.create") }}" class="btn btn-dark">
                            <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Tambah Data Harga Alat
                        </a>
                 @endcan
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <p class="text-center mb-4" style="font-size: 1.5rem; font-weight: 400; font-family: 'Poppins', sans-serif;">
                                Daftar Harga Alat
                            </p>
                        </div>
                        <div class="table-responsive">
                            <table id="toolsTable" class="table card-table table-vcenter text-nowrap">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Uraian Peralatan</th>
                                        <th>Satuan</th>
                                        <th>Harga Satuan Dasar (Rp.)</th>
                                        <th>Keterangan</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- Modal Delete -->
<div class="modal fade" id="modalDelete" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" id="formDelete">
        @csrf
        @method('DELETE')

        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">Konfirmasi Hapus</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <p>Anda yakin mau menghapus data ini?</p>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
          </div>
        </div>
    </form>
  </div>
</div>

@endsection


@push('js')

<script>
    $(function () {
        $('#toolsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('equipment_costs.index') }}",

            columns: [
                { data: 'DT_RowIndex', orderable:false, searchable:false },
                { data: 'code' },
                { data: 'description' },
                { data: 'unit' },
                {
                    data: 'base_unit_price',
                    render: function(data, type) {
                        if (type === 'display') {
                            return 'Rp ' + parseInt(data).toLocaleString('id-ID');
                        }
                        return data; // untuk sorting tetap angka
                    }
                },
                { data: 'notes' },
                { data: 'action', orderable:false, searchable:false },
            ],
            order: [[0, 'desc']], 
                        language: {
                            search: "",
                            searchPlaceholder: "Cari alat...",
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

        // modal delete
        $(document).on('click', '.btn-delete', function() {
            const url = $(this).data('url');
            $('#formDelete').attr('action', url);
            $('#modalDelete').modal('show');
        });

        $(document).on('click', '.btn-duplicate', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Duplikat data?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, duplikat!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/tools/${id}/duplicate`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            $('#toolsTable').DataTable().ajax.reload(null, false);

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
