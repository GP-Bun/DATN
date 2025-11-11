@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Danh sách danh mục</h4>
        <a href="{{ route('categories.create') }}" class="btn btn-primary">+ Thêm danh mục</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr class="text-center">
                <th width="5%">#</th>
                <th>Tên danh mục</th>
                <th>Slug</th>
                <th>Mô tả</th>
                <th width="20%">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $index => $category)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->slug }}</td>
                    <td>{{ $category->description }}</td>
                    <td class="text-center">
                        <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xóa danh mục này?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Chưa có danh mục nào</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
