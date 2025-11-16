import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../store/AuthContext'
import { useState } from 'react'

export default function LoginPage() {
  const { loginUser } = useAuth()
  const navigate = useNavigate()
  const [showPassword, setShowPassword] = useState(false)
  const [rememberMe, setRememberMe] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState('')

  const onSubmit: React.FormEventHandler<HTMLFormElement> = async (e) => {
    e.preventDefault()
    setIsLoading(true)
    setError('')

    const form = new FormData(e.currentTarget)
    const email = String(form.get('email') || '')
    const password = String(form.get('password') || '')

    if (!email || !password) {
      setError('Vui lòng điền đầy đủ email và mật khẩu')
      setIsLoading(false)
      return
    }

    try {
      await loginUser(email, password, rememberMe)
      navigate('/')
    } catch (err: any) {
      setError(err.response?.data?.message || 'Đăng nhập thất bại!')
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="auth-container">
      <div className="auth-card">
        <div className="auth-image">
          <div className="auth-image-content">
            <div className="auth-image-overlay">
              <h2>Chào mừng trở lại!</h2>
              <p>Khám phá thế giới mua sắm tuyệt vời</p>
            </div>
          </div>
        </div>

        <div className="auth-form-container">
          <div className="auth-form-header">
            <div className="auth-logo">
              <div className="logo-icon">🛍️</div>
              <span>TH Store</span>
            </div>
            <h1>Rất vui được gặp lại bạn!</h1>
          </div>

          <form className="auth-form" onSubmit={onSubmit}>
            {error && <div className="form-error">{error}</div>}

            <div className="form-group">
              <label htmlFor="email">Đăng nhập</label>
              <input
                id="email"
                name="email"
                type="email"
                placeholder="Email hoặc số điện thoại"
                className="form-input"
                required
              />
            </div>

            <div className="form-group">
              <label htmlFor="password">Mật khẩu</label>
              <div className="password-input">
                <input
                  id="password"
                  name="password"
                  type={showPassword ? 'text' : 'password'}
                  placeholder="Nhập mật khẩu"
                  className="form-input"
                  required
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
                <span className="checkmark"></span> Ghi nhớ đăng nhập
              </label>
              <Link to="/quen-mat-khau" className="forgot-password">
                Quên mật khẩu?
              </Link>
            </div>

            <button type="submit" className="auth-button primary" disabled={isLoading}>
              {isLoading ? 'Đang đăng nhập...' : 'Đăng nhập'}
            </button>

            <div className="divider">
              <span>Hoặc</span>
            </div>

            <button type="button" className="auth-button google">
              <span className="google-icon">G</span>Đăng nhập với Google
            </button>

            <div className="auth-switch">
              <span>Chưa có tài khoản? </span>
              <Link to="/dang-ky" className="auth-link">
                Đăng ký ngay
              </Link>
            </div>
          </form>
        </div>
      </div>
    </div>
  )
}
