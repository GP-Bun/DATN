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

              const totalStock = (p.variants && p.variants.length > 0)
                ? p.variants.reduce((sum: number, v: any) => sum + (v.stock || 0), 0)
                : (p.stock || 0);

              const rating = p.reviews_avg_rating ? Math.round(Number(p.reviews_avg_rating)) : 5;
              const reviewCount = p.reviews_count || 0;

              return (
                <div className="product-card" key={p.id as number}>
                  <div className="product-image">
                    <img
                      src={getImageUrl()}
                      alt={p.name}
                      loading="lazy"
                    />
                    <div className="product-badges" style={{
                      position: 'absolute',
                      top: '12px',
                      left: '12px',
                      display: 'flex',
                      flexDirection: 'column',
                      gap: '8px',
                      zIndex: 2
                    }}>
                      {p.is_featured === 1 && (
                        <div style={{
                          backgroundColor: '#3b82f6',
                          color: 'white',
                          padding: '4px 10px',
                          borderRadius: '6px',
                          fontSize: '11px',
                          fontWeight: '700',
                          textTransform: 'uppercase',
                          letterSpacing: '0.5px',
                          boxShadow: '0 2px 4px rgba(59, 130, 246, 0.3)'
                        }}>
                          🚀 Nổi bật
                        </div>
                      )}
                      {totalStock === 0 && (
                        <div style={{
                          backgroundColor: '#ef4444',
                          color: 'white',
                          padding: '4px 10px',
                          borderRadius: '6px',
                          fontSize: '11px',
                          fontWeight: '700',
                          textTransform: 'uppercase',
                          letterSpacing: '0.5px',
                          boxShadow: '0 2px 4px rgba(239, 68, 68, 0.3)'
                        }}>
                          Hết hàng
                        </div>
                      )}
                    </div>
                    <div className="product-overlay">
                      <Link to={`/san-pham/${p.id as number}`} className="quick-view-btn">👁️ Xem chi tiết</Link>
                    </div>
                  </div>
                  <div className="product-info">
                    <h3>{p.name as string}</h3>
                    <div className="product-price-row" style={{
                      display: 'flex',
                      alignItems: 'baseline',
                      gap: '8px',
                      marginBottom: '12px'
                    }}>
                      <span className="product-price" style={{
                        fontSize: '1.25rem',
                        fontWeight: '700',
                        color: 'var(--primary)',
                        margin: 0
                      }}>
                        {new Intl.NumberFormat('vi-VN', {
                          style: 'decimal',
                          minimumFractionDigits: 0,
                          maximumFractionDigits: 0,
                        }).format(Number(p.price as number))}đ
                      </span>
                    </div>
                    <div className="product-rating" style={{
                      marginBottom: '16px',
                      display: 'flex',
                      alignItems: 'center',
                      gap: '6px',
                      fontSize: '14px'
                    }}>
                      <div className="stars" style={{ color: '#f59e0b' }}>
                        {'★'.repeat(rating)}{'☆'.repeat(5 - rating)}
                      </div>
                      <span style={{ color: '#6b7280' }}>({reviewCount})</span>
                    </div>
                    <Link
                      to={`/san-pham/${p.id as number}`}
                      className="product-link"
                      style={{
                        display: 'block',
                        textAlign: 'center',
                        padding: '10px',
                        background: 'transparent',
                        border: '1.5px solid var(--primary)',
                        color: 'var(--primary)',
                        borderRadius: '10px',
                        fontWeight: '600',
                        transition: 'all 0.2s',
                        textDecoration: 'none'
                      }}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.background = 'var(--primary)';
                        e.currentTarget.style.color = 'white';
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.background = 'transparent';
                        e.currentTarget.style.color = 'var(--primary)';
                      }}
                    >
                      Mua ngay
                    </Link>
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
        <section className="featured-categories" style={{ padding: '80px 0' }}>
          <div className="section-header">
            <h2 className="section-title">Danh mục nổi bật</h2>
            <p className="section-subtitle">Khám phá các danh mục sản phẩm phổ biến</p>
          </div>
          <div className="categories-grid">
            {categories.map((cat: any) => {
              const getCategoryImage = (name: string) => {
                const map: Record<string, string> = {
                  'Giày Da': 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?q=80&w=500&auto=format&fit=crop',
                  'Giày thể thao': 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=500&auto=format&fit=crop',
                  'Giày chạy bộ': 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=500&auto=format&fit=crop',
                  'Giày thời trang': 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?q=80&w=500&auto=format&fit=crop',
                  'Giày cao gót': 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?q=80&w=500&auto=format&fit=crop',
                  'Nổi bật': 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?q=80&w=500&auto=format&fit=crop'
                };
                return map[name] || 'https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=500&auto=format&fit=crop';
              };

              return (
                <Link key={cat.id} to={`/san-pham?category=${cat.name}`} className="category-card">
                  <img src={getCategoryImage(cat.name)} alt={cat.name} loading="lazy" />
                  <div className="category-overlay">
                    <h3>{cat.name}</h3>
                    <span>Khám phá ngay →</span>
                  </div>
                </Link>
              );
            })}
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
