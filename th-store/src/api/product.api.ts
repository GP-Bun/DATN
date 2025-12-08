import api from "./api";

// Lấy danh sách sản phẩm
export const getProducts = (page = 1) =>
  api.get(`/products?page=${page}`);

// Lấy chi tiết 1 sản phẩm
export const getProductDetail = (id: number) =>
  api.get(`/products/${id}`);
