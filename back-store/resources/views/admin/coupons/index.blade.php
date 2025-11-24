@extends('layouts.app')

@section('title', 'Quản lý mã giảm giá')

@section('content')
<div class="container mt-3">
    <div class="d-flex justify-content-between mb-3">
        <h4>🎟️ Danh sách mã giảm giá</h4>
        <a href="{{ route('coupons.create') }}" class="btn btn-primary">Tạo mã mới</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Loại</th>
                    <th>Giá trị</th>
                    <th>Hạn dùng</th>
                    <th>Đã dùng / Giới hạn</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $c)
                    <tr>
                        <td>{{ $c->id }}</td>
                        <td><strong>{{ $c->code }}</strong></td>
                        <td>{{ $c->type }}</td>
                        <td>
                            @if($c->type == 'percent')
                                {{ $c->value }}%
                            @else
                                {{ number_format($c->value,0,',','.') }} đ
                            @endif
                        </td>
                        <td>
                            @if($c->starts_at){{ $c->starts_at->format('d/m/Y') }}@endif
                            -
                            @if($c->ends_at){{ $c->ends_at->format('d/m/Y') }}@endif
                        </td>
                        <td>{{ $c->used_count }} / {{ $c->usage_limit ?? '∞' }}</td>
                        <td>{!! $c->active ? '<span class="badge bg-success">Kích hoạt</span>' : '<span class="badge bg-secondary">Vô hiệu</span>' !!}</td>
                        <td>
                            <form action="{{ route('coupons.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Xóa mã {{ $c->code }}?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">Không có mã giảm giá</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">{{ $coupons->links() }}</div>
</div>
@endsection
