{{-- Penting --}}
@extends('tablar::page')

@section('content')
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                  
                        <a href=" {{ route("equipment_costs.index") }} " class="btn btn-dark d-none d-sm-inline-block" >
                            Kembali
                        </a>
                        
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
                            <p class="text-center mb-4" style="font-size: 1.5rem; font-weight: 400; font-family: 'Poppins', sans-serif;">
                                Edit Data Harga
                            </p>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('equipment_costs.update', $equipment_cost->id) }}" method="POST">
                                @csrf
                                @method('put')

                                <div class="mb-3">
                                    <label class="form-label">Kode</label>
                                    <input type="text" class="form-control" name="code" value="{{ old('code', $equipment_cost->code) }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Jenis Peralatan</label>
                                    <input type="text" name="description" class="form-control" value="{{ old('description', $equipment_cost->description) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Unit</label>
                                    <input type="text" name="unit" class="form-control" value="{{ old('unit', $equipment_cost->unit) }}">
                                </div>

                                 <div class="mb-3">
                                    <label class="form-label">Harga dasar</label>
                                    <input type="number" name="base_unit_price" step="0.01" class="form-control" value="{{ old('base_unit_price', $equipment_cost->base_unit_price) }}">
                                </div>

                                 <div class="mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <input type="text" name="notes" class="form-control" value="{{ old('notes', $equipment_cost->notes) }}">
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-dark">Simpan Perubahan</button>
                                </div>
                            </form>
 

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection