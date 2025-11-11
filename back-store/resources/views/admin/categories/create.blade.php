@extends('layouts.app')

@section('title', 'Thêm danh mục')

@section('content')
<div class="container">
    <h2>Thêm danh mục mới</h2>

    {{-- Hiển thị thông báo thành công nếu có --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Hiển thị lỗi nếu có --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Tên danh mục</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Mô tả (tùy chọn)</label>
            <textarea name="description" id="description" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Thêm danh mục</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection
