import api from "./api";

export const getProductDetail = async (id: number | string) => {
  const res = await api.get(`/products/${id}`);

  // Laravel trả về object trực tiếp nên chỉ cần res.data
  return res.data;
};
