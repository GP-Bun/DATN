@extends('layouts.app')

@section('title', 'Quản lý sản phẩm')

@section('content')
    <div class="bg-white p-4 rounded shadow-sm">
        <h4 class="mb-3">Danh sách sản phẩm</h4>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <a href="{{ route('admin.products.create') }}" class="btn btn-primary mb-3">Thêm sản phẩm</a>
        <a href="{{ route('admin.products.trash') }}" class="btn btn-secondary mb-3">
            Thùng rác <span class="badge bg-danger">{{ $trashCount }}</span>
        </a>



        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Ảnh</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                        <tr>
                            <td>{{ $p->id }}</td>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->category?->name ?? 'Không' }}</td>
                            <td>{{ number_format($p->price, 0, ',', '.') }}đ</td>
                            <td>
                                @switch($p->status)
                                    @case(0)
                                        <span class="badge bg-secondary">Ẩn</span>
                                    @break

                                    @case(1)
                                        <span class="badge bg-success">Còn hàng</span>
                                    @break

                                    @case(2)
                                        <span class="badge bg-danger">Hết hàng</span>
                                    @break

                                    @default
                                        <span class="badge bg-dark">Không xác định</span>
                                @endswitch
                            </td>
                            <td>
                                @if ($p->thumbnail)
                                    <img src="{{ asset('storage/' . $p->thumbnail) }}" alt="{{ $p->name }}"
                                        width="50">
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.products.show', $p->id) }}" class="btn btn-info btn-sm">Xem chi
                                    tiết</a>
                                <a href="{{ route('admin.products.edit', $p->id) }}" class="btn btn-sm btn-warning">Sửa</a>
                                <form action="{{ route('admin.products.destroy', $p->id) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Xóa sản phẩm?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Xóa</button>
                                </form>

                            </td>

                        </tr>
                        {{-- Biến thể --}}
                        @foreach ($p->variants as $variant)
                            <tr class="table-light">
                                <td></td>
                                <td colspan="2">
                                    <strong>Biến thể:</strong>
                                    <span class="badge bg-warning text-dark">
                                        Màu: {{ $variant->color?->name }}
                                    </span>
                                    <span class="badge bg-info">
                                        Size: {{ $variant->size?->value }}
                                    </span>
                                    <small class="text-muted">({{ $variant->color?->code }})</small>
                                </td>

                                <td>
                                    @if ($variant->sale_price)
                                        <span class="text-danger">
                                            {{ number_format($variant->sale_price, 0, ',', '.') }}đ
                                        </span>
                                        <del class="text-muted ms-1">
                                            {{ number_format($variant->original_price, 0, ',', '.') }}đ
                                        </del>
                                    @else
                                        {{ number_format($variant->original_price, 0, ',', '.') }}đ
                                    @endif
                                </td>

                                <td colspan="3">
                                    <span class="badge bg-secondary">Số lượng: {{ $variant->stock }}</span>
                                    <form action="{{ route('admin.products.variants.destroy', [$p->id, $variant->id]) }}"
                                        method="POST" class="d-inline ms-2"
                                        onsubmit="return confirm('Bạn có chắc muốn xóa biến thể này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach


                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Chưa có sản phẩm nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $products->links() }}
            </div>
        </div>
    @endsection
