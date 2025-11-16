// src/admin/pages/Register.tsx
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../store/AuthContext';

interface FormErrors {
  name?: string;
  email?: string;
  password?: string;
  general?: string;
}

const AdminRegister = () => {
  const { registerAdmin } = useAuth();
  const navigate = useNavigate();

  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [errors, setErrors] = useState<FormErrors>({});
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrors({});
    setIsLoading(true);

    try {
      await registerAdmin(name, email, password);
      navigate('/admin/login');
    } catch (err: any) {
      if (err.errors) {
        setErrors({
          name: err.errors.name?.[0],
          email: err.errors.email?.[0],
          password: err.errors.password?.[0],
        });
      } else if (err.message) {
        setErrors({ general: err.message });
      } else {
        setErrors({ general: 'Đăng ký thất bại!' });
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="admin-login-container">
      <div className="admin-login-card">
        <h2 className="admin-login-title">Đăng ký Admin</h2>

        {errors.general && <div className="form-error">{errors.general}</div>}

        <form onSubmit={handleSubmit} className="admin-login-form">
          <div className="form-group">
            <input
              type="text"
              placeholder="Tên Admin"
              className="admin-login-input"
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
            />
            {errors.name && <div className="form-error">{errors.name}</div>}
          </div>

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

          <button type="submit" className="admin-login-btn" disabled={isLoading}>
            {isLoading ? 'Đang đăng ký...' : 'Đăng ký'}
          </button>
        </form>

        <p>
          Đã có tài khoản? <a href="/admin/login">Đăng nhập</a>
        </p>
      </div>
    </div>
  );
};

export default AdminRegister;
