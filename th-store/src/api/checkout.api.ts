import api from "./api";

export interface CheckoutPayload {
  address_id?: number;
  full_name?: string;
  phone?: string;
  address?: string;
  city?: string;
  province?: string;
  province_id?: number;
  district_id?: number;
  ward_id?: number;
  payment_method?: string;
  coupon_code?: string;
}



export interface CheckoutResponse {
  message: string;
  order: any;
  qr_code?: {
    data: string;
    image_url: string;
    bank_account: string;
    bank_name: string;
    account_name: string;
    amount: number;
    order_id: number;
  };
}

export const checkout = async (payload: CheckoutPayload): Promise<CheckoutResponse> => {
  const res = await api.post("/checkout", payload, { withCredentials: true });
  return res.data;
};
