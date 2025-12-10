@extends('layouts.app')

@section('page-title', 'Danh sách voucher')

@section('content')
    <div class="bg-white p-4 rounded shadow-sm">
        <h4 class="mb-3">Danh sách voucher</h4>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="mb-3">
            <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
                + Thêm voucher
            </a>

        </div>

        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Mã</th>
                    <th>Loại</th>
                    <th>Giá trị</th>
                    <th>Đơn hàng tối thiểu</th>
                    <th>Giảm tối đa</th>
                    <th>Thời gian</th>
                    <th>Sử dụng</th>
                    <th>Trạng thái</th>
                    <th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($coupons as $coupon)
                    <tr>
                        <td><span class="fw-bold">{{ $coupon->code }}</span></td>
                        <td>
                            @if ($coupon->type === 'percent')
                                <span class="badge bg-info">%</span>
                            @else
                                <span class="badge bg-warning">VNĐ</span>
                            @endif
                        </td>
                        <td>
                            @if ($coupon->type === 'percent')
                                {{ number_format($coupon->value, 0, ',', '.') }}%
                            @else
                                {{ number_format($coupon->value, 0, ',', '.') }}đ
                            @endif
                        </td>
                        <td>
                            @if ($coupon->min_order_amount)
                                {{ number_format($coupon->min_order_amount, 0, ',', '.') }}đ
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($coupon->max_discount)
                                {{ number_format($coupon->max_discount, 0, ',', '.') }}đ
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            {{ $coupon->starts_at ? $coupon->starts_at->format('d/m/Y H:i') : '-' }} <br>
                            {{ $coupon->ends_at ? $coupon->ends_at->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td>
                            {{ $coupon->used_count }}/{{ $coupon->usage_limit ?? '∞' }}
                        </td>
                        <td>
                            @if ($coupon->active)
                                <span class="badge bg-success">Hoạt động</span>
                            @else
                                <span class="badge bg-secondary">Ngừng</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-sm btn-warning">Sửa</a>
<form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" class="d-inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
</form>

                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Chưa có voucher nào</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $coupons->links() }}
    </div>
@endsection
