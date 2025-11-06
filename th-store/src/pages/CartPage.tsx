import { useCart } from '../store/CartContext'
import { Link } from 'react-router-dom'

export default function CartPage() {
  const { items, removeFromCart, updateQuantity, clearCart, getTotalPrice } = useCart()

  if (items.length === 0) {
    return (
      <div className="main">
        <h1>Giỏ hàng</h1>
        <p>Giỏ hàng của bạn đang trống</p>
        <Link to="/san-pham">Tiếp tục mua sắm</Link>
      </div>
    )
  }

  return (
    <div className="main">
      <h1>Giỏ hàng</h1>
      <div className="cart-items">
        {items.map(item => (
          <div key={`${item.id}-${item.color}-${item.size}`} className="cart-item">
            <img src={item.image} alt={item.name} className="cart-item-image" />
            <div className="cart-item-info">
              <h3>{item.name}</h3>
              <p>Màu: {item.color}</p>
              <p>Size: {item.size}</p>
            </div>
            <div className="cart-item-price">
              <p>{item.price.toLocaleString('vi-VN')}đ</p>
              <div className="quantity-controls">
                <button onClick={() => updateQuantity(item.id, item.quantity - 1)}>-</button>
                <span>{item.quantity}</span>
                <button onClick={() => updateQuantity(item.id, item.quantity + 1)}>+</button>
              </div>
              <p style={{margin:0,color:'#6b7280'}}>Tạm tính: {(item.price*item.quantity).toLocaleString('vi-VN')}đ</p>
              <button className="remove-btn" onClick={() => removeFromCart(item.id)}>
                Xóa
              </button>
            </div>
          </div>
        ))}
      </div>
      
      <div className="cart-summary">
        <div className="cart-total">
          Tổng cộng: {getTotalPrice().toLocaleString('vi-VN')}đ
        </div>
        <div className="cart-actions">
          <button className="clear-btn" onClick={clearCart}>
            Xóa tất cả
          </button>
          <Link to="/thanh-toan" className="checkout-btn">
            Thanh toán
          </Link>
        </div>
      </div>
    </div>
  )
}
