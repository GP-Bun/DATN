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
      <div class="card-header">
        <h3>📊 Biểu đồ doanh thu</h3>
        <div class="tabs">
          <button class="tab active" data-type="daily7">7 ngày</button>
          <button class="tab" data-type="monthly">12 tháng</button>
          <button class="tab" data-type="yearly5">5 năm</button>
        </div>
      </div>

      <div id="barChart"></div>
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
<script>
const chartData = @json($data['chart']);

const chartEl = document.getElementById('barChart');
const tabs = document.querySelectorAll('.tab');

function formatMoney(value) {
  if (value >= 1000000000) {
    return (value / 1000000000).toFixed(1) + ' tỷ';
  } else if (value >= 1000000) {
    return (value / 1000000).toFixed(1) + ' tr';
  } else if (value >= 1000) {
    return (value / 1000).toFixed(0) + 'k';
  }
  return value.toLocaleString('vi-VN') + 'đ';
}

function renderChart(type) {
  chartEl.innerHTML = '';

  const data = chartData[type];
  const percentArr = data.percent || data;
  const rawArr = data.raw || percentArr;
  const labels = data.labels || [];

  // Đảm bảo có ít nhất 1 cột hiển thị
  const maxPercent = Math.max(...percentArr, 1);

  percentArr.forEach((v, i) => {
    const barWrapper = document.createElement('div');
    barWrapper.className = 'bar-wrapper';

    const bar = document.createElement('div');
    bar.className = 'bar';

    const fill = document.createElement('div');
    fill.className = 'bar-fill';
    // Đảm bảo chiều cao tối thiểu 5% nếu có giá trị
    const height = v > 0 ? Math.max(v, 5) : (rawArr[i] > 0 ? 10 : 0);
    fill.style.height = height + '%';

    // Tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'bar-tooltip';
    tooltip.textContent = formatMoney(rawArr[i] || 0);

    // Label
    const label = document.createElement('div');
    label.className = 'bar-label';
    label.textContent = labels[i] || '';

    bar.appendChild(fill);
    bar.appendChild(tooltip);
    barWrapper.appendChild(bar);
    barWrapper.appendChild(label);
    chartEl.appendChild(barWrapper);
  });
}

tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    tabs.forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    renderChart(tab.dataset.type);
  });
});

// mặc định 7 ngày
renderChart('daily7');
</script>

<style>
#barChart {
  display: flex !important;
  align-items: flex-end !important;
  justify-content: space-around !important;
  gap: 12px !important;
  height: 220px !important;
  padding: 20px 10px 10px !important;
}

#barChart .bar-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  max-width: 80px;
  height: 100%;
}

#barChart .bar {
  width: 100%;
  height: 180px;
  background: #eaeaf5;
  border-radius: 8px;
  display: flex;
  align-items: flex-end;
  position: relative;
  cursor: pointer;
  overflow: visible;
}

#barChart .bar-fill {
  width: 100%;
  background: linear-gradient(180deg, #667eea 0%, #4ade80 100%);
  border-radius: 8px;
  transition: height 0.3s ease;
  min-height: 4px;
}

#barChart .bar-tooltip {
  position: absolute;
  top: -35px;
  left: 50%;
  transform: translateX(-50%);
  background: #1e293b;
  color: #fff;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
  white-space: nowrap;
  opacity: 0;
  transition: opacity 0.2s;
  pointer-events: none;
  z-index: 100;
}

#barChart .bar-tooltip::after {
  content: '';
  position: absolute;
  bottom: -6px;
  left: 50%;
  transform: translateX(-50%);
  border-left: 6px solid transparent;
  border-right: 6px solid transparent;
  border-top: 6px solid #1e293b;
}

#barChart .bar:hover .bar-tooltip {
  opacity: 1;
}

#barChart .bar:hover .bar-fill {
  background: linear-gradient(180deg, #764ba2 0%, #22c55e 100%);
}

#barChart .bar-label {
  margin-top: 8px;
  font-size: 12px;
  color: #374151;
  font-weight: 600;
  text-align: center;
}
</style>

@endsection
