@extends('layouts.app')

@section('title', "Chi tiết đơn hàng #{$order->id}")

@section('content')
<div class="container-fluid mt-3">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

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

    <!-- Thông tin chung -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📦 Chi tiết đơn hàng #{{ $order->id }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted"><i class="bi bi-person"></i> Khách hàng</h6>
                            <p class="mb-1"><strong>{{ $order->user->name ?? '---' }}</strong></p>
                            <p class="mb-1"><i class="bi bi-envelope"></i> {{ $order->user->email ?? '---' }}</p>
                            <p><i class="bi bi-telephone"></i> {{ $order->user->phone ?? '---' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted"><i class="bi bi-map"></i> Địa chỉ giao hàng</h6>
                            <p class="mb-1">
                                <strong>{{ $order->address->address ?? '---' }}</strong>
                            </p>
                            <p class="mb-1">
                                {{ $order->address->city ?? '' }}, {{ $order->address->postal_code ?? '' }}
                            </p>
                            <p class="mb-0">
                                <i class="bi bi-geo-alt"></i> {{ $order->address->country ?? '---' }}
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <h6 class="text-muted"><i class="bi bi-calendar"></i> Ngày tạo</h6>
                            <p>{{ $order->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted"><i class="bi bi-hourglass-split"></i> Trạng thái</h6>
                            <p>
                                @switch($order->order_status)
                                    @case('pending')<span class="badge bg-warning">⏳ Chờ xử lý</span>@break
                                    @case('processing')<span class="badge bg-info">⚙️ Đang xử lý</span>@break
                                    @case('shipped')<span class="badge bg-primary">🚚 Đã gửi</span>@break
                                    @case('delivered')<span class="badge bg-success">✅ Đã giao</span>@break
                                    @case('cancelled')<span class="badge bg-danger">❌ Đã hủy</span>@break
                                @endswitch
                            </p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted"><i class="bi bi-credit-card"></i> Thanh toán</h6>
                            <p>
                                @if ($order->payment_status == 'paid')
                                    <span class="badge bg-success">✅ Đã thanh toán</span>
                                @elseif ($order->payment_status == 'refunded')
                                    <span class="badge bg-info">🔄 Hoàn tiền</span>
                                @else
                                    <span class="badge bg-warning">⏳ Chưa thanh toán</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted"><i class="bi bi-cash"></i> Ghi chú</h6>
                            <p class="small">{{ $order->notes ?? '---' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chi tiết sản phẩm -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">🛍️ Chi tiết sản phẩm ({{ $order->items->count() }} sản phẩm)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><i class="bi bi-hash"></i> #</th>
                                <th><i class="bi bi-box"></i> Sản phẩm</th>
                                <th><i class="bi bi-tag"></i> Giá</th>
                                <th><i class="bi bi-bag"></i> Số lượng</th>
                                <th><i class="bi bi-calculator"></i> Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $item->product_name ?? "Sản phẩm #{$item->id}" }}</td>
                                    <td>{{ number_format($item->price, 0, ',', '.') }} đ</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td><strong>{{ number_format($item->price * $item->quantity, 0, ',', '.') }} đ</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Lịch sử thanh toán -->
            @if ($order->payments->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">💳 Lịch sử thanh toán ({{ $order->payments->count() }} ghi chép)</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="bi bi-hash"></i> #</th>
                                    <th><i class="bi bi-calculator"></i> Số tiền</th>
                                    <th><i class="bi bi-info-circle"></i> Phương thức</th>
                                    <th><i class="bi bi-hourglass-split"></i> Trạng thái</th>
                                    <th><i class="bi bi-calendar"></i> Ngày</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->payments as $key => $payment)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td><span class="badge bg-success">{{ number_format($payment->amount, 0, ',', '.') }} đ</span></td>
                                        <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                                        <td>
                                            @if ($payment->payment_status == 'completed')
                                                <span class="badge bg-success">✅ Hoàn tất</span>
                                            @elseif ($payment->payment_status == 'pending')
                                                <span class="badge bg-warning">⏳ Chờ</span>
                                            @else
                                                <span class="badge bg-danger">❌ Thất bại</span>
                                            @endif
                                        </td>
                                        <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar - Tổng hợp & Hành động -->
        <div class="col-md-4">
            <!-- Tổng hợp tiền -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Tóm tắt thanh toán</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2 d-flex justify-content-between">
                        <span><i class="bi bi-box"></i> Tổng sản phẩm:</span>
                        <strong>{{ number_format($order->items->sum(fn($i) => $i->price * $i->quantity), 0, ',', '.') }} đ</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span><i class="bi bi-truck"></i> Phí vận chuyển:</span>
                        <strong>{{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }} đ</strong>
                    </div>
                    <div class="mb-3 d-flex justify-content-between">
                        <span><i class="bi bi-percent"></i> Giảm giá:</span>
                        <strong class="text-danger">-{{ number_format($order->discount_amount ?? 0, 0, ',', '.') }} đ</strong>
                    </div>
                    <hr>
                    <div class="mb-3 d-flex justify-content-between">
                        <strong><i class="bi bi-cash"></i> TỔNG CỘNG:</strong>
                        <strong class="text-success fs-5">{{ number_format($order->final_amount, 0, ',', '.') }} đ</strong>
                    </div>
                    <div class="mb-3 d-flex justify-content-between">
                        <strong><i class="bi bi-credit-card"></i> Đã thanh toán:</strong>
                        <strong class="text-info">{{ number_format($order->payments->sum('amount'), 0, ',', '.') }} đ</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <strong><i class="bi bi-hourglass-split"></i> Còn lại:</strong>
                        <strong class="text-warning">{{ number_format($order->final_amount - $order->payments->sum('amount'), 0, ',', '.') }} đ</strong>
                    </div>
                </div>
            </div>

            <!-- Cập nhật trạng thái -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil"></i> Cập nhật trạng thái</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('orders.update', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="order_status" class="form-label">Trạng thái mới</label>
                            <select name="order_status" id="order_status" class="form-select">
                                <option value="pending" {{ $order->order_status == 'pending' ? 'selected' : '' }}>⏳ Chờ xử lý</option>
                                <option value="processing" {{ $order->order_status == 'processing' ? 'selected' : '' }}>⚙️ Đang xử lý</option>
                                <option value="shipped" {{ $order->order_status == 'shipped' ? 'selected' : '' }}>🚚 Đã gửi</option>
                                <option value="delivered" {{ $order->order_status == 'delivered' ? 'selected' : '' }}>✅ Đã giao</option>
                                <option value="cancelled" {{ $order->order_status == 'cancelled' ? 'selected' : '' }}>❌ Đã hủy</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check"></i> Lưu thay đổi
                        </button>
                    </form>
                </div>
            </div>

            <!-- Hành động khác -->
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Hành động khác</h5>
                </div>
                <div class="card-body">
                    @if ($order->order_status == 'pending')
                        <form action="{{ route('orders.destroy', $order->id) }}" method="POST"
                              onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng #{{ $order->id }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Xóa đơn hàng
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-secondary w-100" disabled>
                            <i class="bi bi-lock"></i> Không thể xóa
                        </button>
                        <small class="text-muted d-block mt-2 text-center">
                            Chỉ xóa được khi đơn ở trạng thái "Chờ xử lý"
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
