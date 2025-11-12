// src/admin/components/Navbar.tsx
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../store/AuthContext';

const Navbar = () => {
  const navigate = useNavigate();
  const { logoutAdmin } = useAuth();

  const handleLogout = () => {
    logoutAdmin();
    navigate('/admin/login');
  };

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
  );
};

export default Navbar;
