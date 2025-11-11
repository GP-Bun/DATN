import { Link } from 'react-router-dom'
import { useMemo, useState } from 'react'

export default function ProductsPage() {
  const products = [
    { id: 1, name: 'Giày Sneaker TH Runner', price: 1299000, category: 'Giày Sneaker', image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=360&h=240&fit=crop' },
    { id: 2, name: 'Giày Thể Thao TH Sport', price: 1599000, category: 'Giày Thể Thao', image: 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=360&h=240&fit=crop' },
    { id: 3, name: 'Giày Chạy Bộ TH Run', price: 1399000, category: 'Giày Chạy Bộ', image: 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=360&h=240&fit=crop' },
    { id: 4, name: 'Giày Sneaker Cổ Cao TH High', price: 1499000, category: 'Giày Sneaker', image: 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=360&h=240&fit=crop' },
    { id: 5, name: 'Giày Bóng Đá TH Football', price: 1999000, category: 'Giày Bóng Đá', image: 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=360&h=240&fit=crop' },
    { id: 6, name: 'Giày Lifestyle TH Classic', price: 1199000, category: 'Giày Sneaker', image: 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=360&h=240&fit=crop' },
    { id: 7, name: 'Giày Training TH Flex', price: 1099000, category: 'Giày Thể Thao', image: 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=360&h=240&fit=crop' },
    { id: 8, name: 'Giày Tennis TH Court', price: 1699000, category: 'Giày Tennis', image: 'https://i.pinimg.com/236x/c0/e0/7e/c0e07eb909ade4403b37ebaf2cba2d12.jpg' },
    { id: 9, name: 'Giày Hiking TH Trail', price: 1899000, category: 'Giày Hiking', image: 'https://images.unsplash.com/photo-1520256862855-398228c41684?w=360&h=240&fit=crop' },
    { id: 10, name: 'Giày Slip-on TH Ease', price: 999000, category: 'Giày Slip-on', image: 'https://i.pinimg.com/236x/7b/3d/c0/7b3dc06d85ec2ac951874e5ac5e1baf1.jpg' },
    { id: 11, name: 'Giày Canvas TH Retro', price: 899000, category: 'Giày Canvas', image: 'https://salt.tikicdn.com/ts/tmp/8f/c8/b0/8ae911ac40a7add1078fdf9b9c9011b1.jpg' },
    { id: 12, name: 'Giày Runner TH Aero', price: 1499000, category: 'Giày Runner', image: 'https://img.lazcdn.com/g/p/dbfdaa25a9f4333283033c87e4c11376.png_720x720q80.png' },
    { id: 13, name: 'Giày Sneaker TH Neon', price: 1599000, category: 'Giày Sneaker', image: 'https://product.hstatic.net/200000410665/product/giay-the-thao-nam-xb20649fk-2_a2f0e9136e6849da95f6c6476d9dbdec.jpg' },
    { id: 14, name: 'Giày Retro TH 90s', price: 1299000, category: 'Giày Retro', image: 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=360&h=240&fit=crop' },
    { id: 15, name: 'Giày Basket TH Dunk', price: 2099000, category: 'Giày Basket', image: 'https://images.unsplash.com/photo-1535043934128-cf0b28d52f95?w=360&h=240&fit=crop' },
    { id: 16, name: 'Giày Golf TH Drive', price: 1799000, category: 'Giày Golf', image: 'https://tiemchupanh.com/wp-content/uploads/2021/10/9e4143fea71d95290b6c014c644d541d-2-1.jpg' },
    { id: 17, name: 'Giày Lười TH Comfort', price: 899000, category: 'Giày Lười', image: 'https://product.hstatic.net/1000205116/product/54210489_2239813419615590_1954504724371734528_n_30755315f82644d39cb22b50ce04c61c_1024x1024.jpg' },
    { id: 18, name: 'Giày Leo Núi TH Grip', price: 1899000, category: 'Giày Leo Núi', image: 'https://streetstyleshop.vn/wp-content/uploads/2024/06/IMG_5425.jpeg' },
  ]

  // Danh mục duy nhất (tự động lấy từ dữ liệu)
  const categories = useMemo(() => {
    const unique = new Set(products.map(p => p.category))
    return Array.from(unique)
  }, [products])

  const [q, setQ] = useState('')
  const [minPrice, setMinPrice] = useState('')
  const [maxPrice, setMaxPrice] = useState('')
  const [category, setCategory] = useState('')

  const filtered = useMemo(() => {
    const min = minPrice ? parseInt(minPrice) : 0
    const max = maxPrice ? parseInt(maxPrice) : Number.MAX_SAFE_INTEGER
    return products.filter(p =>
      p.name.toLowerCase().includes(q.toLowerCase()) &&
      p.price >= min &&
      p.price <= max &&
      (category ? p.category === category : true)
    )
  }, [products, q, minPrice, maxPrice, category])

  return (
    <div className="main">
      <h1>Sản phẩm</h1>

      {/* Bộ lọc tìm kiếm */}
      <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '16px', margin: '16px 0' }}>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '12px' }}>
          <input
            placeholder="Tìm theo tên"
            value={q}
            onChange={e => setQ(e.target.value)}
          />
          <input
            placeholder="Giá tối thiểu"
            value={minPrice}
            onChange={e => setMinPrice(e.target.value.replace(/[^0-9]/g, ''))}
          />
          <input
            placeholder="Giá tối đa"
            value={maxPrice}
            onChange={e => setMaxPrice(e.target.value.replace(/[^0-9]/g, ''))}
          />
          <select value={category} onChange={e => setCategory(e.target.value)}>
            <option value="">Danh mục</option>
            {categories.map(c => (
              <option key={c} value={c}>{c}</option>
            ))}
          </select>
        </div>
      </div>

      {/* Danh sách sản phẩm */}
      <div className="grid">
        {filtered.map(product => (
          <div key={product.id} className="card">
            <img src={product.image} alt={product.name} />
            <h3>{product.name}</h3>
            <p className="price">{product.price.toLocaleString('vi-VN')}đ</p>
            <p className="category">{product.category}</p>
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
