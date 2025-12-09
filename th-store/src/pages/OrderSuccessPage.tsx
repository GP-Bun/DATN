import { useLocation, useNavigate } from 'react-router-dom'

type OrderItem = {
  id: number
  product_id: number
  product_name: string
  quantity: number
  price: number
  product?: {
    id: number
    name: string
    image: string
    price: number
  }
  variant?: {
    color?: string
    size?: string
  }
}

type Order = {
  id: number
  order_status: string
  payment_status: string
  total_amount: number
  created_at: string
  items: OrderItem[]
  address?: {
    receiver_name: string
    receiver_phone: string
    line1: string
    city: string
    province: string
  }
}

export default function OrderSuccessPage() {
  const location = useLocation()
  const navigate = useNavigate()
  const order: Order | null = location.state?.order || null

  if (!order) {
    return (
      <div className="main">
        <h1>Không tìm thấy đơn hàng</h1>
        <p>Vui lòng quay lại trang chủ.</p>
        <button onClick={() => navigate('/')}>Về trang chủ</button>
      </div>
    )
  }

  const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('vi-VN', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  const getStatusText = (status: string) => {
    const statusMap: Record<string, string> = {
      pending: 'Chờ xử lý',
      processing: 'Đang xử lý',
      shipped: 'Đang giao hàng',
      delivered: 'Đã giao hàng',
      cancelled: 'Đã hủy'
    }
    return statusMap[status] || status
  }

  const getPaymentStatusText = (status: string) => {
    const statusMap: Record<string, string> = {
      unpaid: 'Chưa thanh toán',
      paid: 'Đã thanh toán',
      refunded: 'Đã hoàn tiền'
    }
    return statusMap[status] || status
  }

  return (
    <div className="main">
      <div style={{ maxWidth: '800px', margin: '0 auto' }}>
        <div style={{ 
          textAlign: 'center', 
          padding: '40px 20px',
          background: '#f0f9ff',
          borderRadius: '8px',
          marginBottom: '30px'
        }}>
          <div style={{ fontSize: '48px', marginBottom: '16px' }}>✓</div>
          <h1 style={{ color: '#059669', marginBottom: '8px' }}>Đặt hàng thành công!</h1>
          <p style={{ color: '#666', fontSize: '18px' }}>
            Cảm ơn bạn đã đặt hàng. Mã đơn hàng của bạn là: <strong>#{order.id}</strong>
          </p>
        </div>

        <div style={{ 
          background: 'white', 
          padding: '24px', 
          borderRadius: '8px',
          boxShadow: '0 1px 3px rgba(0,0,0,0.1)',
          marginBottom: '24px'
        }}>
          <h2 style={{ marginBottom: '20px', borderBottom: '2px solid #e5e7eb', paddingBottom: '12px' }}>
            Thông tin đơn hàng
          </h2>
          
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '20px' }}>
            <div>
              <strong>Mã đơn hàng:</strong> #{order.id}
            </div>
            <div>
              <strong>Ngày đặt:</strong> {formatDate(order.created_at)}
            </div>
            <div>
              <strong>Trạng thái:</strong> {getStatusText(order.order_status)}
            </div>
            <div>
              <strong>Thanh toán:</strong> {getPaymentStatusText(order.payment_status)}
            </div>
          </div>

          {order.address && (
            <div style={{ marginTop: '20px', paddingTop: '20px', borderTop: '1px solid #e5e7eb' }}>
              <h3 style={{ marginBottom: '12px' }}>Địa chỉ giao hàng</h3>
              <p><strong>Người nhận:</strong> {order.address.receiver_name}</p>
              <p><strong>Điện thoại:</strong> {order.address.receiver_phone}</p>
              <p><strong>Địa chỉ:</strong> {order.address.line1}, {order.address.city}, {order.address.province}</p>
            </div>
          )}
        </div>

        <div style={{ 
          background: 'white', 
          padding: '24px', 
          borderRadius: '8px',
          boxShadow: '0 1px 3px rgba(0,0,0,0.1)',
          marginBottom: '24px'
        }}>
          <h2 style={{ marginBottom: '20px', borderBottom: '2px solid #e5e7eb', paddingBottom: '12px' }}>
            Sản phẩm đã đặt
          </h2>

          <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
            {order.items.map((item) => (
              <div 
                key={item.id}
                style={{ 
                  display: 'flex', 
                  gap: '16px', 
                  padding: '16px',
                  background: '#f9fafb',
                  borderRadius: '8px',
                  border: '1px solid #e5e7eb'
                }}
              >
                {item.product?.image ? (
                  <img 
                    src={item.product.image} 
                    alt={item.product_name}
                    style={{ 
                      width: '100px', 
                      height: '100px', 
                      objectFit: 'cover',
                      borderRadius: '8px'
                    }} 
                  />
                ) : (
                  <div style={{
                    width: '100px',
                    height: '100px',
                    background: '#e5e7eb',
                    borderRadius: '8px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    color: '#9ca3af'
                  }}>
                    No Image
                  </div>
                )}
                
                <div style={{ flex: 1 }}>
                  <h3 style={{ marginBottom: '8px', fontSize: '18px' }}>
                    {item.product_name}
                  </h3>
                  {item.variant && (
                    <p style={{ color: '#666', marginBottom: '4px' }}>
                      {item.variant.color && `Màu: ${item.variant.color}`}
                      {item.variant.color && item.variant.size && ' • '}
                      {item.variant.size && `Size: ${item.variant.size}`}
                    </p>
                  )}
                  <p style={{ color: '#666', marginBottom: '8px' }}>
                    Số lượng: {item.quantity}
                  </p>
                  <p style={{ fontSize: '18px', fontWeight: 'bold', color: '#059669' }}>
                    {item.price.toLocaleString('vi-VN')}đ
                  </p>
                </div>
                
                <div style={{ textAlign: 'right' }}>
                  <p style={{ fontSize: '20px', fontWeight: 'bold' }}>
                    {(item.price * item.quantity).toLocaleString('vi-VN')}đ
                  </p>
                </div>
              </div>
            ))}
          </div>

          <div style={{ 
            marginTop: '24px', 
            paddingTop: '24px', 
            borderTop: '2px solid #e5e7eb',
            textAlign: 'right'
          }}>
            <div style={{ fontSize: '24px', fontWeight: 'bold', color: '#059669' }}>
              Tổng cộng: {order.total_amount.toLocaleString('vi-VN')}đ
            </div>
          </div>
        </div>

        <div style={{ display: 'flex', gap: '12px', justifyContent: 'center' }}>
          <button 
            onClick={() => navigate('/')}
            style={{
              padding: '12px 24px',
              background: '#3b82f6',
              color: 'white',
              border: 'none',
              borderRadius: '6px',
              cursor: 'pointer',
              fontSize: '16px',
              fontWeight: '500'
            }}
          >
            Tiếp tục mua sắm
          </button>
          <button 
            onClick={() => navigate('/san-pham')}
            style={{
              padding: '12px 24px',
              background: 'white',
              color: '#3b82f6',
              border: '2px solid #3b82f6',
              borderRadius: '6px',
              cursor: 'pointer',
              fontSize: '16px',
              fontWeight: '500'
            }}
          >
            Xem sản phẩm
          </button>
        </div>
      </div>
    </div>
  )
}

