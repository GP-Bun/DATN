import api from "./api";

// GET giỏ hàng
export const getCart = async () => {
  const res = await api.get("/cart", { withCredentials: true });
  // Backend trả về { items: [...], total: ... }
  return res.data.items || res.data.data || [];
};

// POST: thêm sản phẩm vào giỏ
export const addToCart = async (productId: number, quantity = 1, variantId?: number | null) => {
  const payload: any = { 
    product_id: productId, 
    quantity 
  };
  
  if (variantId) {
    payload.variant_id = variantId;
  }
  
  const res = await api.post(
    "/cart",
    payload,
    { withCredentials: true }
  );
  return res.data.data;
};

// PUT: cập nhật số lượng
export const updateCartItem = async (itemId: number, quantity: number) => {
  const res = await api.put(
    `/cart/${itemId}`,
    { quantity },
    { withCredentials: true }
  );
  return res.data.data;
};

// DELETE: xóa 1 item
export const removeCartItem = async (itemId: number) => {
  const res = await api.delete(`/cart/${itemId}`, {
    withCredentials: true,
  });
  return res.data.data;
};

// DELETE: xóa toàn bộ giỏ
export const clearCart = async () => {
  const res = await api.delete("/cart", { withCredentials: true });
  return res.data.data;
};
