import api from "./api";

// Lấy giỏ hàng
export const getCart = async () => {
  const res = await api.get("/cart");
  return res.data.data;
};

// Thêm sản phẩm vào giỏ
export const addToCart = async (productId: number, quantity = 1, color: string | null = null, size: string | null = null) => {
  const res = await api.post("/cart/add", {
    product_id: productId,
    quantity,
    color,
    size
  });
  return res.data.data;
};

// Cập nhật số lượng
export const updateCartItem = async (itemId: string, quantity: number) => {
  const res = await api.put(`/cart/update/${itemId}`, {
    quantity
  });
  return res.data.data;
};

// Xóa 1 sản phẩm trong giỏ
export const removeCartItem = async (itemId: string) => {
  const res = await api.delete(`/cart/remove/${itemId}`);
  return res.data.data;
};

// Xóa tất cả sản phẩm
export const clearCart = async () => {
  const res = await api.delete("/cart/clear");
  return res.data.data;
};
