@extends('layouts.app')

@section('title', 'Chi tiết người dùng')

@section('content')
<div class="container">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Chi tiết tài khoản</h4>
        </div>
        <div class="card-body">
            <p><strong>Tên:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Vai trò:</strong> 
                <span class="badge bg-info text-dark">{{ ucfirst($user->role) }}</span>
            </p>
            <p><strong>Trạng thái:</strong> 
                @if ($user->active)
                    <span class="badge bg-success">Hoạt động</span>
                @else
                    <span class="badge bg-secondary">Ngừng</span>
                @endif
            </p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning">
            <h5 class="mb-0">Hoạt động gần đây</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Hành động</th>
                        <th>Mô tả</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user->activities as $index => $activity)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><span class="badge bg-primary">{{ $activity->action }}</span></td>
                            <td>{{ $activity->description }}</td>
                            <td>{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Chưa có hoạt động nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Đơn hàng</h5>
                </div>
                <div class="card-body">
                    @forelse($user->orders as $order)
                        <div class="mb-2 border-bottom pb-2">
                            <strong>Đơn #{{ $order->id }}</strong> - 
                            <span class="badge bg-info">{{ $order->status }}</span> - 
                            <span class="text-danger">{{ number_format($order->total_amount) }} đ</span>
                        </div>
                    @empty
                        <p class="text-muted">Chưa có đơn hàng</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Đánh giá sản phẩm</h5>
                </div>
                <div class="card-body">
                    @forelse($user->reviews as $review)
                        <div class="mb-2 border-bottom pb-2">
                            <strong>{{ $review->product->name }}</strong> <br>
                            <span class="text-warning">{{ str_repeat('⭐', $review->rating) }}</span> <br>
                            <em>{{ $review->comment }}</em>
                        </div>
                    @empty
                        <p class="text-muted">Chưa có đánh giá</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">← Quay lại danh sách</a>
</div>
@endsection
