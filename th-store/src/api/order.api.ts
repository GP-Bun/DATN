import api from "./api";

export const createOrder = async (data: any) => {
  const res = await api.post("/orders", data);
  return res.data.data;
};
