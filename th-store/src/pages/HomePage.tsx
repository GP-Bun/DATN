import { Link } from 'react-router-dom';
import { useEffect, useState } from 'react';
import api from '../api/api';

export default function HomePage() {
  const [banners, setBanners] = useState([]);
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchHomeData = async () => {
      try {
        const res = await api.get('/home');
        setBanners(res.data.banners || []);
        setProducts(res.data.featured_products || []);
        setCategories(res.data.featured_categories || []);
      } catch (err) {
        console.error('Lỗi khi tải dữ liệu trang chủ:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchHomeData();
  }, []);

  return (
    <div className="home-page">
      {/* Hero Section */}
      <section className="hero-section">
        <div className="hero-content">
          <div className="hero-text">
            <h1 className="hero-title">
              Chào mừng đến với <span className="gradient-text">TH Store</span>
            </h1>
            <p className="hero-subtitle">
              Khám phá thế giới mua sắm tuyệt vời với những sản phẩm chất lượng cao
            </p>
            <p className="hero-description">
              Tìm kiếm những sản phẩm chất lượng cao với giá cả hợp lý.
              Trải nghiệm mua sắm trực tuyến tốt nhất tại TH Store.
            </p>
            <div className="hero-actions">
              <Link to="/san-pham" className="btn-primary">
                🛍️ Khám phá ngay
              </Link>
            </div>
          </div>

          {/* Banner images từ API */}
          <div className="hero-image">
            <div className="hero-image-container">
              {loading ? (
                <p>Đang tải banner...</p>
              ) : banners.length > 0 ? (
                banners.map((b: any) => {
                  const getBannerUrl = () => {
                    if (b.image) {
                      return b.image.startsWith('http') ? b.image : `http://127.0.0.1:8000/storage/${b.image}`;
                    }
                    return "https://bizweb.dktcdn.net/100/347/092/files/giay-sneaker-la-gi-1.jpg?v=1599104032003";
                  };
                  
                  return (
                    <a key={b.id as number} href={b.link as string || '#'}>
                      <img src={getBannerUrl()} alt={`banner-${b.id as number}`} className="hero-img" />
                    </a>
                  );
                })
              ) : (
                <img
                  src="https://bizweb.dktcdn.net/100/347/092/files/giay-sneaker-la-gi-1.jpg?v=1599104032003"
                  alt="Shopping Experience"
                  className="hero-img"
                />
              )}
            </div>
          </div>
        </div>
      </section>

      {/* Featured Products */}
      <section className="featured-products">
        <div className="section-header">
          <h2 className="section-title">Sản phẩm nổi bật</h2>
          <p className="section-subtitle">Những sản phẩm được yêu thích nhất</p>
        </div>
        <div className="products-grid">
          {loading ? (
            <p>Đang tải sản phẩm...</p>
          ) : products.length > 0 ? (
            products.map((p: any) => {
              const getImageUrl = () => {
                if (p.thumbnail) {
                  // Nếu thumbnail đã là URL đầy đủ thì dùng luôn, nếu không thì thêm prefix
                  if (p.thumbnail.startsWith('http')) {
                    return p.thumbnail;
                  }
                  return `http://127.0.0.1:8000/storage/${p.thumbnail}`;
                }
                if (p.image) {
                  return p.image.startsWith('http') ? p.image : `http://127.0.0.1:8000/storage/${p.image}`;
                }
                return "https://cdn-icons-png.flaticon.com/512/1828/1828817.png";
              };
              
              return (
              <div className="product-card" key={p.id as number}>
                <div className="product-image">
                  <img
                    src={getImageUrl()}
                    alt={p.name}
                  />
                  <div className="product-overlay">
                    <Link to={`/san-pham/${p.id as number}`} className="quick-view-btn">👁️ Xem nhanh</Link>
                  </div>
                </div>
                <div className="product-info">
                  <h3>{p.name as string}</h3>
                  <p className="product-price">{new Intl.NumberFormat('vi-VN', {
                    style: 'decimal',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                  }).format(Number(p.price as number))}đ</p>
                  <div className="product-rating">
                    {/* Nếu API không có reviews, hiển thị 0 */}
                    <span>⭐⭐⭐⭐⭐</span>
                    <span>({p.reviews as number || 0} đánh giá)</span>
                  </div>
                  <Link to={`/san-pham/${p.id as number}`} className="product-link">Xem chi tiết</Link>
                </div>
              </div>
              );
            })
          ) : (
            <p>Không có sản phẩm nào</p>
          )}

        </div>
        <div className="view-all-section">
          <Link to="/san-pham" className="btn-outline">Xem tất cả sản phẩm →</Link>
        </div>
      </section>

      {/* Featured Categories */}
      {categories.length > 0 && (
        <section className="featured-categories">
          <div className="section-header">
            <h2 className="section-title">Danh mục nổi bật</h2>
            <p className="section-subtitle">Khám phá các danh mục sản phẩm phổ biến</p>
          </div>
          <div className="categories-grid">
            {categories.map((cat: any) => (
              <Link key={cat.id} to={`/san-pham?category=${cat.name}`} className="category-card">
                <h3>{cat.name}</h3>
              </Link>
            ))}
          </div>
        </section>
      )}

      {/* Features Section */}
      <section className="features-section">
        <div className="section-header">
          <h2 className="section-title">Tại sao chọn TH Store?</h2>
          <p className="section-subtitle">Những lý do khiến khách hàng tin tưởng</p>
        </div>
        <div className="features-grid">
          <div className="feature-card">
            <div className="feature-icon">🚚</div>
            <h3>Giao hàng nhanh</h3>
            <p>Giao hàng trong 24h với dịch vụ chuyên nghiệp và đáng tin cậy</p>
          </div>
          <div className="feature-card">
            <div className="feature-icon">🛡️</div>
            <h3>Bảo hành chính hãng</h3>
            <p>Cam kết chất lượng sản phẩm 100% chính hãng với chế độ bảo hành tốt</p>
          </div>
          <div className="feature-card">
            <div className="feature-icon">💳</div>
            <h3>Thanh toán an toàn</h3>
            <p>Hỗ trợ nhiều phương thức thanh toán bảo mật và tiện lợi</p>
          </div>
          <div className="feature-card">
            <div className="feature-icon">🎁</div>
            <h3>Ưu đãi hấp dẫn</h3>
            <p>Nhiều chương trình khuyến mãi và ưu đãi đặc biệt cho khách hàng</p>
          </div>
        </div>
      </section>
    </div>
  );
}
