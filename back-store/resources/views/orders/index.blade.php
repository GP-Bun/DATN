@extends('layouts.app')

@section('title', 'Quản lý đơn hàng')

@section('content')
<div class="container-fluid mt-3">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📋 Danh sách đơn hàng</h5>
        </div>
        <div class="card-body">
            <!-- Form lọc và tìm kiếm -->
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="🔍 Tìm ID hoặc tên khách...">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">📊 Tất cả trạng thái</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Chờ xử lý</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>⚙️ Đang xử lý</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>🚚 Đã gửi</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>✅ Đã giao</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Lọc</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-counterclockwise"></i> Đặt lại
                    </a>
                </div>
            </form>

            <!-- Thông báo -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Bảng danh sách -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th><i class="bi bi-hash"></i> ID</th>
                            <th><i class="bi bi-person"></i> Khách hàng</th>
                            <th><i class="bi bi-envelope"></i> Email</th>
                            <th><i class="bi bi-cash"></i> Tổng tiền</th>
                            <th><i class="bi bi-info-circle"></i> Trạng thái</th>
                            <th><i class="bi bi-credit-card"></i> Thanh toán</th>
                            <th><i class="bi bi-calendar"></i> Ngày tạo</th>
                            <th><i class="bi bi-gear"></i> Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td><strong>#{{ $order->id }}</strong></td>
                                <td>{{ $order->user->name ?? '---' }}</td>
                                <td>{{ $order->user->email ?? '---' }}</td>
                                <td><span class="badge bg-success">{{ number_format($order->final_amount, 0, ',', '.') }} đ</span></td>
                                <td>
                                    @switch($order->order_status)
                                        @case('pending')<span class="badge bg-warning">⏳ Chờ xử lý</span>@break
                                        @case('processing')<span class="badge bg-info">⚙️ Đang xử lý</span>@break
                                        @case('shipped')<span class="badge bg-primary">🚚 Đã gửi</span>@break
                                        @case('delivered')<span class="badge bg-success">✅ Đã giao</span>@break
                                        @case('cancelled')<span class="badge bg-danger">❌ Đã hủy</span>@break
                                    @endswitch
                                </td>
                                <td>
                                    @if ($order->payment_status == 'paid')
                                        <span class="badge bg-success">✅ Đã thanh toán</span>
                                    @elseif ($order->payment_status == 'refunded')
                                        <span class="badge bg-info">🔄 Hoàn tiền</span>
                                    @else
                                        <span class="badge bg-warning">⏳ Chưa thanh toán</span>
                                    @endif
                                </td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if ($order->order_status == 'pending')
                                        <form action="{{ route('orders.destroy', $order->id) }}" method="POST" style="display:inline;"
                                              onsubmit="return confirm('Xóa đơn hàng #{{ $order->id }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox"></i> Không có đơn hàng nào
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            <div class="d-flex justify-content-center mt-3">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
