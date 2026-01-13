@extends('layouts.app')

@section('title', 'Thùng rác sản phẩm')

@section('content')
<div class="bg-white p-4 rounded shadow-sm">
    <h4 class="mb-3">Thùng rác sản phẩm</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary mb-3">Quay lại danh sách</a>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#ID</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Ảnh</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    <tr class="table-secondary text-muted">
                        <td>{{ $p->id }}</td>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->category?->name ?? 'Không' }}</td>
                        <td>{{ number_format($p->price, 0, ',', '.') }}đ</td>
                        <td>
                            @if ($p->thumbnail)
                                <img src="{{ asset('storage/' . $p->thumbnail) }}" alt="{{ $p->name }}" width="50">
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.products.restore', $p->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-success">Khôi phục</button>
                            </form>
                            <form action="{{ route('admin.products.forceDelete', $p->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Không có sản phẩm nào trong thùng rác.</td>
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
