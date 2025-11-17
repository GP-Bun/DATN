@extends('layouts.app')

@section('title', 'Chỉnh sửa danh mục')

@section('content')
<div class="bg-white p-4 rounded shadow-sm">
    <h4 class="mb-3">Chỉnh sửa danh mục</h4>

    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Tên danh mục</label>
            <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" class="form-control" required>
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Mô tả (tùy chọn)</label>
            <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $category->description) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Hủy</a>
    </form>
</div>
@endsection
