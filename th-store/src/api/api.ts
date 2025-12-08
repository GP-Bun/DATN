import axios from "axios";

// Tạo instance Axios để gọi Laravel API
const api = axios.create({
  baseURL: "http://127.0.0.1:8000/api", 
});

// (Tùy chọn) Nếu bạn có token đăng nhập:
// api.interceptors.request.use((config) => {
//   const token = localStorage.getItem("token");
//   if (token) {
//     config.headers.Authorization = `Bearer ${token}`;
//   }
//   return config;
// });

export default api;
