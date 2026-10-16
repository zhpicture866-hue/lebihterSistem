@extends('tablar::page')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl"><h2 class="page-title">Edit Kategori</h2></div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="card" style="max-width:600px"><div class="card-body">
            <form method="POST" action="{{ route('categories.update', $category) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}"
                           class="form-control @error('slug') is-invalid @enderror">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <button class="btn btn-primary">Perbarui</button>
                <a href="{{ route('categories.index') }}" class="btn btn-link">Batal</a>
            </form>
        </div></div>
    </div>
</div>
@endsection