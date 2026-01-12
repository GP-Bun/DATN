import api from "./api";

export interface Review {
  id: number;
  product_id: number;
  user_id: number | null;
  user_name: string;
  user_email: string | null;
  rating: number;
  comment: string | null;
  status: number;
  created_at: string;
  updated_at: string;
  user?: {
    id: number;
    name: string;
  };
}

export interface ReviewResponse {
  reviews: Review[];
  average_rating: number;
  total_reviews: number;
  rating_counts: Record<number, number>;
}

export interface CreateReviewData {
  rating: number;
  comment?: string;
}

// Lấy danh sách đánh giá của sản phẩm
export const getProductReviews = async (productId: number | string): Promise<ReviewResponse> => {
  const res = await api.get(`/products/${productId}/reviews`);
  return res.data;
};

// Thêm đánh giá mới
export const createReview = async (
  productId: number | string,
  data: CreateReviewData
): Promise<{ message: string; review: Review }> => {
  const res = await api.post(`/products/${productId}/reviews`, data);
  return res.data;
};

// Xóa đánh giá
export const deleteReview = async (productId: number | string, reviewId: number): Promise<void> => {
  await api.delete(`/products/${productId}/reviews/${reviewId}`);
};

