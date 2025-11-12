
const Users = () => {
  return (
    <div>
      <div className="admin-page-header">
        <h1 className="admin-page-title">Quản lý người dùng</h1>
        <p className="admin-page-subtitle">Quản lý tài khoản và thông tin người dùng</p>
      </div>
      
      <div className="admin-card">
        <div className="admin-card-header">
          <h3 className="admin-card-title">Danh sách người dùng</h3>
          <button className="admin-btn">+ Thêm người dùng</button>
        </div>
        <div className="admin-card-content">
          <table className="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Email</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>001</td>
                <td>Nguyễn Văn A</td>
                <td>nguyenvana@email.com</td>
                <td><span style={{color: '#10b981'}}>Hoạt động</span></td>
                <td>2024-01-10</td>
                <td>
                  <button className="admin-btn" style={{marginRight: '8px', padding: '6px 12px', fontSize: '0.8rem'}}>Sửa</button>
                  <button className="admin-btn-secondary" style={{padding: '6px 12px', fontSize: '0.8rem'}}>Khóa</button>
                </td>
              </tr>
              <tr>
                <td>002</td>
                <td>Trần Thị B</td>
                <td>tranthib@email.com</td>
                <td><span style={{color: '#10b981'}}>Hoạt động</span></td>
                <td>2024-01-12</td>
                <td>
                  <button className="admin-btn" style={{marginRight: '8px', padding: '6px 12px', fontSize: '0.8rem'}}>Sửa</button>
                  <button className="admin-btn-secondary" style={{padding: '6px 12px', fontSize: '0.8rem'}}>Khóa</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}

export default Users
  