@extends('layouts.app')

@section('content')
    <div class="container my-5">
        {{-- Thông báo --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="row">
            {{-- Cột trái --}}
            <div class="col-lg-8">
                <div class="card shadow-lg mb-4 border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">🧾 Đơn hàng #{{ $order->id }}</h4>
                        <span class="badge bg-{{ $order->order_status_color }}">
                            {{ $order->order_status_label }}
                        </span>

                    </div>
                    <div class="card-body">
                        {{-- Form cập nhật trạng thái --}}
                        <form action="{{ route('admin.orders.update', $order->id) }}" method="POST"
                            class="d-flex align-items-center mb-3">
                            @csrf
                            @method('PUT')
                            <label for="order_status" class="me-2 fw-bold">Cập nhật trạng thái:</label>
                            @php
                                $allowedTransitions = [
                                    'pending' => ['processing', 'cancelled'],
                                    'processing' => ['shipped', 'cancelled'],
                                    'shipped' => ['delivered'],
                                    'delivered' => [],
                                    'cancelled' => [],
                                ];

                                $statusOptions = [
                                    'pending' => 'Chờ xử lý',
                                    'processing' => 'Đang xử lý',
                                    'shipped' => 'Đã gửi hàng',
                                    'delivered' => 'Đã giao',
                                    'cancelled' => 'Đã hủy',
                                ];

                                $currentStatus = $order->order_status;
                            @endphp

                            <select name="order_status" id="order_status" class="form-select w-auto me-2">
                                @foreach ($statusOptions as $value => $label)
                                    @php
                                        $isAllowed = in_array($value, $allowedTransitions[$currentStatus] ?? []);
                                        $isCurrent = $value === $currentStatus;
                                    @endphp
                                    <option value="{{ $value }}" {{ $isCurrent ? 'selected' : '' }}
                                        {{ !$isAllowed && !$isCurrent ? 'disabled class=text-muted' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-sm btn-success">Cập nhật</button>
                        </form>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form action="{{ route('admin.orders.updatePayment', $order->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <select name="payment_status">
                                        <option value="pending"
                                            {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Chưa thanh toán
                                        </option>
                                        <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Đã
                                            thanh toán</option>
                                        <option value="refunded"
                                            {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Hoàn tiền
                                        </option>
                                    </select>
                                    <button type="submit">Cập nhật</button>
                                </form>

                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Ngày tạo</h6>
                                <p class="fw-bold">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <h6 class="text-muted">Phí vận chuyển</h6>
                                    <p class="fw-bold text-primary">{{ number_format($order->shipping_cost) }}đ</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <h6 class="text-muted">Giảm giá</h6>
                                    <p class="fw-bold text-success">-{{ number_format($order->discount_amount) }}đ</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <h6 class="text-muted">Tổng thanh toán</h6>
                                    <p class="fw-bold text-danger">{{ number_format($order->final_amount) }}đ</p>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-muted">Ghi chú</h6>
                        <p>{{ $order->notes ?? 'Không có' }}</p>
                    </div>
                </div>

                {{-- Sản phẩm --}}
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">🛍️ Sản phẩm trong đơn</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ảnh</th>
                                        <th>Sản phẩm</th>
                                        <th>Số lượng</th>
                                        <th>Giá</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td>
                                                @if ($item->product && $item->product->thumbnail)
                                                    <img src="{{ asset('storage/' . $item->product->thumbnail) }}"
                                                        alt="{{ $item->product->name }}" class="img-thumbnail"
                                                        style="width:80px;height:auto;">
                                                @else
                                                    <span class="text-muted">Không có ảnh</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->product_name }}</td>
                                            <td><span class="badge bg-info">{{ $item->quantity }}</span></td>
                                            <td>{{ number_format($item->price) }}đ</td>
                                            <td class="fw-bold">{{ number_format($item->price * $item->quantity) }}đ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Voucher --}}
                @if ($order->couponRedemptions && $order->couponRedemptions->count())
                    <div class="card shadow-lg mt-4 border-0">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">🎟️ Voucher áp dụng</h5>
                        </div>
                        <div class="card-body">
                            @foreach ($order->couponRedemptions as $redeem)
                                <p>
                                    <strong>Mã:</strong> <span class="badge bg-primary">{{ $redeem->coupon->code }}</span>
                                    <strong>Giảm:</strong>
                                    <span class="text-success fw-bold">
                                        {{ number_format($redeem->discount_amount) }}đ
                                    </span>
                                </p>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Cột phải: khách hàng & địa chỉ --}}
            <div class="col-lg-4">
                <div class="card shadow-lg mb-4 border-0">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">👤 Khách hàng</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Tên:</strong> {{ $order->user->name }}</p>
                        <p><strong>Email:</strong> {{ $order->user->email }}</p>
                        <p><strong>SĐT:</strong> {{ $order->user->phone ?? 'Không có' }}</p>
                        <p><strong>Vai trò:</strong> <span class="badge bg-info">{{ $order->user->role }}</span></p>
                        <p><strong>Trạng thái:</strong>
                            <span class="badge {{ $order->user->active ? 'bg-success' : 'bg-danger' }}">
                                {{ $order->user->active ? 'Hoạt động' : 'Khoá' }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="card shadow-lg border-0">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">📦 Địa chỉ giao hàng</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Người nhận:</strong> {{ $order->address->receiver_name }}</p>
                        <p><strong>SĐT:</strong> {{ $order->address->receiver_phone }}</p>
                        <p><strong>Địa chỉ:</strong> {{ $order->address->full_address }}</p>
                        <p><strong>Mã bưu điện:</strong> {{ $order->address->zip ?? 'Không có' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
