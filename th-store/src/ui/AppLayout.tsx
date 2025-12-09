import { Link, NavLink, Outlet } from 'react-router-dom'
import { useAuth } from '../store/AuthContext'
import { useCart } from '../store/CartContext'

export default function AppLayout() {
  const { user, logoutUser } = useAuth()
  const { getTotalItems } = useCart()
  
  const handleLogout = async () => {
    if (window.confirm('Bạn có chắc chắn muốn đăng xuất không?')) {
      await logoutUser()
      alert('Đăng xuất thành công!')
    }
  }

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
            <button
              onClick={handleLogout}
              className="nav-link nav-link-button"
            >
              <span className="nav-icon">👤</span>
              <span>Đăng xuất ({user.name})</span>
            </button>
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


