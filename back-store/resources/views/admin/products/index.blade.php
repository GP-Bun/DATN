@extends('layouts.app')

@section('page-title', 'Danh sách sản phẩm')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h2>Danh sách sản phẩm</h2>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus"></i> Thêm sản phẩm
    </a>
</div>

{{-- Thông báo thành công --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Tên sản phẩm</th>
            <th>Danh mục</th>
            <th>Giá</th>
            <th>Trạng thái</th>
            <th>Hình ảnh</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name ?? '-' }}</td>
                <td>{{ number_format($product->price, 0, ',', '.') }}₫</td>
                <td>{{ $product->status }}</td>
                <td>
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="Ảnh" width="80" class="img-thumbnail">
                    @else
                        —
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning btn-sm mb-1">Sửa</a>

                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                    </form>
                </td>
            </tr>

            {{-- Hiển thị biến thể ngay dưới sản phẩm --}}
            @foreach($product->variants as $variant)
                <tr class="table-secondary">
                    <td></td>
                    <td colspan="2"><strong>Biến thể:</strong> {{ $variant->color }} / {{ $variant->size }}</td>
                    <td>{{ number_format($variant->price, 0, ',', '.') }}₫</td>
                    <td colspan="3">Stock: {{ $variant->stock }}
                        <form action="{{ route('admin.variants.destroy', $variant->id) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Bạn có chắc muốn xóa biến thể này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="7" class="text-center">
                    Chưa có sản phẩm nào. <a href="{{ route('admin.products.create') }}">Thêm sản phẩm mới</a>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Pagination --}}
<div class="d-flex justify-content-center mt-3">
    {{ $products->links() }}
</div>
@endsection