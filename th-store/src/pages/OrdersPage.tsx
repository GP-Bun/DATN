import { useState, useEffect } from 'react'
import { useAuth } from '../store/AuthContext'
import { useNavigate } from 'react-router-dom'
import axios from 'axios'

type OrderItem = {
    id: number
    product_id: number
    product_name: string
    quantity: number
    price: number
    product?: {
        id: number
        name: string
        image?: string
        thumbnail?: string
        thumbnail_url?: string
        images?: string[]
    }
    variant?: {
        color?: string
        size?: string
    }
}

type Order = {
    id: number
    order_status: string
    payment_status: string
    final_amount: number
    discount_amount: number
    shipping_cost: number
    created_at: string
    items: OrderItem[]
    address?: {
        receiver_name: string;
        receiver_phone: string;
        line1: string;
        city: any;
        province: any;
    };
    coupon?: {
        code: string;
        type: string;
        value: number;
    };
}


const userApi = axios.create({ baseURL: "http://127.0.0.1:8000/api" })

userApi.interceptors.request.use((config) => {
    const token = localStorage.getItem("user_token") || sessionStorage.getItem("user_token");
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
});

export default function OrdersPage() {
    const { user } = useAuth()
    const navigate = useNavigate()
    const [orders, setOrders] = useState<Order[]>([])
    const [loading, setLoading] = useState(true)
    const [expandedOrderId, setExpandedOrderId] = useState<number | null>(null)

    useEffect(() => {
        if (!user) {
            navigate('/dang-nhap')
            return
        }
        loadOrders()
    }, [user])

    const loadOrders = async () => {
        try {
            setLoading(true)
            const res = await userApi.get('/orders')
            setOrders(res.data)
        } catch (err) {
            console.error('Lỗi tải đơn hàng:', err)
        } finally {
            setLoading(false)
        }
    }

    const getImageUrl = (image: string | undefined | null) => {
        if (!image) return "https://via.placeholder.com/150?text=No+Image";
        if (image.startsWith("http")) return image;
        if (image.startsWith("/")) return `http://127.0.0.1:8000${image}`;
        return `http://127.0.0.1:8000/storage/${image}`;
    };

    const formatDate = (dateString: string) => {
        const date = new Date(dateString)
        return date.toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        })
    }

    const getStatusText = (status: string) => {
        const statusMap: Record<string, string> = {
            pending: 'Chờ xử lý',
            processing: 'Đang xử lý',
            shipped: 'Đang giao hàng',
            delivered: 'Đã giao hàng',
            cancelled: 'Đã hủy'
        }
        return statusMap[status] || status
    }

    const getStatusColor = (status: string) => {
        const colorMap: Record<string, string> = {
            pending: '#f59e0b',
            processing: '#3b82f6',
            shipped: '#8b5cf6',
            delivered: '#10b981',
            cancelled: '#ef4444'
        }
        return colorMap[status] || '#6b7280'
    }

    const getPaymentStatusText = (status: string) => {
        const statusMap: Record<string, string> = {
            unpaid: 'Chưa thanh toán',
            paid: 'Đã thanh toán',
            refunded: 'Đã hoàn tiền'
        }
        return statusMap[status] || status
    }

    if (loading) {
        return (
            <div className="main" style={{ textAlign: "center", padding: "100px" }}>
                <p>Đang tải đơn hàng...</p>
            </div>
        )
    }

    return (
        <div className="main" style={{ maxWidth: "1200px", margin: "0 auto", padding: "40px 20px" }}>
            <h1 style={{
                marginBottom: "32px",
                fontSize: "32px",
                fontWeight: "700",
                color: "#1f2937"
            }}>
                Đơn hàng của tôi
            </h1>

            <div style={{
                background: "white",
                borderRadius: "12px",
                padding: "32px",
                boxShadow: "0 1px 3px rgba(0,0,0,0.1)",
                border: "1px solid #e5e7eb"
            }}>
                {orders.length === 0 ? (
                    <div style={{ textAlign: "center", padding: "40px" }}>
                        <span style={{ fontSize: "48px", display: "block", marginBottom: "16px" }}>📦</span>
                        <p style={{ color: "#6b7280", fontSize: "18px" }}>Bạn chưa có đơn hàng nào.</p>
                        <button
                            onClick={() => navigate('/san-pham')}
                            style={{
                                marginTop: "20px",
                                padding: "12px 24px",
                                background: "#3b82f6",
                                color: "white",
                                border: "none",
                                borderRadius: "8px",
                                fontWeight: "600",
                                cursor: "pointer"
                            }}
                        >
                            Mua sắm ngay
                        </button>
                    </div>
                ) : (
                    <div style={{ display: "flex", flexDirection: "column", gap: "16px" }}>
                        {orders.map((order) => (
                            <div
                                key={order.id}
                                style={{
                                    border: "1px solid #e5e7eb",
                                    borderRadius: "8px",
                                    overflow: "hidden"
                                }}
                            >
                                <div
                                    style={{
                                        padding: "20px",
                                        background: "#f9fafb",
                                        cursor: "pointer",
                                        display: "flex",
                                        justifyContent: "space-between",
                                        alignItems: "center"
                                    }}
                                    onClick={() => setExpandedOrderId(expandedOrderId === order.id ? null : order.id)}
                                >
                                    <div style={{ flex: 1 }}>
                                        <div style={{ display: "flex", gap: "16px", alignItems: "center", marginBottom: "8px" }}>
                                            <span style={{
                                                fontSize: "16px",
                                                fontWeight: "600",
                                                color: "#1f2937"
                                            }}>
                                                Đơn hàng #{order.id}
                                            </span>
                                            <span style={{
                                                padding: "4px 12px",
                                                background: getStatusColor(order.order_status),
                                                color: "white",
                                                borderRadius: "4px",
                                                fontSize: "12px",
                                                fontWeight: "600"
                                            }}>
                                                {getStatusText(order.order_status)}
                                            </span>
                                            <span style={{
                                                padding: "4px 12px",
                                                background: order.payment_status === 'paid' ? "#10b981" : "#f59e0b",
                                                color: "white",
                                                borderRadius: "4px",
                                                fontSize: "12px",
                                                fontWeight: "600"
                                            }}>
                                                {getPaymentStatusText(order.payment_status)}
                                            </span>
                                        </div>
                                        <div style={{ display: "flex", gap: "24px", fontSize: "14px", color: "#6b7280" }}>
                                            <span>Ngày đặt: {formatDate(order.created_at)}</span>
                                            <span style={{ fontWeight: "600", color: "#059669", fontSize: "16px" }}>
                                                Tổng tiền: {new Intl.NumberFormat('vi-VN', {
                                                    style: 'decimal',
                                                    minimumFractionDigits: 0,
                                                    maximumFractionDigits: 0,
                                                }).format(order.final_amount)}đ
                                            </span>
                                        </div>
                                    </div>
                                    <div style={{
                                        fontSize: "20px",
                                        color: "#6b7280",
                                        transform: expandedOrderId === order.id ? "rotate(180deg)" : "rotate(0deg)",
                                        transition: "transform 0.2s"
                                    }}>
                                        ▼
                                    </div>
                                </div>

                                {expandedOrderId === order.id && (
                                    <div style={{ padding: "20px", background: "white" }}>
                                        {order.address && (
                                            <div style={{ marginBottom: "20px", paddingBottom: "20px", borderBottom: "1px solid #e5e7eb" }}>
                                                <h3 style={{ fontSize: "16px", fontWeight: "600", marginBottom: "12px", color: "#1f2937" }}>
                                                    Địa chỉ giao hàng
                                                </h3>
                                                <p style={{ margin: "4px 0", fontSize: "14px", color: "#374151" }}>
                                                    <strong>Người nhận:</strong> {order.address.receiver_name}
                                                </p>
                                                <p style={{ margin: "4px 0", fontSize: "14px", color: "#374151" }}>
                                                    <strong>Điện thoại:</strong> {order.address.receiver_phone}
                                                </p>
                                                <p style={{ margin: "4px 0", fontSize: "14px", color: "#374151" }}>
                                                    <strong>Địa chỉ:</strong> {order.address.line1}
                                                    {order.address.city && `, ${typeof order.address.city === 'object' ? order.address.city.name : order.address.city}`}
                                                    {order.address.province && `, ${typeof order.address.province === 'object' ? order.address.province.name : order.address.province}`}
                                                </p>

                                            </div>
                                        )}

                                        <div style={{ marginBottom: "20px" }}>
                                            <h3 style={{ fontSize: "16px", fontWeight: "600", marginBottom: "12px", color: "#1f2937" }}>
                                                Sản phẩm
                                            </h3>
                                            <div style={{ display: "flex", flexDirection: "column", gap: "12px" }}>
                                                {order.items.map((item) => {
                                                    const imageUrl = (item.product as any)?.image ||
                                                        (item.product as any)?.thumbnail_url ||
                                                        ((item.product as any)?.thumbnail ? getImageUrl((item.product as any).thumbnail) : null) ||
                                                        ((item.product as any)?.images && (item.product as any).images.length > 0 ? getImageUrl((item.product as any).images[0]) : null);

                                                    return (
                                                        <div
                                                            key={item.id}
                                                            style={{
                                                                display: "flex",
                                                                gap: "16px",
                                                                padding: "12px",
                                                                background: "#f9fafb",
                                                                borderRadius: "8px",
                                                                border: "1px solid #e5e7eb"
                                                            }}
                                                        >
                                                            <img
                                                                src={imageUrl || "https://via.placeholder.com/100?text=No+Image"}
                                                                alt={item.product_name}
                                                                onError={(e) => {
                                                                    e.currentTarget.src = "https://via.placeholder.com/100?text=No+Image";
                                                                }}
                                                                style={{
                                                                    width: '80px',
                                                                    height: '80px',
                                                                    objectFit: 'cover',
                                                                    borderRadius: '8px',
                                                                    border: '1px solid #e5e7eb',
                                                                    flexShrink: 0
                                                                }}
                                                            />
                                                            <div style={{ flex: 1 }}>
                                                                <p style={{ margin: "0 0 4px 0", fontSize: "16px", fontWeight: "600", color: "#1f2937" }}>
                                                                    {item.product_name}
                                                                </p>
                                                                {item.variant && (
                                                                    <p style={{ margin: "4px 0", fontSize: "14px", color: "#6b7280" }}>
                                                                        {item.variant.color && `Màu: ${item.variant.color}`}
                                                                        {item.variant.color && item.variant.size && ' • '}
                                                                        {item.variant.size && `Size: ${item.variant.size}`}
                                                                    </p>
                                                                )}
                                                                <p style={{ margin: "4px 0", fontSize: "14px", color: "#6b7280" }}>
                                                                    Số lượng: {item.quantity}
                                                                </p>
                                                                <p style={{ margin: "8px 0 0 0", fontSize: "16px", fontWeight: "600", color: "#059669" }}>
                                                                    {new Intl.NumberFormat('vi-VN', {
                                                                        style: 'decimal',
                                                                        minimumFractionDigits: 0,
                                                                        maximumFractionDigits: 0,
                                                                    }).format(item.price)}đ × {item.quantity} = {new Intl.NumberFormat('vi-VN', {
                                                                        style: 'decimal',
                                                                        minimumFractionDigits: 0,
                                                                        maximumFractionDigits: 0,
                                                                    }).format(item.price * item.quantity)}đ
                                                                </p>
                                                            </div>
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        </div>

                                        <div style={{
                                            paddingTop: "16px",
                                            borderTop: "2px solid #e5e7eb",
                                            textAlign: "right"
                                        }}>
                                            <div style={{ display: "flex", flexDirection: "column", gap: "8px", alignItems: "flex-end" }}>
                                                <p style={{ margin: 0, fontSize: "14px", color: "#6b7280" }}>
                                                    Tạm tính: {new Intl.NumberFormat('vi-VN', {
                                                        style: 'decimal',
                                                        minimumFractionDigits: 0,
                                                        maximumFractionDigits: 0,
                                                    }).format(order.items.reduce((sum, item) => sum + item.price * item.quantity, 0))}đ
                                                </p>
                                                {order.discount_amount > 0 && (
                                                    <p style={{ margin: 0, fontSize: "14px", color: "#6b7280" }}>
                                                        Giảm giá {order.coupon?.code ? (
                                                            <span style={{
                                                                color: "#059669",
                                                                fontWeight: "600",
                                                                marginLeft: "4px",
                                                                padding: "2px 6px",
                                                                background: "#ecfdf5",
                                                                borderRadius: "4px",
                                                                border: "1px dashed #059669"
                                                            }}>
                                                                {order.coupon.code}
                                                            </span>
                                                        ) : ''}: -{new Intl.NumberFormat('vi-VN', {
                                                            style: 'decimal',
                                                            minimumFractionDigits: 0,
                                                            maximumFractionDigits: 0,
                                                        }).format(order.discount_amount)}đ
                                                    </p>
                                                )}


                                                <p style={{ margin: 0, fontSize: "14px", color: "#6b7280" }}>
                                                    Phí vận chuyển: {new Intl.NumberFormat('vi-VN', {
                                                        style: 'decimal',
                                                        minimumFractionDigits: 0,
                                                        maximumFractionDigits: 0,
                                                    }).format(order.shipping_cost)}đ
                                                </p>
                                                <p style={{
                                                    margin: "8px 0 0 0",
                                                    fontSize: "20px",
                                                    fontWeight: "700",
                                                    color: "#059669"
                                                }}>
                                                    Tổng cộng: {new Intl.NumberFormat('vi-VN', {
                                                        style: 'decimal',
                                                        minimumFractionDigits: 0,
                                                        maximumFractionDigits: 0,
                                                    }).format(order.final_amount)}đ
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    )
}
