import { useCart } from '../store/CartContext'
import { useAuth } from '../store/AuthContext'
import { useState, useEffect } from 'react'
import { useNavigate, Link, useLocation } from 'react-router-dom'
import { checkout } from '../api/checkout.api'
import { createOrder } from '../api/order.api'
import type { CheckoutPayload } from '../api/checkout.api'
import { formatPrice } from '../utils/formatPrice'
import { geoApi } from '../api/geo.api'
import type { Province, District, Ward } from '../api/geo.api'


export default function CheckoutPage() {
  const { items, getTotalPrice, clearCart, reloadCart } = useCart()
  const { user } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const buyNowItem = location.state?.buyNowItem




  const [formData, setFormData] = useState({
    fullName: user?.name || '',
    email: user?.email || '',
    phone: '',
    address: '',
    city: '',
    province: '',
    paymentMethod: 'cod'
  })

  const [isSubmitting, setIsSubmitting] = useState(false)
  const [couponCode, setCouponCode] = useState<string | null>(null)
  const [showQRModal, setShowQRModal] = useState(false)
  const [qrData, setQrData] = useState<any>(null)
  const [currentOrder, setCurrentOrder] = useState<any>(null)
  const [isCheckingPayment, setIsCheckingPayment] = useState(false)

  // Geo State
  const [provinces, setProvinces] = useState<Province[]>([]);
  const [districts, setDistricts] = useState<District[]>([]);
  const [wards, setWards] = useState<Ward[]>([]);

  const [selectedProvince, setSelectedProvince] = useState<Province | null>(null);
  const [selectedDistrict, setSelectedDistrict] = useState<District | null>(null);
  const [selectedWard, setSelectedWard] = useState<Ward | null>(null);

  // Xác định danh sách sản phẩm cần thanh toán (từ giỏ hàng hoặc mua ngay)
  const checkoutItems = buyNowItem ? [buyNowItem] : items;

  // Tính tổng tiền
  const checkoutTotal = buyNowItem
    ? buyNowItem.price * buyNowItem.quantity
    : getTotalPrice();

  // Load coupon từ localStorage
  useEffect(() => {

    const savedCouponCode = localStorage.getItem("coupon_code");
    if (savedCouponCode) {
      setCouponCode(savedCouponCode);
    }
  }, []);

  // Fetch Provinces
  useEffect(() => {
    const fetchProvinces = async () => {
      try {
        const data = await geoApi.getProvinces();
        setProvinces(data);
      } catch (error) {
        console.error("Failed to fetch provinces:", error);
      }
    };
    fetchProvinces();
  }, []);

  // Fetch Districts when Province changes
  useEffect(() => {
    if (selectedProvince) {
      const fetchDistricts = async () => {
        try {
          const data = await geoApi.getDistricts(selectedProvince.id);
          setDistricts(data);
          setWards([]);
          setSelectedDistrict(null);
          setSelectedWard(null);
        } catch (error) {
          console.error("Failed to fetch districts:", error);
        }
      };
      fetchDistricts();
    } else {
      setDistricts([]);
      setWards([]);
    }
  }, [selectedProvince]);

  // Fetch Wards when District changes
  useEffect(() => {
    if (selectedDistrict) {
      const fetchWards = async () => {
        try {
          const data = await geoApi.getWards(selectedDistrict.id);
          setWards(data);
          setSelectedWard(null);
        } catch (error) {
          console.error("Failed to fetch wards:", error);
        }
      };
      fetchWards();
    } else {
      setWards([]);
    }
  }, [selectedDistrict]);

  const handleProvinceChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const provinceId = Number(e.target.value);
    const province = provinces.find(p => p.id === provinceId) || null;
    setSelectedProvince(province);
    setFormData({ ...formData, province: province ? province.name : '' });
  };

  const handleDistrictChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const districtId = Number(e.target.value);
    const district = districts.find(d => d.id === districtId) || null;
    setSelectedDistrict(district);
    setFormData({ ...formData, city: district ? district.name : '' });
  };

  const handleWardChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const wardId = Number(e.target.value);
    const ward = wards.find(w => w.id === wardId) || null;
    setSelectedWard(ward);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    // Validate form
    if (!formData.fullName || !formData.phone || !formData.address || !formData.city) {
      alert('Vui lòng điền đầy đủ thông tin bắt buộc!')
      return
    }

    const checkoutData: CheckoutPayload = {
      full_name: formData.fullName.trim(),
      phone: formData.phone.trim(),
      address: formData.address.trim(),
      city: formData.city.trim(),
      province: formData.province?.trim() || undefined,
      province_id: selectedProvince?.id,
      district_id: selectedDistrict?.id,
      ward_id: selectedWard?.id,
      payment_method: formData.paymentMethod === 'bank_transfer' ? 'bank_transfer' : 'cod'
    }

    // Thêm coupon_code nếu có
    if (couponCode) {
      checkoutData.coupon_code = couponCode.trim();
    }

    setIsSubmitting(true)
    try {
      console.log('Sending checkout data:', checkoutData)

      let response;
      let order;

      if (buyNowItem) {
        // Xử lý tạo đơn hàng trực tiếp
        const orderData = {
          ...checkoutData,
          items: [{
            product_id: buyNowItem.product_id,
            variant_id: buyNowItem.variant_id,
            quantity: buyNowItem.quantity,
            price: buyNowItem.price
          }],
          total_price: checkoutTotal
        };
        console.log('Creating direct order:', orderData);
        // createOrder returns { message: string, data: Order } usually, need to check API response structure
        const result = await createOrder(orderData);
        // Assuming createOrder returns the order object or { data: order }
        // Looking at order.api.ts: return res.data.data. So result is the order object.
        order = result;
        response = { order, qr_code: order.qr_code }; // Mock response structure to match below logic if needed
      } else {
        const res = await checkout(checkoutData)
        response = res;
        order = res.order;
      }

      console.log('Checkout response:', response)
      console.log('Order created:', order);

      if (!order) {
        throw new Error('Không nhận được dữ liệu đơn hàng từ server')
      }

      // Nếu là chuyển khoản và có QR code
      // Check response.qr_code for checkout() or order.qr_code for createOrder()
      const qrCode = (response && response.qr_code) || (order && order.qr_code);

      if (formData.paymentMethod === 'bank_transfer' && qrCode) {
        setQrData(qrCode)
        setCurrentOrder(order)
        setShowQRModal(true)
        setIsCheckingPayment(true)
        startPaymentPolling(order.id)
      } else {
        // Success logic
        if (!buyNowItem) {
          // Chỉ xóa giỏ hàng nếu là checkout thường
          await clearCart()
          await reloadCart()
        }

        localStorage.removeItem("applied_coupon");
        localStorage.removeItem("coupon_discount");
        localStorage.removeItem("coupon_code");

        navigate('/dat-hang-thanh-cong', {
          state: { order }
        })
      }
    } catch (err: any) {
      console.error('Checkout error:', err)
      console.error('Error response:', err?.response?.data)
      console.error('Error status:', err?.response?.status)

      let errorMessage = 'Đặt hàng thất bại! Vui lòng thử lại.'

      if (err?.response?.data) {
        // Nếu có validation errors
        if (err.response.data.errors) {
          const errors = err.response.data.errors
          const errorList = Object.keys(errors).map(key => {
            return `${key}: ${errors[key].join(', ')}`
          }).join('\n')
          errorMessage = `Lỗi xác thực:\n${errorList}`
        } else if (err.response.data.error) {
          // Ưu tiên hiển thị error message chi tiết
          errorMessage = err.response.data.error
        } else if (err.response.data.message) {
          errorMessage = err.response.data.message
        }
      } else if (err?.message) {
        errorMessage = err.message
      }

      // Hiển thị lỗi chi tiết hơn
      alert(`Lỗi: ${errorMessage}\n\nVui lòng kiểm tra:\n- Thông tin đã điền đầy đủ chưa\n- Sản phẩm còn tồn kho không\n- Kết nối mạng`)
    } finally {
      setIsSubmitting(false)
    }
  }

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    })
  }

  // Hàm lấy URL ảnh đầy đủ
  const getImageUrl = (image: string | undefined | null) => {
    if (!image) return "https://via.placeholder.com/150?text=No+Image";
    if (image.startsWith("http")) return image;
    if (image.startsWith("/")) return `http://127.0.0.1:8000${image}`;
    return `http://127.0.0.1:8000/storage/${image}`;
  };

  // Polling để kiểm tra thanh toán
  const startPaymentPolling = (orderId: number) => {
    const pollInterval = setInterval(async () => {
      try {
        const token = localStorage.getItem("user_token") || sessionStorage.getItem("user_token");
        const response = await fetch(`http://127.0.0.1:8000/api/orders/${orderId}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.ok) {
          const order = await response.json();
          if (order.payment_status === 'paid') {
            clearInterval(pollInterval);
            setIsCheckingPayment(false);

            // Xóa giỏ hàng và coupon
            await clearCart()
            await reloadCart()
            localStorage.removeItem("applied_coupon");
            localStorage.removeItem("coupon_discount");
            localStorage.removeItem("coupon_code");

            // Đóng modal và chuyển đến trang thành công
            setShowQRModal(false);
            navigate('/dat-hang-thanh-cong', {
              state: { order }
            });
          }
        }
      } catch (err) {
        console.error('Lỗi kiểm tra thanh toán:', err);
      }
    }, 3000); // Kiểm tra mỗi 3 giây

    // Dừng polling sau 10 phút
    setTimeout(() => {
      clearInterval(pollInterval);
      setIsCheckingPayment(false);
    }, 600000);
  };


  if (items.length === 0 && !buyNowItem) {
    return (
      <div className="main" style={{ maxWidth: "1200px", margin: "0 auto", padding: "40px 20px" }}>
        <div style={{
          textAlign: "center",
          padding: "60px 20px",
          background: "#f9fafb",
          borderRadius: "12px"
        }}>
          <div style={{ fontSize: "64px", marginBottom: "20px" }}>🛒</div>
          <h1 style={{ marginBottom: "16px", color: "#1f2937" }}>Giỏ hàng của bạn đang trống</h1>
          <p style={{ color: "#6b7280", marginBottom: "32px", fontSize: "18px" }}>
            Hãy thêm sản phẩm vào giỏ hàng để tiếp tục thanh toán
          </p>
          <Link
            to="/san-pham"
            style={{
              display: "inline-block",
              padding: "14px 32px",
              background: "#3b82f6",
              color: "white",
              textDecoration: "none",
              borderRadius: "8px",
              fontSize: "16px",
              fontWeight: "600",
              transition: "all 0.2s"
            }}
            onMouseEnter={(e) => e.currentTarget.style.background = "#2563eb"}
            onMouseLeave={(e) => e.currentTarget.style.background = "#3b82f6"}
          >
            Tiếp tục mua sắm
          </Link>
        </div>
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
        Thanh toán
      </h1>

      <div style={{
        display: "grid",
        gridTemplateColumns: "1fr 400px",
        gap: "32px",
        alignItems: "start"
      }}>
        {/* FORM THANH TOÁN */}
        <div style={{
          background: "white",
          borderRadius: "12px",
          padding: "32px",
          boxShadow: "0 1px 3px rgba(0,0,0,0.1)",
          border: "1px solid #e5e7eb"
        }}>
          <h2 style={{
            margin: 0,
            marginBottom: "24px",
            fontSize: "24px",
            fontWeight: "700",
            color: "#1f2937",
            borderBottom: "2px solid #e5e7eb",
            paddingBottom: "16px"
          }}>
            Thông tin giao hàng
          </h2>

          <form onSubmit={handleSubmit} style={{ display: "flex", flexDirection: "column", gap: "20px" }}>
            <div>
              <label style={{
                display: "block",
                marginBottom: "8px",
                fontSize: "14px",
                fontWeight: "600",
                color: "#374151"
              }}>
                Họ và tên *
              </label>
              <input
                name="fullName"
                placeholder="Nhập họ và tên"
                value={formData.fullName}
                onChange={handleChange}
                required
                style={{
                  width: "100%",
                  padding: "12px 16px",
                  border: "1px solid #d1d5db",
                  borderRadius: "8px",
                  fontSize: "16px",
                  transition: "all 0.2s",
                  boxSizing: "border-box"
                }}
                onFocus={(e) => {
                  e.currentTarget.style.borderColor = "#3b82f6";
                  e.currentTarget.style.outline = "none";
                  e.currentTarget.style.boxShadow = "0 0 0 3px rgba(59, 130, 246, 0.1)";
                }}
                onBlur={(e) => {
                  e.currentTarget.style.borderColor = "#d1d5db";
                  e.currentTarget.style.boxShadow = "none";
                }}
              />
            </div>

            <div>
              <label style={{
                display: "block",
                marginBottom: "8px",
                fontSize: "14px",
                fontWeight: "600",
                color: "#374151"
              }}>
                Email *
              </label>
              <input
                name="email"
                type="email"
                placeholder="Nhập email"
                value={formData.email}
                onChange={handleChange}
                required
                style={{
                  width: "100%",
                  padding: "12px 16px",
                  border: "1px solid #d1d5db",
                  borderRadius: "8px",
                  fontSize: "16px",
                  transition: "all 0.2s",
                  boxSizing: "border-box"
                }}
                onFocus={(e) => {
                  e.currentTarget.style.borderColor = "#3b82f6";
                  e.currentTarget.style.outline = "none";
                  e.currentTarget.style.boxShadow = "0 0 0 3px rgba(59, 130, 246, 0.1)";
                }}
                onBlur={(e) => {
                  e.currentTarget.style.borderColor = "#d1d5db";
                  e.currentTarget.style.boxShadow = "none";
                }}
              />
            </div>

            <div>
              <label style={{
                display: "block",
                marginBottom: "8px",
                fontSize: "14px",
                fontWeight: "600",
                color: "#374151"
              }}>
                Số điện thoại *
              </label>
              <input
                name="phone"
                placeholder="Nhập số điện thoại"
                value={formData.phone}
                onChange={handleChange}
                required
                style={{
                  width: "100%",
                  padding: "12px 16px",
                  border: "1px solid #d1d5db",
                  borderRadius: "8px",
                  fontSize: "16px",
                  transition: "all 0.2s",
                  boxSizing: "border-box"
                }}
                onFocus={(e) => {
                  e.currentTarget.style.borderColor = "#3b82f6";
                  e.currentTarget.style.outline = "none";
                  e.currentTarget.style.boxShadow = "0 0 0 3px rgba(59, 130, 246, 0.1)";
                }}
                onBlur={(e) => {
                  e.currentTarget.style.borderColor = "#d1d5db";
                  e.currentTarget.style.boxShadow = "none";
                }}
              />
            </div>

            <div>
              <label style={{
                display: "block",
                marginBottom: "8px",
                fontSize: "14px",
                fontWeight: "600",
                color: "#374151"
              }}>
                Địa chỉ *
              </label>
              <input
                name="address"
                placeholder="Nhập địa chỉ"
                value={formData.address}
                onChange={handleChange}
                required
                style={{
                  width: "100%",
                  padding: "12px 16px",
                  border: "1px solid #d1d5db",
                  borderRadius: "8px",
                  fontSize: "16px",
                  transition: "all 0.2s",
                  boxSizing: "border-box"
                }}
                onFocus={(e) => {
                  e.currentTarget.style.borderColor = "#3b82f6";
                  e.currentTarget.style.outline = "none";
                  e.currentTarget.style.boxShadow = "0 0 0 3px rgba(59, 130, 246, 0.1)";
                }}
                onBlur={(e) => {
                  e.currentTarget.style.borderColor = "#d1d5db";
                  e.currentTarget.style.boxShadow = "none";
                }}
              />
            </div>

            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "16px" }}>
              <div>
                <label style={{
                  display: "block",
                  marginBottom: "8px",
                  fontSize: "14px",
                  fontWeight: "600",
                  color: "#374151"
                }}>
                  Tỉnh/Thành phố *
                </label>
              </div>
              <div>
                <label style={{
                  display: "block",
                  marginBottom: "8px",
                  fontSize: "14px",
                  fontWeight: "600",
                  color: "#374151"
                }}>
                </label>
                <select
                  name="province"
                  value={selectedProvince?.id || ''}
                  onChange={handleProvinceChange}
                  required
                  style={{
                    width: "100%",
                    padding: "12px 16px",
                    border: "1px solid #d1d5db",
                    borderRadius: "8px",
                    fontSize: "16px",
                    background: "white",
                    cursor: "pointer",
                    transition: "all 0.2s",
                    boxSizing: "border-box"
                  }}
                  onFocus={(e) => {
                    e.currentTarget.style.borderColor = "#3b82f6";
                    e.currentTarget.style.outline = "none";
                    e.currentTarget.style.boxShadow = "0 0 0 3px rgba(59,130,246,0.1)";
                  }}
                  onBlur={(e) => {
                    e.currentTarget.style.borderColor = "#d1d5db";
                    e.currentTarget.style.boxShadow = "none";
                  }}
                >
                  <option value="">-- Chọn Tỉnh / Thành phố --</option>
                  {provinces.map((p) => (
                    <option key={p.id} value={p.id}>{p.name}</option>
                  ))}
                </select>
              </div>

              <div>
                <label style={{
                  display: "block",
                  marginBottom: "8px",
                  fontSize: "14px",
                  fontWeight: "600",
                  color: "#374151"
                }}>
                  Quận/Huyện *
                </label>
                <select
                  name="city"
                  value={selectedDistrict?.id || ''}
                  onChange={handleDistrictChange}
                  required
                  disabled={!selectedProvince}
                  style={{
                    width: "100%",
                    padding: "12px 16px",
                    border: "1px solid #d1d5db",
                    borderRadius: "8px",
                    fontSize: "16px",
                    background: !selectedProvince ? "#f3f4f6" : "white",
                    cursor: !selectedProvince ? "not-allowed" : "pointer",
                    transition: "all 0.2s",
                    boxSizing: "border-box"
                  }}
                  onFocus={(e) => {
                    if (selectedProvince) {
                      e.currentTarget.style.borderColor = "#3b82f6";
                      e.currentTarget.style.outline = "none";
                      e.currentTarget.style.boxShadow = "0 0 0 3px rgba(59, 130, 246, 0.1)";
                    }
                  }}
                  onBlur={(e) => {
                    e.currentTarget.style.borderColor = "#d1d5db";
                    e.currentTarget.style.boxShadow = "none";
                  }}
                >
                  <option value="">-- Chọn Quận / Huyện --</option>
                  {districts.map((d) => (
                    <option key={d.id} value={d.id}>{d.name}</option>
                  ))}
                </select>
              </div>

              <div>
                <label style={{
                  display: "block",
                  marginBottom: "8px",
                  fontSize: "14px",
                  fontWeight: "600",
                  color: "#374151"
                }}>
                  Phường/Xã *
                </label>
                <select
                  name="ward"
                  value={selectedWard?.id || ''}
                  onChange={handleWardChange}
                  required
                  disabled={!selectedDistrict}
                  style={{
                    width: "100%",
                    padding: "12px 16px",
                    border: "1px solid #d1d5db",
                    borderRadius: "8px",
                    fontSize: "16px",
                    background: !selectedDistrict ? "#f3f4f6" : "white",
                    cursor: !selectedDistrict ? "not-allowed" : "pointer",
                    transition: "all 0.2s",
                    boxSizing: "border-box"
                  }}
                >
                  <option value="">-- Chọn Phường / Xã --</option>
                  {wards.map((w) => (
                    <option key={w.id} value={w.id}>{w.name}</option>
                  ))}
                </select>
              </div>
            </div>

            <div style={{ marginTop: "8px" }}>
              <label style={{
                display: "block",
                marginBottom: "8px",
                fontSize: "14px",
                fontWeight: "600",
                color: "#374151"
              }}>
                Phương thức thanh toán *
              </label>
              <select
                name="paymentMethod"
                value={formData.paymentMethod}
                onChange={handleChange}
                style={{
                  width: "100%",
                  padding: "12px 16px",
                  border: "1px solid #d1d5db",
                  borderRadius: "8px",
                  fontSize: "16px",
                  background: "white",
                  cursor: "pointer",
                  transition: "all 0.2s",
                  boxSizing: "border-box"
                }}
                onFocus={(e) => {
                  e.currentTarget.style.borderColor = "#3b82f6";
                  e.currentTarget.style.outline = "none";
                  e.currentTarget.style.boxShadow = "0 0 0 3px rgba(59, 130, 246, 0.1)";
                }}
                onBlur={(e) => {
                  e.currentTarget.style.borderColor = "#d1d5db";
                  e.currentTarget.style.boxShadow = "none";
                }}
              >
                <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                <option value="bank_transfer">Chuyển khoản ngân hàng</option>
              </select>
            </div>

            <button
              type="submit"
              disabled={isSubmitting}
              style={{
                width: "100%",
                padding: "16px",
                background: isSubmitting ? "#9ca3af" : "#3b82f6",
                color: "white",
                border: "none",
                borderRadius: "8px",
                fontSize: "18px",
                fontWeight: "600",
                cursor: isSubmitting ? "not-allowed" : "pointer",
                transition: "all 0.2s",
                marginTop: "8px"
              }}
              onMouseEnter={(e) => {
                if (!isSubmitting) {
                  e.currentTarget.style.background = "#2563eb";
                  e.currentTarget.style.transform = "translateY(-2px)";
                  e.currentTarget.style.boxShadow = "0 4px 12px rgba(59, 130, 246, 0.4)";
                }
              }}
              onMouseLeave={(e) => {
                if (!isSubmitting) {
                  e.currentTarget.style.background = "#3b82f6";
                  e.currentTarget.style.transform = "translateY(0)";
                  e.currentTarget.style.boxShadow = "none";
                }
              }}
            >
              {isSubmitting ? "Đang xử lý..." : "Đặt hàng"}
            </button>
          </form>
        </div>

        {/* TÓM TẮT ĐƠN HÀNG */}
        <div style={{
          position: "sticky",
          top: "20px",
          background: "white",
          borderRadius: "12px",
          padding: "24px",
          boxShadow: "0 1px 3px rgba(0,0,0,0.1)",
          border: "1px solid #e5e7eb",
          height: "fit-content"
        }}>
          <h2 style={{
            margin: 0,
            marginBottom: "24px",
            fontSize: "20px",
            fontWeight: "700",
            color: "#1f2937"
          }}>
            Đơn hàng của bạn
          </h2>

          {/* Danh sách sản phẩm */}
          <div style={{ marginBottom: "24px", maxHeight: "400px", overflowY: "auto" }}>
            {checkoutItems.map((item) => (
              <div
                key={item.id || item.product_id}
                style={{
                  display: "flex",
                  gap: "12px",
                  padding: "16px",
                  background: "#f9fafb",
                  borderRadius: "8px",
                  marginBottom: "12px",
                  border: "1px solid #e5e7eb"
                }}
              >
                <img
                  src={getImageUrl(item.image)}
                  alt={item.name}
                  onError={(e) => {
                    e.currentTarget.src = "https://via.placeholder.com/150?text=No+Image";
                  }}
                  style={{
                    width: "80px",
                    height: "80px",
                    objectFit: "cover",
                    borderRadius: "8px",
                    border: "1px solid #e5e7eb",
                    flexShrink: 0
                  }}
                />

                <div style={{ flex: 1, minWidth: 0 }}>
                  <h4 style={{
                    margin: 0,
                    marginBottom: "4px",
                    fontSize: "14px",
                    fontWeight: "600",
                    color: "#1f2937",
                    overflow: "hidden",
                    textOverflow: "ellipsis",
                    whiteSpace: "nowrap"
                  }}>
                    {item.name}
                  </h4>
                  {(item.color || item.size) && (
                    <div style={{ display: "flex", gap: "8px", flexWrap: "wrap", marginBottom: "4px" }}>
                      {item.color && (
                        <span style={{
                          padding: "2px 8px",
                          background: "#f3f4f6",
                          borderRadius: "4px",
                          fontSize: "12px",
                          color: "#6b7280"
                        }}>
                          {item.color}
                        </span>
                      )}
                      {item.size && (
                        <span style={{
                          padding: "2px 8px",
                          background: "#f3f4f6",
                          borderRadius: "4px",
                          fontSize: "12px",
                          color: "#6b7280"
                        }}>
                          Size {item.size}
                        </span>
                      )}
                    </div>
                  )}
                  <div style={{
                    display: "flex",
                    justifyContent: "space-between",
                    alignItems: "center",
                    marginTop: "8px"
                  }}>
                    <span style={{ fontSize: "12px", color: "#6b7280" }}>
                      SL: {item.quantity}
                    </span>
                    <span style={{ fontSize: "14px", fontWeight: "600", color: "#059669" }}>
                      {formatPrice(item.price)}
                    </span>
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Tổng tiền */}
          <div style={{
            borderTop: "2px solid #e5e7eb",
            paddingTop: "16px",
            marginTop: "16px"
          }}>
            <div style={{
              display: "flex",
              justifyContent: "space-between",
              marginBottom: "12px"
            }}>
              <span style={{ color: "#6b7280" }}>Tạm tính:</span>
              <span style={{ fontWeight: "600" }}>
                {formatPrice(checkoutTotal)}
              </span>
            </div>
            <div style={{
              display: "flex",
              justifyContent: "space-between",
              marginBottom: "12px"
            }}>
              <span style={{ color: "#6b7280" }}>Phí vận chuyển:</span>
              <span style={{ fontWeight: "600", color: "#059669" }}>Miễn phí</span>
            </div>

            {!buyNowItem && couponCode && (
              <div style={{
                display: "flex",
                justifyContent: "space-between",
                marginBottom: "12px"
              }}>
                <span style={{ color: "#6b7280" }}>Mã giảm giá ({couponCode}):</span>
                <span style={{ fontWeight: "600", color: "#ef4444" }}>
                  -{formatPrice(Number(localStorage.getItem("coupon_discount") || 0))}
                </span>
              </div>
            )}

            <div style={{
              display: "flex",
              justifyContent: "space-between",
              paddingTop: "16px",
              borderTop: "2px solid #e5e7eb",
              marginTop: "16px"
            }}>
              <span style={{ fontSize: "18px", fontWeight: "700", color: "#1f2937" }}>
                Tổng cộng:
              </span>
              <span style={{
                fontSize: "24px",
                fontWeight: "700",
                color: "#059669"
              }}>
                {formatPrice(Math.max(0, checkoutTotal - (buyNowItem ? 0 : Number(localStorage.getItem("coupon_discount") || 0))))}
              </span>

            </div>

          </div>

          <Link
            to="/gio-hang"
            style={{
              display: "block",
              width: "100%",
              padding: "12px",
              background: "white",
              color: "#3b82f6",
              textDecoration: "none",
              borderRadius: "8px",
              fontSize: "14px",
              fontWeight: "600",
              textAlign: "center",
              border: "2px solid #3b82f6",
              transition: "all 0.2s",
              marginTop: "16px"
            }}
            onMouseEnter={(e) => {
              e.currentTarget.style.background = "#eff6ff";
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.background = "white";
            }}
          >
            ← Quay lại giỏ hàng
          </Link>
        </div>
      </div>

      {/* MODAL QR CODE */}
      {showQRModal && qrData && (
        <div style={{
          position: "fixed",
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          background: "rgba(0, 0, 0, 0.7)",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          zIndex: 1000,
          padding: "20px"
        }}>
          <div style={{
            background: "white",
            borderRadius: "16px",
            padding: "32px",
            maxWidth: "500px",
            width: "100%",
            position: "relative"
          }}>
            <button
              onClick={() => {
                setShowQRModal(false);
                setIsCheckingPayment(false);
              }}
              style={{
                position: "absolute",
                top: "16px",
                right: "16px",
                background: "transparent",
                border: "none",
                fontSize: "24px",
                cursor: "pointer",
                color: "#6b7280",
                width: "32px",
                height: "32px",
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
                borderRadius: "50%",
                transition: "all 0.2s"
              }}
              onMouseEnter={(e) => {
                e.currentTarget.style.background = "#f3f4f6";
                e.currentTarget.style.color = "#1f2937";
              }}
              onMouseLeave={(e) => {
                e.currentTarget.style.background = "transparent";
                e.currentTarget.style.color = "#6b7280";
              }}
            >
              ×
            </button>

            <div style={{ textAlign: "center" }}>
              <h2 style={{
                margin: "0 0 8px 0",
                fontSize: "24px",
                fontWeight: "700",
                color: "#1f2937"
              }}>
                Quét mã QR để thanh toán
              </h2>
              <p style={{
                margin: "0 0 24px 0",
                fontSize: "14px",
                color: "#6b7280"
              }}>
                Đơn hàng #{currentOrder?.id}
              </p>

              <div style={{
                display: "flex",
                justifyContent: "center",
                marginBottom: "24px"
              }}>
                <img
                  src={qrData.image_url}
                  alt="QR Code"
                  style={{
                    width: "300px",
                    height: "300px",
                    border: "1px solid #e5e7eb",
                    borderRadius: "8px",
                    padding: "16px",
                    background: "white"
                  }}
                />
              </div>

              <div style={{
                background: "#f9fafb",
                borderRadius: "8px",
                padding: "16px",
                marginBottom: "24px",
                textAlign: "left"
              }}>
                <div style={{ marginBottom: "8px" }}>
                  <span style={{ fontSize: "14px", color: "#6b7280" }}>Ngân hàng: </span>
                  <strong style={{ fontSize: "14px", color: "#1f2937" }}>{qrData.bank_name}</strong>
                </div>
                <div style={{ marginBottom: "8px" }}>
                  <span style={{ fontSize: "14px", color: "#6b7280" }}>Số tài khoản: </span>
                  <strong style={{ fontSize: "14px", color: "#1f2937" }}>{qrData.bank_account}</strong>
                </div>
                <div style={{ marginBottom: "8px" }}>
                  <span style={{ fontSize: "14px", color: "#6b7280" }}>Chủ tài khoản: </span>
                  <strong style={{ fontSize: "14px", color: "#1f2937" }}>{qrData.account_name}</strong>
                </div>
                <div>
                  <span style={{ fontSize: "14px", color: "#6b7280" }}>Số tiền: </span>
                  <strong style={{ fontSize: "16px", color: "#059669" }}>
                    {new Intl.NumberFormat('vi-VN', {
                      style: 'decimal',
                      minimumFractionDigits: 0,
                      maximumFractionDigits: 0,
                    }).format(qrData.amount)}đ
                  </strong>
                </div>
              </div>

              {isCheckingPayment && (
                <div style={{
                  padding: "16px",
                  background: "#eff6ff",
                  borderRadius: "8px",
                  marginBottom: "16px"
                }}>
                  <div style={{
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    gap: "12px"
                  }}>
                    <div style={{
                      width: "20px",
                      height: "20px",
                      border: "3px solid #3b82f6",
                      borderTop: "3px solid transparent",
                      borderRadius: "50%",
                      animation: "spin 1s linear infinite"
                    }}></div>
                    <span style={{ fontSize: "14px", color: "#3b82f6", fontWeight: "600" }}>
                      Đang chờ thanh toán...
                    </span>
                  </div>
                  <p style={{
                    margin: "8px 0 0 0",
                    fontSize: "12px",
                    color: "#6b7280",
                    textAlign: "center"
                  }}>
                    Hệ thống sẽ tự động xác nhận khi bạn thanh toán thành công
                  </p>
                </div>
              )}

              <div style={{ display: "flex", gap: "12px" }}>

                <button
                  onClick={() => {
                    setShowQRModal(false);
                    setIsCheckingPayment(false);
                  }}
                  style={{
                    flex: 1,
                    padding: "12px 24px",
                    background: "#f3f4f6",
                    color: "#374151",
                    border: "none",
                    borderRadius: "8px",
                    fontSize: "16px",
                    fontWeight: "600",
                    cursor: "pointer",
                    transition: "all 0.2s"
                  }}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.background = "#e5e7eb";
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.background = "#f3f4f6";
                  }}
                >
                  Đóng
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      <style>{`
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  )
}
