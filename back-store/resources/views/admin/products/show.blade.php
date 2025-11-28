@extends('layouts.app')

@section('page-title', 'Chi tiết sản phẩm')

@section('content')
<div class="bg-white p-4 rounded shadow-sm">
    <h4 class="mb-3">Chi tiết sản phẩm</h4>

    <div class="mb-3">
        <strong>Tên sản phẩm:</strong> {{ $product->name }}
    </div>

    <div class="mb-3">
        <strong>Slug:</strong> {{ $product->slug }}
    </div>

    <div class="mb-3">
        <strong>Danh mục:</strong> {{ $product->category->name ?? 'Không có' }}
    </div>

    <div class="mb-3">
        <strong>Mô tả:</strong> {{ $product->description ?? '...' }}
    </div>

    <div class="mb-3">
        <strong>Giá hiển thị:</strong> {{ number_format($product->price, 0, ',', '.') }} VND
    </div>

    <div class="mb-3">
        <strong>Trạng thái:</strong>
        @if ($product->status == 1)
            <span class="badge bg-success">Còn hàng</span>
        @elseif ($product->status == 2)
            <span class="badge bg-danger">Hết hàng</span>
        @else
            <span class="badge bg-secondary">Ẩn</span>
        @endif
    </div>

    <div class="mb-3">
        <strong>Ảnh đại diện:</strong><br>
        <img src="{{ asset('storage/' . $product->thumbnail) }}" style="width:150px" class="rounded">
    </div>

    <div class="mb-3">
        <strong>Ảnh bổ sung:</strong><br>
        @foreach ($product->images ?? [] as $img)
            <img src="{{ asset('storage/' . $img) }}" style="width:100px" class="me-2 rounded">
        @endforeach
    </div>

    <hr>
    <h5>Biến thể</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Màu</th>
                <th>Size</th>
                <th>Giá gốc</th>
                <th>Giá giảm</th>
                <th>Tồn kho</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($product->variants as $variant)
                <tr>
                    <td>{{ $variant->color }}</td>
                    <td>{{ $variant->size }}</td>
                    <td>{{ number_format($variant->original_price, 0, ',', '.') }} VND</td>
                    <td>{{ number_format($variant->sale_price, 0, ',', '.') }} VND</td>
                    <td>{{ $variant->stock }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-3">
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Quay lại</a>
        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">Sửa</a>
    </div>
</div>
@endsection
