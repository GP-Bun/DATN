import api from "./api";

export const createOrder = async (data: any) => {
  const res = await api.post("/orders", data);
  return res.data.data;
};
export const cancelOrder = async (orderId: number) => {
  const res = await api.post(`/orders/${orderId}/cancel`);
  return res.data;
};

export const deleteOrder = async (orderId: number) => {
  const res = await api.delete(`/orders/${orderId}`);
  return res.data;
};
