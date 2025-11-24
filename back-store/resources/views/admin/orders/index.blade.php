@extends('layouts.app')

@section('title', 'Quản lý đơn hàng')

@section('content')
<div class="bg-white p-4 rounded shadow-sm">
    <h4 class="mb-3">Danh sách đơn hàng</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#ID</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                <tr>
                    <td>{{ $o->id }}</td>
                    <td>{{ $o->user?->name ?? 'Khách ẩn' }}</td>
                    <td>{{ number_format($o->total, 0, ',', '.') }} đ</td>
                    <td>
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'processing' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$o->status] ?? 'secondary' }}">
                            {{ ucfirst($o->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.orders.show', $o->id) }}" class="btn btn-sm btn-info">Xem</a>
                        <form action="{{ route('admin.orders.destroy', $o->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa đơn hàng?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Không có đơn hàng nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination gọn --}}
    <div class="d-flex justify-content-center mt-3">
       {{ $orders->onEachSide(1)->links() }}

    </div>
</div>
@endsection
