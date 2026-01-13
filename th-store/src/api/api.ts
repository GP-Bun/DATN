import axios from "axios";

// Tạo instance Axios để gọi Laravel API
const api = axios.create({
  baseURL: "http://127.0.0.1:8000/api",
  withCredentials: true,   // <--- Bun phải thêm dòng này ở đây
});

// Thêm token vào header nếu user đã đăng nhập
// Kiểm tra cả localStorage và sessionStorage
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("user_token") || sessionStorage.getItem("user_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Interceptor response để xử lý lỗi 401 (Unauthorized)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Xóa token khi token hết hạn hoặc không hợp lệ
      localStorage.removeItem("user_token");
      sessionStorage.removeItem("user_token");
      // Chỉ redirect nếu đang ở trang cần authentication
      if (window.location.pathname !== '/dang-nhap' && window.location.pathname !== '/dang-ky') {
        window.location.href = '/dang-nhap';
      }
    }
    return Promise.reject(error);
  }
);

export default api;
