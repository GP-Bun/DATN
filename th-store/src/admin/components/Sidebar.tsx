import { NavLink } from "react-router-dom";

const Sidebar = () => {
  const menu = [
    { name: "Dashboard", path: "/admin", icon: "📊" },
    { name: "Sản phẩm", path: "/admin/products", icon: "📦" },
    { name: "Đơn hàng", path: "/admin/orders", icon: "📋" },
    { name: "Người dùng", path: "/admin/users", icon: "👥" },
  ];

  return (
    <div className="admin-sidebar">
      <div className="admin-sidebar-header">
        <h2>TH Store Admin</h2>
      </div>
      <nav className="admin-sidebar-nav">
        {menu.map((item) => (
          <NavLink
            key={item.path}
            to={item.path}
            className={({ isActive }) =>
              `admin-nav-item ${isActive ? "active" : ""}`
            }
          >
            <span style={{ marginRight: "12px" }}>{item.icon}</span>
            {item.name}
          </NavLink>
        ))}
      </nav>
    </div>
  );
};

export default Sidebar;
