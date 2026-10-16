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
                                Tambah Data Harga Alat
                            </p>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('equipment_costs.store', ) }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Kode</label>
                                    <input type="text" class="form-control" name="code">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Jenis Peralatan</label>
                                    <input type="text" name="description" class="form-control" value="{{ old('description') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Unit</label>
                                    <input type="text" name="unit" class="form-control" value="{{ old('unit') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Harga dasar</label>

                                    <input 
                                        type="number" 
                                        name="base_unit_price" 
                                        step="0.01"
                                        class="form-control" 
                                        value="{{ old('base_unit_price') }}"
                                        required
                                    >
{{-- 
                                    <input 
                                        type="hidden"
                                        name="base_unit_price"
                                        id="base_unit_price"
                                        value="{{ old('base_unit_price') }}"
                                    > --}}
                                </div>

                                 <div class="mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
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
{{-- @push('js')
<script>

function formatRupiah(number){

    number = Number(number) || 0

    return 'Rp ' + number.toLocaleString('id-ID')

}

function parseRupiah(val){

    if(!val) return 0

    return Number(
        val
        .replace(/[^\d,]/g,'')
        .replace(/\./g,'')
        .replace(',', '.')
    )

}

const displayInput = document.getElementById('base_unit_price_display')
const hiddenInput  = document.getElementById('base_unit_price')


// isi default saat edit / validation error

if(hiddenInput.value){

    displayInput.value = formatRupiah(hiddenInput.value)

}


// realtime formatter

displayInput.addEventListener('input', function(){

    let numericValue = parseRupiah(this.value)

    hiddenInput.value = numericValue

    this.value = formatRupiah(numericValue)

})

</script>
@endpush --}}