@extends('layouts.app')

@section('page-title', 'Quản lý sản phẩm')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1 text-primary fw-bold">Danh sách sản phẩm</h4>
        <p class="text-muted mb-0">Quản lý các sản phẩm, biến thể và kho hàng của bạn.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.products.trash') }}" class="btn btn-light border">
            🗑️ Thùng rác <span class="badge bg-secondary text-white ms-1">{{ $trashCount }}</span>
        </a>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> + Thêm sản phẩm mới
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="bg-light text-uppercase text-muted small fw-bold">
                    <tr>
                        <th class="ps-3" style="width: 50px;">#ID</th>
                        <th style="min-width: 250px;">Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th class="text-end pe-3">Hành động</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse ($products as $product)
                        <tr class="{{ $loop->even ? 'bg-light bg-opacity-10' : '' }}">
                            <td class="ps-3 text-muted fw-bold">#{{ $product->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3 position-relative">
                                        <img src="{{ asset('storage/' . $product->thumbnail) }}" 
                                             class="rounded border" 
                                             style="width: 50px; height: 50px; object-fit: cover;" 
                                             alt="{{ $product->name }}">
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.products.show', $product->id) }}" class="text-dark fw-bold text-decoration-none d-block">
                                            {{ \Illuminate\Support\Str::limit($product->name, 40) }}
                                        </a>
                                        @if($product->variants->count() > 0)
                                            <small class="text-muted">
                                                <i class="fas fa-layer-group me-1"></i>{{ $product->variants->count() }} biến thể
                                            </small>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Variants Collapse (Optional, displayed inline just below if needed, or structured differently) --}}
                                @if($product->variants->isNotEmpty())
                                    <div class="mt-2 text-muted small ps-5 border-start border-3 ms-2">
                                        @foreach($product->variants as $variant)
                                            <div class="d-flex gap-3 align-items-center mb-1">
                                                <span>
                                                    <span class="fw-semibold">Màu:</span> {{ $variant->color?->name ?? 'N/A' }} 
                                                    - <span class="fw-semibold">Size:</span> {{ $variant->size?->value ?? 'N/A' }}
                                                </span>
                                                <span class="badge {{ $variant->stock > 0 ? 'bg-info bg-opacity-10 text-info' : 'bg-danger bg-opacity-10 text-danger' }} border border-light">
                                                    Kho: {{ $variant->stock }}
                                                </span>
                                                @if($variant->sale_price)
                                                    <span>{{ number_format($variant->sale_price, 0, ',', '.') }}đ <del class="text-muted ms-1">{{ number_format($variant->original_price, 0, ',', '.') }}đ</del></span>
                                                @else
                                                    <span>{{ number_format($variant->original_price, 0, ',', '.') }}đ</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $product->category->name ?? 'Chưa phân loại' }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary">
                                    {{ number_format($product->price, 0, ',', '.') }}đ
                                </span>
                            </td>
                            <td>
                                @if ($product->status == 1)
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">
                                        <i class="fas fa-check-circle me-1"></i>Còn hàng
                                    </span>
                                @elseif ($product->status == 2)
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">
                                        <i class="fas fa-times-circle me-1"></i>Hết hàng
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">
                                        <i class="fas fa-eye-slash me-1"></i>Ẩn
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group">
                                    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                        Xem
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-warning" title="Sửa">
                                        Sửa
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này vào thùng rác?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted mb-3">
                                    <i class="fas fa-box-open fa-3x"></i>
                                </div>
                                <h5 class="text-muted">Chưa có sản phẩm nào</h5>
                                <a href="{{ route('admin.products.create') }}" class="btn btn-primary mt-2">
                                    + Thêm sản phẩm ngay
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
