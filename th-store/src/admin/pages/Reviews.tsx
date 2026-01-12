import { useState, useEffect } from "react";
import {
  getAdminReviews,
  updateReviewStatus,
  deleteReview,
  getReviewStats,
  type Review,
  type ReviewStats,
} from "../../api/adminReviews.api";
import { toast } from "react-hot-toast";
import Swal from "sweetalert2";

const Reviews = () => {
  const [reviews, setReviews] = useState<Review[]>([]);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState<ReviewStats | null>(null);
  const [filters, setFilters] = useState({
    status: "",
    search: "",
    sort_by: "created_at",
    sort_order: "desc",
  });
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });

  // Load reviews
  const loadReviews = async () => {
    try {
      setLoading(true);
      const params: any = {
        page: pagination.current_page,
        per_page: pagination.per_page,
        sort_by: filters.sort_by,
        sort_order: filters.sort_order,
      };

      if (filters.status) {
        params.status = filters.status;
      }
      if (filters.search) {
        params.search = filters.search;
      }

      const data = await getAdminReviews(params);
      setReviews(data.reviews.data);
      setPagination({
        current_page: data.current_page,
        last_page: data.last_page,
        per_page: data.per_page,
        total: data.total,
      });
    } catch (error: any) {
      console.error("Lỗi khi tải reviews:", error);
      toast.error(error.response?.data?.message || "Không thể tải danh sách reviews");
    } finally {
      setLoading(false);
    }
  };

  // Load stats
  const loadStats = async () => {
    try {
      const data = await getReviewStats();
      setStats(data);
    } catch (error) {
      console.error("Lỗi khi tải thống kê:", error);
    }
  };

  useEffect(() => {
    loadReviews();
    loadStats();
  }, [filters, pagination.current_page]);

  // Handle toggle status
  const handleToggleStatus = async (review: Review) => {
    const result = await Swal.fire({
      title: 'Xác nhận?',
      text: `Bạn có chắc muốn ${review.status ? "ẩn" : "hiển thị"} review này?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Đồng ý',
      cancelButtonText: 'Hủy'
    });

    if (!result.isConfirmed) return;

    try {
      const newStatus = review.status === 1 ? 0 : 1;
      await updateReviewStatus(review.id, newStatus as 0 | 1);
      toast.success("Cập nhật trạng thái thành công!");
      loadReviews();
      loadStats();
    } catch (error: any) {
      console.error("Lỗi khi cập nhật trạng thái:", error);
      toast.error(error.response?.data?.message || "Không thể cập nhật trạng thái");
    }
  };

  // Handle delete
  const handleDelete = async (id: number) => {
    const result = await Swal.fire({
      title: 'Xóa đánh giá?',
      text: "Bạn có chắc muốn xóa review này? Hành động này không thể hoàn tác!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Xóa ngay',
      cancelButtonText: 'Hủy'
    });

    if (!result.isConfirmed) return;

    try {
      await deleteReview(id);
      toast.success("Xóa review thành công!");
      loadReviews();
      loadStats();
    } catch (error: any) {
      console.error("Lỗi khi xóa review:", error);
      toast.error(error.response?.data?.message || "Không thể xóa review");
    }
  };

  // Render stars
  const renderStars = (rating: number) => {
    return (
      <div style={{ display: "flex", gap: "2px" }}>
        {[1, 2, 3, 4, 5].map((star) => (
          <span
            key={star}
            style={{
              fontSize: "16px",
              color: star <= rating ? "#FFD700" : "#e5e7eb",
            }}
          >
            ★
          </span>
        ))}
      </div>
    );
  };

  return (
    <div style={{ padding: '24px', fontFamily: 'Inter, sans-serif' }}>
      {/* Header Section */}
      <div style={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: '32px',
        background: 'white',
        padding: '20px',
        borderRadius: '12px',
        boxShadow: '0 2px 4px rgba(0,0,0,0.05)'
      }}>
        <div>
          <h1 style={{ fontSize: '24px', fontWeight: '700', color: '#111827', margin: 0 }}>Quản lý đánh giá</h1>
          <p style={{ color: '#6b7280', margin: '4px 0 0 0', fontSize: '14px' }}>
            Quản lý và duyệt các đánh giá từ người dùng
          </p>
        </div>
      </div>

      {/* Stats Cards */}
      {stats && (
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))", gap: "20px", marginBottom: "30px" }}>
          <StatCard title="Tổng số" value={stats.total} color="#3b82f6" />
          <StatCard title="Đã duyệt" value={stats.active} color="#10b981" />
          <StatCard title="Chưa duyệt" value={stats.inactive} color="#ef4444" />
          <StatCard title="Đánh giá TB" value={`${stats.average_rating.toFixed(1)} ⭐`} color="#f59e0b" />
        </div>
      )}

      {/* Filters */}
      <div style={{
        background: 'white',
        padding: '20px',
        borderRadius: '12px',
        boxShadow: '0 2px 4px rgba(0,0,0,0.05)',
        marginBottom: '24px'
      }}>
        <div style={{ display: "flex", gap: "16px", flexWrap: "wrap", alignItems: "end" }}>
          <div style={{ flex: "1", minWidth: "200px" }}>
            <label style={labelStyle}>Tìm kiếm</label>
            <input
              type="text"
              placeholder="Tìm theo nội dung comment..."
              value={filters.search}
              onChange={(e) => setFilters({ ...filters, search: e.target.value })}
              style={inputStyle}
            />
          </div>
          <div style={{ minWidth: "150px" }}>
            <label style={labelStyle}>Trạng thái</label>
            <select
              value={filters.status}
              onChange={(e) => setFilters({ ...filters, status: e.target.value })}
              style={inputStyle}
            >
              <option value="">Tất cả</option>
              <option value="active">Đã duyệt</option>
              <option value="inactive">Chưa duyệt</option>
            </select>
          </div>
          <div style={{ minWidth: "150px" }}>
            <label style={labelStyle}>Sắp xếp</label>
            <select
              value={`${filters.sort_by}_${filters.sort_order}`}
              onChange={(e) => {
                const [sort_by, sort_order] = e.target.value.split("_");
                setFilters({ ...filters, sort_by, sort_order });
              }}
              style={inputStyle}
            >
              <option value="created_at_desc">Mới nhất</option>
              <option value="created_at_asc">Cũ nhất</option>
              <option value="rating_desc">Đánh giá cao</option>
              <option value="rating_asc">Đánh giá thấp</option>
            </select>
          </div>
        </div>
      </div>

      {/* Reviews Table */}
      <div style={{
        background: 'white',
        borderRadius: '12px',
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
        overflow: 'hidden'
      }}>
        <div style={{ overflowX: 'auto' }}>
          <table style={{ width: '100%', borderCollapse: 'collapse', minWidth: '800px' }}>
            <thead style={{ backgroundColor: '#f9fafb', borderBottom: '1px solid #e5e7eb' }}>
              <tr>
                <th style={headerStyle}>ID</th>
                <th style={headerStyle}>Người dùng</th>
                <th style={headerStyle}>Sản phẩm</th>
                <th style={headerStyle}>Đánh giá</th>
                <th style={headerStyle}>Nội dung</th>
                <th style={headerStyle}>Ngày tạo</th>
                <th style={headerStyle}>Trạng thái</th>
                <th style={{ ...headerStyle, textAlign: 'right' }}>Hành động</th>
              </tr>
            </thead>
            <tbody>
              {reviews.map((review) => (
                <tr key={review.id} style={{ borderBottom: '1px solid #f3f4f6' }}>
                  <td style={cellStyle}>#{review.id}</td>
                  <td style={cellStyle}>
                    <div>
                      <div style={{ fontWeight: "600", color: "#111827" }}>
                        {review.user?.name || review.user_name}
                      </div>
                      <div style={{ fontSize: "12px", color: "#6b7280" }}>
                        {review.user?.email || review.user_email}
                      </div>
                    </div>
                  </td>
                  <td style={cellStyle}>
                    <div style={{ maxWidth: "200px" }}>
                      <div style={{ fontWeight: "500", color: "#374151" }}>{review.product?.name || `Product #${review.product_id}`}</div>
                    </div>
                  </td>
                  <td style={cellStyle}>{renderStars(review.rating)}</td>
                  <td style={{ ...cellStyle, maxWidth: "300px" }}>
                    <div
                      style={{
                        overflow: "hidden",
                        textOverflow: "ellipsis",
                        whiteSpace: "nowrap",
                        color: "#4b5563"
                      }}
                      title={review.comment || ""}
                    >
                      {review.comment || <span style={{ color: "#9ca3af", fontStyle: "italic" }}>Không có nội dung</span>}
                    </div>
                  </td>
                  <td style={cellStyle}>{new Date(review.created_at).toLocaleDateString("vi-VN")}</td>
                  <td style={cellStyle}>
                    <span
                      style={{
                        display: 'inline-block',
                        padding: '4px 12px',
                        borderRadius: '9999px',
                        fontSize: '12px',
                        fontWeight: '600',
                        color: review.status === 1 ? '#059669' : '#d97706',
                        backgroundColor: review.status === 1 ? '#d1fae5' : '#fef3c7',
                      }}
                    >
                      {review.status === 1 ? "Đã duyệt" : "Chưa duyệt"}
                    </span>
                  </td>
                  <td style={{ ...cellStyle, textAlign: 'right' }}>
                    <div style={{ display: "flex", gap: "8px", justifyContent: "flex-end" }}>
                      <button
                        onClick={() => handleToggleStatus(review)}
                        style={actionBtnStyle(review.status === 1 ? '#f59e0b' : '#10b981')}
                        title={review.status === 1 ? "Ẩn review" : "Duyệt review"}
                      >
                        {review.status === 1 ? '🚫' : '✅'}
                      </button>
                      <button
                        onClick={() => handleDelete(review.id)}
                        style={actionBtnStyle('#ef4444')}
                        title="Xóa review"
                      >
                        🗑️
                      </button>
                    </div>
                  </td>
                </tr>
              ))}

              {reviews.length === 0 && !loading && (
                <tr>
                  <td colSpan={8} style={{ padding: '40px', textAlign: 'center', color: '#6b7280' }}>
                    Không tìm thấy đánh giá nào.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        {pagination.last_page > 1 && (
          <div style={{ display: "flex", justifyContent: "center", gap: "10px", padding: "20px", borderTop: "1px solid #e5e7eb" }}>
            <button
              disabled={pagination.current_page === 1}
              onClick={() => setPagination({ ...pagination, current_page: pagination.current_page - 1 })}
              style={paginationBtnStyle(pagination.current_page === 1)}
            >
              Trước
            </button>
            <span style={{ padding: "8px 16px", display: "flex", alignItems: "center", fontWeight: "600", color: "#374151" }}>
              Trang {pagination.current_page} / {pagination.last_page}
            </span>
            <button
              disabled={pagination.current_page === pagination.last_page}
              onClick={() => setPagination({ ...pagination, current_page: pagination.current_page + 1 })}
              style={paginationBtnStyle(pagination.current_page === pagination.last_page)}
            >
              Sau
            </button>
          </div>
        )}
      </div>
    </div>
  );
};

// Styles and Components
const StatCard = ({ title, value, color }: { title: string, value: string | number, color: string }) => (
  <div style={{
    background: 'white',
    padding: '20px',
    borderRadius: '12px',
    boxShadow: '0 2px 4px rgba(0,0,0,0.05)',
    borderLeft: `4px solid ${color}`
  }}>
    <div style={{ fontSize: "14px", color: "#6b7280", marginBottom: "8px", fontWeight: "500" }}>{title}</div>
    <div style={{ fontSize: "28px", fontWeight: "700", color: "#111827" }}>{value}</div>
  </div>
);

const headerStyle: React.CSSProperties = {
  padding: '16px 24px',
  textAlign: 'left',
  fontSize: '13px',
  fontWeight: '600',
  color: '#6b7280',
  textTransform: 'uppercase',
  letterSpacing: '0.05em'
};

const cellStyle: React.CSSProperties = {
  padding: '16px 24px',
  verticalAlign: 'middle',
  fontSize: '14px',
  color: '#374151'
};

const labelStyle: React.CSSProperties = {
  display: "block",
  marginBottom: "5px",
  fontSize: "14px",
  fontWeight: "500",
  color: "#374151"
};

const inputStyle: React.CSSProperties = {
  width: "100%",
  padding: "8px 12px",
  border: "1px solid #d1d5db",
  borderRadius: "6px",
  fontSize: "14px",
  color: "#111827"
};

const actionBtnStyle = (color: string): React.CSSProperties => ({
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  width: '32px',
  height: '32px',
  borderRadius: '6px',
  border: 'none',
  backgroundColor: `${color}15`,
  color: color,
  cursor: 'pointer',
  transition: 'all 0.2s',
});

const paginationBtnStyle = (disabled: boolean): React.CSSProperties => ({
  padding: "8px 16px",
  borderRadius: "6px",
  border: "1px solid #d1d5db",
  backgroundColor: disabled ? "#f3f4f6" : "white",
  color: disabled ? "#9ca3af" : "#374151",
  cursor: disabled ? "not-allowed" : "pointer",
  fontWeight: "500"
});

export default Reviews;
