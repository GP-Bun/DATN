
const Dashboard = () => {
  return (
    <div>
      <div className="admin-page-header">
        <h1 className="admin-page-title">Tổng quan hệ thống</h1>
        <p className="admin-page-subtitle">Chào mừng bạn đến với trang quản trị TH Store 👋</p>
      </div>
      
      <div className="admin-stats-grid">
        <div className="admin-stat-card">
          <h3 className="admin-stat-title">Tổng sản phẩm</h3>
          <p className="admin-stat-value blue">156</p>
        </div>
        <div className="admin-stat-card">
          <h3 className="admin-stat-title">Đơn hàng hôm nay</h3>
          <p className="admin-stat-value green">23</p>
        </div>
        <div className="admin-stat-card">
          <h3 className="admin-stat-title">Khách hàng</h3>
          <p className="admin-stat-value purple">1,234</p>
        </div>
        <div className="admin-stat-card">
          <h3 className="admin-stat-title">Doanh thu tháng</h3>
          <p className="admin-stat-value orange">45.2M</p>
        </div>
      </div>

      <div className="admin-card">
        <div className="admin-card-header">
          <h3 className="admin-card-title">Hoạt động gần đây</h3>
          <button className="admin-btn">Xem tất cả</button>
        </div>
        <div className="admin-card-content">
          <p>Đây là nơi hiển thị các hoạt động gần đây của hệ thống...</p>
        </div>
      </div>
    </div>
  )
}

export default Dashboard
  