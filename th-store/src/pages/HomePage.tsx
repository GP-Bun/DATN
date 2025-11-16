import { Link } from 'react-router-dom'

export default function HomePage() {
  return (
    <div className="home-page">
      {/* Hero Section */}
      <section className="hero-section">
        <div className="hero-content">
          <div className="hero-text">
            <h1 className="hero-title">
              Chào mừng đến với <span className="gradient-text">TH Store</span>
            </h1>
            <p className="hero-subtitle">
              Khám phá thế giới mua sắm tuyệt vời với những sản phẩm chất lượng cao
            </p>
            <p className="hero-description">
              Tìm kiếm những sản phẩm chất lượng cao với giá cả hợp lý. 
              Trải nghiệm mua sắm trực tuyến tốt nhất tại TH Store.
            </p>
            <div className="hero-actions">
              <Link to="/san-pham" className="btn-primary">
                🛍️ Khám phá ngay
              </Link>
            </div>
          </div>
          <div className="hero-image">
            <div className="hero-image-container">
              <img 
                src="https://bizweb.dktcdn.net/100/347/092/files/giay-sneaker-la-gi-1.jpg?v=1599104032003" 
                alt="Shopping Experience" 
                className="hero-img"
              />
              <div className="floating-card card-1">
                <span className="card-icon">📦</span>
                <span>Miễn phí vận chuyển</span>
              </div>
              <div className="floating-card card-2">
                <span className="card-icon">⭐</span>
                <span>Đánh giá 5 sao</span>
              </div>
              <div className="floating-card card-3">
                <span className="card-icon">🔒</span>
                <span>Thanh toán an toàn</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Featured Products */}
      <section className="featured-products">
        <div className="section-header">
          <h2 className="section-title">Sản phẩm nổi bật</h2>
          <p className="section-subtitle">Những sản phẩm được yêu thích nhất</p>
        </div>
        <div className="products-grid">
          <div className="product-card">
            <div className="product-image">
              <img src="https://bizweb.dktcdn.net/100/479/837/files/giay-sneaker-catsofa-loang-mau-hong-6.jpg?v=1683452738991" alt="Áo thun nam" />
              <div className="product-overlay">
                <Link to="/san-pham/1" className="quick-view-btn">👁️ Xem nhanh</Link>
              </div>
            </div>
            <div className="product-info">
              <h3>Giày</h3>
              <p className="product-price">299.000đ</p>
              <div className="product-rating">
                <span>⭐⭐⭐⭐⭐</span>
                <span>(128 đánh giá)</span>
              </div>
              <Link to="/san-pham/1" className="product-link">Xem chi tiết</Link>
            </div>
          </div>
          
          <div className="product-card">
            <div className="product-image">
              <img src="https://img.lovepik.com/element/40144/8398.png_1200.png" alt="Quần jean nữ" />
              <div className="product-overlay">
                <Link to="/san-pham/2" className="quick-view-btn">👁️ Xem nhanh</Link>
              </div>
            </div>
            <div className="product-info">
              <h3>Quần jean nữ</h3>
              <p className="product-price">599.000đ</p>
              <div className="product-rating">
                <span>⭐⭐⭐⭐⭐</span>
                <span>(95 đánh giá)</span>
              </div>
              <Link to="/san-pham/2" className="product-link">Xem chi tiết</Link>
            </div>
          </div>
          
          <div className="product-card">
            <div className="product-image">
              <img src="https://png.pngtree.com/png-vector/20230501/ourlarge/pngtree-a-pair-of-sneakers-png-image_7078507.png" alt="Giày thể thao" />
              <div className="product-overlay">
                <Link to="/san-pham/3" className="quick-view-btn">👁️ Xem nhanh</Link>
              </div>
            </div>
            <div className="product-info">
              <h3>Giày thể thao</h3>
              <p className="product-price">1.299.000đ</p>
              <div className="product-rating">
                <span>⭐⭐⭐⭐⭐</span>
                <span>(203 đánh giá)</span>
              </div>
              <Link to="/san-pham/3" className="product-link">Xem chi tiết</Link>
            </div>
          </div>
        </div>
        <div className="view-all-section">
          <Link to="/san-pham" className="btn-outline">Xem tất cả sản phẩm →</Link>
        </div>
      </section>

      {/* Features Section */}
      <section className="features-section">
        <div className="section-header">
          <h2 className="section-title">Tại sao chọn TH Store?</h2>
          <p className="section-subtitle">Những lý do khiến khách hàng tin tưởng</p>
        </div>
        <div className="features-grid">
          <div className="feature-card">
            <div className="feature-icon">🚚</div>
            <h3>Giao hàng nhanh</h3>
            <p>Giao hàng trong 24h với dịch vụ chuyên nghiệp và đáng tin cậy</p>
          </div>
          <div className="feature-card">
            <div className="feature-icon">🛡️</div>
            <h3>Bảo hành chính hãng</h3>
            <p>Cam kết chất lượng sản phẩm 100% chính hãng với chế độ bảo hành tốt</p>
          </div>
          <div className="feature-card">
            <div className="feature-icon">💳</div>
            <h3>Thanh toán an toàn</h3>
            <p>Hỗ trợ nhiều phương thức thanh toán bảo mật và tiện lợi</p>
          </div>
          <div className="feature-card">
            <div className="feature-icon">🎁</div>
            <h3>Ưu đãi hấp dẫn</h3>
            <p>Nhiều chương trình khuyến mãi và ưu đãi đặc biệt cho khách hàng</p>
          </div>
        </div>
      </section>
    </div>
  )
}