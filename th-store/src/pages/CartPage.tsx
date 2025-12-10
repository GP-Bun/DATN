import { Link } from "react-router-dom";
import { useCart } from "../store/CartContext";
import { useState, useEffect } from "react";
import { applyCoupon, getAvailableCoupons, type Coupon } from "../api/coupon.api";
import { formatPrice } from "../utils/formatPrice";

export default function CartPage() {
  const { items, updateCartItem, removeCartItem, getTotalPrice, reloadCart } = useCart();
  const [availableCoupons, setAvailableCoupons] = useState<Coupon[]>([]);
  const [loadingCoupons, setLoadingCoupons] = useState(false);
  const [appliedCoupon, setAppliedCoupon] = useState<Coupon | null>(null);
  const [discount, setDiscount] = useState(0);
  const [isApplyingCoupon, setIsApplyingCoupon] = useState(false);
  const [couponError, setCouponError] = useState("");

  // Load coupon từ localStorage khi component mount
  useEffect(() => {
    const savedCoupon = localStorage.getItem("applied_coupon");
    const savedDiscount = localStorage.getItem("coupon_discount");
    if (savedCoupon && savedDiscount) {
      try {
        setAppliedCoupon(JSON.parse(savedCoupon));
        setDiscount(parseFloat(savedDiscount));
      } catch (e) {
        console.error("Error loading coupon:", e);
      }
    }
  }, []);

  // Load danh sách voucher có sẵn
  useEffect(() => {
    const loadAvailableCoupons = async () => {
      try {
        setLoadingCoupons(true);
        const total = getTotalPrice();
        const data = await getAvailableCoupons(total);
        setAvailableCoupons(data.coupons || []);
      } catch (error) {
        console.error("Error loading coupons:", error);
      } finally {
        setLoadingCoupons(false);
      }
    };

    if (items.length > 0) {
      loadAvailableCoupons();
    } else {
      setAvailableCoupons([]);
    }
  }, [items]);

  // Áp dụng voucher từ danh sách
  const handleSelectCoupon = async (coupon: Coupon) => {
    if (appliedCoupon?.id === coupon.id) {
      // Nếu đã chọn voucher này rồi thì xóa
      handleRemoveCoupon();
      return;
    }

    setIsApplyingCoupon(true);
    setCouponError("");

    try {
      const total = getTotalPrice();
      const result = await applyCoupon(coupon.code, total);

      if (result.ok && result.coupon && result.discount !== undefined) {
        setAppliedCoupon(result.coupon);
        setDiscount(result.discount);
        // Lưu vào localStorage
        localStorage.setItem("applied_coupon", JSON.stringify(result.coupon));
        localStorage.setItem("coupon_discount", result.discount.toString());
        localStorage.setItem("coupon_code", result.coupon.code);
      } else {
        setCouponError(result.message || "Không thể áp dụng voucher này");
        alert(result.message || "Không thể áp dụng voucher này");
      }
    } catch (error: any) {
      console.error("Error applying coupon:", error);
      const errorMessage = error.response?.data?.message || "Có lỗi xảy ra khi áp dụng mã giảm giá";
      setCouponError(errorMessage);
      alert(errorMessage);
    } finally {
      setIsApplyingCoupon(false);
    }
  };

  // Xóa voucher
  const handleRemoveCoupon = () => {
    setAppliedCoupon(null);
    setDiscount(0);
    setVoucher("");
    localStorage.removeItem("applied_coupon");
    localStorage.removeItem("coupon_discount");
    localStorage.removeItem("coupon_code");
  };

  // Tính tổng sau khi giảm giá
  const getFinalPrice = () => {
    return Math.max(0, getTotalPrice() - discount);
  };
  // Hàm xử lý cập nhật số lượng
  const handleUpdateQuantity = async (itemId: number, newQuantity: number) => {
    if (newQuantity < 1) {
      if (window.confirm("Bạn có muốn xóa sản phẩm này khỏi giỏ hàng không?")) {
        await removeCartItem(itemId);
      }
      return;
    }
    await updateCartItem(itemId, newQuantity);
  };

  // Hàm xử lý xóa sản phẩm
  const handleRemoveItem = async (itemId: number) => {
    if (window.confirm("Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng không?")) {
      await removeCartItem(itemId);
    }
  };

  // Hàm lấy URL ảnh đầy đủ
  const getImageUrl = (image: string | undefined | null) => {
    if (!image) return "https://via.placeholder.com/150?text=No+Image";
    if (image.startsWith("http")) return image;
    if (image.startsWith("/")) return `http://127.0.0.1:8000${image}`;
    return `http://127.0.0.1:8000/storage/${image}`;
  };


  if (items.length === 0) {
    return (
      <div className="main" style={{ maxWidth: "1200px", margin: "0 auto", padding: "40px 20px" }}>
        <div style={{ textAlign: "center", padding: "60px 20px", background: "#f9fafb", borderRadius: "12px" }}>
          <div style={{ fontSize: "64px", marginBottom: "20px" }}>🛒</div>
          <h1 style={{ marginBottom: "16px", color: "#1f2937" }}>Giỏ hàng của bạn đang trống</h1>
          <p style={{ color: "#6b7280", marginBottom: "32px", fontSize: "18px" }}>
            Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm
          </p>
          <Link
            to="/san-pham"
            style={{
              display: "inline-block",
              padding: "14px 32px",
              background: "#3b82f6",
              color: "white",
              textDecoration: "none",
              borderRadius: "8px",
              fontSize: "16px",
              fontWeight: "600",
              transition: "all 0.2s",
            }}
          >
            Tiếp tục mua sắm
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="main" style={{ maxWidth: "1200px", margin: "0 auto", padding: "40px 20px" }}>
      <h1 style={{
        marginBottom: "32px",
        fontSize: "32px",
        fontWeight: "700",
        color: "#1f2937"
      }}>
        Giỏ hàng của bạn ({items.length} {items.length === 1 ? "sản phẩm" : "sản phẩm"})
      </h1>

      <div style={{
        display: "grid",
        gridTemplateColumns: "1fr 400px",
        gap: "32px",
        alignItems: "start"
      }}>
        {/* Danh sách sản phẩm */}
        <div>
          {items.map((item) => (
            <div
              key={item.id}
              style={{
                background: "white",
                borderRadius: "12px",
                padding: "24px",
                marginBottom: "20px",
                boxShadow: "0 1px 3px rgba(0,0,0,0.1)",
                border: "1px solid #e5e7eb",
                display: "flex",
                gap: "20px",
                transition: "all 0.2s"
              }}
              onMouseEnter={(e) => {
                e.currentTarget.style.boxShadow = "0 4px 12px rgba(0,0,0,0.15)";
                e.currentTarget.style.transform = "translateY(-2px)";
              }}
              onMouseLeave={(e) => {
                e.currentTarget.style.boxShadow = "0 1px 3px rgba(0,0,0,0.1)";
                e.currentTarget.style.transform = "translateY(0)";
              }}
            >
              {/* Ảnh sản phẩm */}
              <div style={{ flexShrink: 0 }}>
                <img
                  src={getImageUrl(item.image)}
                  alt={item.name}
                  onError={(e) => {
                    e.currentTarget.src = "https://via.placeholder.com/150?text=No+Image";
                  }}
                  style={{
                    width: "120px",
                    height: "120px",
                    objectFit: "cover",
                    borderRadius: "8px",
                    border: "1px solid #e5e7eb"
                  }}
                />
              </div>

              {/* Thông tin sản phẩm */}
              <div style={{ flex: 1, display: "flex", flexDirection: "column", gap: "12px" }}>
                <div>
                  <h3 style={{
                    margin: 0,
                    marginBottom: "8px",
                    fontSize: "18px",
                    fontWeight: "600",
                    color: "#1f2937"
                  }}>
                    {item.name}
                  </h3>
                  {(item.color || item.size) && (
                    <div style={{ display: "flex", gap: "12px", flexWrap: "wrap" }}>
                      {item.color && (
                        <span style={{
                          padding: "4px 12px",
                          background: "#f3f4f6",
                          borderRadius: "6px",
                          fontSize: "14px",
                          color: "#6b7280"
                        }}>
                          Màu: {item.color}
                        </span>
                      )}
                      {item.size && (
                        <span style={{
                          padding: "4px 12px",
                          background: "#f3f4f6",
                          borderRadius: "6px",
                          fontSize: "14px",
                          color: "#6b7280"
                        }}>
                          Size: {item.size}
                        </span>
                      )}
                    </div>
                  )}
                </div>

                {/* Giá và số lượng */}
                <div style={{
                  display: "flex",
                  justifyContent: "space-between",
                  alignItems: "center",
                  marginTop: "auto"
                }}>
                  <div style={{ fontSize: "20px", fontWeight: "700", color: "#059669" }}>
                    {formatPrice(item.price)}
                  </div>

                  {/* Điều khiển số lượng */}
                  <div style={{
                    display: "flex",
                    alignItems: "center",
                    gap: "12px",
                    background: "#f9fafb",
                    padding: "6px",
                    borderRadius: "8px",
                    border: "1px solid #e5e7eb"
                  }}>
                    <button
                      onClick={() => handleUpdateQuantity(Number(item.id), item.quantity - 1)}
                      style={{
                        width: "32px",
                        height: "32px",
                        border: "none",
                        background: "white",
                        borderRadius: "6px",
                        cursor: "pointer",
                        fontSize: "18px",
                        fontWeight: "600",
                        color: "#6b7280",
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "center",
                        transition: "all 0.2s"
                      }}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.background = "#fee2e2";
                        e.currentTarget.style.color = "#dc2626";
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.background = "white";
                        e.currentTarget.style.color = "#6b7280";
                      }}
                    >
                      −
                    </button>
                    <span style={{
                      minWidth: "40px",
                      textAlign: "center",
                      fontSize: "16px",
                      fontWeight: "600",
                      color: "#1f2937"
                    }}>
                      {item.quantity}
                    </span>
                    <button
                      onClick={() => handleUpdateQuantity(Number(item.id), item.quantity + 1)}
                      style={{
                        width: "32px",
                        height: "32px",
                        border: "none",
                        background: "white",
                        borderRadius: "6px",
                        cursor: "pointer",
                        fontSize: "18px",
                        fontWeight: "600",
                        color: "#6b7280",
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "center",
                        transition: "all 0.2s"
                      }}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.background = "#dbeafe";
                        e.currentTarget.style.color = "#2563eb";
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.background = "white";
                        e.currentTarget.style.color = "#6b7280";
                      }}
                    >
                      +
                    </button>
                  </div>
                </div>
              </div>

              {/* Nút xóa */}
              <div style={{ flexShrink: 0 }}>
                <button
                  onClick={() => handleRemoveItem(Number(item.id))}
                  style={{
                    padding: "8px 16px",
                    border: "none",
                    background: "#fee2e2",
                    color: "#dc2626",
                    borderRadius: "8px",
                    cursor: "pointer",
                    fontSize: "14px",
                    fontWeight: "600",
                    transition: "all 0.2s"
                  }}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.background = "#fecaca";
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.background = "#fee2e2";
                  }}
                >
                  🗑️ Xóa
                </button>
              </div>
            </div>
          ))}
        </div>

        {/* Tóm tắt đơn hàng */}
        
        <div style={{
          position: "sticky",
          top: "20px",
          background: "white",
          borderRadius: "12px",
          padding: "24px",
          boxShadow: "0 1px 3px rgba(0,0,0,0.1)",
          border: "1px solid #e5e7eb",
          height: "fit-content"
        }}>
          <h2 style={{
            margin: 0,
            marginBottom: "24px",
            fontSize: "20px",
            fontWeight: "700",
            color: "#1f2937"
          }}>
            Tóm tắt đơn hàng
          </h2>

          {/* Danh sách voucher */}
          <div style={{ marginBottom: "20px" }}>
            <label style={{ display: "block", marginBottom: "8px", fontWeight: "500" }}>
              Mã giảm giá
            </label>
            {appliedCoupon ? (
              <div style={{
                padding: "12px",
                background: "#d1fae5",
                borderRadius: "6px",
                border: "1px solid #10b981",
                marginBottom: "12px"
              }}>
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                  <div>
                    <div style={{ fontWeight: "600", color: "#059669", marginBottom: "4px" }}>
                      ✓ Đã chọn: {appliedCoupon.code}
                    </div>
                    <div style={{ fontSize: "12px", color: "#047857" }}>
                      {appliedCoupon.type === "percent"
                        ? `Giảm ${appliedCoupon.value}%`
                        : `Giảm ${formatPrice(appliedCoupon.value)}`}
                      {appliedCoupon.max_discount && appliedCoupon.type === "percent" && (
                        <span> (tối đa {formatPrice(appliedCoupon.max_discount)})</span>
                      )}
                    </div>
                  </div>
                  <button
                    onClick={handleRemoveCoupon}
                    style={{
                      padding: "6px 12px",
                      background: "#ef4444",
                      color: "white",
                      border: "none",
                      borderRadius: "4px",
                      cursor: "pointer",
                      fontSize: "12px"
                    }}
                  >
                    ✕
                  </button>
                </div>
              </div>
            ) : null}

            {/* Danh sách voucher có sẵn */}
            {loadingCoupons ? (
              <div style={{ textAlign: "center", padding: "20px", color: "#6b7280" }}>
                Đang tải voucher...
              </div>
            ) : availableCoupons.length === 0 ? (
              <div style={{ 
                padding: "12px", 
                background: "#f3f4f6", 
                borderRadius: "6px",
                textAlign: "center",
                color: "#6b7280",
                fontSize: "14px"
              }}>
                Hiện không có voucher nào khả dụng
              </div>
            ) : (
              <div style={{ 
                maxHeight: "200px", 
                overflowY: "auto",
                display: "flex",
                flexDirection: "column",
                gap: "8px"
              }}>
                {availableCoupons.map((coupon) => {
                  const isSelected = appliedCoupon?.id === coupon.id;
                  const isDisabled = !coupon.is_applicable;
                  
                  return (
                    <button
                      key={coupon.id}
                      onClick={() => !isDisabled && handleSelectCoupon(coupon)}
                      disabled={isDisabled || isApplyingCoupon}
                      style={{
                        padding: "12px",
                        background: isSelected 
                          ? "#d1fae5" 
                          : isDisabled 
                            ? "#f3f4f6" 
                            : "white",
                        border: isSelected 
                          ? "2px solid #10b981" 
                          : "1px solid #e5e7eb",
                        borderRadius: "6px",
                        cursor: isDisabled ? "not-allowed" : "pointer",
                        textAlign: "left",
                        transition: "all 0.2s",
                        opacity: isDisabled ? 0.6 : 1
                      }}
                      onMouseEnter={(e) => {
                        if (!isDisabled && !isSelected) {
                          e.currentTarget.style.background = "#f0fdf4";
                          e.currentTarget.style.borderColor = "#10b981";
                        }
                      }}
                      onMouseLeave={(e) => {
                        if (!isDisabled && !isSelected) {
                          e.currentTarget.style.background = "white";
                          e.currentTarget.style.borderColor = "#e5e7eb";
                        }
                      }}
                    >
                      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                        <div style={{ flex: 1 }}>
                          <div style={{ 
                            fontWeight: "600", 
                            color: isSelected ? "#059669" : "#1f2937",
                            marginBottom: "4px",
                            fontSize: "14px"
                          }}>
                            {isSelected && "✓ "}{coupon.code}
                          </div>
                          <div style={{ 
                            fontSize: "12px", 
                            color: isDisabled ? "#9ca3af" : "#6b7280"
                          }}>
                            {coupon.description || (
                              <>
                                {coupon.type === "percent"
                                  ? `Giảm ${coupon.value}%`
                                  : `Giảm ${formatPrice(coupon.value)}`}
                                {coupon.estimated_discount && (
                                  <span style={{ marginLeft: "8px", color: "#059669", fontWeight: "500" }}>
                                    (Tiết kiệm ~{formatPrice(coupon.estimated_discount)})
                                  </span>
                                )}
                              </>
                            )}
                          </div>
                        </div>
                        {coupon.estimated_discount && !isDisabled && (
                          <div style={{
                            padding: "4px 8px",
                            background: "#10b981",
                            color: "white",
                            borderRadius: "4px",
                            fontSize: "12px",
                            fontWeight: "600"
                          }}>
                            -{formatPrice(coupon.estimated_discount)}
                          </div>
                        )}
                      </div>
                    </button>
                  );
                })}
              </div>
            )}
            {couponError && (
              <div style={{ color: "#ef4444", fontSize: "12px", marginTop: "8px" }}>
                {couponError}
              </div>
            )}
          </div>

          
          <div style={{ borderBottom: "1px solid #e5e7eb", paddingBottom: "16px", marginBottom: "16px" }}>
            <div style={{ display: "flex", justifyContent: "space-between", marginBottom: "12px" }}>
              <span style={{ color: "#6b7280" }}>Tạm tính:</span>
              <span style={{ fontWeight: "600" }}>{formatPrice(getTotalPrice())}</span>
            </div>
            {discount > 0 && (
              <div style={{ display: "flex", justifyContent: "space-between", marginBottom: "12px" }}>
                <span style={{ color: "#6b7280" }}>Giảm giá:</span>
                <span style={{ fontWeight: "600", color: "#ef4444" }}>
                  -{formatPrice(discount)}
                </span>
              </div>
            )}
            <div style={{ display: "flex", justifyContent: "space-between", marginBottom: "12px" }}>
              <span style={{ color: "#6b7280" }}>Phí vận chuyển:</span>
              <span style={{ fontWeight: "600", color: "#059669" }}>Miễn phí</span>
            </div>
          </div>

          <div style={{ display: "flex", justifyContent: "space-between", marginBottom: "24px", paddingTop: "16px", borderTop: "2px solid #e5e7eb" }}>
            <span style={{ fontSize: "18px", fontWeight: "700", color: "#1f2937" }}>Tổng cộng:</span>
            <span style={{ fontSize: "24px", fontWeight: "700", color: "#059669" }}>
              {formatPrice(getFinalPrice())}
            </span>
          </div>

          <Link
            to="/thanh-toan"
            style={{
              display: "block",
              width: "100%",
              padding: "16px",
              background: "#3b82f6",
              color: "white",
              textDecoration: "none",
              borderRadius: "8px",
              fontSize: "16px",
              fontWeight: "600",
              textAlign: "center",
              transition: "all 0.2s",
              marginBottom: "12px"
            }}
            onMouseEnter={(e) => {
              e.currentTarget.style.background = "#2563eb";
              e.currentTarget.style.transform = "translateY(-2px)";
              e.currentTarget.style.boxShadow = "0 4px 12px rgba(59, 130, 246, 0.4)";
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.background = "#3b82f6";
              e.currentTarget.style.transform = "translateY(0)";
              e.currentTarget.style.boxShadow = "none";
            }}
          >
            Thanh toán
          </Link>

          <Link
            to="/san-pham"
            style={{
              display: "block",
              width: "100%",
              padding: "16px",
              background: "white",
              color: "#3b82f6",
              textDecoration: "none",
              borderRadius: "8px",
              fontSize: "16px",
              fontWeight: "600",
              textAlign: "center",
              border: "2px solid #3b82f6",
              transition: "all 0.2s"
            }}
            onMouseEnter={(e) => {
              e.currentTarget.style.background = "#eff6ff";
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.background = "white";
            }}
          >
            Tiếp tục mua sắm
          </Link>
        </div>
      </div>
    </div>
  );
}
