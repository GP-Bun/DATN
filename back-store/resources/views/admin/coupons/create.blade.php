@extends('layouts.app')

@section('page-title', 'Thêm voucher')

@section('content')
    <div class="bg-white p-4 rounded shadow-sm">
        <h4 class="mb-3">Thêm voucher mới</h4>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.coupons.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Mã voucher</label>
                <input type="text" name="code" class="form-control" value="{{ old('code') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Loại giảm giá</label>
                <select name="type" id="discount_type" class="form-select" required onchange="toggleMaxDiscount()">
                    <option value="percent" {{ old('type') == 'percent' ? 'selected' : '' }}>Phần trăm (%)</option>
                    <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Số tiền (VNĐ)</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Giá trị</label>
                <input type="number" name="value" class="form-control" value="{{ old('value') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Đơn hàng tối thiểu</label>
                <input type="number" name="min_order_amount" class="form-control" value="{{ old('min_order_amount') }}">
            </div>

            <div class="mb-3" id="max_discount_group">
                <label class="form-label">Giảm tối đa</label>
                <input type="number" name="max_discount" class="form-control" value="{{ old('max_discount') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Ngày bắt đầu</label>
                <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Ngày kết thúc</label>
                <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Giới hạn số lần sử dụng</label>
                <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit') }}">
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="active" class="form-check-input" id="active"
                    {{ old('active') ? 'checked' : '' }}>
                <label class="form-check-label" for="active">Kích hoạt voucher</label>
            </div>

            <button type="submit" class="btn btn-success">Lưu</button>
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Hủy</a>
        </form>
    </div>
    
@endsection

@section('scripts')
<script>
function toggleMaxDiscount() {
    const type = document.getElementById('discount_type').value;
    const group = document.getElementById('max_discount_group');
    if (type === 'percent') {
        group.style.display = 'block';
    } else {
        group.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', toggleMaxDiscount);
</script>
@endsection
