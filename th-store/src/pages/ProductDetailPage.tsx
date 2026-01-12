import { useParams, useNavigate } from "react-router-dom";
import { useState, useEffect } from "react";
import { getProductDetail } from "../api/productDetail.api";
import { useCart } from "../store/CartContext";
import { useAuth } from "../store/AuthContext";
import { getProductReviews, createReview, type Review } from "../api/review.api";
import { toast } from "react-hot-toast";
import Swal from "sweetalert2";

export default function ProductDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { addToCart } = useCart();
  const { user } = useAuth();

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
  });
  const [submittingReview, setSubmittingReview] = useState(false);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [hoverRating, setHoverRating] = useState(0);

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
    // Kiểm tra user đã đăng nhập chưa
    if (!user) {
      Swal.fire({
        title: "Bạn cần đăng nhập",
        text: "Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng. Bạn có muốn đăng nhập ngay không?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Đăng nhập ngay",
        cancelButtonText: "Hủy"
      }).then((result) => {
        if (result.isConfirmed) {
          navigate("/dang-nhap");
        }
      });
      return;
    }

    if (!selectedVariant) {
      toast.error("Vui lòng chọn màu và kích thước hợp lệ!");
      return;
    }
    if (!product) {
      toast.error("Không tìm thấy sản phẩm!");
      return;
    }
    if (quantity > (selectedVariant.stock ?? 0)) {
      toast.error("Số lượng vượt quá tồn kho!");
      return;
    }

    setIsAdding(true);
    try {
      // Gửi product_id (product.id) và variant_id (selectedVariant.id)
      await addToCart(
        product.id, // product_id
        quantity,
        selectedVariant.id // variant_id
      );
      // Không reload trang, chỉ hiển thị thông báo
      // Giỏ hàng sẽ tự động cập nhật qua CartContext
      toast.success("🛒 Đã thêm vào giỏ hàng!");
    } catch (err: any) {
      console.error("Lỗi thêm giỏ hàng:", err);
      const errorMessage = err?.response?.data?.message || "Lỗi khi thêm vào giỏ!";

      if (errorMessage.includes("Unauthenticated") || err?.response?.status === 401) {
        Swal.fire({
          title: "Phiên đăng nhập hết hạn",
          text: "Phiên đăng nhập của bạn đã hết hạn. Bạn có muốn đăng nhập lại không?",
          icon: "error",
          showCancelButton: true,
          confirmButtonText: "Đăng nhập lại",
          cancelButtonText: "Hủy"
        }).then((result) => {
          if (result.isConfirmed) {
            navigate("/dang-nhap");
          }
        });
      } else {
        toast.error(errorMessage);
      }
    } finally {
      setIsAdding(false);
    }
  };

  const handleBuyNow = async () => {
    if (!user) {
      Swal.fire({
        title: "Bạn cần đăng nhập",
        text: "Bạn cần đăng nhập để mua hàng. Bạn có muốn đăng nhập ngay không?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Đăng nhập ngay",
        cancelButtonText: "Hủy"
      }).then((result) => {
        if (result.isConfirmed) {
          navigate("/dang-nhap");
        }
      });
      return;
    }
    if (!selectedVariant) {
      toast.error("Vui lòng chọn màu và kích thước hợp lệ!");
      return;
    }
    if (!product) {
      toast.error("Không tìm thấy sản phẩm!");
      return;
    }
    if (quantity > (selectedVariant.stock ?? 0)) {
      toast.error("Số lượng vượt quá tồn kho!");
      return;
    }

    try {
      // Tạo item tạm thời để gửi sang trang checkout
      const buyNowItem = {
        product_id: product.id,
        variant_id: selectedVariant.id,
        quantity: quantity,
        price: selectedVariant.sale_price ?? product.price,
        name: product.name,
        image: product.thumbnail,
        color: product.colors?.find((c: any) => c.id === selectedColor)?.name,
        size: product.sizes?.find((s: any) => s.id === selectedSize)?.value ?? product.sizes?.find((s: any) => s.id === selectedSize)?.name
      };

      console.log("Navigating to checkout with item:", buyNowItem);
      navigate('/thanh-toan', { state: { buyNowItem } });
    } catch (error) {
      console.error("Error in handleBuyNow:", error);
      toast.error("Có lỗi xảy ra khi chuyển hướng. Vui lòng thử lại.");
    }

  };


  // Xử lý submit đánh giá
  const handleSubmitReview = async () => {
    if (!id) return;

    // Kiểm tra user đã đăng nhập chưa
    if (!user) {
      Swal.fire({
        title: "Cần đăng nhập",
        text: "Bạn cần đăng nhập để đánh giá sản phẩm. Bạn có muốn đăng nhập ngay không?",
        icon: "info",
        showCancelButton: true,
        confirmButtonText: "Đăng nhập ngay",
        cancelButtonText: "Hủy"
      }).then((result) => {
        if (result.isConfirmed) {
          navigate("/dang-nhap");
        }
      });
      return;
    }

    setSubmittingReview(true);
    try {
      await createReview(Number(id), {
        rating: reviewForm.rating,
        comment: reviewForm.comment || undefined,
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
      });
      setShowReviewForm(false);
      toast.success("Cảm ơn bạn đã đánh giá!");
    } catch (error: any) {
      console.error("Lỗi khi thêm đánh giá:", error);
      const errorMessage = error.response?.data?.message || "Có lỗi xảy ra khi thêm đánh giá";

      // Nếu lỗi Unauthenticated, yêu cầu đăng nhập lại
      if (errorMessage.includes("đăng nhập") || error.response?.status === 401 || error.response?.status === 403) {
        Swal.fire({
          title: "Thông báo",
          text: "Phiên đăng nhập của bạn đã hết hạn hoặc bạn chưa đăng nhập. Bạn có muốn đăng nhập lại không?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Đăng nhập",
          cancelButtonText: "Hủy"
        }).then((result) => {
          if (result.isConfirmed) {
            navigate("/dang-nhap");
          }
        });
      } else {
        toast.error(errorMessage);
      }
    } finally {
      setSubmittingReview(false);
    }
  };

  // Render stars với hiệu ứng đẹp hơn
  const renderStars = (rating: number, size: "small" | "large" = "small", showNumber: boolean = false, interactive: boolean = false, onRatingChange?: (rating: number) => void, currentHover?: number) => {
    const starSize = size === "large" ? "28px" : "18px";
    const fullStar = "★";
    const emptyStar = "☆";

    const handleStarClick = (star: number) => {
      if (interactive && onRatingChange) {
        onRatingChange(star);
      }
    };

    return (
      <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
        <div style={{ display: "flex", gap: "2px" }}>
          {[1, 2, 3, 4, 5].map((star) => {
            const isActive = star <= (currentHover || rating);

            return (
              <span
                key={star}
                onClick={() => handleStarClick(star)}
                onMouseEnter={() => interactive && setHoverRating(star)}
                onMouseLeave={() => interactive && setHoverRating(0)}
                style={{
                  fontSize: starSize,
                  color: isActive ? "#FFD700" : "#ddd",
                  textShadow: isActive ? "0 0 8px rgba(255, 215, 0, 0.6), 0 0 12px rgba(255, 215, 0, 0.4)" : "none",
                  transition: "all 0.3s cubic-bezier(0.4, 0, 0.2, 1)",
                  display: "inline-block",
                  lineHeight: "1",
                  cursor: interactive ? "pointer" : "default",
                  transform: interactive && isActive ? "scale(1.15)" : "scale(1)",
                  filter: isActive ? "drop-shadow(0 0 4px rgba(255, 215, 0, 0.8))" : "none",
                  animation: isActive && interactive && currentHover === star ? "starPulse 0.6s ease-in-out" : "none",
                }}
              >
                {isActive ? fullStar : emptyStar}
              </span>
            );
          })}
        </div>
        {showNumber && (
          <span style={{
            marginLeft: "8px",
            fontSize: size === "large" ? "16px" : "14px",
            fontWeight: "600",
            color: "#666",
            backgroundColor: "#f0f0f0",
            padding: "4px 8px",
            borderRadius: "6px"
          }}>
            {rating}/5
          </span>
        )}
        <style>{`
          @keyframes starPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
          }
        `}</style>
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
          {/* Main Image */}
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
            <h1 className="product-title">
              {product.name}
              {/* Hiển thị badge Hết hàng nếu tổng tồn kho = 0 */}
              {(() => {
                const totalStock = (product.variants && product.variants.length > 0)
                  ? product.variants.reduce((sum: number, v: any) => sum + (v.stock || 0), 0)
                  : (product.stock || 0);
                if (totalStock === 0) {
                  return <span className="out-of-stock-badge">HẾT HÀNG</span>;
                }
                return null;
              })()}
            </h1>

            <div className="product-price-section">
              <span className="current-price">
                {new Intl.NumberFormat('vi-VN', {
                  style: 'decimal',
                  minimumFractionDigits: 0,
                  maximumFractionDigits: 0,
                }).format(selectedVariant?.sale_price ?? product.price)}đ
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
                  disabled={(selectedVariant?.stock ?? 0) === 0}
                >
                  −
                </button>
                <input
                  type="number"
                  value={quantity}
                  min="1"
                  max={selectedVariant?.stock ?? 1}
                  onChange={(e) => {
                    const val = parseInt(e.target.value);
                    if (!isNaN(val)) {
                      const maxStock = selectedVariant?.stock ?? 1;
                      const sanitizedVal = Math.max(1, Math.min(val, maxStock));
                      setQuantity(sanitizedVal);
                    }
                  }}
                  onBlur={(e) => {
                    const val = parseInt(e.target.value);
                    if (isNaN(val) || val < 1) {
                      setQuantity(1);
                    }
                  }}
                  style={{
                    width: "60px",
                    textAlign: "center",
                    fontSize: "18px",
                    fontWeight: "700",
                    color: "#1f2937",
                    border: "1px solid #e5e7eb",
                    borderRadius: "8px",
                    padding: "8px 4px",
                    background: "white"
                  }}
                />
                <button
                  className="quantity-btn"
                  onClick={() =>
                    setQuantity(Math.min(selectedVariant?.stock ?? quantity, quantity + 1))
                  }
                  disabled={(selectedVariant?.stock ?? 0) === 0 || quantity >= (selectedVariant?.stock ?? 0)}
                  style={quantity >= (selectedVariant?.stock ?? 0) ? { cursor: "not-allowed", opacity: 0.5 } : {}}
                >
                  +
                </button>
              </div>
            </div>

            {/* NÚT */}
            <div className="action-buttons">
              <button
                className={`add-to-cart-btn ${isAdding ? "loading" : ""} ${(selectedVariant?.stock ?? 0) === 0 ? "disabled" : ""}`}
                onClick={handleAddToCart}
                disabled={isAdding || (selectedVariant?.stock ?? 0) === 0}
                style={(selectedVariant?.stock ?? 0) === 0 ? { backgroundColor: "#ccc", cursor: "not-allowed" } : {}}
              >
                {isAdding ? "Đang thêm..." : (selectedVariant?.stock ?? 0) === 0 ? "HẾT HÀNG" : "🛒 Thêm vào giỏ hàng"}
              </button>
              <button
                className="buy-now-btn"
                onClick={handleBuyNow}
                disabled={(selectedVariant?.stock ?? 0) === 0}
                style={(selectedVariant?.stock ?? 0) === 0 ? { backgroundColor: "#999", cursor: "not-allowed" } : {}}
              >
                {(selectedVariant?.stock ?? 0) === 0 ? "HẾT HÀNG" : "💳 Mua ngay"}
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
              {user && (
                <div style={{
                  marginBottom: "15px",
                  padding: "10px",
                  backgroundColor: "#f0f0f0",
                  borderRadius: "4px",
                  fontSize: "14px"
                }}>
                  Đang đánh giá với tài khoản: <strong>{user.name}</strong> ({user.email})
                </div>
              )}
              {!user && (
                <div style={{
                  marginBottom: "15px",
                  padding: "10px",
                  backgroundColor: "#fff3cd",
                  borderRadius: "4px",
                  fontSize: "14px",
                  color: "#856404"
                }}>
                  ⚠️ Bạn cần đăng nhập để đánh giá sản phẩm
                </div>
              )}
              <div style={{ marginBottom: "15px" }}>
                <label style={{ display: "block", marginBottom: "10px", fontWeight: "bold" }}>
                  Đánh giá (sao): *
                </label>
                <div style={{ display: "flex", gap: "8px", alignItems: "center", flexWrap: "wrap" }}>
                  {renderStars(
                    reviewForm.rating,
                    "large",
                    false,
                    true,
                    (rating) => setReviewForm({ ...reviewForm, rating }),
                    hoverRating
                  )}
                  <span style={{
                    marginLeft: "12px",
                    fontSize: "18px",
                    fontWeight: "600",
                    color: "#333",
                    padding: "6px 12px",
                    backgroundColor: "#f0f0f0",
                    borderRadius: "6px"
                  }}>
                    {reviewForm.rating}/5 sao
                  </span>
                </div>
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
                disabled={submittingReview || !user}
                style={{
                  padding: "10px 20px",
                  backgroundColor: submittingReview || !user ? "#ccc" : "#28a745",
                  color: "white",
                  border: "none",
                  borderRadius: "6px",
                  cursor: submittingReview || !user ? "not-allowed" : "pointer",
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
                        <div style={{ fontWeight: "bold", marginBottom: "5px", fontSize: "16px" }}>
                          {review.user?.name || review.user_name}
                        </div>
                        <div style={{ display: "flex", alignItems: "center", gap: "12px", flexWrap: "wrap" }}>
                          {renderStars(review.rating, "small", true)}
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
