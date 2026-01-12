@extends('layouts.app')

@section('title','Dashboard - Nhân viên')

@section('content')
<div class="dashboard staff-dashboard">

  {{-- HEADER --}}
  <div class="dashboard-header">
    <div>
      <h1 class="title">👋 Xin chào, {{ session('admin_user_name', 'Nhân viên') }}!</h1>
      <p class="subtitle">Bạn đang đăng nhập với tư cách Nhân viên</p>
    </div>
    <div class="dashboard-date">
      📅 {{ now()->format('d/m/Y') }}
    </div>
  </div>

  {{-- QUICK ACCESS --}}
  <div class="staff-quick-access">
    <h2>🚀 Truy cập nhanh</h2>
    <div class="quick-cards">

      {{-- Quản lý sản phẩm --}}
      <a href="{{ route('admin.products.index') }}" class="quick-card">
        <div class="quick-icon">📦</div>
        <div class="quick-info">
          <h3>Sản phẩm</h3>
          <p>Xem, thêm, sửa sản phẩm</p>
        </div>
        <span class="quick-arrow">→</span>
      </a>

      {{-- Quản lý đơn hàng --}}
      <a href="{{ route('admin.orders.index') }}" class="quick-card">
        <div class="quick-icon">🧾</div>
        <div class="quick-info">
          <h3>Đơn hàng</h3>
          <p>Xem, xác nhận, cập nhật trạng thái</p>
        </div>
        <span class="quick-arrow">→</span>
      </a>

      {{-- Quản lý đánh giá --}}
      <a href="{{ route('admin.reviews.index') }}" class="quick-card">
        <div class="quick-icon">⭐</div>
        <div class="quick-info">
          <h3>Đánh giá</h3>
          <p>Xem và phản hồi đánh giá</p>
        </div>
        <span class="quick-arrow">→</span>
      </a>

      {{-- Chat với khách --}}
      <a href="{{ route('admin.chat.index') }}" class="quick-card">
        <div class="quick-icon">💬</div>
        <div class="quick-info">
          <h3>Chat hỗ trợ</h3>
          <p>Hỗ trợ khách hàng</p>
        </div>
        <span class="quick-arrow">→</span>
      </a>
    </div>
  </div>

  {{-- PERMISSIONS INFO --}}
  <div class="staff-permissions">
    <h2>📋 Quyền hạn của bạn</h2>
    <div class="permissions-grid">
      <div class="perm-card allowed">
        <h4>✅ Được phép</h4>
        <ul>
          <li>📦 Xem, thêm, sửa sản phẩm</li>
          <li>🧾 Xem, xác nhận, cập nhật đơn hàng</li>
          <li>⭐ Xem và phản hồi đánh giá</li>
          <li>💬 Hỗ trợ khách hàng qua chat</li>
        </ul>
      </div>
      <div class="perm-card denied">
        <h4>🚫 Không được phép</h4>
        <ul>
          <li>📊 Xem thống kê doanh thu</li>
          <li>📂 Quản lý danh mục</li>
          <li>🎫 Quản lý mã giảm giá</li>
          <li>👥 Quản lý tài khoản</li>
        </ul>
      </div>
    </div>
  </div>

</div>

<style>
.staff-dashboard {
  padding: 24px;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.dashboard-header .title {
  font-size: 28px;
  color: #1e293b;
  margin: 0;
}

.dashboard-header .subtitle {
  color: #64748b;
  margin: 4px 0 0;
}

.dashboard-date {
  background: #f1f5f9;
  padding: 10px 16px;
  border-radius: 8px;
  color: #475569;
  font-weight: 500;
}

.staff-quick-access h2,
.staff-permissions h2 {
  font-size: 20px;
  color: #334155;
  margin-bottom: 20px;
}

.quick-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
  margin-bottom: 40px;
}

.quick-card {
  display: flex;
  align-items: center;
  gap: 16px;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  text-decoration: none;
  color: inherit;
  transition: all 0.2s ease;
  border: 1px solid #e2e8f0;
}

.quick-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
  border-color: #667eea;
}

.quick-icon {
  font-size: 36px;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
}

.quick-info h3 {
  font-size: 16px;
  color: #1e293b;
  margin: 0 0 4px;
}

.quick-info p {
  font-size: 13px;
  color: #64748b;
  margin: 0;
}

.quick-arrow {
  margin-left: auto;
  font-size: 20px;
  color: #94a3b8;
  transition: transform 0.2s;
}

.quick-card:hover .quick-arrow {
  transform: translateX(4px);
  color: #667eea;
}

.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.perm-card {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  border: 1px solid #e2e8f0;
}

.perm-card h4 {
  font-size: 16px;
  margin: 0 0 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e2e8f0;
}

.perm-card.allowed h4 {
  color: #059669;
}

.perm-card.denied h4 {
  color: #dc2626;
}

.perm-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.perm-card ul li {
  padding: 8px 0;
  color: #475569;
  font-size: 14px;
}

.perm-card.allowed ul li::before {
  content: '•';
  color: #10b981;
  margin-right: 8px;
}

.perm-card.denied ul li::before {
  content: '•';
  color: #ef4444;
  margin-right: 8px;
}
</style>
@endsection
