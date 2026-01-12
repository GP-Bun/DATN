import React, { useEffect, useState } from 'react';
import api from '../../api/api';
import { toast } from 'react-hot-toast';
import Swal from 'sweetalert2';

interface User {
  id: number;
  name: string;
  email: string;
  role: string; // admin / user
  status: number; // 1 = active, 0 = inactive (assuming)
  created_at: string;
}

const Users = () => {
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchUsers = async () => {
    try {
      const res = await api.get('/admin/users');
      // Normalize response data
      const items = res.data?.data?.data || res.data?.data || res.data || [];
      setUsers(items);
    } catch (error) {
      console.error(error);
      toast.error('Không thể tải danh sách người dùng');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchUsers();
  }, []);

  const handleToggleStatus = async (id: number, currentStatus: number) => {
    // Implement status toggle if API exists
    toast.error("Tính năng đang phát triển");
  }

  const handleDelete = async (id: number) => {
    const result = await Swal.fire({
      title: 'Bạn có chắc chắn?',
      text: "Hành động này không thể hoàn tác!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Xóa người dùng',
      cancelButtonText: 'Hủy'
    });

    if (result.isConfirmed) {
      try {
        await api.delete(`/admin/users/${id}`);
        toast.success('Đã xóa người dùng');
        fetchUsers();
      } catch (error) {
        toast.error('Xóa thất bại');
      }
    }
  };


  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('vi-VN');
  }

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
          <h1 style={{ fontSize: '24px', fontWeight: '700', color: '#111827', margin: 0 }}>Quản lý người dùng</h1>
          <p style={{ color: '#6b7280', margin: '4px 0 0 0', fontSize: '14px' }}>
            Quản lý tài khoản khách hàng và quản trị viên
          </p>
        </div>
        {/* <button style={{
            display: 'flex',
            alignItems: 'center',
            gap: '8px',
            padding: '10px 16px',
            backgroundColor: '#2563eb',
            borderRadius: '8px',
            color: 'white',
            fontWeight: '600',
            fontSize: '14px',
            border: 'none',
            cursor: 'pointer'
        }}>
            <span>+</span> Thêm người dùng
        </button> */}
      </div>

      {/* Users List */}
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
                <th style={headerStyle}>ID</th>
                <th style={headerStyle}>Thông tin cá nhân</th>
                <th style={headerStyle}>Vai trò</th>
                <th style={headerStyle}>Ngày tham gia</th>
                <th style={headerStyle}>Trạng thái</th>
                <th style={{ ...headerStyle, textAlign: 'right' }}>Hành động</th>
              </tr>
            </thead>
            <tbody>
              {users.map(user => (
                <tr key={user.id} style={{ borderBottom: '1px solid #f3f4f6' }}>
                  <td style={cellStyle}>#{user.id}</td>
                  <td style={cellStyle}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                      <div style={{
                        width: '40px',
                        height: '40px',
                        borderRadius: '50%',
                        backgroundColor: '#e5e7eb',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontSize: '14px',
                        fontWeight: 'bold',
                        color: '#6b7280'
                      }}>
                        {user.name.charAt(0).toUpperCase()}
                      </div>
                      <div>
                        <div style={{ fontWeight: '600', color: '#111827' }}>{user.name}</div>
                        <div style={{ fontSize: '12px', color: '#6b7280' }}>{user.email}</div>
                      </div>
                    </div>
                  </td>
                  <td style={cellStyle}>
                    <span style={{
                      padding: '4px 10px',
                      borderRadius: '12px',
                      fontSize: '12px',
                      fontWeight: '600',
                      backgroundColor: user.role === 'admin' ? '#e0e7ff' : '#f3f4f6',
                      color: user.role === 'admin' ? '#4338ca' : '#4b5563'
                    }}>
                      {user.role === 'admin' ? 'Quản trị viên' : 'Khách hàng'}
                    </span>
                  </td>
                  <td style={cellStyle}>{formatDate(user.created_at)}</td>
                  <td style={cellStyle}>
                    <span style={{
                      color: user.status !== 0 ? '#10b981' : '#ef4444',
                      fontWeight: '600',
                      fontSize: '14px'
                    }}>
                      {user.status !== 0 ? '● Hoạt động' : '● Đã khóa'}
                    </span>
                  </td>
                  <td style={{ ...cellStyle, textAlign: 'right' }}>
                    <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '8px' }}>
                      {/* <button 
                                    onClick={() => handleToggleStatus(user.id, user.status)}
                                    style={actionBtnStyle('#f59e0b')} 
                                    title="Khóa/Mở khóa"
                                >
                                    🔒
                                </button> */}
                      <button
                        onClick={() => handleDelete(user.id)}
                        style={actionBtnStyle('#ef4444')}
                        title="Xóa"
                      >
                        🗑️
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {users.length === 0 && (
          <div style={{ padding: '40px', textAlign: 'center', color: '#6b7280' }}>
            Chưa có người dùng nào.
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
});

export default Users;