@extends('layouts.app')

@section('title', 'Quản lý sản phẩm')

@section('content')
<div class="bg-white p-4 rounded shadow-sm">
    <h4 class="mb-3">Danh sách sản phẩm</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('admin.products.create') }}" class="btn btn-primary mb-3">Thêm sản phẩm</a>

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
                    <td>{{ number_format($p->price,0,',','.') }}₫</td>
                    <td>
                        @php
                            $statusColors = [
                                'ACTIVE'=>'success',
                                'INACTIVE'=>'secondary',
                                'OUT_OF_STOCK'=>'danger',
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$p->status] ?? 'secondary' }}">{{ $p->status }}</span>
                    </td>
                    <td>
                        @if($p->image)
                        <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->name }}" width="50">
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.products.edit', $p->id) }}" class="btn btn-sm btn-warning">Sửa</a>
                        <form action="{{ route('admin.products.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa sản phẩm?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
                {{-- Variants --}}
                @foreach($p->variants as $variant)
                <tr class="table-light">
                    <td></td>
                    <td colspan="2"><strong>Biến thể:</strong> {{ $variant->color }} / {{ $variant->size }}</td>
                    <td>{{ number_format($variant->price,0,',','.') }}₫</td>
                    <td colspan="3">
                        Số lượng: {{ $variant->stock }}
                        <form action="{{ route('admin.products.variants.destroy', [$p->id, $variant->id]) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Bạn có chắc muốn xóa biến thể này?');">
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
