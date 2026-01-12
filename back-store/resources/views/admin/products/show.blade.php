@extends('layouts.app')

@section('page-title', 'Chi tiết sản phẩm')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Chi tiết sản phẩm</h4>
        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-secondary">
            ← Quay lại danh sách
        </a>
    </div>

    <div class="card-body">

        {{-- Thông tin cơ bản --}}
        <h5 class="text-primary fw-bold mb-3">Thông tin sản phẩm</h5>
        <div class="row mb-4">

            <div class="col-md-6">
                <p><strong>Tên sản phẩm:</strong> {{ $product->name }}</p>
                <p><strong>Slug:</strong> {{ $product->slug }}</p>
                <p><strong>Danh mục:</strong> {{ $product->category->name ?? 'Không có' }}</p>

                <p>
                    <strong>Trạng thái:</strong>
                    @if ($product->status == 1)
                        <span class="badge bg-success">Còn hàng</span>
                    @elseif ($product->status == 2)
                        <span class="badge bg-danger">Hết hàng</span>
                    @else
                        <span class="badge bg-secondary">Ẩn</span>
                    @endif
                </p>

                <p>
                    <strong>Giá hiển thị:</strong>
                    <span class="text-danger fw-bold">
                        {{ number_format($product->price, 0, ',', '.') }}đ
                    </span>
                </p>
            </div>

            <div class="col-md-6">
                <p><strong>Mô tả:</strong></p>
                <div class="p-2 bg-light rounded border" style="min-height: 80px;">
                    {{ $product->description ?? 'Không có mô tả' }}
                </div>
            </div>

        </div>

        {{-- Hình ảnh --}}
        <h5 class="text-primary fw-bold mb-3">Hình ảnh</h5>

        <div class="row mb-4">
            <div class="col-md-4">
                <p class="fw-semibold">Ảnh đại diện:</p>
                <img src="{{ asset('storage/' . $product->thumbnail) }}"
                     class="img-thumbnail rounded"
                     style="width: 100%; max-width: 250px;">
            </div>

            <div class="col-md-8">
                <p class="fw-semibold">Ảnh bổ sung:</p>
                @if ($product->images && count($product->images))
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($product->images as $img)
                            <img src="{{ asset('storage/' . $img) }}"
                                class="rounded border"
                                style="width:100px; height:100px; object-fit:cover;">
                        @endforeach
                    </div>
                @else
                    <p>Không có ảnh bổ sung.</p>
                @endif
            </div>
        </div>

        {{-- Biến thể sản phẩm --}}
        <h5 class="text-primary fw-bold mt-4 mb-3">Biến thể sản phẩm</h5>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Màu</th>
                        <th>Size</th>
                        <th>Giá gốc</th>
                        <th>Giá giảm</th>
                        <th>Tồn kho</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($product->variants as $variant)
                        <tr class="text-center">
                            <td>{{ $variant->color?->name ?: '-' }}</td>
                            <td>{{ $variant->size?->value ?: '-' }}</td>
                            <td class="text-nowrap">{{ number_format($variant->original_price, 0, ',', '.') }}đ</td>
                            <td class="text-nowrap">
                                @if ($variant->sale_price)
                                    <span class="text-danger fw-semibold">
                                        {{ number_format($variant->sale_price, 0, ',', '.') }}đ
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $variant->stock }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Buttons --}}
        <div class="mt-4 text-end">
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">
                ✏️ Chỉnh sửa sản phẩm
            </a>
        </div>

    </div>
</div>
@endsection
