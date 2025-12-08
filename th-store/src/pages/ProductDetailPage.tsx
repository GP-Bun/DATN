import { useParams } from "react-router-dom";
import { useState, useEffect } from "react";
import { getProductDetail } from "../api/productDetail.api";
import { addToCart } from "../api/cart.api";

export default function ProductDetailPage() {
  const { id } = useParams();

  const [product, setProduct] = useState<any>(null);
  const [quantity, setQuantity] = useState(1);

  const [selectedColor, setSelectedColor] = useState<number | null>(null);
  const [selectedSize, setSelectedSize] = useState<number | null>(null);

  const [loading, setLoading] = useState(true);
  const [isAdding, setIsAdding] = useState(false);

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

  return (
    <div className="main">
      <div className="product-detail-container">
        <div className="product-detail">

          {/* ẢNH */}
          <div className="product-image-section">
            <div className="product-image-wrapper">
              <img
                src={product.thumbnail}
                alt={product.name}
                className="product-main-image"
              />
            </div>
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
                <h3 className="variant-title">Màu sắc</h3>
                <div className="color-options" style={{ display: "flex", gap: "10px" }}>
                  {product.colors.map((c: any) => (
                    <button
                      key={c.id}
                      onClick={() => setSelectedColor(c.id)}
                      className={`color-option ${selectedColor === c.id ? "selected" : ""}`}
                      style={{
                        width: "35px",
                        height: "35px",
                        borderRadius: "50%",
                        border: selectedColor === c.id ? "3px solid black" : "1px solid #ccc",
                        backgroundColor: c.hex ?? "#fff",
                        cursor: "pointer",
                      }}
                    >
                      {!c.hex && <span style={{ fontSize: "12px" }}>{c.name}</span>}
                    </button>
                  ))}
                </div>
              </div>
            )}

            {/* SIZE */}
            {product.sizes?.length > 0 && (
              <div className="variant-section">
                <h3 className="variant-title">Kích thước</h3>
                <div className="size-options" style={{ display: "flex", gap: "10px" }}>
                  {product.sizes.map((s: any) => {
                    const variantForSize = product.variants?.find(
                      (v: any) => v.size_id === s.id && v.color_id === selectedColor
                    );
                    return (
                      <button
                        key={s.id}
                        onClick={() => setSelectedSize(s.id)}
                        disabled={!variantForSize || variantForSize.stock === 0}
                        className={`size-option ${selectedSize === s.id ? "selected" : ""}`}
                        style={{
                          padding: "8px 14px",
                          borderRadius: "6px",
                          border: selectedSize === s.id ? "2px solid black" : "1px solid #ccc",
                          background: selectedSize === s.id ? "black" : "white",
                          color: selectedSize === s.id ? "white" : "black",
                          cursor: variantForSize?.stock > 0 ? "pointer" : "not-allowed",
                        }}
                      >
                        {s.name} ({variantForSize?.stock ?? 0} sp)
                      </button>
                    );
                  })}
                </div>
              </div>
            )}

            {/* TỒN KHO */}
            {selectedVariant && (
              <div
                className="stock-info"
                style={{
                  marginTop: "15px",
                  padding: "10px 15px",
                  borderRadius: "8px",
                  backgroundColor: selectedVariant.stock > 0 ? "#e6ffed" : "#ffe6e6",
                  color: selectedVariant.stock > 0 ? "#1a7f37" : "#b30000",
                  fontWeight: "bold",
                  display: "inline-flex",
                  alignItems: "center",
                  gap: "8px"
                }}
              >
                <span style={{ fontSize: "18px" }}>
                  {selectedVariant.stock > 0 ? "✅" : "❌"}
                </span>
                <span>
                  {selectedVariant.stock > 0
                    ? `Còn ${selectedVariant.stock} sản phẩm trong kho`
                    : "Hết hàng"}
                </span>
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
      </div>
    </div>
  );
}
