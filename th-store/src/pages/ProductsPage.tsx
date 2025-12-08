import { Link } from 'react-router-dom'
import { useMemo, useState, useEffect } from 'react'
import api from '../api/api'

export default function ProductsPage() {
  const [products, setProducts] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        const res = await api.get("/products")

        const items =
          res.data?.data?.data ||   // paginate
          res.data?.data ||         // array trong object
          res.data ||               // array trực tiếp
          []

        setProducts(items)
      } catch (err) {
        console.error("Lỗi khi lấy sản phẩm:", err)
      } finally {
        setLoading(false)
      }
    }

    fetchProducts()
  }, [])

  const categories = useMemo(() => {
    const unique = new Set(products.map((p: any) => p.category?.name || "Khác"))
    return Array.from(unique)
  }, [products])

  const [q, setQ] = useState('')
  const [minPrice, setMinPrice] = useState('')
  const [maxPrice, setMaxPrice] = useState('')
  const [category, setCategory] = useState('')

  const filtered = useMemo(() => {
    const min = minPrice ? parseInt(minPrice) : 0
    const max = maxPrice ? parseInt(maxPrice) : Number.MAX_SAFE_INTEGER

    return products.filter((p: any) =>
      p.name.toLowerCase().includes(q.toLowerCase()) &&
      p.price >= min &&
      p.price <= max &&
      (category ? p.category?.name === category : true)
    )
  }, [products, q, minPrice, maxPrice, category])

  if (loading) return <p>Đang tải sản phẩm...</p>

  const getImage = (product: any) => {
    if (product.thumbnail)
      return `http://127.0.0.1:8000/storage/${product.thumbnail}`

    return "/placeholder.png"
  }

  return (
    <div className="main">
      <h1>Sản phẩm</h1>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '16px', margin: '16px 0' }}>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '12px' }}>
          <input placeholder="Tìm theo tên" value={q} onChange={e => setQ(e.target.value)} />
          <input placeholder="Giá tối thiểu" value={minPrice} onChange={e => setMinPrice(e.target.value.replace(/[^0-9]/g, ''))} />
          <input placeholder="Giá tối đa" value={maxPrice} onChange={e => setMaxPrice(e.target.value.replace(/[^0-9]/g, ''))} />
          <select value={category} onChange={e => setCategory(e.target.value)}>
            <option value="">Danh mục</option>
            {categories.map(c => (
              <option key={c} value={c}>{c}</option>
            ))}
          </select>
        </div>
      </div>

      <div className="grid">
        {filtered.map((product: any) => (
          <div key={product.id} className="card">
            <img
              src={getImage(product)}
              alt={product.name}
            />
            <h3>{product.name}</h3>
            <p className="price">{product.price.toLocaleString('vi-VN')}đ</p>
            <p className="category">{product.category?.name || "Không có danh mục"}</p>
            <Link to={`/san-pham/${product.id}`}>Xem chi tiết</Link>
          </div>
        ))}
      </div>

      {filtered.length === 0 && (
        <p style={{ textAlign: 'center', marginTop: '20px' }}>Không tìm thấy sản phẩm phù hợp.</p>
      )}
    </div>
  )
}
