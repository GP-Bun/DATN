@extends('layouts.app')

@section('title', 'Quản lý đơn hàng')

@section('content')
<div class="bg-white p-4 rounded shadow-sm">
    <h4 class="mb-4">📦 Quản lý đơn hàng</h4>

    {{-- Thông báo --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Bộ lọc --}}
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-3 position-relative">
            <input type="text" id="searchCustomer" name="keyword" class="form-control"
                   placeholder="Tìm theo tên khách hàng..."
                   value="{{ request('keyword') }}">
            <div id="searchResults" class="list-group position-absolute w-100"></div>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">-- Trạng thái đơn hàng --</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Đã gửi hàng</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Đã giao</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Lọc</button>
        </div>
    </form>

    {{-- Bảng đơn hàng --}}
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
                    <td><strong>#{{ $o->id }}</strong></td>
                    <td>{{ $o->user?->name ?? 'Khách ẩn' }}</td>
                    <td class="text-primary fw-bold">{{ number_format($o->final_amount, 0, ',', '.') }}đ</td>
                    <td>
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'processing' => 'info',
                                'shipped' => 'primary',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                            ];
                            $statusIcons = [
                                'pending' => '⏳',
                                'processing' => '🔄',
                                'shipped' => '🚚',
                                'delivered' => '✅',
                                'cancelled' => '❌',
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$o->order_status] ?? 'secondary' }}">
                            {{ $statusIcons[$o->order_status] ?? '' }} {{ ucfirst($o->order_status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.orders.show', $o->id) }}" class="btn btn-sm btn-outline-primary">
                            👁️ Xem
                        </a>
                        <form action="{{ route('admin.orders.destroy', $o->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">🗑️ Xóa</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Không có đơn hàng nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Phân trang --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $orders->onEachSide(1)->links() }}
    </div>
</div>

{{-- Script live search --}}
<script>
document.getElementById('searchCustomer').addEventListener('keyup', function() {
    let keyword = this.value;
    if (keyword.length < 2) {
        document.getElementById('searchResults').innerHTML = '';
        return;
    }
    fetch(`/admin/orders/search?keyword=${keyword}`)
        .then(res => res.json())
        .then(data => {
            let html = '';
            data.forEach(order => {
                html += `<a href="/admin/orders/${order.id}" class="list-group-item list-group-item-action">
                            #${order.id} - ${order.user?.name ?? 'Khách ẩn'} - ${order.final_amount}đ
                         </a>`;
            });
            document.getElementById('searchResults').innerHTML = html;
        });
});
</script>
@endsection
