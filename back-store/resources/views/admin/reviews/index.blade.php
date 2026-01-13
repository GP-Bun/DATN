@extends('layouts.app')

@section('title', 'Quản lý đánh giá')

@section('content')
<div class="bg-white p-4 rounded shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Quản lý đánh giá</h4>
    </div>


        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Tổng số</h6>
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Đã duyệt</h6>
                    <h3 class="mb-0">{{ $stats['active'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Chưa duyệt</h6>
                    <h3 class="mb-0">{{ $stats['inactive'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Đánh giá TB</h6>
                    <h3 class="mb-0">{{ $stats['average_rating'] }} ⭐</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.reviews.index') }}" class="mb-3">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Tìm theo nội dung..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Chưa duyệt</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="product_id" class="form-select">
                    <option value="">Tất cả sản phẩm</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Lọc</button>
            </div>
        </div>
    </form>

    <!-- Reviews Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Người dùng</th>
                    <th>Sản phẩm</th>
                    <th>Đánh giá</th>
                    <th>Nội dung</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                <tr>
                    <td>{{ $review->id }}</td>
                    <td>
                        <div>
                            <strong>{{ $review->user->name ?? $review->user_name }}</strong><br>
                            <small class="text-muted">{{ $review->user->email ?? $review->user_email }}</small>
                        </div>
                    </td>
                    <td>
                        <div style="max-width: 200px;">
                            <strong>{{ $review->product->name ?? 'Product #' . $review->product_id }}</strong>
                        </div>
                    </td>
                    <td>
                        <div>
                            @for($i = 1; $i <= 5; $i++)
                                <span style="color: {{ $i <= $review->rating ? '#FFD700' : '#ddd' }};">⭐</span>
                            @endfor
                            <br>
                            <small class="text-muted">{{ $review->rating }}/5</small>
                        </div>
                    </td>
                    <td style="max-width: 300px;">
                        @if($review->comment)
                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                                 title="{{ $review->comment }}">
                                {{ $review->comment }}
                            </div>
                        @else
                            <span class="text-muted">Không có comment</span>
                        @endif
                    </td>
                    <td>{{ $review->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge bg-{{ $review->status == 1 ? 'success' : 'danger' }}">
                            {{ $review->status == 1 ? 'Đã duyệt' : 'Chưa duyệt' }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <form action="{{ route('admin.reviews.updateStatus', $review->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="{{ $review->status == 1 ? 0 : 1 }}">
                                <button type="submit" class="btn btn-sm btn-{{ $review->status == 1 ? 'warning' : 'success' }}"
                                        onclick="return confirm('Bạn có chắc muốn {{ $review->status == 1 ? 'ẩn' : 'duyệt' }} review này?')">
                                    {{ $review->status == 1 ? 'Ẩn' : 'Duyệt' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Bạn có chắc muốn xóa review này? Hành động này không thể hoàn tác!')">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Không có đánh giá nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $reviews->links() }}
    </div>
</div>
@endsection

