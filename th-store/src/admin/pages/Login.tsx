import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../store/AuthContext';

interface FormErrors {
  email?: string;
  password?: string;
  general?: string;
}

const AdminLogin = () => {
  const { loginAdmin } = useAuth();
  const navigate = useNavigate();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(true);
  const [errors, setErrors] = useState<FormErrors>({});
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrors({});
    setIsLoading(true);

    try {
      await loginAdmin(email, password, remember); // truyền remember
      navigate('/admin/dashboard'); // Chuyển đến dashboard admin
    } catch (err: any) {
      if (err.errors) {
        setErrors({
          email: err.errors.email?.[0] ?? undefined,
          password: err.errors.password?.[0] ?? undefined,
        });
      } else if (err.message) {
        setErrors({ general: err.message });
      } else {
        setErrors({ general: 'Đăng nhập thất bại!' });
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="admin-login-container">
      <div className="admin-login-card">
        <h2 className="admin-login-title">Đăng nhập Admin</h2>

        {errors.general && <div className="form-error">{errors.general}</div>}

        <form onSubmit={handleSubmit} className="admin-login-form">
          <div className="form-group">
            <input
              type="email"
              placeholder="Email"
              className="admin-login-input"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
            {errors.email && <div className="form-error">{errors.email}</div>}
          </div>

          <div className="form-group">
            <input
              type="password"
              placeholder="Mật khẩu"
              className="admin-login-input"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
            {errors.password && <div className="form-error">{errors.password}</div>}
          </div>

          <div className="form-group">
            {/* <label>
              <input 
                type="checkbox" 
                checked={remember} 
                onChange={(e) => setRemember(e.target.checked)}
              />
              Nhớ đăng nhập
            </label> */}
          </div>


          <button type="submit" className="admin-login-btn" disabled={isLoading}>
            {isLoading ? 'Đang đăng nhập...' : 'Đăng nhập'}
          </button>
        </form>
      </div>
    </div>
  );
};

export default AdminLogin;
