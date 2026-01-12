import React from 'react';
import { Link } from 'react-router-dom';

const Dashboard = () => {
  // Fake data for visual demonstration
  const stats = [
    { label: 'Tổng doanh thu', value: '45.2M', change: '+12.5%', isPositive: true, icon: '💰', color: '#10b981' },
    { label: 'Đờn hàng mới', value: '156', change: '+8.2%', isPositive: true, icon: '📦', color: '#3b82f6' },
    { label: 'Khách hàng', value: '1,234', change: '-2.4%', isPositive: false, icon: '👥', color: '#8b5cf6' },
    { label: 'Sản phẩm', value: '89', change: '+0.0%', isPositive: true, icon: '🏷️', color: '#f59e0b' },
  ];

  return (
    <div style={{ padding: '24px', fontFamily: 'Inter, sans-serif' }}>
      <div style={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: '32px',
        background: 'white',
        padding: '24px',
        borderRadius: '16px',
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.05)'
      }}>
        <div>
          <h1 style={{ fontSize: '24px', fontWeight: '700', color: '#111827', margin: 0 }}>Tổng quan hệ thống</h1>
          <p style={{ color: '#6b7280', margin: '4px 0 0 0', fontSize: '14px' }}>
            Chào mừng trở lại, Administrator 👋
          </p>
        </div>
        <div style={{ display: 'flex', gap: '12px' }}>
          <button style={{
            padding: '10px 16px',
            backgroundColor: 'white',
            border: '1px solid #e5e7eb',
            borderRadius: '8px',
            color: '#374151',
            fontWeight: '600',
            cursor: 'pointer'
          }}>
            📅 Hôm nay
          </button>
        </div>
      </div>

      <div style={{
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))',
        gap: '24px',
        marginBottom: '32px'
      }}>
        {stats.map((stat, index) => (
          <div key={index} style={{
            background: 'white',
            padding: '24px',
            borderRadius: '16px',
            boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.05)',
            display: 'flex',
            alignItems: 'flex-start',
            justifyContent: 'space-between'
          }}>
            <div>
              <div style={{ color: '#6b7280', fontSize: '14px', fontWeight: '500', marginBottom: '8px' }}>
                {stat.label}
              </div>
              <div style={{ fontSize: '28px', fontWeight: '700', color: '#111827', marginBottom: '8px' }}>
                {stat.value}
              </div>
              <div style={{
                fontSize: '13px',
                fontWeight: '600',
                color: stat.isPositive ? '#10b981' : '#ef4444',
                display: 'flex',
                alignItems: 'center',
                gap: '4px'
              }}>
                <span>{stat.isPositive ? '↑' : '↓'}</span>
                {stat.change}
                <span style={{ color: '#9ca3af', fontWeight: '400' }}> so với tháng trước</span>
              </div>
            </div>
            <div style={{
              width: '48px',
              height: '48px',
              borderRadius: '12px',
              backgroundColor: `${stat.color}15`,
              color: stat.color,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '24px'
            }}>
              {stat.icon}
            </div>
          </div>
        ))}
      </div>

      <div style={{
        display: 'grid',
        gridTemplateColumns: '2fr 1fr',
        gap: '24px',
        alignItems: 'start'
      }}>
        {/* Recent Orders Section */}
        <div style={{
          background: 'white',
          borderRadius: '16px',
          boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.05)',
          padding: '24px',
          minHeight: '400px'
        }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
            <h3 style={{ margin: 0, fontSize: '18px', fontWeight: '700', color: '#111827' }}>Đơn hàng gần đây</h3>
            <Link to="/admin/orders" style={{ color: '#3b82f6', textDecoration: 'none', fontWeight: '600', fontSize: '14px' }}>
              Xem tất cả →
            </Link>
          </div>
          {/* Placeholder for chart or list */}
          <div style={{
            height: '300px',
            backgroundColor: '#f9fafb',
            borderRadius: '12px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: '#9ca3af',
            border: '2px dashed #e5e7eb'
          }}>
            Chưa có dữ liệu biểu đồ
          </div>
        </div>

        {/* Quick Actions / Notifications */}
        <div style={{
          background: 'white',
          borderRadius: '16px',
          boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.05)',
          padding: '24px'
        }}>
          <h3 style={{ margin: '0 0 24px 0', fontSize: '18px', fontWeight: '700', color: '#111827' }}>Hoạt động</h3>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
            {[1, 2, 3].map(i => (
              <div key={i} style={{ display: 'flex', gap: '16px' }}>
                <div style={{
                  width: '32px',
                  height: '32px',
                  borderRadius: '50%',
                  backgroundColor: '#e5e7eb',
                  flexShrink: 0
                }} />
                <div>
                  <div style={{ fontSize: '14px', color: '#374151', marginBottom: '4px' }}>
                    <span style={{ fontWeight: '600' }}>User {i}</span> vừa đặt hàng mới
                  </div>
                  <div style={{ fontSize: '12px', color: '#9ca3af' }}>2 phút trước</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}

export default Dashboard