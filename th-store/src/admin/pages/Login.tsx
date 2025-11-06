import React, { useState } from 'react'
import { useNavigate } from 'react-router-dom'

const Login = () => {
  const [username, setUsername] = useState('')
  const [password, setPassword] = useState('')
  const navigate = useNavigate()

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    // Simple admin login (in real app, this would be more secure)
    if (username === 'admin' && password === 'admin') {
      navigate('/admin')
    } else {
      alert('Sai tên đăng nhập hoặc mật khẩu!')
    }
  }

  return (
    <div className="admin-login-container">
      <div className="admin-login-card">
        <h2 className="admin-login-title">Đăng nhập Admin</h2>
        <form onSubmit={handleSubmit} className="admin-login-form">
          <input
            type="text"
            placeholder="Tên đăng nhập"
            className="admin-login-input"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            required
          />
          <input
            type="password"
            placeholder="Mật khẩu"
            className="admin-login-input"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
          <button 
            type="submit"
            className="admin-login-btn"
          >
            Đăng nhập
          </button>
        </form>
        <p className="admin-login-demo">
          Demo: admin / admin
        </p>
      </div>
    </div>
  )
}

export default Login
  