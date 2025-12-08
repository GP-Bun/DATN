import api from "./api";

export const register = (data: any) => api.post("/register", data);

export const login = (data: any) => api.post("/login", data);
