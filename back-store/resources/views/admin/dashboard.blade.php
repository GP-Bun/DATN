@extends('layouts.app')

@section('title','Dashboard')

@php
function money($v){
    return number_format($v,0,',','.') . 'đ';
}
@endphp

@section('content')
<div class="dashboard">

  {{-- HEADER --}}
  <div class="dashboard-header">
    <div>
      <h1 class="title">Dashboard Thống Kê</h1>
      <p class="subtitle">Tổng quan hoạt động kinh doanh TH Store</p>
    </div>
    <div class="dashboard-date">
      📅 {{ now()->format('d/m/Y') }}
    </div>
  </div>

  {{-- TOP STATS --}}
  <div class="stats-grid">
    <div class="stat-card green">
      <div class="stat-icon">💰</div>
      <p class="stat-label">Doanh thu năm</p>
      <h3>{{ money($data['topStats']['yearRevenue']) }}</h3>
      <span class="stat-down">
        ▼ {{ $data['topStats']['customerGrowthPercent'] }}%
      </span>
    </div>

    <div class="stat-card blue">
      <div class="stat-icon">🧾</div>
      <p class="stat-label">Tổng đơn hàng</p>
      <h3>{{ $data['topStats']['orderCount'] }}</h3>
      <span class="stat-sub">
        TB: {{ $data['topStats']['orderAvgPerMonth'] }}/tháng
      </span>
    </div>

    <div class="stat-card purple">
      <div class="stat-icon">👥</div>
      <p class="stat-label">Khách hàng</p>
      <h3>{{ $data['topStats']['customerCount'] }}</h3>
      <span class="stat-up">
        ▲ {{ $data['topStats']['customerGrowthPercent'] }}% tháng này
      </span>
    </div>

    <div class="stat-card orange">
      <div class="stat-icon">📦</div>
      <p class="stat-label">Sản phẩm</p>
      <h3>{{ $data['topStats']['productCount'] }}</h3>
      <span class="stat-sub">Đang bán</span>
    </div>
  </div>

  {{-- MAIN GRID --}}
  <div class="main-grid">
    <div class="card large">
      <h3>📊 Biểu đồ doanh thu (triệu)</h3>
      <div class="bar-chart">
        @foreach($data['chart']['monthlyRevenue'] as $i => $v)
          <div class="bar">
            <div class="bar-fill {{ $i === count($data['chart']['monthlyRevenue'])-1 ? 'highlight':'' }}"
                 style="height: {{ $v }}%">
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="card">
      <h3>🔄 Tỷ lệ chuyển đổi</h3>
      <div class="donut">
        <div class="donut-inner">{{ $data['conversion']['done'] }}%</div>
      </div>
      <div class="donut-legend">
        <span class="done">● Hoàn thành: {{ $data['conversion']['done'] }}%</span>
        <span class="pending">● Đang xử lý: {{ $data['conversion']['pending'] }}%</span>
      </div>
    </div>
  </div>

  {{-- BOTTOM --}}
  <div class="bottom-grid">
    <div class="card">
      <h3>🕒 Đơn hàng gần đây</h3>
      <ul class="order-list">
        @foreach($data['recentOrders'] as $o)
          <li>
            {{ $o['code'] }}
            <span class="green-text">{{ money($o['amount']) }}</span>
          </li>
        @endforeach
      </ul>
    </div>

    <div class="card">
      <h3>🏆 Top doanh thu tháng</h3>
      <ul class="top-list">
        @foreach($data['topMonths'] as $i => $m)
          <li class="{{ $i==0 ? 'top active':'' }}">
            {{ $m['month'] }}
            <span>{{ money($m['amount']) }}</span>
          </li>
        @endforeach
      </ul>
    </div>
  </div>

  {{-- QUICK STATS --}}
  <div class="quick-stats">
    <div class="quick-card">
      <span>📈 Doanh thu TB/ngày</span>
      <strong>{{ money($data['quickStats']['avgDailyRevenue']) }}</strong>
    </div>
    <div class="quick-card">
      <span>🛒 Giá trị TB/đơn</span>
      <strong>{{ money($data['quickStats']['avgOrderValue']) }}</strong>
    </div>
    <div class="quick-card up">
      <span>⬆️ Cao nhất</span>
      <strong>{{ money($data['quickStats']['maxMonth']) }}</strong>
    </div>
    <div class="quick-card down">
      <span>⬇️ Thấp nhất</span>
      <strong>{{ money($data['quickStats']['minMonth']) }}</strong>
    </div>
  </div>

</div>
@endsection
