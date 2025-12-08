import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import {
  getCart,
  updateCartItem,
  removeCartItem,
} from "../api/cart.api";

export default function CartPage() {
  const [items, setItems] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  // Lấy giỏ hàng từ API
  const loadCart = async () => {
    try {
      setLoading(true);
      const res = await getCart();

      // Nếu backend trả dạng { data: [...] }
      setItems(res.data || []);

      // Nếu backend trả dạng { items: [...] } -> đổi theo backend thật
      // setItems(res.items);
    } catch (error) {
      console.error("Lỗi lấy giỏ hàng:", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadCart();
  }, []);

  // Tăng giảm số lượng
  const handleUpdateQuantity = async (cartItemId: number, newQty: number) => {
    if (newQty <= 0) return;
    try {
      await updateCartItem(cartItemId.toString(), newQty);
      loadCart(); // load lại
    } catch (error) {
      console.error("Lỗi cập nhật số lượng:", error);
    }
  };

  // Xóa item
  const handleRemoveItem = async (cartItemId: number) => {
    try {
      await removeCartItem(cartItemId.toString());
      loadCart();
    } catch (error) {
      console.error("Lỗi xóa item:", error);
    }
  };

  // Tính tổng tiền
  const getTotalPrice = () => {
    return items.reduce(
      (sum, item) => sum + item.product.price * item.quantity,
      0
    );
  };

  if (loading) {
    return (
      <div className="main">
        <h1>Giỏ hàng</h1>
        <p>Đang tải dữ liệu...</p>
      </div>
    );
  }

  if (items.length === 0) {
    return (
      <div className="main">
        <h1>Giỏ hàng</h1>
        <p>Giỏ hàng của bạn đang trống</p>
        <Link to="/san-pham">Tiếp tục mua sắm</Link>
      </div>
    );
  }

  return (
    <div className="main">
      <h1>Giỏ hàng</h1>

      <div className="cart-items">
        {items.map((item) => (
          <div key={item.id} className="cart-item">
            <img
              src={item.product.image}
              alt={item.product.name}
              className="cart-item-image"
            />

            <div className="cart-item-info">
              <h3>{item.product.name}</h3>
            </div>

            <div className="cart-item-price">
              <p>{item.product.price.toLocaleString("vi-VN")}đ</p>

              <div className="quantity-controls">
                <button
                  onClick={() =>
                    handleUpdateQuantity(item.id, item.quantity - 1)
                  }
                >
                  -
                </button>

                <span>{item.quantity}</span>

                <button
                  onClick={() =>
                    handleUpdateQuantity(item.id, item.quantity + 1)
                  }
                >
                  +
                </button>
              </div>

              <button
                className="remove-btn"
                onClick={() => handleRemoveItem(item.id)}
              >
                Xóa
              </button>
            </div>
          </div>
        ))}
      </div>

      <div className="cart-summary">
        <div className="cart-total">
          Tổng cộng: {getTotalPrice().toLocaleString("vi-VN")}đ
        </div>

        <div className="cart-actions">
          <Link to="/thanh-toan" className="checkout-btn">
            Thanh toán
          </Link>
        </div>
      </div>
    </div>
  );
}
