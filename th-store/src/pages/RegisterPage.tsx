import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../store/AuthContext'
import { useState } from 'react'

export default function RegisterPage() {
  const { register } = useAuth()
  const navigate = useNavigate()
  const [showPassword, setShowPassword] = useState(false)
  const [showConfirmPassword, setShowConfirmPassword] = useState(false)
  const [agreeTerms, setAgreeTerms] = useState(false)
  const [isLoading, setIsLoading] = useState(false)

  const onSubmit: React.FormEventHandler<HTMLFormElement> = async (e) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      const form = new FormData(e.currentTarget)
      const password = String(form.get('password')||'')
      const confirmPassword = String(form.get('confirmPassword')||'')
      
      if (password !== confirmPassword) {
        alert('Mật khẩu xác nhận không khớp!')
        return
      }
      
      await register(String(form.get('name')||''), String(form.get('email')||''), password)
      navigate('/')
    } catch (error) {
      console.error('Register error:', error)
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
              <h2>Tham gia cùng chúng tôi!</h2>
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
            <h1>Chào mừng bạn đến với TH Store!</h1>
          </div>

          <form className="auth-form" onSubmit={onSubmit}>
            <div className="form-group">
              <label htmlFor="name">Họ và tên</label>
              <input 
                id="name"
                name="name" 
                type="text" 
                placeholder="Nhập họ và tên của bạn" 
                required 
                className="form-input"
              />
            </div>

            <div className="form-group">
              <label htmlFor="email">Email</label>
              <input 
                id="email"
                name="email" 
                type="email" 
                placeholder="Nhập email của bạn" 
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
                  placeholder="Tạo mật khẩu mạnh" 
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

            <div className="form-group">
              <label htmlFor="confirmPassword">Xác nhận mật khẩu</label>
              <div className="password-input">
                <input 
                  id="confirmPassword"
                  name="confirmPassword" 
                  type={showConfirmPassword ? "text" : "password"} 
                  placeholder="Nhập lại mật khẩu" 
                  required 
                  className="form-input"
                />
                <button 
                  type="button" 
                  className="password-toggle"
                  onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                >
                  {showConfirmPassword ? '👁️' : '👁️‍🗨️'}
                </button>
              </div>
            </div>

            <div className="form-options">
              <label className="checkbox-container">
                <input 
                  type="checkbox" 
                  checked={agreeTerms}
                  onChange={(e) => setAgreeTerms(e.target.checked)}
                  required
                />
                <span className="checkmark"></span>
                Tôi đồng ý với <Link to="/terms" className="terms-link">Điều khoản sử dụng</Link>
              </label>
            </div>

            <button 
              type="submit" 
              className="auth-button primary"
              disabled={isLoading || !agreeTerms}
            >
              {isLoading ? 'Đang tạo tài khoản...' : 'Tạo tài khoản'}
            </button>

            <div className="divider">
              <span>Hoặc</span>
            </div>

            <button type="button" className="auth-button google">
              <span className="google-icon">G</span>
              Đăng ký với Google
            </button>

            <div className="auth-switch">
              <span>Đã có tài khoản? </span>
              <Link to="/dang-nhap" className="auth-link">
                Đăng nhập ngay
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
