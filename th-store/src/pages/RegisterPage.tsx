import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../store/AuthContext'
import { useState } from 'react'

interface FormErrors {
  name?: string[]
  email?: string[]
  password?: string[]
  confirmPassword?: string[]
  general?: string
}

export default function RegisterPage() {
  const { registerUser } = useAuth()
  const navigate = useNavigate()
  const [showPassword, setShowPassword] = useState(false)
  const [showConfirmPassword, setShowConfirmPassword] = useState(false)
  const [agreeTerms, setAgreeTerms] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [errors, setErrors] = useState<FormErrors>({})

  const onSubmit: React.FormEventHandler<HTMLFormElement> = async (e) => {
    e.preventDefault()
    setIsLoading(true)
    setErrors({})

    const form = new FormData(e.currentTarget)
    const name = String(form.get("name") || "")
    const email = String(form.get("email") || "")
    const password = String(form.get("password") || "")
    const confirmPassword = String(form.get("confirmPassword") || "")

    // Kiểm tra confirm password
    if (password !== confirmPassword) {
      setErrors({ confirmPassword: ["Mật khẩu xác nhận không khớp!"] })
      setIsLoading(false)
      return
    }

    try {
      // Gọi API register
      await registerUser(name, email, password)
      alert("Đăng ký thành công! Vui lòng đăng nhập.")
      navigate("/dang-nhap")
    } catch (err: any) {
      // Xử lý lỗi validation từ Laravel
      if (err.errors) {
        setErrors({
          name: err.errors.name,
          email: err.errors.email,
          password: err.errors.password,
        })
      } else if (err.message) {
        setErrors({ general: err.message })
      } else {
        setErrors({ general: "Đăng ký thất bại!" })
      }
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
              <h2>Tham gia cùng chúng tôi!</h2>
              <p>Khám phá thế giới mua sắm tuyệt vời</p>
            </div>
          </div>
        </div>

        <div className="auth-form-container">
          <div className="auth-form-header">
            <div className="auth-logo"><div className="logo-icon">🛍️</div><span>TH Store</span></div>
            <h1>Chào mừng bạn đến với TH Store!</h1>
          </div>

          <form className="auth-form" onSubmit={onSubmit} noValidate>
            {errors.general && <div className="form-error">{errors.general}</div>}

            <div className="form-group">
              <label htmlFor="name">Họ và tên</label>
              <input id="name" name="name" type="text" placeholder="Nhập họ và tên của bạn" required className="form-input"/>
              {errors.name && Array.isArray(errors.name) && <div className="form-error">{errors.name[0]}</div>}
            </div>

            <div className="form-group">
              <label htmlFor="email">Email</label>
              <input id="email" name="email" type="email" placeholder="Nhập email của bạn" required className="form-input"/>
              {errors.email && Array.isArray(errors.email) && <div className="form-error">{errors.email[0]}</div>}
            </div>

            <div className="form-group">
              <label htmlFor="password">Mật khẩu</label>
              <div className="password-input">
                <input id="password" name="password" type={showPassword ? 'text':'password'} placeholder="Tạo mật khẩu mạnh" required className="form-input"/>
                <button type="button" className="password-toggle" onClick={() => setShowPassword(!showPassword)}>
                  {showPassword ? '👁️' : '👁️‍🗨️'}
                </button>
              </div>
              {errors.password && Array.isArray(errors.password) && <div className="form-error">{errors.password[0]}</div>}
            </div>

            <div className="form-group">
              <label htmlFor="confirmPassword">Xác nhận mật khẩu</label>
              <div className="password-input">
                <input id="confirmPassword" name="confirmPassword" type={showConfirmPassword ? 'text':'password'} placeholder="Nhập lại mật khẩu" required className="form-input"/>
                <button type="button" className="password-toggle" onClick={() => setShowConfirmPassword(!showConfirmPassword)}>
                  {showConfirmPassword ? '👁️' : '👁️‍🗨️'}
                </button>
              </div>
              {errors.confirmPassword && Array.isArray(errors.confirmPassword) && <div className="form-error">{errors.confirmPassword[0]}</div>}
            </div>

            <div className="form-options">
              <label className="checkbox-container">
                <input type="checkbox" checked={agreeTerms} onChange={e => setAgreeTerms(e.target.checked)} required/>
                <span className="checkmark"></span>
                Tôi đồng ý với <Link to="/terms" className="terms-link">Điều khoản sử dụng</Link>
              </label>
            </div>

            <button type="submit" className="auth-button primary" disabled={isLoading || !agreeTerms}>
              {isLoading ? 'Đang tạo tài khoản...' : 'Tạo tài khoản'}
            </button>

            <div className="divider"><span>Hoặc</span></div>
            <button type="button" className="auth-button google"><span className="google-icon">G</span>Đăng ký với Google</button>

            <div className="auth-switch">
              <span>Đã có tài khoản? </span><Link to="/dang-nhap" className="auth-link">Đăng nhập ngay</Link>
            </div>
          </form>
        </div>
      </div>
    </div>
  )
}
