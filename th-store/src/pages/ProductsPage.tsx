import { Link } from 'react-router-dom'
import { useMemo, useState } from 'react'

export default function ProductsPage() {
  const products = [
    { id: 1, name: 'Giày Sneaker TH Runner', price: 1299000, image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=360&h=240&fit=crop' },
    { id: 2, name: 'Giày Thể Thao TH Sport', price: 1599000, image: 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=360&h=240&fit=crop' },
    { id: 3, name: 'Giày Chạy Bộ TH Run', price: 1399000, image: 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=360&h=240&fit=crop' },
    { id: 4, name: 'Giày Sneaker Cổ Cao TH High', price: 1499000, image: 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=360&h=240&fit=crop' },
    { id: 5, name: 'Giày Bóng Đá TH Football', price: 1999000, image: 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=360&h=240&fit=crop' },
    { id: 6, name: 'Giày Lifestyle TH Classic', price: 1199000, image: 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=360&h=240&fit=crop' },
    { id: 7, name: 'Giày Training TH Flex', price: 1099000, image: 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=360&h=240&fit=crop' },
    { id: 8, name: 'Giày Tennis TH Court', price: 1699000, image: 'https://images.unsplash.com/photo-1547331540-9fae6901f22d?w=360&h=240&fit=crop' },
    { id: 9, name: 'Giày Hiking TH Trail', price: 1899000, image: 'https://images.unsplash.com/photo-1520256862855-398228c41684?w=360&h=240&fit=crop' },
    { id: 10, name: 'Giày Slip-on TH Ease', price: 999000, image: 'https://images.unsplash.com/photo-1543508282-6319a3e2621f?w=360&h=240&fit=crop' },
    { id: 11, name: 'Giày Canvas TH Retro', price: 899000, image: 'https://images.unsplash.com/photo-1526318472351-c75fcf070305?w=360&h=240&fit=crop' },
    { id: 12, name: 'Giày Runner TH Aero', price: 1499000, image: 'https://images.unsplash.com/photo-1542291029-0c7f0b3f4f8f?w=360&h=240&fit=crop' },
    { id: 13, name: 'Giày Sneaker TH Neon', price: 1599000, image: 'https://images.unsplash.com/photo-1542291025-0b75012a023a?w=360&h=240&fit=crop' },
    { id: 14, name: 'Giày Retro TH 90s', price: 1299000, image: 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=360&h=240&fit=crop' },
    { id: 15, name: 'Giày Basket TH Dunk', price: 2099000, image: 'https://images.unsplash.com/photo-1535043934128-cf0b28d52f95?w=360&h=240&fit=crop' },
    { id: 16, name: 'Giày Golf TH Drive', price: 1799000, image: 'https://images.unsplash.com/photo-1556906781-9a412961c28d?w=360&h=240&fit=crop' },
    { id: 17, name: 'Giày Lười TH Comfort', price: 899000, image: 'https://images.unsplash.com/photo-1519741497674-611481863552?w=360&h=240&fit=crop' },
    { id: 18, name: 'Giày Leo Núi TH Grip', price: 1899000, image: 'https://images.unsplash.com/photo-1465453867471-5b3e0c403219?w=360&h=240&fit=crop' },
  ]

  const enriched = useMemo(() => products.map((p, idx) => ({
    ...p,
    year: 2019 + ((idx % 6)),
  })), [products])

  const [q, setQ] = useState('')
  const [minPrice, setMinPrice] = useState('')
  const [maxPrice, setMaxPrice] = useState('')
  const [year, setYear] = useState('')

  const filtered = useMemo(() => {
    const min = minPrice ? parseInt(minPrice) : 0
    const max = maxPrice ? parseInt(maxPrice) : Number.MAX_SAFE_INTEGER
    return enriched.filter(p =>
      p.name.toLowerCase().includes(q.toLowerCase()) &&
      p.price >= min && p.price <= max &&
      (year ? String(p.year) === year : true)
    )
  }, [enriched, q, minPrice, maxPrice, year])

  return (
    <div className="main">
      <h1>Sản phẩm</h1>
      <div style={{display:'grid',gridTemplateColumns:'1fr',gap:'16px',margin:'16px 0'}}>
        <div style={{display:'grid',gridTemplateColumns:'1fr 1fr 1fr 1fr',gap:'12px'}}>
          <input placeholder="Tìm theo tên" value={q} onChange={e=>setQ(e.target.value)} />
          <input placeholder="Giá tối thiểu" value={minPrice} onChange={e=>setMinPrice(e.target.value.replace(/[^0-9]/g,''))} />
          <input placeholder="Giá tối đa" value={maxPrice} onChange={e=>setMaxPrice(e.target.value.replace(/[^0-9]/g,''))} />
          <select value={year} onChange={e=>setYear(e.target.value)}>
            <option value="">Năm sản xuất</option>
            {[2019,2020,2021,2022,2023,2024].map(y=> <option key={y} value={y}>{y}</option>)}
          </select>
        </div>
      </div>
      <div className="grid">
        {filtered.map(product => (
          <div key={product.id} className="card">
            <img src={product.image} alt={product.name} />
            <h3>{product.name}</h3>
            <p className="price">{product.price.toLocaleString('vi-VN')}đ · {product.year}</p>
            <Link to={`/san-pham/${product.id}`}>Xem chi tiết</Link>
          </div>
        ))}
      </div>
    </div>
  )
}
