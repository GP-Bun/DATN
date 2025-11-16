// src/admin/AdminRoute.tsx
import React from "react";
import { Navigate } from "react-router-dom";
import { useAuth } from "../store/AuthContext";

interface AdminRouteProps {
  children: React.ReactNode;
}

const AdminRoute: React.FC<AdminRouteProps> = ({ children }) => {
  const { admin, loading } = useAuth();

  if (loading) return <div>Loading...</div>;
  if (!admin) return <Navigate to="/admin/login" replace />;

  return <>{children}</>;
};

export default AdminRoute;
