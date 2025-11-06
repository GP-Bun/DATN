import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../store/AuthContext'
import { useState } from 'react'

export default function LoginPage() {
  const { login } = useAuth()
  const navigate = useNavigate()
  const [showPassword, setShowPassword] = useState(false)
  const [rememberMe, setRememberMe] = useState(false)
  const [isLoading, setIsLoading] = useState(false)

  const onSubmit: React.FormEventHandler<HTMLFormElement> = async (e) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      const form = new FormData(e.currentTarget)
      await login(String(form.get('email')||''), String(form.get('password')||''))
      navigate('/')
    } catch (error) {
      console.error('Login error:', error)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="auth-container">
      <div className="auth-card">
        {/* Left side - Image */}
        <div className="auth-image">
          <div className="auth-image-content">
            <div className="auth-image-overlay">
              <h2>Chào mừng trở lại!</h2>
              <p>Khám phá thế giới mua sắm tuyệt vời</p>
            </div>
          </div>
        </div>

        {/* Right side - Form */}
        <div className="auth-form-container">
          <div className="auth-form-header">
            <div className="auth-logo">
              <div className="logo-icon">🛍️</div>
              <span>TH Store</span>
            </div>
            <h1>Rất vui được gặp lại bạn!</h1>
          </div>

          <form className="auth-form" onSubmit={onSubmit}>
            <div className="form-group">
              <label htmlFor="email">Đăng nhập</label>
              <input 
                id="email"
                name="email" 
                type="email" 
                placeholder="Email hoặc số điện thoại" 
                required 
                className="form-input"
              />
            </div>

            <div className="form-group">
              <label htmlFor="password">Mật khẩu</label>
              <div className="password-input">
                <input 
                  id="password"
                  name="password" 
                  type={showPassword ? "text" : "password"} 
                  placeholder="Nhập mật khẩu" 
                  required 
                  className="form-input"
                />
                <button 
                  type="button" 
                  className="password-toggle"
                  onClick={() => setShowPassword(!showPassword)}
                >
                  {showPassword ? '👁️' : '👁️‍🗨️'}
                </button>
              </div>
            </div>

            <div className="form-options">
              <label className="checkbox-container">
                <input 
                  type="checkbox" 
                  checked={rememberMe}
                  onChange={(e) => setRememberMe(e.target.checked)}
                />
                <span className="checkmark"></span>
                Ghi nhớ đăng nhập
              </label>
              <Link to="/quen-mat-khau" className="forgot-password">
                Quên mật khẩu?
              </Link>
            </div>

            <button 
              type="submit" 
              className="auth-button primary"
              disabled={isLoading}
            >
              {isLoading ? 'Đang đăng nhập...' : 'Đăng nhập'}
            </button>

            <div className="divider">
              <span>Hoặc</span>
            </div>

            <button type="button" className="auth-button google">
              <span className="google-icon">G</span>
              Đăng nhập với Google
            </button>

            <div className="auth-switch">
              <span>Chưa có tài khoản? </span>
              <Link to="/dang-ky" className="auth-link">
                Đăng ký ngay
              </Link>
            </div>
          </form>

          <div className="auth-footer">
            <div className="footer-left">
              <span className="footer-icon">🛍️</span>
              <span>@thstore</span>
            </div>
            <div className="footer-right">
              © TH Store 2024
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
