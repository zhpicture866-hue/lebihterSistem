@extends('tablar::page')

@section('content')
@php
    $excluded = array_merge([$category->id], $category->descendantIds());
    $options  = \App\Models\Category::flatTree()->reject(fn ($c) => in_array($c->id, $excluded));
@endphp

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
                    <label class="form-label">Induk kategori</label>
                    <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                        <option value="">— Tanpa induk —</option>
                        @foreach($options as $c)
                            <option value="{{ $c->id }}"
                                @selected((string) old('parent_id', $category->parent_id) === (string) $c->id)>
                                {{ str_repeat('— ', $c->depth) }}{{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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