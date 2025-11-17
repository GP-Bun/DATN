@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="admin-page-header">
    <h1 class="admin-page-title">Tổng quan hệ thống</h1>
    <p class="admin-page-subtitle">Chào mừng bạn đến với trang quản trị TH Store 👋</p>
</div>

<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <h3 class="admin-stat-title">Tổng sản phẩm</h3>
        <p class="admin-stat-value blue">156</p>
    </div>
    <div class="admin-stat-card">
        <h3 class="admin-stat-title">Đơn hàng hôm nay</h3>
        <p class="admin-stat-value green">23</p>
    </div>
    <div class="admin-stat-card">
        <h3 class="admin-stat-title">Khách hàng</h3>
        <p class="admin-stat-value purple">1,234</p>
    </div>
    <div class="admin-stat-card">
        <h3 class="admin-stat-title">Doanh thu tháng</h3>
        <p class="admin-stat-value orange">45.2M</p>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">Hoạt động gần đây</h3>
        <button class="admin-btn">Xem tất cả</button>
    </div>
    <div class="admin-card-content">
        <p>Đây là nơi hiển thị các hoạt động gần đây của hệ thống...</p>
    </div>
</div>
@endsection
