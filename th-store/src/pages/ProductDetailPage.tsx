import { useParams } from "react-router-dom";
import { useState, useEffect } from "react";
import { getProductDetail } from "../api/productDetail.api";
import { addToCart } from "../api/cart.api";
import { getProductReviews, createReview, type Review } from "../api/review.api";

export default function ProductDetailPage() {
  const { id } = useParams();

  const [product, setProduct] = useState<any>(null);
  const [quantity, setQuantity] = useState(1);

  const [selectedColor, setSelectedColor] = useState<number | null>(null);
  const [selectedSize, setSelectedSize] = useState<number | null>(null);

  const [loading, setLoading] = useState(true);
  const [isAdding, setIsAdding] = useState(false);

  // Reviews state
  const [reviews, setReviews] = useState<Review[]>([]);
  const [reviewStats, setReviewStats] = useState<{
    average_rating: number;
    total_reviews: number;
    rating_counts: Record<number, number>;
  }>({
    average_rating: 0,
    total_reviews: 0,
    rating_counts: {},
  });
  const [loadingReviews, setLoadingReviews] = useState(true);
  const [showReviewForm, setShowReviewForm] = useState(false);
  const [reviewForm, setReviewForm] = useState({
    rating: 5,
    comment: "",
    user_name: "",
    user_email: "",
  });
  const [submittingReview, setSubmittingReview] = useState(false);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);

  // Load sản phẩm từ API
  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const data = await getProductDetail(Number(id));
        setProduct(data);

        // Auto chọn biến thể đầu tiên
        if (data.colors?.length) {
          setSelectedColor(data.colors[0].id);
        }
        if (data.sizes?.length) {
          setSelectedSize(data.sizes[0].id);
        }
      } catch (error) {
        console.error("Lỗi khi tải sản phẩm:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchProduct();
  }, [id]);

  // Load reviews
  useEffect(() => {
    const fetchReviews = async () => {
      if (!id) return;
      try {
        setLoadingReviews(true);
        const data = await getProductReviews(Number(id));
        setReviews(data.reviews);
        setReviewStats({
          average_rating: data.average_rating,
          total_reviews: data.total_reviews,
          rating_counts: data.rating_counts,
        });
      } catch (error) {
        console.error("Lỗi khi tải đánh giá:", error);
      } finally {
        setLoadingReviews(false);
      }
    };

    fetchReviews();
  }, [id]);

  if (loading) return <p className="text-center mt-10">Đang tải...</p>;
  if (!product) return <p className="text-center mt-10">Không tìm thấy sản phẩm</p>;

  // Tìm đúng variant theo màu + size
  const selectedVariant = product.variants?.find(
    (v: any) =>
      v.color_id === Number(selectedColor) &&
      v.size_id === Number(selectedSize)
  );

  const handleAddToCart = async () => {
    if (!selectedVariant) {
      alert("Vui lòng chọn màu và kích thước hợp lệ!");
      return;
    }
    if (quantity > (selectedVariant.stock ?? 0)) {
      alert("Số lượng vượt quá tồn kho!");
      return;
    }

    setIsAdding(true);
    try {
      await addToCart(
        selectedVariant.id,
        quantity,
        selectedColor?.toString() ?? null,
        selectedSize?.toString() ?? null
      );
      alert("🛒 Đã thêm vào giỏ hàng!");
    } catch (err) {
      console.error("Lỗi thêm giỏ hàng:", err);
      alert("Lỗi khi thêm vào giỏ!");
    }
    setIsAdding(false);
  };

  // Xử lý submit đánh giá
  const handleSubmitReview = async () => {
    if (!id) return;
    if (!reviewForm.user_name.trim()) {
      alert("Vui lòng nhập tên của bạn");
      return;
    }
    if (reviewForm.user_email && !reviewForm.user_email.includes("@")) {
      alert("Email không hợp lệ");
      return;
    }

    setSubmittingReview(true);
    try {
      await createReview(Number(id), {
        rating: reviewForm.rating,
        comment: reviewForm.comment || undefined,
        user_name: reviewForm.user_name,
        user_email: reviewForm.user_email || undefined,
      });
      
      // Reload reviews
      const data = await getProductReviews(Number(id));
      setReviews(data.reviews);
      setReviewStats({
        average_rating: data.average_rating,
        total_reviews: data.total_reviews,
        rating_counts: data.rating_counts,
      });

      // Reset form
      setReviewForm({
        rating: 5,
        comment: "",
        user_name: "",
        user_email: "",
      });
      setShowReviewForm(false);
      alert("Cảm ơn bạn đã đánh giá!");
    } catch (error: any) {
      console.error("Lỗi khi thêm đánh giá:", error);
      alert(error.response?.data?.message || "Có lỗi xảy ra khi thêm đánh giá");
    } finally {
      setSubmittingReview(false);
    }
  };

  // Render stars
  const renderStars = (rating: number, size: "small" | "large" = "small") => {
    const starSize = size === "large" ? "24px" : "16px";
    return (
      <div style={{ display: "flex", gap: "2px" }}>
        {[1, 2, 3, 4, 5].map((star) => (
          <span
            key={star}
            style={{
              fontSize: starSize,
              color: star <= rating ? "#FFD700" : "#ddd",
            }}
          >
            ⭐
          </span>
        ))}
      </div>
    );
  };

  // Get product images
  const getProductImages = () => {
    const images: string[] = [];
    
    // Thêm thumbnail
    if (product.thumbnail) {
      const thumbUrl = product.thumbnail.startsWith('http') 
        ? product.thumbnail 
        : `http://127.0.0.1:8000/storage/${product.thumbnail}`;
      images.push(thumbUrl);
    }
    
    // Thêm các ảnh khác từ images array
    if (product.images && Array.isArray(product.images)) {
      product.images.forEach((img: string) => {
        if (img && img !== product.thumbnail) {
          const imgUrl = img.startsWith('http') 
            ? img 
            : `http://127.0.0.1:8000/storage/${img}`;
          images.push(imgUrl);
        }
      });
    }
    
    // Fallback nếu không có ảnh nào
    if (images.length === 0) {
      images.push("https://cdn-icons-png.flaticon.com/512/1828/1828817.png");
    }
    
    return images;
  };

  const productImages = getProductImages();

  return (
    <div className="main">
      <div className="product-detail-container">
        <div className="product-detail">

          {/* ẢNH - Gallery */}
          <div className="product-image-section">
            {/* Main Image */}
            <div className="product-image-wrapper">
              <img
                src={productImages[selectedImageIndex]}
                alt={`${product.name} - Ảnh ${selectedImageIndex + 1}`}
                className="product-main-image"
              />
              {productImages.length > 1 && (
                <div className="image-navigation">
                  <button
                    className="image-nav-btn prev"
                    onClick={() => setSelectedImageIndex((prev) => 
                      prev > 0 ? prev - 1 : productImages.length - 1
                    )}
                    aria-label="Ảnh trước"
                  >
                    ‹
                  </button>
                  <button
                    className="image-nav-btn next"
                    onClick={() => setSelectedImageIndex((prev) => 
                      prev < productImages.length - 1 ? prev + 1 : 0
                    )}
                    aria-label="Ảnh sau"
                  >
                    ›
                  </button>
                </div>
              )}
              <div className="image-counter">
                {selectedImageIndex + 1} / {productImages.length}
              </div>
            </div>
            
            {/* Thumbnail Gallery */}
            {productImages.length > 1 && (
              <div className="thumbnail-gallery">
                {productImages.map((img, index) => (
                  <button
                    key={index}
                    className={`thumbnail-item ${selectedImageIndex === index ? 'active' : ''}`}
                    onClick={() => setSelectedImageIndex(index)}
                  >
                    <img src={img} alt={`Thumbnail ${index + 1}`} />
                  </button>
                ))}
              </div>
            )}
          </div>

          {/* THÔNG TIN */}
          <div className="product-info-section">
            <h1 className="product-title">{product.name}</h1>

            <div className="product-price-section">
              <span className="current-price">
                {(selectedVariant?.sale_price ?? product.price).toLocaleString("vi-VN")}đ
              </span>
            </div>

            <div className="product-description">
              <p>{product.description}</p>
            </div>

            {/* MÀU */}
            {product.colors?.length > 0 && (
              <div className="variant-section">
                <div className="variant-header">
                  <h3 className="variant-title">Màu sắc</h3>
                  {selectedColor && (
                    <span className="variant-selected-label">
                      {product.colors.find((c: any) => c.id === selectedColor)?.name || "Đã chọn"}
                    </span>
                  )}
                </div>
                <div className="color-options">
                  {product.colors.map((c: any) => {
                    const isSelected = selectedColor === c.id;
                    return (
                      <button
                        key={c.id}
                        onClick={() => setSelectedColor(c.id)}
                        className={`color-option ${isSelected ? "selected" : ""}`}
                        style={{
                          backgroundColor: c.code || c.hex || "#fff",
                        }}
                        title={c.name}
                      >
                        {!c.code && !c.hex && (
                          <span style={{ 
                            fontSize: "10px", 
                            color: "#333",
                            fontWeight: "600"
                          }}>
                            {c.name}
                          </span>
                        )}
                      </button>
                    );
                  })}
                </div>
              </div>
            )}

            
            {/* SIZE */}
            {product.sizes?.length > 0 && (
              <div className="variant-section">
                <div className="variant-header">
                  <h3 className="variant-title">Kích thước</h3>
                  {selectedSize && (
                    <span className="variant-selected-label">
                      {(() => {
                        const selectedSizeObj = product.sizes.find((s: any) => s.id === selectedSize);
                        return selectedSizeObj?.value ?? selectedSizeObj?.name ?? "Đã chọn";
                      })()}
                    </span>
                  )}
                </div>
                <div className="size-options">
                  {product.sizes.map((s: any) => {
                    const variantForSize = product.variants?.find(
                      (v: any) => v.size_id === s.id && v.color_id === selectedColor
                    );
                    const isSelected = selectedSize === s.id;
                    const isAvailable = variantForSize && variantForSize.stock > 0;
                    const stock = variantForSize?.stock ?? 0;
                    
                    return (
                      <button
                        key={s.id}
                        onClick={() => isAvailable && setSelectedSize(s.id)}
                        disabled={!isAvailable}
                        className={`size-option ${isSelected ? "selected" : ""} ${!isAvailable ? "disabled" : ""}`}
                      >
                        <div className="size-content">
                          <span className="size-name">
                            {s.value ?? s.name ?? `Size ${s.id}`}
                          </span>
                          {isAvailable ? (
                            <span className={`size-stock ${isSelected ? "selected" : ""}`}>
                            {stock} sản phẩm
                            </span>
                          ) : (
                            <span className="size-out-of-stock">Hết hàng</span>
                          )}
                        </div>
                        {isSelected && <span className="size-checkmark">✓</span>}
                      </button>
                    );
                  })}
                </div>
              </div>
            )}

            {/* SỐ LƯỢNG */}
            <div className="quantity-section">
              <h3 className="variant-title">Số lượng</h3>
              <div className="quantity-controls">
                <button
                  className="quantity-btn"
                  onClick={() => setQuantity(Math.max(1, quantity - 1))}
                >
                  −
                </button>
                <span className="quantity-display">{quantity}</span>
                <button
                  className="quantity-btn"
                  onClick={() =>
                    setQuantity(Math.min(selectedVariant?.stock ?? quantity, quantity + 1))
                  }
                >
                  +
                </button>
              </div>
            </div>

            {/* NÚT */}
            <div className="action-buttons">
              <button
                className={`add-to-cart-btn ${isAdding ? "loading" : ""}`}
                onClick={handleAddToCart}
                disabled={isAdding || (selectedVariant?.stock ?? 0) === 0}
              >
                {isAdding ? "Đang thêm..." : "🛒 Thêm vào giỏ hàng"}
              </button>
              <button className="buy-now-btn" disabled={(selectedVariant?.stock ?? 0) === 0}>
                💳 Mua ngay
              </button>
            </div>

          </div>
        </div>

        {/* REVIEWS SECTION */}
        <div className="reviews-section" style={{ marginTop: "40px", padding: "20px" }}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "30px" }}>
            <h2 style={{ fontSize: "24px", fontWeight: "bold" }}>Đánh giá sản phẩm</h2>
            <button
              onClick={() => setShowReviewForm(!showReviewForm)}
              style={{
                padding: "10px 20px",
                backgroundColor: "#007bff",
                color: "white",
                border: "none",
                borderRadius: "6px",
                cursor: "pointer",
              }}
            >
              {showReviewForm ? "✕ Đóng" : "+ Viết đánh giá"}
            </button>
          </div>

          {/* Rating Summary */}
          {!loadingReviews && (
            <div
              style={{
                display: "flex",
                gap: "40px",
                padding: "20px",
                backgroundColor: "#f8f9fa",
                borderRadius: "8px",
                marginBottom: "30px",
              }}
            >
              <div style={{ textAlign: "center" }}>
                <div style={{ fontSize: "48px", fontWeight: "bold", color: "#007bff" }}>
                  {reviewStats.average_rating.toFixed(1)}
                </div>
                <div>{renderStars(Math.round(reviewStats.average_rating), "large")}</div>
                <div style={{ marginTop: "8px", color: "#666" }}>
                  {reviewStats.total_reviews} đánh giá
                </div>
              </div>
              <div style={{ flex: 1 }}>
                {[5, 4, 3, 2, 1].map((star) => {
                  const count = reviewStats.rating_counts[star] || 0;
                  const percentage = reviewStats.total_reviews > 0 
                    ? (count / reviewStats.total_reviews) * 100 
                    : 0;
                  return (
                    <div key={star} style={{ display: "flex", alignItems: "center", gap: "10px", marginBottom: "8px" }}>
                      <span style={{ width: "60px" }}>{star} sao</span>
                      <div
                        style={{
                          flex: 1,
                          height: "8px",
                          backgroundColor: "#e0e0e0",
                          borderRadius: "4px",
                          overflow: "hidden",
                        }}
                      >
                        <div
                          style={{
                            width: `${percentage}%`,
                            height: "100%",
                            backgroundColor: "#FFD700",
                          }}
                        />
                      </div>
                      <span style={{ width: "40px", textAlign: "right", fontSize: "14px" }}>
                        {count}
                      </span>
                    </div>
                  );
                })}
              </div>
            </div>
          )}

          {/* Review Form */}
          {showReviewForm && (
            <div
              style={{
                padding: "20px",
                border: "1px solid #ddd",
                borderRadius: "8px",
                marginBottom: "30px",
                backgroundColor: "#fff",
              }}
            >
              <h3 style={{ marginBottom: "20px" }}>Viết đánh giá của bạn</h3>
              <div style={{ marginBottom: "15px" }}>
                <label style={{ display: "block", marginBottom: "5px", fontWeight: "bold" }}>
                  Đánh giá (sao):
                </label>
                <div style={{ display: "flex", gap: "5px", alignItems: "center" }}>
                  {[1, 2, 3, 4, 5].map((star) => (
                    <button
                      key={star}
                      onClick={() => setReviewForm({ ...reviewForm, rating: star })}
                      style={{
                        fontSize: "24px",
                        border: "none",
                        background: "none",
                        cursor: "pointer",
                        color: star <= reviewForm.rating ? "#FFD700" : "#ddd",
                        padding: 0,
                      }}
                    >
                      ⭐
                    </button>
                  ))}
                  <span style={{ marginLeft: "10px" }}>{reviewForm.rating}/5</span>
                </div>
              </div>
              <div style={{ marginBottom: "15px" }}>
                <label style={{ display: "block", marginBottom: "5px", fontWeight: "bold" }}>
                  Tên của bạn: *
                </label>
                <input
                  type="text"
                  value={reviewForm.user_name}
                  onChange={(e) => setReviewForm({ ...reviewForm, user_name: e.target.value })}
                  style={{
                    width: "100%",
                    padding: "8px",
                    border: "1px solid #ddd",
                    borderRadius: "4px",
                  }}
                  placeholder="Nhập tên của bạn"
                />
              </div>
              <div style={{ marginBottom: "15px" }}>
                <label style={{ display: "block", marginBottom: "5px", fontWeight: "bold" }}>
                  Email (tùy chọn):
                </label>
                <input
                  type="email"
                  value={reviewForm.user_email}
                  onChange={(e) => setReviewForm({ ...reviewForm, user_email: e.target.value })}
                  style={{
                    width: "100%",
                    padding: "8px",
                    border: "1px solid #ddd",
                    borderRadius: "4px",
                  }}
                  placeholder="email@example.com"
                />
              </div>
              <div style={{ marginBottom: "15px" }}>
                <label style={{ display: "block", marginBottom: "5px", fontWeight: "bold" }}>
                  Nhận xét (tùy chọn):
                </label>
                <textarea
                  value={reviewForm.comment}
                  onChange={(e) => setReviewForm({ ...reviewForm, comment: e.target.value })}
                  rows={4}
                  style={{
                    width: "100%",
                    padding: "8px",
                    border: "1px solid #ddd",
                    borderRadius: "4px",
                    resize: "vertical",
                  }}
                  placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..."
                />
              </div>
              <button
                onClick={handleSubmitReview}
                disabled={submittingReview}
                style={{
                  padding: "10px 20px",
                  backgroundColor: submittingReview ? "#ccc" : "#28a745",
                  color: "white",
                  border: "none",
                  borderRadius: "6px",
                  cursor: submittingReview ? "not-allowed" : "pointer",
                }}
              >
                {submittingReview ? "Đang gửi..." : "Gửi đánh giá"}
              </button>
            </div>
          )}

          {/* Reviews List */}
          <div>
            {loadingReviews ? (
              <p>Đang tải đánh giá...</p>
            ) : reviews.length === 0 ? (
              <p style={{ textAlign: "center", padding: "40px", color: "#666" }}>
                Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá sản phẩm này!
              </p>
            ) : (
              <div style={{ display: "flex", flexDirection: "column", gap: "20px" }}>
                {reviews.map((review) => (
                  <div
                    key={review.id}
                    style={{
                      padding: "20px",
                      border: "1px solid #e0e0e0",
                      borderRadius: "8px",
                      backgroundColor: "#fff",
                    }}
                  >
                    <div style={{ display: "flex", justifyContent: "space-between", marginBottom: "10px" }}>
                      <div>
                        <div style={{ fontWeight: "bold", marginBottom: "5px" }}>
                          {review.user?.name || review.user_name}
                        </div>
                        <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                          {renderStars(review.rating)}
                          <span style={{ color: "#666", fontSize: "14px" }}>
                            {new Date(review.created_at).toLocaleDateString("vi-VN")}
                          </span>
                        </div>
                      </div>
                    </div>
                    {review.comment && (
                      <div style={{ marginTop: "10px", color: "#333", lineHeight: "1.6" }}>
                        {review.comment}
                      </div>
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
