// src/admin/PrivateRoute.tsx
import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';

const PrivateRoute: React.FC = () => {
  // Lấy token admin từ localStorage
  const token = localStorage.getItem('admin_token');

  // Nếu không có token → redirect về login
  if (!token) {
    return <Navigate to="/admin/login" replace />;
  }

  // Nếu có token → render Outlet (page con)
  return <Outlet />;
};

export default PrivateRoute;
