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
              color: star <= rating ? "#FFD700" : "#ddd",
            }}
          >
            ⭐
          </span>
        ))}
      </div>
    );
  };

  return (
    <div>
      <div className="admin-page-header">
        <h1 className="admin-page-title">Quản lý đánh giá</h1>
        <p className="admin-page-subtitle">Quản lý và duyệt các đánh giá từ người dùng</p>
      </div>

      {/* Stats Cards */}
      {stats && (
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))", gap: "20px", marginBottom: "30px" }}>
          <div className="admin-card">
            <div style={{ padding: "20px" }}>
              <div style={{ fontSize: "14px", color: "#666", marginBottom: "8px" }}>Tổng số</div>
              <div style={{ fontSize: "32px", fontWeight: "bold", color: "#3b82f6" }}>{stats.total}</div>
            </div>
          </div>
          <div className="admin-card">
            <div style={{ padding: "20px" }}>
              <div style={{ fontSize: "14px", color: "#666", marginBottom: "8px" }}>Đã duyệt</div>
              <div style={{ fontSize: "32px", fontWeight: "bold", color: "#10b981" }}>{stats.active}</div>
            </div>
          </div>
          <div className="admin-card">
            <div style={{ padding: "20px" }}>
              <div style={{ fontSize: "14px", color: "#666", marginBottom: "8px" }}>Chưa duyệt</div>
              <div style={{ fontSize: "32px", fontWeight: "bold", color: "#ef4444" }}>{stats.inactive}</div>
            </div>
          </div>
          <div className="admin-card">
            <div style={{ padding: "20px" }}>
              <div style={{ fontSize: "14px", color: "#666", marginBottom: "8px" }}>Đánh giá TB</div>
              <div style={{ fontSize: "32px", fontWeight: "bold", color: "#f59e0b" }}>
                {stats.average_rating.toFixed(1)} ⭐
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Filters */}
      <div className="admin-card" style={{ marginBottom: "20px" }}>
        <div style={{ padding: "20px" }}>
          <div style={{ display: "flex", gap: "15px", flexWrap: "wrap", alignItems: "end" }}>
            <div style={{ flex: "1", minWidth: "200px" }}>
              <label style={{ display: "block", marginBottom: "5px", fontSize: "14px", fontWeight: "500" }}>
                Tìm kiếm
              </label>
              <input
                type="text"
                placeholder="Tìm theo nội dung comment..."
                value={filters.search}
                onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                style={{
                  width: "100%",
                  padding: "8px 12px",
                  border: "1px solid #ddd",
                  borderRadius: "6px",
                }}
              />
            </div>
            <div style={{ minWidth: "150px" }}>
              <label style={{ display: "block", marginBottom: "5px", fontSize: "14px", fontWeight: "500" }}>
                Trạng thái
              </label>
              <select
                value={filters.status}
                onChange={(e) => setFilters({ ...filters, status: e.target.value })}
                style={{
                  width: "100%",
                  padding: "8px 12px",
                  border: "1px solid #ddd",
                  borderRadius: "6px",
                }}
              >
                <option value="">Tất cả</option>
                <option value="active">Đã duyệt</option>
                <option value="inactive">Chưa duyệt</option>
              </select>
            </div>
            <div style={{ minWidth: "150px" }}>
              <label style={{ display: "block", marginBottom: "5px", fontSize: "14px", fontWeight: "500" }}>
                Sắp xếp
              </label>
              <select
                value={`${filters.sort_by}_${filters.sort_order}`}
                onChange={(e) => {
                  const [sort_by, sort_order] = e.target.value.split("_");
                  setFilters({ ...filters, sort_by, sort_order });
                }}
                style={{
                  width: "100%",
                  padding: "8px 12px",
                  border: "1px solid #ddd",
                  borderRadius: "6px",
                }}
              >
                <option value="created_at_desc">Mới nhất</option>
                <option value="created_at_asc">Cũ nhất</option>
                <option value="rating_desc">Đánh giá cao</option>
                <option value="rating_asc">Đánh giá thấp</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      {/* Reviews Table */}
      <div className="admin-card">
        <div className="admin-card-header">
          <h3 className="admin-card-title">
            Danh sách đánh giá ({pagination.total})
          </h3>
        </div>
        <div className="admin-card-content">
          {loading ? (
            <div style={{ textAlign: "center", padding: "40px" }}>Đang tải...</div>
          ) : reviews.length === 0 ? (
            <div style={{ textAlign: "center", padding: "40px", color: "#666" }}>
              Không có đánh giá nào
            </div>
          ) : (
            <>
              <table className="admin-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Người dùng</th>
                    <th>Sản phẩm</th>
                    <th>Đánh giá</th>
                    <th>Nội dung</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                  </tr>
                </thead>
                <tbody>
                  {reviews.map((review) => (
                    <tr key={review.id}>
                      <td>{review.id}</td>
                      <td>
                        <div>
                          <div style={{ fontWeight: "500" }}>
                            {review.user?.name || review.user_name}
                          </div>
                          <div style={{ fontSize: "12px", color: "#666" }}>
                            {review.user?.email || review.user_email}
                          </div>
                        </div>
                      </td>
                      <td>
                        <div style={{ maxWidth: "200px" }}>
                          <div style={{ fontWeight: "500" }}>{review.product?.name || `Product #${review.product_id}`}</div>
                        </div>
                      </td>
                      <td>{renderStars(review.rating)}</td>
                      <td style={{ maxWidth: "300px" }}>
                        <div
                          style={{
                            overflow: "hidden",
                            textOverflow: "ellipsis",
                            whiteSpace: "nowrap",
                          }}
                          title={review.comment || ""}
                        >
                          {review.comment || <span style={{ color: "#999" }}>Không có comment</span>}
                        </div>
                      </td>
                      <td>{new Date(review.created_at).toLocaleDateString("vi-VN")}</td>
                      <td>
                        <span
                          style={{
                            color: review.status === 1 ? "#10b981" : "#ef4444",
                            fontWeight: "500",
                          }}
                        >
                          {review.status === 1 ? "Đã duyệt" : "Chưa duyệt"}
                        </span>
                      </td>
                      <td>
                        <div style={{ display: "flex", gap: "8px", flexWrap: "wrap" }}>
                          <button
                            className="admin-btn"
                            style={{ padding: "6px 12px", fontSize: "0.8rem" }}
                            onClick={() => handleToggleStatus(review)}
                          >
                            {review.status === 1 ? "Ẩn" : "Duyệt"}
                          </button>
                          <button
                            className="admin-btn-secondary"
                            style={{ padding: "6px 12px", fontSize: "0.8rem" }}
                            onClick={() => handleDelete(review.id)}
                          >
                            Xóa
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>

              {/* Pagination */}
              {pagination.last_page > 1 && (
                <div style={{ display: "flex", justifyContent: "center", gap: "10px", marginTop: "20px" }}>
                  <button
                    className="admin-btn"
                    disabled={pagination.current_page === 1}
                    onClick={() =>
                      setPagination({ ...pagination, current_page: pagination.current_page - 1 })
                    }
                    style={{ padding: "8px 16px" }}
                  >
                    Trước
                  </button>
                  <span style={{ padding: "8px 16px", display: "flex", alignItems: "center" }}>
                    Trang {pagination.current_page} / {pagination.last_page}
                  </span>
                  <button
                    className="admin-btn"
                    disabled={pagination.current_page === pagination.last_page}
                    onClick={() =>
                      setPagination({ ...pagination, current_page: pagination.current_page + 1 })
                    }
                    style={{ padding: "8px 16px" }}
                  >
                    Sau
                  </button>
                </div>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default Reviews;

