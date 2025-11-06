import { useParams } from 'react-router-dom'
import { useCart } from '../store/CartContext'
import { useState } from 'react'
import { useFavorites } from '../store/FavoritesContext'

export default function ProductDetailPage() {
  const { id } = useParams()
  const { addToCart } = useCart()
  const { toggle, isFavorite } = useFavorites()
  const [quantity, setQuantity] = useState(1)
  const [selectedColor, setSelectedColor] = useState('black')
  const [selectedSize, setSelectedSize] = useState('M')
  const [isAdding, setIsAdding] = useState(false)

  const product = {
    id: Number(id),
    name: 'Giày Sneaker TH Runner',
    price: 1299000,
    image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&h=400&fit=crop',
    description: 'Giày chạy bộ cao cấp với đế cao su chống trượt và công nghệ đệm khí hiện đại. Thiết kế nhẹ, thoáng khí, phù hợp cho mọi hoạt động thể thao. Chất liệu da cao cấp, bền đẹp theo thời gian. Sản phẩm được thiết kế với công nghệ hiện đại, đảm bảo sự thoải mái tối đa cho đôi chân.',
    colors: [
      { name: 'Đen', value: 'black', hex: '#000000' },
      { name: 'Trắng', value: 'white', hex: '#ffffff' },
      { name: 'Xanh dương', value: 'blue', hex: '#3b82f6' },
      { name: 'Đỏ', value: 'red', hex: '#ef4444' }
    ],
    sizes: ['39', '40', '41', '42', '43'],
    stock: 50,
    rating: 4.8,
    reviews: 128
  }

  const handleAddToCart = async () => {
    setIsAdding(true)
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    addToCart({
      name: product.name,
      price: product.price,
      image: product.image,
      quantity,
      color: selectedColor,
      size: selectedSize
    })
    
    setIsAdding(false)
    
    // Show success animation
    const button = document.querySelector('.add-to-cart-btn')
    if (button) {
      button.classList.add('success-animation')
      setTimeout(() => {
        button.classList.remove('success-animation')
      }, 2000)
    }
  }

  return (
    <div className="main">
      <div className="product-detail-container">
        <div className="product-detail">
          <div className="product-image-section">
            <div className="product-image-wrapper">
              <img src={product.image} alt={product.name} className="product-main-image" />
              <div className="image-overlay">
                <span className="zoom-hint">🔍 Hover để phóng to</span>
              </div>
            </div>
          </div>
          
          <div className="product-info-section">
            <div className="product-header">
              <h1 className="product-title">{product.name}</h1>
              <div className="product-rating">
                <div className="stars">
                  {[...Array(5)].map((_, i) => (
                    <span key={i} className={i < Math.floor(product.rating) ? 'star filled' : 'star'}>⭐</span>
                  ))}
                </div>
                <span className="rating-text">{product.rating}/5 ({product.reviews} đánh giá)</span>
                <button
                  onClick={() => toggle({ id: product.id, name: product.name, image: product.image, price: product.price })}
                  className="btn-secondary"
                  style={{marginLeft:'auto'}}
                >
                  {isFavorite(product.id) ? '♥ Đã yêu thích' : '♡ Thêm vào yêu thích'}
                </button>
              </div>
            </div>
            
            <div className="product-price-section">
              <span className="current-price">{product.price.toLocaleString('vi-VN')}đ</span>
              <span className="original-price">399.000đ</span>
              <span className="discount-badge">-25%</span>
            </div>
            
            <div className="product-description">
              <p>{product.description}</p>
              <div className="stock-info"><span className="stock-icon">📦</span> Tồn kho: {product.stock} đôi</div>
            </div>
            
            <div className="variant-section">
              <h3 className="variant-title">Màu sắc</h3>
              <div className="color-options">
                {product.colors.map(color => (
                  <button
                    key={color.value}
                    className={`color-option ${selectedColor === color.value ? 'selected' : ''}`}
                    onClick={() => setSelectedColor(color.value)}
                    style={{ backgroundColor: color.hex }}
                    title={color.name}
                  >
                    <span className="color-name">{color.name}</span>
                  </button>
                ))}
              </div>
            </div>

            <div className="variant-section">
              <h3 className="variant-title">Kích thước</h3>
              <div className="size-options">
                {product.sizes.map(size => (
                  <button
                    key={size}
                    className={`size-option ${selectedSize === size ? 'selected' : ''}`}
                    onClick={() => setSelectedSize(size)}
                  >
                    {size}
                  </button>
                ))}
              </div>
            </div>

            <div className="quantity-section">
              <h3 className="variant-title">Số lượng</h3>
              <div className="quantity-controls">
                <button 
                  className="quantity-btn"
                  onClick={() => setQuantity(Math.max(1, quantity - 1))}
                  disabled={quantity <= 1}
                >
                  −
                </button>
                <span className="quantity-display">{quantity}</span>
                <button 
                  className="quantity-btn"
                  onClick={() => setQuantity(Math.min(product.stock, quantity + 1))}
                  disabled={quantity >= product.stock}
                >
                  +
                </button>
              </div>
              <p className="stock-info">
                <span className="stock-icon">📦</span>
                Còn {product.stock} sản phẩm
              </p>
            </div>

            <div className="action-buttons">
              <button 
                className={`add-to-cart-btn ${isAdding ? 'loading' : ''}`}
                onClick={handleAddToCart}
                disabled={isAdding}
              >
                {isAdding ? (
                  <>
                    <span className="loading-spinner"></span>
                    Đang thêm...
                  </>
                ) : (
                  <>
                    🛒 Thêm vào giỏ hàng
                  </>
                )}
              </button>
              
              <button className="buy-now-btn">
                💳 Mua ngay
              </button>
            </div>

            <div className="product-features">
              <div className="feature-item">
                <span className="feature-icon">🚚</span>
                <span>Miễn phí vận chuyển</span>
              </div>
              <div className="feature-item">
                <span className="feature-icon">🔄</span>
                <span>Đổi trả trong 30 ngày</span>
              </div>
              <div className="feature-item">
                <span className="feature-icon">🛡️</span>
                <span>Bảo hành 12 tháng</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
