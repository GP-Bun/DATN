import api from "./api";

export interface Coupon {
  id: number;
  code: string;
  type: "percent" | "fixed";
  value: number;
  min_order_amount: number | null;
  max_discount: number | null;
  estimated_discount?: number;
  is_applicable?: boolean;
  description?: string;
}

export interface ApplyCouponResponse {
  ok: boolean;
  message?: string;
  discount?: number;
  final_amount?: number;
  coupon?: Coupon;
}

export interface AvailableCouponsResponse {
  coupons: Coupon[];
  count: number;
}

// Lấy danh sách voucher có sẵn
export const getAvailableCoupons = async (
  amount: number = 0
): Promise<AvailableCouponsResponse> => {
  const res = await api.get("/coupons/available", { params: { amount } });
  return res.data;
};

// Áp dụng mã giảm giá
export const applyCoupon = async (
  code: string,
  amount: number
): Promise<ApplyCouponResponse> => {
  const res = await api.post("/coupons/apply", { code, amount });
  return res.data;
};

