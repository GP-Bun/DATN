import { useEffect, useMemo, useState } from 'react'
import { api } from '../../store/api'

interface OrderDTO {
  id: number
  code: string
  customerName: string
  total: number
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled'
  createdAt?: string
}

const Orders = () => {
  const [orders, setOrders] = useState<OrderDTO[]>([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const loadOrders = async () => {
    setLoading(true)
    setError(null)
    try {
      const data = await api.get<OrderDTO[]>('/admin/orders')
      setOrders(data)
    } catch (e: any) {
      setError(e.message || 'Không thể tải danh sách đơn hàng')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { loadOrders() }, [])

  const updateStatus = async (o: OrderDTO, status: OrderDTO['status']) => {
    setError(null)
    try {
      await api.patch(`/admin/orders/${o.id}/status`, { status })
      setOrders(prev => prev.map(x => x.id === o.id ? { ...x, status } : x))
    } catch (e: any) {
      setError(e.message || 'Không thể cập nhật trạng thái đơn hàng')
    }
  }

  const cancelOrder = async (o: OrderDTO) => {
    if (!confirm('Hủy đơn hàng này?')) return
    await updateStatus(o, 'cancelled')
  }

  const deleteOrder = async (id: number) => {
    if (!confirm('Xóa đơn hàng này vĩnh viễn?')) return
    setError(null)
    try {
      await api.delete(`/admin/orders/${id}`)
      setOrders(prev => prev.filter(o => o.id !== id))
    } catch (e: any) {
      setError(e.message || 'Không thể xóa đơn hàng')
    }
  }

  const statusOptions: { value: OrderDTO['status']; label: string }[] = [
    { value: 'pending', label: 'Chờ xử lý' },
    { value: 'processing', label: 'Đang xử lý' },
    { value: 'shipped', label: 'Đã gửi' },
    { value: 'delivered', label: 'Đã giao' },
    { value: 'cancelled', label: 'Đã hủy' },
  ]

  const statusColor = useMemo(() => ({
    pending: '#f59e0b',
    processing: '#3b82f6',
    shipped: '#06b6d4',
    delivered: '#10b981',
    cancelled: '#ef4444',
  }), [])

  return (
    <div>
      <div className="admin-page-header">
        <h1 className="admin-page-title">Quản lý đơn hàng</h1>
        <p className="admin-page-subtitle">Theo dõi và quản lý tất cả đơn hàng của khách hàng</p>
      </div>
      <div className="admin-card">
        <div className="admin-card-header">
          <h3 className="admin-card-title">Danh sách đơn hàng</h3>
          <button className="admin-btn" onClick={loadOrders}>Làm mới</button>
        </div>
        <div className="admin-card-content">
          {error && <div className="admin-alert-error" style={{marginBottom: 12}}>{error}</div>}
          {loading ? (
            <div>Đang tải...</div>
          ) : (
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Mã đơn</th>
                  <th>Khách hàng</th>
                  <th>Tổng tiền</th>
                  <th>Trạng thái</th>
                  <th>Ngày tạo</th>
                  <th>Hành động</th>
                </tr>
              </thead>
              <tbody>
                {orders.length === 0 ? (
                  <tr>
                    <td colSpan={6} style={{ textAlign: 'center' }}>Chưa có đơn hàng</td>
                  </tr>
                ) : orders.map(o => (
                  <tr key={o.id}>
                    <td>#{o.code}</td>
                    <td>{o.customerName}</td>
                    <td>{o.total.toLocaleString('vi-VN')}đ</td>
                    <td>
                      <span style={{ color: statusColor[o.status] }}>{statusOptions.find(s => s.value === o.status)?.label}</span>
                    </td>
                    <td>{o.createdAt || '-'}</td>
                    <td>
                      <select
                        value={o.status}
                        onChange={e => updateStatus(o, e.target.value as OrderDTO['status'])}
                        style={{ marginRight: 8 }}
                      >
                        {statusOptions.map(s => (
                          <option key={s.value} value={s.value}>{s.label}</option>
                        ))}
                      </select>
                      <button className="admin-btn-secondary" style={{marginRight: '8px', padding: '6px 12px', fontSize: '0.8rem'}} onClick={() => cancelOrder(o)}>Hủy</button>
                      <button className="admin-btn-secondary" style={{padding: '6px 12px', fontSize: '0.8rem'}} onClick={() => deleteOrder(o.id)}>Xóa</button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </div>
    </div>
  )
}

export default Orders
  