import { useCart } from '../store/CartContext'
import { useAuth } from '../store/AuthContext'
import { useState } from 'react'
import { useNavigate } from 'react-router-dom'

export default function CheckoutPage() {
  const { items, getTotalPrice, clearCart } = useCart()
  const { user } = useAuth()
  const navigate = useNavigate()
  const [formData, setFormData] = useState({
    fullName: user?.name || '',
    email: user?.email || '',
    phone: '',
    address: '',
    city: '',
    paymentMethod: 'cod'
  })
  const [bankInfo, setBankInfo] = useState({ bankName: '', accountNumber: '', accountName: '' })
  const [cardInfo, setCardInfo] = useState({ cardNumber: '', cardName: '', expiry: '', cvv: '' })

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    alert('Đặt hàng thành công!')
    clearCart()
    navigate('/')
  }

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    })
  }

  if (items.length === 0) {
    return (
      <div className="main">
        <h1>Thanh toán</h1>
        <p>Giỏ hàng của bạn đang trống</p>
      </div>
    )
  }

  return (
    <div className="main">
      <h1>Thanh toán</h1>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '40px' }}>
        <form className="checkout-form" onSubmit={handleSubmit}>
          <h2>Thông tin giao hàng</h2>
          <input
            name="fullName"
            placeholder="Họ và tên"
            value={formData.fullName}
            onChange={handleChange}
            required
          />
          <input
            name="email"
            type="email"
            placeholder="Email"
            value={formData.email}
            onChange={handleChange}
            required
          />
          <input
            name="phone"
            placeholder="Số điện thoại"
            value={formData.phone}
            onChange={handleChange}
            required
          />
          <input
            name="address"
            placeholder="Địa chỉ"
            value={formData.address}
            onChange={handleChange}
            required
          />
          <input
            name="city"
            placeholder="Thành phố"
            value={formData.city}
            onChange={handleChange}
            required
          />
          
          <h3>Phương thức thanh toán</h3>
          <select name="paymentMethod" value={formData.paymentMethod} onChange={handleChange}>
            <option value="cod">Thanh toán khi nhận hàng</option>
            <option value="bank">Chuyển khoản ngân hàng</option>
            <option value="card">Thẻ tín dụng</option>
          </select>

          {formData.paymentMethod === 'bank' && (
            <div style={{display:'grid',gap:'10px'}}>
              <input placeholder="Ngân hàng" value={bankInfo.bankName} onChange={e=>setBankInfo({...bankInfo, bankName: e.target.value})} />
              <input placeholder="Số tài khoản" value={bankInfo.accountNumber} onChange={e=>setBankInfo({...bankInfo, accountNumber: e.target.value})} />
              <input placeholder="Tên chủ tài khoản" value={bankInfo.accountName} onChange={e=>setBankInfo({...bankInfo, accountName: e.target.value})} />
            </div>
          )}
          {formData.paymentMethod === 'card' && (
            <div style={{display:'grid',gap:'10px'}}>
              <input placeholder="Số thẻ" value={cardInfo.cardNumber} onChange={e=>setCardInfo({...cardInfo, cardNumber: e.target.value})} />
              <input placeholder="Tên chủ thẻ" value={cardInfo.cardName} onChange={e=>setCardInfo({...cardInfo, cardName: e.target.value})} />
              <div style={{display:'grid',gridTemplateColumns:'1fr 1fr',gap:'10px'}}>
                <input placeholder="MM/YY" value={cardInfo.expiry} onChange={e=>setCardInfo({...cardInfo, expiry: e.target.value})} />
                <input placeholder="CVV" value={cardInfo.cvv} onChange={e=>setCardInfo({...cardInfo, cvv: e.target.value})} />
              </div>
            </div>
          )}
          
          <button type="submit">Đặt hàng</button>
        </form>

        <div>
          <h2>Đơn hàng của bạn</h2>
          {items.map(item => (
            <div key={`${item.id}-${item.color}-${item.size}`} style={{ display: 'flex', gap: '12px', marginBottom: '12px' }}>
              <img src={item.image} alt={item.name} style={{ width: '60px', height: '60px', objectFit: 'cover' }} />
              <div>
                <h4>{item.name}</h4>
                <p>Màu: {item.color}, Size: {item.size}</p>
                <p>Số lượng: {item.quantity}</p>
                <p>Giá: {item.price.toLocaleString('vi-VN')}đ</p>
              </div>
            </div>
          ))}
          <div style={{ borderTop: '1px solid #e5e7eb', paddingTop: '16px', marginTop: '16px' }}>
            <h3>Tổng cộng: {getTotalPrice().toLocaleString('vi-VN')}đ</h3>
            <p style={{color:'#6b7280'}}>Phí vận chuyển sẽ được tính ở bước tiếp theo.</p>
          </div>
        </div>
      </div>
    </div>
  )
}
