@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded shadow-sm">
    <div class="d-flex justify-content-between mb-3">
        <h4>Danh sách sản phẩm</h4>
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ Thêm sản phẩm</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Danh mục</th>
                <th>Giá</th>
                <th>Trạng thái</th>
                <th>Biến thể</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $p)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    @if($p->image)
                        <img src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->name }}" width="80" class="img-thumbnail">
                    @else
                        —
                    @endif
                </td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->category->name ?? '—' }}</td>
                <td>{{ number_format($p->price, 0, ',', '.') }}₫</td>
                <td>{{ $p->status }}</td>
                <td>
                    @if($p->variants->count())
                        <ul class="list-unstyled mb-0">
                            @foreach($p->variants as $v)
                                <li>
                                    <strong>{{ $v->color }}/{{ $v->size }}</strong> - 
                                    {{ number_format($v->price,0,',','.') }}₫, 
                                    Stock: {{ $v->stock }}
                                    <form action="{{ route('variants.destroy', $v->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Xóa biến thể này?')">Xóa</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <span class="text-muted">Chưa có biến thể</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('products.edit', $p->id) }}" class="btn btn-warning btn-sm mb-1">Sửa</a>
                    <form action="{{ route('products.destroy', $p->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Xóa sản phẩm này?')">Xóa</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted">Chưa có sản phẩm</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
