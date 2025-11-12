// src/admin/AdminLayout.tsx
import React from "react";
import Sidebar from "./components/Sidebar";
import Navbartsx from "./components/Navbar.tsx";
import { Outlet } from "react-router-dom";

const AdminLayout: React.FC = () => {
  return (
    <div className="admin-layout">
      <Sidebar />
      <div className="admin-main">
        <Navbartsx />
        <div className="admin-content">
          <Outlet />
        </div>
      </div>
    </div>
  );
};

export default AdminLayout;
