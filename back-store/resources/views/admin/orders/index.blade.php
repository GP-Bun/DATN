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
    <div class="card mb-4 shadow-sm border border-primary border-2">
        <div class="card-body">
            <h5 class="card-title mb-3">🔍 Bộ lọc đơn hàng</h5>
            <form method="GET" class="row g-3 align-items-end">
                {{-- Tìm khách hàng --}}
                <div class="col-md-3 position-relative">
                    {{-- <label class="form-label">Khách hàng</label> --}}
                    <input type="text" id="searchCustomer" name="keyword" class="form-control"
                           placeholder="Nhập tên khách hàng..." value="{{ request('keyword') }}">
                    <div id="searchResults" class="list-group position-absolute w-100"></div>
                </div>

                {{-- Trạng thái --}}
                <div class="col-md-3">
                    {{-- <label class="form-label">Trạng thái</label> --}}
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Chờ xử lý</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>🔄 Đang xử lý</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>🚚 Đã gửi hàng</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>✅ Đã giao</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ Đã hủy</option>
                    </select>
                </div>

                {{-- Thời gian --}}
                <div class="col-md-4">
                    {{-- <label class="form-label">Thời gian</label> --}}
                    <div class="border rounded p-2">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="allOrders" name="all" value="1"
                                   {{ request('all') ? 'checked' : '' }}>
                            <label class="form-check-label" for="allOrders">Hiển thị tất cả đơn hàng</label>
                        </div>
                        <div id="timeFilters" class="d-flex gap-2">
                            <input type="date" id="day" name="day" class="form-control" value="{{ request('day') }}">
                            <input type="month" id="month" name="month" class="form-control" value="{{ request('month') }}">
                        </div>
                    </div>
                </div>

                {{-- Nút lọc --}}
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">🔎 Lọc</button>
                </div>
            </form>

            {{-- Trạng thái hiển thị --}}
            <div class="mt-3 text-muted">
                @if(request('all'))
                    <span>📋 Đang hiển thị: <strong>Tất cả đơn hàng</strong></span>
                @elseif(request('day'))
                    <span>📅 Ngày: <strong>{{ \Carbon\Carbon::parse(request('day'))->format('d/m/Y') }}</strong></span>
                @elseif(request('month'))
                    <span>🗓️ Tháng: <strong>{{ \Carbon\Carbon::parse(request('month').'-01')->translatedFormat('F Y') }}</strong></span>
                @else
                    <span>📋 Đang hiển thị: <strong>Tất cả đơn hàng</strong></span>
                @endif
            </div>
        </div>
    </div>

    {{-- Bảng đơn hàng --}}
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
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
                    <td class="text-center"><strong>#{{ $o->id }}</strong></td>
                    <td>{{ $o->user?->name ?? 'Khách ẩn' }}</td>
                    <td class="text-primary fw-bold">{{ number_format($o->final_amount, 0, ',', '.') }}đ</td>
                    <td class="text-center">
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
                    <td class="text-center">
                        <a href="{{ route('admin.orders.show', $o->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Xem chi tiết">
                            👁️ Xem
                        </a>
                        <form action="{{ route('admin.orders.destroy', $o->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Xóa đơn hàng">🗑️ Xóa</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <div>
                            <span style="font-size: 2rem;">📭</span>
                            <p class="mt-2">Không có đơn hàng nào phù hợp với bộ lọc.</p>
                        </div>
                    </td>
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

{{-- Script live search + toggle filters --}}
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

const allCheckbox = document.getElementById('allOrders');
const timeFilters = document.getElementById('timeFilters');
const dayInput = document.getElementById('day');
const monthInput = document.getElementById('month');

function toggleTimeFilters() {
    const isAll = allCheckbox.checked;
    timeFilters.style.opacity = isAll ? '0.5' : '1';
    dayInput.disabled = isAll;
    monthInput.disabled = isAll;
    if (isAll) {
        dayInput.value = '';
        monthInput.value = '';
    }
}
allCheckbox.addEventListener('change', toggleTimeFilters);
toggleTimeFilters();
</script>
@endsection
