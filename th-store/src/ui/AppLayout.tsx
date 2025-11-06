import { Link, NavLink, Outlet } from 'react-router-dom'
import { useAuth } from '../store/AuthContext'

export default function AppLayout() {
  const { user, logout } = useAuth()
  return (
    <div className="app-container">
      <header className="header">
        <div className="brand">
          <Link to="/">TH</Link>
        </div>
        <nav className="nav">
          <NavLink to="/">Trang chủ</NavLink>
          <NavLink to="/san-pham">Sản phẩm</NavLink>
          <NavLink to="/gio-hang">Giỏ hàng</NavLink>
          <NavLink to="/thanh-toan">Thanh toán</NavLink>
          {user ? (
            <button onClick={logout}>Đăng xuất ({user.name})</button>
          ) : (
            <>
              <NavLink to="/dang-nhap">Đăng nhập</NavLink>
              <NavLink to="/dang-ky">Đăng ký</NavLink>
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


