import axios from "axios";

// Tạo instance Axios cho admin API
const adminApi = axios.create({
  baseURL: "http://127.0.0.1:8000/api",
  withCredentials: true,
});

// Thêm token vào header
adminApi.interceptors.request.use((config) => {
  const token = localStorage.getItem("admin_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export interface Review {
  id: number;
  product_id: number;
  user_id: number;
  user_name: string;
  user_email: string;
  rating: number;
  comment: string | null;
  status: number;
  created_at: string;
  updated_at: string;
  user?: {
    id: number;
    name: string;
    email: string;
  };
  product?: {
    id: number;
    name: string;
    thumbnail?: string;
  };
}

export interface ReviewsResponse {
  reviews: {
    data: Review[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
}

export interface ReviewStats {
  total: number;
  active: number;
  inactive: number;
  average_rating: number;
  rating_stats: Record<number, number>;
}

// Lấy danh sách reviews với filter và pagination
export const getAdminReviews = async (params?: {
  status?: string;
  product_id?: number;
  user_id?: number;
  search?: string;
  sort_by?: string;
  sort_order?: string;
  page?: number;
  per_page?: number;
}): Promise<ReviewsResponse> => {
  const res = await adminApi.get("/admin/reviews", { params });
  return res.data;
};

// Lấy chi tiết một review
export const getAdminReview = async (id: number): Promise<{ review: Review }> => {
  const res = await adminApi.get(`/admin/reviews/${id}`);
  return res.data;
};

// Cập nhật trạng thái review
export const updateReviewStatus = async (
  id: number,
  status: 0 | 1
): Promise<{ message: string; review: Review }> => {
  const res = await adminApi.put(`/admin/reviews/${id}/status`, { status });
  return res.data;
};

// Xóa review
export const deleteReview = async (id: number): Promise<{ message: string }> => {
  const res = await adminApi.delete(`/admin/reviews/${id}`);
  return res.data;
};

// Lấy thống kê reviews
export const getReviewStats = async (): Promise<ReviewStats> => {
  const res = await adminApi.get("/admin/reviews/stats");
  return res.data;
};

