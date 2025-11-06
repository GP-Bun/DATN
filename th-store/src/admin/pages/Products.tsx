import { useEffect, useMemo, useState } from 'react'
import { api } from '../../store/api'

interface ProductDTO {
  id: number
  name: string
  price: number
  status: 'active' | 'inactive'
}

interface UpsertProductInput {
  name: string
  price: number
  status: 'active' | 'inactive'
}

const emptyForm: UpsertProductInput = { name: '', price: 0, status: 'active' }

const Products = () => {
  const [products, setProducts] = useState<ProductDTO[]>([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const [isModalOpen, setIsModalOpen] = useState(false)
  const [editing, setEditing] = useState<ProductDTO | null>(null)
  const [form, setForm] = useState<UpsertProductInput>(emptyForm)
  const [submitting, setSubmitting] = useState(false)

  const loadProducts = async () => {
    setLoading(true)
    setError(null)
    try {
      const data = await api.get<ProductDTO[]>('/admin/products')
      setProducts(data)
    } catch (e: any) {
      setError(e.message || 'Không thể tải danh sách sản phẩm')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { loadProducts() }, [])

  const openCreate = () => {
    setEditing(null)
    setForm(emptyForm)
    setIsModalOpen(true)
  }

  const openEdit = (p: ProductDTO) => {
    setEditing(p)
    setForm({ name: p.name, price: p.price, status: p.status })
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
        await api.put(`/admin/products/${editing.id}`, form)
      } else {
        await api.post('/admin/products', form)
      }
      await loadProducts()
      setIsModalOpen(false)
      setEditing(null)
      setForm(emptyForm)
    } catch (e: any) {
      setError(e.message || 'Không thể lưu sản phẩm')
    } finally {
      setSubmitting(false)
    }
  }

  const deleteProduct = async (id: number) => {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return
    setError(null)
    try {
      await api.delete(`/admin/products/${id}`)
      setProducts(prev => prev.filter(p => p.id !== id))
    } catch (e: any) {
      setError(e.message || 'Không thể xóa sản phẩm')
    }
  }

  const statusLabel = useMemo(() => ({
    active: 'Còn hàng',
    inactive: 'Hết hàng',
  }), [])

  return (
    <div>
      <div className="admin-page-header">
        <h1 className="admin-page-title">Quản lý sản phẩm</h1>
        <p className="admin-page-subtitle">Quản lý danh sách sản phẩm, thêm/sửa/xóa sản phẩm</p>
      </div>
      <div className="admin-card">
        <div className="admin-card-header">
          <h3 className="admin-card-title">Danh sách sản phẩm</h3>
          <button className="admin-btn" onClick={openCreate}>+ Thêm sản phẩm mới</button>
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
                  <th>Tên sản phẩm</th>
                  <th>Giá</th>
                  <th>Trạng thái</th>
                  <th>Hành động</th>
                </tr>
              </thead>
              <tbody>
                {products.length === 0 ? (
                  <tr>
                    <td colSpan={5} style={{ textAlign: 'center' }}>Chưa có sản phẩm</td>
                  </tr>
                ) : products.map(p => (
                  <tr key={p.id}>
                    <td>{p.id}</td>
                    <td>{p.name}</td>
                    <td>{p.price.toLocaleString('vi-VN')}đ</td>
                    <td>
                      <span style={{ color: p.status === 'active' ? '#10b981' : '#ef4444' }}>
                        {statusLabel[p.status]}
                      </span>
                    </td>
                    <td>
                      <button className="admin-btn" style={{marginRight: '8px', padding: '6px 12px', fontSize: '0.8rem'}} onClick={() => openEdit(p)}>Sửa</button>
                      <button className="admin-btn-secondary" style={{padding: '6px 12px', fontSize: '0.8rem'}} onClick={() => deleteProduct(p.id)}>Xóa</button>
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
              <h3 className="admin-modal-title">{editing ? 'Sửa sản phẩm' : 'Thêm sản phẩm'}</h3>
            </div>
            <div className="admin-modal-body">
              <div className="admin-form-group">
                <label>Tên sản phẩm</label>
                <input
                  type="text"
                  value={form.name}
                  onChange={e => setForm(f => ({ ...f, name: e.target.value }))}
                  placeholder="Nhập tên sản phẩm"
                />
              </div>
              <div className="admin-form-group">
                <label>Giá</label>
                <input
                  type="number"
                  value={form.price}
                  onChange={e => setForm(f => ({ ...f, price: Number(e.target.value) }))}
                  placeholder="Nhập giá"
                  min={0}
                />
              </div>
              <div className="admin-form-group">
                <label>Trạng thái</label>
                <select
                  value={form.status}
                  onChange={e => setForm(f => ({ ...f, status: e.target.value as 'active' | 'inactive' }))}
                >
                  <option value="active">Còn hàng</option>
                  <option value="inactive">Hết hàng</option>
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

export default Products
  