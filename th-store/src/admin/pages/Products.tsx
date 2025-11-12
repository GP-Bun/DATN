
const Products = () => {
  return (
    <div>
      <div className="admin-page-header">
        <h1 className="admin-page-title">Quản lý sản phẩm</h1>
        <p className="admin-page-subtitle">Quản lý danh sách sản phẩm, thêm/sửa/xóa sản phẩm</p>
      </div>
      
      <div className="admin-card">
        <div className="admin-card-header">
          <h3 className="admin-card-title">Danh sách sản phẩm</h3>
          <button className="admin-btn">+ Thêm sản phẩm mới</button>
        </div>
        <div className="admin-card-content">
          <table className="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Tên sản phẩm</th>
                <th>Giá</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>001</td>
                <td>Áo thun nam</td>
                <td>299,000đ</td>
                <td><span style={{color: '#10b981'}}>Còn hàng</span></td>
                <td>
                  <button className="admin-btn" style={{marginRight: '8px', padding: '6px 12px', fontSize: '0.8rem'}}>Sửa</button>
                  <button className="admin-btn-secondary" style={{padding: '6px 12px', fontSize: '0.8rem'}}>Xóa</button>
                </td>
              </tr>
              <tr>
                <td>002</td>
                <td>Quần jean nữ</td>
                <td>599,000đ</td>
                <td><span style={{color: '#10b981'}}>Còn hàng</span></td>
                <td>
                  <button className="admin-btn" style={{marginRight: '8px', padding: '6px 12px', fontSize: '0.8rem'}}>Sửa</button>
                  <button className="admin-btn-secondary" style={{padding: '6px 12px', fontSize: '0.8rem'}}>Xóa</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}

export default Products
  