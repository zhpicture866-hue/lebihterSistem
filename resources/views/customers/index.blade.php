@extends('tablar::page')

@section('content')
    @php
        $cards = [
            1 => ['label' => 'Total Leads',  'icon' => 'ti-user-search'],
            2 => ['label' => 'Prospek',      'icon' => 'ti-target-arrow'],
            3 => ['label' => 'New Customer', 'icon' => 'ti-user-plus'],
            4 => ['label' => 'Silver',       'icon' => 'ti-medal'],
            5 => ['label' => 'Gold',         'icon' => 'ti-trophy'],
            6 => ['label' => 'Platinum',     'icon' => 'ti-diamond'],
        ];
        $grand = array_sum($stats);
    @endphp

    <style>
        /* ---------- Stat cards ---------- */
        .stat-card { border: 1px solid #eef0f4; border-radius: 14px; box-shadow: 0 1px 2px rgba(16,24,40,.04); }
        .stat-card .stat-label { font-size: .8rem; color: #667085; }
        .stat-card .stat-value { font-size: 1.7rem; font-weight: 700; line-height: 1.2; color: #101828; }
        .stat-card .stat-sub   { font-size: .72rem; color: #98a2b3; }
        .stat-icon { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; }

        /* ---------- Warna per level ---------- */
        .lv-1 { --c: #667085; --bg: #f2f4f7; }
        .lv-2 { --c: #b54708; --bg: #fffaeb; }
        .lv-3 { --c: #175cd3; --bg: #eff8ff; }
        .lv-4 { --c: #475467; --bg: #eaecf0; }
        .lv-5 { --c: #b78103; --bg: #fef7c3; }
        .lv-6 { --c: #6941c6; --bg: #f4ebff; }
        .stat-icon.lv-1, .stat-icon.lv-2, .stat-icon.lv-3,
        .stat-icon.lv-4, .stat-icon.lv-5, .stat-icon.lv-6 { color: var(--c); background: var(--bg); }
        .lv-badge { display: inline-block; padding: 3px 12px; border-radius: 999px; font-size: .75rem; font-weight: 600; color: var(--c); background: var(--bg); }

        /* ---------- Tabs filter ---------- */
        /* Kartu Total Customer (highlight) */
        .stat-total { background: #101828; border-color: #101828; }
        .stat-total .stat-label, .stat-total .stat-sub { color: #d0d5dd; }
        .stat-total .stat-value { color: #fff; }
        .stat-total .stat-icon { background: rgba(255,255,255,.14); color: #fff; }

        /* Tab bisa digeser (swipe) horizontal di mobile */
        .filter-tabs {
            display: flex; flex-wrap: nowrap; gap: 2px;
            width: fit-content; max-width: 100%;
            overflow-x: auto; -webkit-overflow-scrolling: touch;
            scroll-snap-type: x proximity; scrollbar-width: none;
            background: #f2f4f7; padding: 4px; border-radius: 10px;
        }
        .filter-tabs::-webkit-scrollbar { display: none; }
        .filter-tabs .tab-btn { white-space: nowrap; flex-shrink: 0; scroll-snap-align: center; }
        @media (max-width: 575.98px) { .filter-tabs { width: 100%; } }
        .filter-tabs .tab-btn { border: 0; background: transparent; padding: 6px 14px; border-radius: 8px; font-size: .8rem; font-weight: 500; color: #667085; cursor: pointer; }
        .filter-tabs .tab-btn.active { background: #fff; color: #101828; box-shadow: 0 1px 2px rgba(16,24,40,.1); }

        /* ---------- Tabel ---------- */
        #tableCustomers thead th { background: #f9fafb; font-size: .72rem; text-transform: none; letter-spacing: 0; color: #667085; font-weight: 600; border-bottom: 1px solid #eaecf0; }
        #tableCustomers tbody td { padding-top: 14px; padding-bottom: 14px; border-bottom: 1px solid #f2f4f7; }
        #tableCustomers tbody tr:hover { background: #fafbff; }
        .cust-avatar { width: 38px; height: 38px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: .78rem; font-weight: 700; flex-shrink: 0; }
        .cust-avatar.av-0 { background: #e0eaff; color: #3538cd; }
        .cust-avatar.av-1 { background: #d1fadf; color: #027a48; }
        .cust-avatar.av-2 { background: #fef0c7; color: #b54708; }
        .cust-avatar.av-3 { background: #ffe4e8; color: #c01048; }
        .cust-avatar.av-4 { background: #f4ebff; color: #6941c6; }
        .cust-name  { font-weight: 600; color: #101828; text-decoration: none; }
        .cust-name:hover { color: #4f46e5; }
        .cust-email { font-size: .75rem; color: #98a2b3; }

        /* DataTables */
        .dt-container .dt-search input { border-radius: 999px; padding-left: 14px; min-width: 240px; }
        .dt-container .dt-paging .dt-paging-button.current { background: #4f46e5 !important; color: #fff !important; border-color: #4f46e5 !important; border-radius: 8px; }
    </style>

    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Customers</h2>
                    <div class="text-secondary small">Semua data pelanggan dalam satu tempat.</div>
                </div>
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    @can('tambah data customer')
                        <a href="{{ route('customers.create') }}" class="btn btn-dark">
                            <i class="ti ti-plus me-1"></i> Tambah Data Customer
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            {{-- ===== Kartu ringkasan ===== --}}
            <div class="row g-3 mb-3">
                {{-- Total seluruh customer --}}
                <div class="col-12 col-xl">
                    <div class="card stat-card stat-total">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="stat-label">Total Customer</div>
                                <span class="stat-icon"><i class="ti ti-users"></i></span>
                            </div>
                            <div class="stat-value mt-1" data-stat="total">{{ number_format($grand) }}</div>
                            <div class="stat-sub">Seluruh level</div>
                        </div>
                    </div>
                </div>

                @foreach ($cards as $level => $c)
                    <div class="col-6 col-md-4 col-xl">
                        <div class="card stat-card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="stat-label">{{ $c['label'] }}</div>
                                    <span class="stat-icon lv-{{ $level }}"><i class="ti {{ $c['icon'] }}"></i></span>
                                </div>
                                <div class="stat-value mt-1" data-stat="{{ $level }}">{{ number_format($stats[$level]) }}</div>
                                <div class="stat-sub" data-stat-pct="{{ $level }}">
                                    {{ $grand > 0 ? round($stats[$level] / $grand * 100) : 0 }}% dari total
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ===== Tabel ===== --}}
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div class="filter-tabs" id="loyaltyTabs">
                            <button type="button" class="tab-btn active" data-level="">Semua</button>
                            @foreach ($cards as $level => $c)
                                <button type="button" class="tab-btn" data-level="{{ $level }}">
                                    {{ $level === 1 ? 'Lead' : $c['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="tableCustomers" class="table table-vcenter text-nowrap w-100">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Customer</th>
                                    <th>ID Customer</th>
                                    <th>Channel</th>
                                    <th>Kategori</th>
                                    <th>Order</th>
                                    <th>Loyalty</th>
                                    <th width="15%" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('tambah data customer')
        <a href="{{ route('customers.create') }}" class="mobile-fab d-md-none">
            <i class="ti ti-plus" style="font-size:26px"></i>
        </a>
    @endcan
@endsection

@push('js')
    <script>
        $(function () {
            let activeLevel = '';

            const table = $('#tableCustomers').DataTable({
                scrollX: true,
                serverSide: true,
                processing: true,
                responsive: false,
                ajax: {
                    url: '{{ route('customers.index') }}',
                    data: function (d) { d.loyalty = activeLevel; }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'customer', name: 'customer', orderable: false },
                    { data: 'nic', name: 'nic' },
                    { data: 'channel', name: 'channel' },
                    { data: 'category', name: 'category' },
                    { data: 'orders', name: 'orders', searchable: false },
                    { data: 'loyalty', name: 'loyalty', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' },
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Cari pelanggan...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    zeroRecords: "Data tidak ditemukan",
                    paginate: { first: "Awal", last: "Akhir", next: "›", previous: "‹" }
                },
                initComplete: function () {
                    $('.dt-search input').removeClass('form-control-sm').addClass('form-control');
                }
            });

            // Tab filter
            $('#loyaltyTabs').on('click', '.tab-btn', function () {
                $('#loyaltyTabs .tab-btn').removeClass('active');
                $(this).addClass('active');
                this.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                activeLevel = $(this).data('level');
                table.ajax.reload();
            });

            // Update kartu setelah data di-reload (misal habis hapus)
            table.on('xhr.dt', function (e, settings, json) {
                if (!json || !json.stats) return;
                const total = Object.values(json.stats).reduce((a, b) => a + b, 0);
                $('[data-stat="total"]').text(total.toLocaleString('id-ID'));
                Object.entries(json.stats).forEach(([lvl, n]) => {
                    $('[data-stat="' + lvl + '"]').text(n.toLocaleString('id-ID'));
                    $('[data-stat-pct="' + lvl + '"]').text((total ? Math.round(n / total * 100) : 0) + '% dari total');
                });
            });

            // Hapus customer
            $('#tableCustomers').on('click', '.delete-customer', function () {
                const customerId = $(this).data('id');

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
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/customers/${customerId}`,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data Pelanggan telah dihapus.', timer: 2000, showConfirmButton: false });
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire('Gagal', response.message || 'Tidak bisa menghapus data.', 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Terjadi kesalahan saat menghapus.', 'error');
                        }
                    });
                });
            });
        });
    </script>

    @if (session('success'))
        <script>
            Swal.fire({ icon: 'success', title: 'Sukses!', text: '{{ session('success') }}', timer: 2000, showConfirmButton: false });
        </script>
    @endif
@endpush