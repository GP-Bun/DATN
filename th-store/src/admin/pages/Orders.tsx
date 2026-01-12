import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/api';
import { toast } from 'react-hot-toast';
import Swal from 'sweetalert2';

interface Order {
  id: number;
  user_name?: string; // Tên user nếu có
  total_price: number;
  status: string;
  created_at: string;
  user?: { name: string; email: string };
  // Add other fields as needed
}

const Orders = () => {
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchOrders = async () => {
    try {
      // Assuming endpoint is /admin/orders based on routes
      const res = await api.get('/admin/orders');
      const items = res.data?.data?.data || res.data?.data || res.data || [];
      setOrders(items);
    } catch (error) {
      console.error(error);
      toast.error('Không thể tải danh sách đơn hàng');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchOrders();
  }, []);

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('vi-VN');
  }

  // Helper for status badge
  const renderStatus = (status: string) => {
    // Map status strings to colors
    const statusMap: Record<string, { color: string, bg: string, label: string }> = {
      'pending': { color: '#d97706', bg: '#fffbeb', label: 'Chờ xử lý' },
      'processing': { color: '#3b82f6', bg: '#eff6ff', label: 'Đang xử lý' },
      'shipping': { color: '#8b5cf6', bg: '#f5f3ff', label: 'Đang giao' },
      'completed': { color: '#10b981', bg: '#ecfdf5', label: 'Hoàn thành' },
      'cancelled': { color: '#ef4444', bg: '#fef2f2', label: 'Đã hủy' },
    };

    const config = statusMap[status] || { color: '#6b7280', bg: '#f3f4f6', label: status };

    return (
      <span style={{
        display: 'inline-block',
        padding: '4px 12px',
        borderRadius: '9999px',
        fontSize: '12px',
        fontWeight: '600',
        color: config.color,
        backgroundColor: config.bg,
        textAlign: 'center',
        minWidth: '100px'
      }}>
        {config.label}
      </span>
    );
  };

  if (loading) {
    return <div style={{ padding: '20px', textAlign: 'center' }}>Đang tải dữ liệu...</div>;
  }

  return (
    <div style={{ padding: '24px', fontFamily: 'Inter, sans-serif' }}>
      {/* Header Section */}
      <div style={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: '32px',
        background: 'white',
        padding: '20px',
        borderRadius: '12px',
        boxShadow: '0 2px 4px rgba(0,0,0,0.05)'
      }}>
        <div>
          <h1 style={{ fontSize: '24px', fontWeight: '700', color: '#111827', margin: 0 }}>Quản lý đơn hàng</h1>
          <p style={{ color: '#6b7280', margin: '4px 0 0 0', fontSize: '14px' }}>
            Theo dõi vận chuyển và xử lý đơn hàng của khách hàng
          </p>
        </div>
        <div>
          <button style={{
            display: 'flex',
            alignItems: 'center',
            gap: '8px',
            padding: '10px 16px',
            backgroundColor: '#fff',
            border: '1px solid #e5e7eb',
            borderRadius: '8px',
            color: '#374151',
            fontWeight: '600',
            fontSize: '14px',
            cursor: 'pointer'
          }}>
            <span>📥</span> Xuất báo cáo
          </button>
        </div>
      </div>

      {/* Orders List */}
      <div style={{
        background: 'white',
        borderRadius: '12px',
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
        overflow: 'hidden'
      }}>
        <div style={{ overflowX: 'auto' }}>
          <table style={{ width: '100%', borderCollapse: 'collapse', minWidth: '800px' }}>
            <thead style={{ backgroundColor: '#f9fafb', borderBottom: '1px solid #e5e7eb' }}>
              <tr>
                <th style={headerStyle}>Mã đơn</th>
                <th style={headerStyle}>Khách hàng</th>
                <th style={headerStyle}>Ngày đặt</th>
                <th style={headerStyle}>Tổng tiền</th>
                <th style={headerStyle}>Trạng thái</th>
                <th style={{ ...headerStyle, textAlign: 'right' }}>Hành động</th>
              </tr>
            </thead>
            <tbody>
              {orders.map(order => (
                <tr key={order.id} style={{ borderBottom: '1px solid #f3f4f6' }}>
                  <td style={{ ...cellStyle, fontWeight: '600' }}>#{order.id}</td>
                  <td style={cellStyle}>
                    <div style={{ fontWeight: '500', color: '#111827' }}>
                      {order.user?.name || order.user_name || 'Khách vãng lai'}
                    </div>
                    <div style={{ fontSize: '12px', color: '#6b7280' }}>
                      {order.user?.email}
                    </div>
                  </td>
                  <td style={cellStyle}>{formatDate(order.created_at)}</td>
                  <td style={{ ...cellStyle, fontWeight: '600', color: '#111827' }}>
                    {formatPrice(order.total_price)}
                  </td>
                  <td style={cellStyle}>
                    {renderStatus(order.status)}
                  </td>
                  <td style={{ ...cellStyle, textAlign: 'right' }}>
                    <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '8px' }}>
                      <Link
                        to={`/admin/orders/${order.id}`} // Assuming detail page exists
                        style={actionBtnStyle('#2563eb')} // Blue
                        title="Xem chi tiết"
                      >
                        👁️
                      </Link>
                      {/* Add actions like Delete or Update Status if APIs exist */}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {orders.length === 0 && (
          <div style={{ padding: '40px', textAlign: 'center', color: '#6b7280' }}>
            Chưa có đơn hàng nào.
          </div>
        )}
      </div>
    </div>
  );
};

// Styles
const headerStyle: React.CSSProperties = {
  padding: '16px 24px',
  textAlign: 'left',
  fontSize: '13px',
  fontWeight: '600',
  color: '#6b7280',
  textTransform: 'uppercase',
  letterSpacing: '0.05em'
};

const cellStyle: React.CSSProperties = {
  padding: '16px 24px',
  verticalAlign: 'middle',
  fontSize: '14px',
  color: '#374151'
};

const actionBtnStyle = (color: string): React.CSSProperties => ({
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  width: '32px',
  height: '32px',
  borderRadius: '6px',
  border: 'none',
  backgroundColor: `${color}15`,
  color: color,
  cursor: 'pointer',
  transition: 'all 0.2s',
  textDecoration: 'none'
});

export default Orders;