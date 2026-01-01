import { Link, NavLink, Outlet } from 'react-router-dom'
import { useAuth } from '../store/AuthContext'
import { useCart } from '../store/CartContext'

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
      <footer className="footer">
        <p>© {new Date().getFullYear()} TH Sneaker Store</p>
      </footer>
    </div>
  )
}


