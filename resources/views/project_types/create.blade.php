@extends('tablar::page')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('project_types.index') }}" class="btn btn-dark d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>
                
                    <h2 class="page-title mb-0">Tambah Jenis Proyek</h2>
                
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
                                Tambah Jenis Proyek
                            </p>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('project_types.store') }}" method="POST">
                                @csrf
                                @include('project_types._form')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection