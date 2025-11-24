@extends('layouts.app')

@section('title', 'Tạo mã giảm giá')

@section('content')
<div class="container mt-3">
    <div class="card">
        <div class="card-header bg-primary text-white">Tạo mã giảm giá mới</div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('coupons.store') }}" method="POST">
                @csrf
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Mã (code)</label>
                        <input name="code" class="form-control" value="{{ old('code') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Loại</label>
                        <select name="type" class="form-select">
                            <option value="percent">Phần trăm</option>
                            <option value="fixed">Tiền cố định</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Giá trị</label>
                        <input name="value" type="number" step="0.01" class="form-control" value="{{ old('value', 0) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Giới hạn dùng</label>
                        <input name="usage_limit" type="number" class="form-control" value="{{ old('usage_limit') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Min đơn (đ)</label>
                        <input name="min_order_amount" type="number" step="0.01" class="form-control" value="{{ old('min_order_amount', 0) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Bắt đầu</label>
                        <input name="starts_at" type="date" class="form-control" value="{{ old('starts_at') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kết thúc</label>
                        <input name="ends_at" type="date" class="form-control" value="{{ old('ends_at') }}">
                    </div>
                    <div class="col-md-4 align-self-end">
                        <div class="form-check">
                            <input name="active" class="form-check-input" type="checkbox" id="active" checked>
                            <label class="form-check-label" for="active">Kích hoạt</label>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <button class="btn btn-success">Lưu mã giảm giá</button>
                        <a href="{{ route('coupons.index') }}" class="btn btn-secondary">Hủy</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
