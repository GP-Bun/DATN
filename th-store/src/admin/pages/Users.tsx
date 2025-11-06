import { useEffect, useMemo, useState } from 'react'
import { api } from '../../store/api'

interface UserDTO {
  id: number
  name: string
  email: string
  status: 'active' | 'blocked'
  createdAt?: string
}

interface UpsertUserInput {
  name: string
  email: string
  password?: string
  status: 'active' | 'blocked'
}

const emptyForm: UpsertUserInput = { name: '', email: '', password: '', status: 'active' }

const Users = () => {
  const [users, setUsers] = useState<UserDTO[]>([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const [isModalOpen, setIsModalOpen] = useState(false)
  const [editing, setEditing] = useState<UserDTO | null>(null)
  const [form, setForm] = useState<UpsertUserInput>(emptyForm)
  const [submitting, setSubmitting] = useState(false)

  const loadUsers = async () => {
    setLoading(true)
    setError(null)
    try {
      const data = await api.get<UserDTO[]>('/admin/users')
      setUsers(data)
    } catch (e: any) {
      setError(e.message || 'Không thể tải danh sách người dùng')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { loadUsers() }, [])

  const openCreate = () => {
    setEditing(null)
    setForm(emptyForm)
    setIsModalOpen(true)
  }

  const openEdit = (u: UserDTO) => {
    setEditing(u)
    setForm({ name: u.name, email: u.email, status: u.status })
    setIsModalOpen(true)
  }

  const closeModal = () => {
    if (submitting) return
    setIsModalOpen(false)
    setEditing(null)
    setForm(emptyForm)
  }

  const submitForm = async () => {
    setSubmitting(true)
    setError(null)
    try {
      if (editing) {
        const body: UpsertUserInput = { name: form.name, email: form.email, status: form.status }
        if (form.password) body.password = form.password
        await api.put(`/admin/users/${editing.id}`, body)
      } else {
        await api.post('/admin/users', form)
      }
      await loadUsers()
      setIsModalOpen(false)
      setEditing(null)
      setForm(emptyForm)
    } catch (e: any) {
      setError(e.message || 'Không thể lưu người dùng')
    } finally {
      setSubmitting(false)
    }
  }

  const deleteUser = async (id: number) => {
    if (!confirm('Bạn có chắc muốn xóa người dùng này?')) return
    setError(null)
    try {
      await api.delete(`/admin/users/${id}`)
      setUsers(prev => prev.filter(u => u.id !== id))
    } catch (e: any) {
      setError(e.message || 'Không thể xóa người dùng')
    }
  }

  const toggleBlock = async (u: UserDTO) => {
    setError(null)
    try {
      const newStatus = u.status === 'active' ? 'blocked' : 'active'
      await api.patch(`/admin/users/${u.id}/status`, { status: newStatus })
      setUsers(prev => prev.map(x => x.id === u.id ? { ...x, status: newStatus } : x))
    } catch (e: any) {
      setError(e.message || 'Không thể cập nhật trạng thái')
    }
  }

  const statusLabel = useMemo(() => ({
    active: 'Hoạt động',
    blocked: 'Bị khóa',
  }), [])

  return (
    <div>
      <div className="admin-page-header">
        <h1 className="admin-page-title">Quản lý người dùng</h1>
        <p className="admin-page-subtitle">Quản lý tài khoản và thông tin người dùng</p>
      </div>

      <div className="admin-card">
        <div className="admin-card-header">
          <h3 className="admin-card-title">Danh sách người dùng</h3>
          <button className="admin-btn" onClick={openCreate}>+ Thêm người dùng</button>
        </div>
        <div className="admin-card-content">
          {error && <div className="admin-alert-error" style={{marginBottom: 12}}>{error}</div>}
          {loading ? (
            <div>Đang tải...</div>
          ) : (
            <table className="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Tên</th>
                  <th>Email</th>
                  <th>Trạng thái</th>
                  <th>Ngày tạo</th>
                  <th>Hành động</th>
                </tr>
              </thead>
              <tbody>
                {users.length === 0 ? (
                  <tr>
                    <td colSpan={6} style={{ textAlign: 'center' }}>Chưa có người dùng</td>
                  </tr>
                ) : users.map(u => (
                  <tr key={u.id}>
                    <td>{u.id}</td>
                    <td>{u.name}</td>
                    <td>{u.email}</td>
                    <td>
                      <span style={{ color: u.status === 'active' ? '#10b981' : '#ef4444' }}>
                        {statusLabel[u.status]}
                      </span>
                    </td>
                    <td>{u.createdAt || '-'}</td>
                    <td>
                      <button className="admin-btn" style={{marginRight: '8px', padding: '6px 12px', fontSize: '0.8rem'}} onClick={() => openEdit(u)}>Sửa</button>
                      <button className="admin-btn-secondary" style={{marginRight: '8px', padding: '6px 12px', fontSize: '0.8rem'}} onClick={() => toggleBlock(u)}>
                        {u.status === 'active' ? 'Khóa' : 'Mở khóa'}
                      </button>
                      <button className="admin-btn-secondary" style={{padding: '6px 12px', fontSize: '0.8rem'}} onClick={() => deleteUser(u.id)}>Xóa</button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </div>

      {isModalOpen && (
        <div className="admin-modal-backdrop">
          <div className="admin-modal">
            <div className="admin-modal-header">
              <h3 className="admin-modal-title">{editing ? 'Sửa người dùng' : 'Thêm người dùng'}</h3>
            </div>
            <div className="admin-modal-body">
              <div className="admin-form-group">
                <label>Tên</label>
                <input
                  type="text"
                  value={form.name}
                  onChange={e => setForm(f => ({ ...f, name: e.target.value }))}
                  placeholder="Nhập tên"
                />
              </div>
              <div className="admin-form-group">
                <label>Email</label>
                <input
                  type="email"
                  value={form.email}
                  onChange={e => setForm(f => ({ ...f, email: e.target.value }))}
                  placeholder="Nhập email"
                />
              </div>
              {!editing && (
                <div className="admin-form-group">
                  <label>Mật khẩu</label>
                  <input
                    type="password"
                    value={form.password || ''}
                    onChange={e => setForm(f => ({ ...f, password: e.target.value }))}
                    placeholder="Nhập mật khẩu"
                  />
                </div>
              )}
              <div className="admin-form-group">
                <label>Trạng thái</label>
                <select
                  value={form.status}
                  onChange={e => setForm(f => ({ ...f, status: e.target.value as 'active' | 'blocked' }))}
                >
                  <option value="active">Hoạt động</option>
                  <option value="blocked">Bị khóa</option>
                </select>
              </div>
            </div>
            <div className="admin-modal-footer">
              <button className="admin-btn-secondary" onClick={closeModal} disabled={submitting}>Hủy</button>
              <button className="admin-btn" onClick={submitForm} disabled={submitting}>
                {submitting ? 'Đang lưu...' : 'Lưu'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Users
  