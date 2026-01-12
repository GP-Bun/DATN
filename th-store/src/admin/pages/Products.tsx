import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/api';
import { toast } from 'react-hot-toast';
import Swal from 'sweetalert2';

interface Variant {
  id: number;
  color_id: number;
  size_id: number;
  stock: number;
  original_price: number;
  sale_price: number | null;
  color?: { name: string; hex_code?: string }; // Assuming backend returns color object
  size?: { value: string; name?: string };   // Assuming backend returns size object
}

interface Product {
  id: number;
  name: string;
  category: { name: string } | null;
  price: number;
  status: number;
  thumbnail: string;
  variants: Variant[];
}

const Products = () => {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchProducts = async () => {
    try {
      const res = await api.get('/products');
      // Handle pagination structure
      const items =
        res.data?.data?.data ||
        res.data?.data ||
        res.data ||
        [];

      setProducts(items);
    } catch (error) {
      console.error(error);
      toast.error('Không thể tải danh sách sản phẩm');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProducts();
  }, []);

  const handleDelete = async (id: number) => {
    const result = await Swal.fire({
      title: 'Bạn có chắc chắn?',
      text: "Sản phẩm sẽ được chuyển vào thùng rác!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Có, xóa nó!',
      cancelButtonText: 'Hủy'
    });

    if (result.isConfirmed) {
      try {
        await api.delete(`/products/${id}`);
        toast.success('Đã xóa sản phẩm');
        fetchProducts();
      } catch (error) {
        toast.error('Xóa thất bại');
      }
    }
  };

  const handleVariantDelete = async (variantId: number) => {
    // Optional: Implement variant deletion directly from list if API supports it
    // Currently we just focus on display
  };

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
  };

  const getImage = (product: any) => {
    if (product.thumbnail) {
      return product.thumbnail.startsWith("http")
        ? product.thumbnail
        : `http://127.0.0.1:8000/storage/${product.thumbnail}`;
    }
    return "https://placehold.co/50x50?text=No+Image";
  };

  if (loading) {
    return <div style={{ padding: '20px', textAlign: 'center' }}>Đang tải dữ liệu...</div>;
  }

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
          <h1 style={{ fontSize: '24px', fontWeight: '700', color: '#111827', margin: 0 }}>Quản lý sản phẩm</h1>
          <p style={{ color: '#6b7280', margin: '4px 0 0 0', fontSize: '14px' }}>
            Quản lý danh sách sản phẩm, biến thể và kho hàng
          </p>
        </div>
        <div style={{ display: 'flex', gap: '12px' }}>
          <Link to="/admin/products/trash" style={{
            display: 'flex',
            alignItems: 'center',
            gap: '8px',
            padding: '10px 16px',
            backgroundColor: 'white',
            border: '1px solid #e5e7eb',
            borderRadius: '8px',
            color: '#4b5563',
            fontWeight: '600',
            textDecoration: 'none',
            fontSize: '14px',
            transition: 'all 0.2s'
          }}>
            <span>🗑️</span> Thùng rác
          </Link>
          <Link to="/admin/products/create" style={{
            display: 'flex',
            alignItems: 'center',
            gap: '8px',
            padding: '10px 16px',
            backgroundColor: '#2563eb', // Blue-600
            borderRadius: '8px',
            color: 'white',
            fontWeight: '600',
            textDecoration: 'none',
            fontSize: '14px',
            boxShadow: '0 2px 4px rgba(37, 99, 235, 0.2)'
          }}>
            <span>+</span> Thêm sản phẩm
          </Link>
        </div>
      </div>

      {/* Product List */}
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
                <th style={headerStyle}>#ID</th>
                <th style={headerStyle}>Tên sản phẩm</th>
                <th style={headerStyle}>Danh mục</th>
                <th style={headerStyle}>Giá</th>
                <th style={headerStyle}>Trạng thái</th>
                <th style={{ ...headerStyle, textAlign: 'right' }}>Hành động</th>
              </tr>
            </thead>
            <tbody>
              {products.map(product => {
                const hasVariants = product.variants && product.variants.length > 0;
                return (
                  <React.Fragment key={product.id}>
                    <tr style={{ borderBottom: hasVariants ? 'none' : '1px solid #f3f4f6' }}>
                      <td style={cellStyle}>#{product.id}</td>
                      <td style={cellStyle}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                          <div style={{
                            width: '48px',
                            height: '48px',
                            borderRadius: '8px',
                            overflow: 'hidden',
                            border: '1px solid #e5e7eb',
                            flexShrink: 0
                          }}>
                            <img
                              src={getImage(product)}
                              alt={product.name}
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                            />
                          </div>
                          <span style={{ fontWeight: '600', color: '#1f2937' }}>{product.name}</span>
                        </div>
                      </td>
                      <td style={cellStyle}>
                        <span style={{
                          backgroundColor: '#f3f4f6',
                          padding: '4px 10px',
                          borderRadius: '16px',
                          fontSize: '12px',
                          color: '#4b5563',
                          fontWeight: '500'
                        }}>
                          {product.category?.name || 'Chưa phân loại'}
                        </span>
                      </td>
                      <td style={{ ...cellStyle, fontWeight: '600', color: '#059669' }}>
                        {formatPrice(product.price)}
                      </td>
                      <td style={cellStyle}>
                        {renderStatus(product.status)}
                      </td>
                      <td style={{ ...cellStyle, textAlign: 'right' }}>
                        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '8px' }}>
                          <Link
                            to={`/admin/products/${product.id}/edit`}
                            style={actionBtnStyle('#f59e0b')} // Amber
                            title="Sửa"
                          >
                            ✏️
                          </Link>
                          <button
                            onClick={() => handleDelete(product.id)}
                            style={actionBtnStyle('#ef4444')} // Red
                            title="Xóa"
                          >
                            🗑️
                          </button>
                        </div>
                      </td>
                    </tr>

                    {/* Variants Section */}
                    {hasVariants && (
                      <tr style={{ borderBottom: '1px solid #e5e7eb' }}>
                        <td colSpan={6} style={{ padding: '0 0 16px 0', backgroundColor: '#fff' }}>
                          <div style={{
                            margin: '0 16px 0 64px', // Connect visually with product name
                            padding: '12px',
                            backgroundColor: '#f8fafc',
                            borderRadius: '8px',
                            border: '1px solid #f1f5f9'
                          }}>
                            <div style={{ fontSize: '12px', fontWeight: '600', color: '#64748b', marginBottom: '8px', textTransform: 'uppercase' }}>
                              Các biến thể
                            </div>
                            {product.variants.map((variant) => (
                              <div key={variant.id} style={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'space-between',
                                padding: '8px 12px',
                                backgroundColor: 'white',
                                borderRadius: '6px',
                                marginBottom: '4px',
                                border: '1px solid #e2e8f0',
                                fontSize: '13px'
                              }}>
                                <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                                  <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                                    <span style={{ color: '#64748b' }}>Màu:</span>
                                    <span style={{ fontWeight: '500' }}>{variant.color?.name || `ID:${variant.color_id}`}</span>
                                  </div>
                                  <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                                    <span style={{ color: '#64748b' }}>Size:</span>
                                    <span style={{
                                      backgroundColor: '#e0f2fe',
                                      color: '#0369a1',
                                      padding: '2px 8px',
                                      borderRadius: '4px',
                                      fontWeight: '600',
                                      fontSize: '12px'
                                    }}>
                                      {variant.size?.value || variant.size?.name || `ID:${variant.size_id}`}
                                    </span>
                                  </div>
                                </div>

                                <div style={{ display: 'flex', alignItems: 'center', gap: '24px' }}>
                                  <div style={{ color: variant.sale_price ? '#ef4444' : '#4b5563', fontWeight: '500' }}>
                                    {variant.sale_price
                                      ? <><span style={{ textDecoration: 'line-through', color: '#9ca3af', fontSize: '12px', marginRight: '4px' }}>{formatPrice(variant.original_price)}</span>{formatPrice(variant.sale_price)}</>
                                      : formatPrice(variant.original_price)
                                    }
                                  </div>
                                  <div style={{ width: '100px' }}>
                                    <span style={{
                                      padding: '2px 8px',
                                      background: '#f1f5f9',
                                      borderRadius: '4px',
                                      fontSize: '12px',
                                      color: '#475569',
                                      whiteSpace: 'nowrap'
                                    }}>
                                      Kho: <b>{variant.stock}</b>
                                    </span>
                                  </div>
                                  {/* Optional details button or link could go here */}
                                </div>
                              </div>
                            ))}
                          </div>
                        </td>
                      </tr>
                    )}
                  </React.Fragment>
                );
              })}
            </tbody>
          </table>
        </div>

        {products.length === 0 && (
          <div style={{ padding: '40px', textAlign: 'center', color: '#6b7280' }}>
            Chưa có sản phẩm nào.
          </div>
        )}
      </div>
    </div>
  );
};

// Styles
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

const actionBtnStyle = (color: string): React.CSSProperties => ({
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  width: '32px',
  height: '32px',
  borderRadius: '6px',
  border: 'none',
  backgroundColor: `${color}15`, // 10% opacity
  color: color,
  cursor: 'pointer',
  transition: 'all 0.2s',
  textDecoration: 'none'
});

const renderStatus = (status: number) => {
  switch (status) {
    case 1:
      return <StatusBadge color="#10b981" bg="#ecfdf5" text="Còn hàng" />;
    case 2:
      return <StatusBadge color="#ef4444" bg="#fef2f2" text="Hết hàng" />;
    default:
      return <StatusBadge color="#6b7280" bg="#f3f4f6" text="Ẩn" />;
  }
};

const StatusBadge = ({ color, bg, text }: { color: string, bg: string, text: string }) => (
  <span style={{
    display: 'inline-block',
    padding: '4px 12px',
    borderRadius: '9999px',
    fontSize: '12px',
    fontWeight: '600',
    color: color,
    backgroundColor: bg,
    textAlign: 'center',
    minWidth: '80px'
  }}>
    {text}
  </span>
);

export default Products;