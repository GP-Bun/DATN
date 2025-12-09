import { Link } from "react-router-dom";
import { useCart } from "../store/CartContext";

export default function CartPage() {
  const { items, updateCartItem, removeCartItem, getTotalPrice, reloadCart } = useCart();

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

  // Hàm format giá tiền theo chuẩn Việt Nam
  const formatPrice = (price: number) => {
    // Làm tròn về số nguyên và format với dấu chấm ngăn cách hàng nghìn
    return Math.round(price).toLocaleString('vi-VN') + 'đ';
  };

  if (items.length === 0) {
    return (
      <div className="main" style={{ maxWidth: "1200px", margin: "0 auto", padding: "40px 20px" }}>
        <div style={{ 
          textAlign: "center", 
          padding: "60px 20px",
          background: "#f9fafb",
          borderRadius: "12px"
        }}>
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
              transition: "all 0.2s"
            }}
            onMouseEnter={(e) => e.currentTarget.style.background = "#2563eb"}
            onMouseLeave={(e) => e.currentTarget.style.background = "#3b82f6"}
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

          <div style={{ 
            borderBottom: "1px solid #e5e7eb", 
            paddingBottom: "16px",
            marginBottom: "16px"
          }}>
            <div style={{ 
              display: "flex", 
              justifyContent: "space-between", 
              marginBottom: "12px"
            }}>
              <span style={{ color: "#6b7280" }}>Tạm tính:</span>
              <span style={{ fontWeight: "600" }}>
                {formatPrice(getTotalPrice())}
              </span>
            </div>
            <div style={{ 
              display: "flex", 
              justifyContent: "space-between",
              marginBottom: "12px"
            }}>
              <span style={{ color: "#6b7280" }}>Phí vận chuyển:</span>
              <span style={{ fontWeight: "600", color: "#059669" }}>Miễn phí</span>
            </div>
          </div>

          <div style={{ 
            display: "flex", 
            justifyContent: "space-between",
            marginBottom: "24px",
            paddingTop: "16px",
            borderTop: "2px solid #e5e7eb"
          }}>
            <span style={{ fontSize: "18px", fontWeight: "700", color: "#1f2937" }}>
              Tổng cộng:
            </span>
            <span style={{ 
              fontSize: "24px", 
              fontWeight: "700", 
              color: "#059669"
            }}>
              {formatPrice(getTotalPrice())}
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
