import api from "./api";

export const checkout = async (payload: {
  address_id?: number;
  full_name?: string;
  phone?: string;
  address?: string;
  city?: string;
  payment_method?: string;
}) => {
  const res = await api.post("/checkout", payload, { withCredentials: true });
  return res.data;
};
