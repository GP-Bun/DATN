
const Orders = () => {
  return (
    <div>
      <div className="admin-page-header">
        <h1 className="admin-page-title">Quản lý đơn hàng</h1>
        <p className="admin-page-subtitle">Theo dõi và quản lý tất cả đơn hàng của khách hàng</p>
      </div>
      
      <div className="admin-card">
        <div className="admin-card-header">
          <h3 className="admin-card-title">Danh sách đơn hàng</h3>
          <button className="admin-btn">Xuất báo cáo</button>
        </div>
        <div className="admin-card-content">
          <table className="admin-table">
            <thead>
              <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>#001</td>
                <td>Nguyễn Văn A</td>
                <td>1,500,000đ</td>
                <td><span style={{color: '#10b981'}}>Đã giao</span></td>
                <td>2024-01-15</td>
                <td>
                  <button className="admin-btn" style={{padding: '6px 12px', fontSize: '0.8rem'}}>Xem chi tiết</button>
                </td>
              </tr>
              <tr>
                <td>#002</td>
                <td>Trần Thị B</td>
                <td>899,000đ</td>
                <td><span style={{color: '#f59e0b'}}>Đang xử lý</span></td>
                <td>2024-01-16</td>
                <td>
                  <button className="admin-btn" style={{padding: '6px 12px', fontSize: '0.8rem'}}>Xem chi tiết</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}

export default Orders
  