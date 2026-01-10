import { Link, NavLink, Outlet } from 'react-router-dom'
import { useAuth } from '../store/AuthContext'
import { useCart } from '../store/CartContext'
import ChatBot from './ChatBot'

export default function AppLayout() {
  const { user, logoutUser } = useAuth()
  const { getTotalItems } = useCart()

  return (
    <div className="app-container">
      <header className="header">
        <div className="brand">
          <Link to="/">
            <span style={{ fontSize: '28px', fontWeight: '800' }}>👟</span>
            <span style={{ marginLeft: '8px' }}>TH Store</span>
          </Link>
        </div>
        <nav className="nav">
          <NavLink to="/" className="nav-link">
            <span className="nav-icon">🏠</span>
            <span>Trang chủ</span>
          </NavLink>
          <NavLink to="/san-pham" className="nav-link">
            <span className="nav-icon">🛍️</span>
            <span>Sản phẩm</span>
          </NavLink>
          <NavLink to="/gioi-thieu" className="nav-link">
            <span className="nav-icon">ℹ️</span>
            <span>Giới thiệu</span>
          </NavLink>
          <NavLink to="/gio-hang" className="nav-link nav-link-cart">
            <span className="nav-icon">🛒</span>
            <span>Giỏ hàng</span>
            {getTotalItems() > 0 && (
              <span className="cart-badge">{getTotalItems()}</span>
            )}
          </NavLink>
          <NavLink to="/thanh-toan" className="nav-link">
            <span className="nav-icon">💳</span>
            <span>Thanh toán</span>
          </NavLink>
          {user ? (
            <div className="nav-dropdown">
              <NavLink to="/tai-khoan" className="nav-link">
                <span className="nav-icon">👤</span>
                <span>Tài khoản ({user.name})</span>
              </NavLink>
              <div className="dropdown-menu">
                <Link to="/tai-khoan" className="dropdown-item">
                  <span className="dropdown-icon">👤</span>
                  Thông tin tài khoản
                </Link>
                <Link to="/don-hang" className="dropdown-item">
                  <span className="dropdown-icon">📦</span>
                  Đơn hàng của tôi
                </Link>
                <button onClick={logoutUser} className="dropdown-item logout-btn">
                  <span className="dropdown-icon">🚪</span>
                  Đăng xuất
                </button>
              </div>
            </div>
          ) : (

            <>
              <NavLink to="/dang-nhap" className="nav-link">
                <span className="nav-icon">🔐</span>
                <span>Đăng nhập</span>
              </NavLink>
              <NavLink to="/dang-ky" className="nav-link nav-link-primary">
                <span className="nav-icon">✏️</span>
                <span>Đăng ký</span>
              </NavLink>
            </>
          )}
        </nav>
      </header>
      <main className="main">
        <Outlet />
      </main>
      <footer className="footer" style={{ textAlign: 'left', padding: '60px 0' }}>
        <div style={{ maxWidth: '1200px', margin: '0 auto', display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '40px', padding: '0 24px' }}>
          <div>
            <h3 style={{ fontSize: '1.5rem', fontWeight: '800', marginBottom: '20px', background: 'var(--gradient)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }}>TH Store</h3>
            <p style={{ color: '#6b7280', lineHeight: '1.6' }}>Chuyên cung cấp các dòng sneaker chính hãng, chất lượng cao và cập nhật xu hướng mới nhất thế giới.</p>
          </div>
          <div>
            <h4 style={{ fontSize: '1.1rem', fontWeight: '700', marginBottom: '20px' }}>Liên kết nhanh</h4>
            <ul style={{ listStyle: 'none', padding: 0, display: 'flex', flexDirection: 'column', gap: '12px' }}>
              <li><Link to="/" style={{ color: '#6b7280', textDecoration: 'none' }}>Trang chủ</Link></li>
              <li><Link to="/san-pham" style={{ color: '#6b7280', textDecoration: 'none' }}>Sản phẩm</Link></li>
              <li><Link to="/gioi-thieu" style={{ color: '#6b7280', textDecoration: 'none' }}>Giới thiệu</Link></li>
              <li><Link to="/tai-khoan" style={{ color: '#6b7280', textDecoration: 'none' }}>Tài khoản</Link></li>
            </ul>
          </div>
          <div>
            <h4 style={{ fontSize: '1.1rem', fontWeight: '700', marginBottom: '20px' }}>Chính sách</h4>
            <ul style={{ listStyle: 'none', padding: 0, display: 'flex', flexDirection: 'column', gap: '12px' }}>
              <li><a href="#" style={{ color: '#6b7280', textDecoration: 'none' }}>Chính sách đổi trả</a></li>
              <li><a href="#" style={{ color: '#6b7280', textDecoration: 'none' }}>Chính sách bảo mật</a></li>
              <li><a href="#" style={{ color: '#6b7280', textDecoration: 'none' }}>Điều khoản dịch vụ</a></li>
              <li><a href="#" style={{ color: '#6b7280', textDecoration: 'none' }}>Hướng dẫn chọn size</a></li>
            </ul>
          </div>
          <div>
            <h4 style={{ fontSize: '1.1rem', fontWeight: '700', marginBottom: '20px' }}>Liên hệ</h4>
            <ul style={{ listStyle: 'none', padding: 0, display: 'flex', flexDirection: 'column', gap: '12px' }}>
              <li style={{ color: '#6b7280' }}>📍 240 Phú Mỹ, Cầu Giấy, HN</li>
              <li style={{ color: '#6b7280' }}>📞 0964232666</li>
              <li style={{ color: '#6b7280' }}>✉️ contact@thstore.vn</li>
            </ul>
          </div>
        </div>
        <div style={{ borderTop: '1px solid var(--border)', marginTop: '40px', paddingTop: '20px', textAlign: 'center' }}>
          <p style={{ color: '#9ca3af', fontSize: '14px' }}>© {new Date().getFullYear()} TH Sneaker Store. All rights reserved.</p>
        </div>
      </footer>
      <ChatBot />
    </div>
  )
}


