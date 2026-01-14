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
  cart_item_ids?: number[];
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
  vnpay?: {
    payment_url: string;
    txn_ref: string;
    amount: number;
  };
}

export const checkout = async (payload: CheckoutPayload): Promise<CheckoutResponse> => {
  const res = await api.post("/checkout", payload, { withCredentials: true });
  return res.data;
};
