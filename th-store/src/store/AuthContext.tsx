// src/store/AuthContext.tsx
import React, { createContext, useContext, useMemo, useState, useEffect } from "react";
import axios, { AxiosError } from "axios";

// ==================== Types ====================
type User = { id: number; name: string; email: string };
type Admin = { id: number; name: string; email: string };

type AuthContextValue = {
  user: User | null;
  admin: Admin | null;
  loading: boolean;
  loginUser: (email: string, password: string, remember?: boolean) => Promise<void>;
  registerUser: (name: string, email: string, password: string) => Promise<void>;
  loginAdmin: (email: string, password: string, remember?: boolean) => Promise<void>;
  registerAdmin: (name: string, email: string, password: string) => Promise<void>;
  logoutUser: () => void;
  logoutAdmin: () => void;
};

// ==================== Context ====================
const AuthContext = createContext<AuthContextValue | null>(null);
export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}

// ==================== Axios Instances ====================
const userApi = axios.create({ baseURL: "http://127.0.0.1:8000/api" });
const adminApi = axios.create({ baseURL: "http://127.0.0.1:8000/api" });

// Interceptor user token
userApi.interceptors.request.use((config) => {
  const token = localStorage.getItem("user_token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Interceptor admin token
adminApi.interceptors.request.use((config) => {
  const token = localStorage.getItem("admin_token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// ==================== Provider ====================
export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [admin, setAdmin] = useState<Admin | null>(null);
  const [loading, setLoading] = useState(true);

  // -------------------- User --------------------
  const loginUser = async (email: string, password: string, remember = false) => {
    try {
      const res = await userApi.post<{ user: User; access_token: string }>("/login", { email, password });
      setUser(res.data.user);

      if (remember) {
        localStorage.setItem("user_token", res.data.access_token);
      }
      // nếu không tick remember thì không lưu token → reload sẽ đăng xuất
    } catch (err: any) {
      throw err.response?.data || { message: "Login User thất bại" };
    }
  };

  const registerUser = async (name: string, email: string, password: string) => {
    try {
      await userApi.post("/register", { name, email, password });
    } catch (err: any) {
      throw err.response?.data || { message: "Đăng ký User thất bại" };
    }
  };

  const logoutUser = () => {
    setUser(null);
    localStorage.removeItem("user_token");
  };

  // -------------------- Admin --------------------
  const loginAdmin = async (email: string, password: string, remember = false) => {
    try {
      const res = await adminApi.post<{ admin: Admin; access_token: string }>("/admin/login", { email, password });
      setAdmin(res.data.admin);
      if (remember) localStorage.setItem("admin_token", res.data.access_token);
    } catch (err: any) {
      if (axios.isAxiosError(err)) throw err.response?.data || { message: "Login Admin thất bại" };
      throw { message: "Login Admin thất bại" };
    }
  };

  const registerAdmin = async (name: string, email: string, password: string) => {
    try {
      await adminApi.post("/admin/register", { name, email, password });
    } catch (err: any) {
      if (axios.isAxiosError(err)) throw err.response?.data || { message: "Đăng ký Admin thất bại" };
      throw { message: "Đăng ký Admin thất bại" };
    }
  };

  const logoutAdmin = () => {
    setAdmin(null);
    localStorage.removeItem("admin_token");
  };

  // -------------------- Init Auth --------------------
  useEffect(() => {
    const initAuth = async () => {
      const userToken = localStorage.getItem("user_token");
      const adminToken = localStorage.getItem("admin_token");

      if (userToken) {
        try {
          const res = await userApi.get<{ user: User }>("/user-profile");
          setUser(res.data.user);
        } catch {
          localStorage.removeItem("user_token");
        }
      }

      if (adminToken) {
        try {
          const res = await adminApi.get<{ admin: Admin }>("/admin/profile");
          setAdmin(res.data.admin);
        } catch {
          localStorage.removeItem("admin_token");
        }
      }

      setLoading(false);
    };

    initAuth();
  }, []);

  const value = useMemo(
    () => ({ user, admin, loading, loginUser, registerUser, loginAdmin, registerAdmin, logoutUser, logoutAdmin }),
    [user, admin, loading]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
