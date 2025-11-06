import { useNavigate } from 'react-router-dom'

const Navbar = () => {
  const navigate = useNavigate()

  const handleLogout = () => {
    navigate('/admin/login')
  }

  return (
    <div className="admin-navbar">
      <h1>TH Store Admin</h1>
      <div className="admin-navbar-user">
        <span>Xin chào, Admin! 👋</span>
        <button 
          onClick={handleLogout}
          className="admin-logout-btn"
        >
          Đăng xuất
        </button>
      </div>
    </div>
  )
}

export default Navbar
  