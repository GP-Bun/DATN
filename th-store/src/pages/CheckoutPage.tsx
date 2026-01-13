import { useCart } from '../store/CartContext'
import { useAuth } from '../store/AuthContext'
import { useState, useEffect } from 'react'
import { useNavigate, Link, useLocation } from 'react-router-dom'
import { checkout } from '../api/checkout.api'
import { createOrder } from '../api/order.api'
import type { CheckoutPayload } from '../api/checkout.api'
import { formatPrice } from '../utils/formatPrice'
import { getAvailableCoupons, applyCoupon } from '../api/coupon.api'
import { geoApi } from '../api/geo.api'
import type { Province, District, Ward } from '../api/geo.api'
import api from '../api/api'
import { toast } from 'react-hot-toast'
import Swal from 'sweetalert2'

interface Address {
  id: number
  receiver_name: string
  receiver_phone: string
  line1: string
  province: {
    id: number
    name: string
  }
  district: {
    id: number
    name: string
  }
  ward: {
    id: number
    name: string
  }
  zip?: string
  is_default: boolean
}


export default function CheckoutPage() {
  const { items, reloadCart } = useCart()
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
  const [discount, setDiscount] = useState(0)
  const [availableCoupons, setAvailableCoupons] = useState<any[]>([])
  const [loadingCoupons, setLoadingCoupons] = useState(false)
  const [showQRModal, setShowQRModal] = useState(false)
  const [qrData, setQrData] = useState<any>(null)
  const [currentOrder, setCurrentOrder] = useState<any>(null)
  const [isCheckingPayment, setIsCheckingPayment] = useState(false)
  const [isConfirmingPayment, setIsConfirmingPayment] = useState(false)

  // Address selection state
  const [savedAddresses, setSavedAddresses] = useState<Address[]>([])
  const [addressType, setAddressType] = useState<'saved' | 'new'>(user ? 'saved' : 'new')
  const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null)

  // Geo State
  const [provinces, setProvinces] = useState<Province[]>([]);
  const [districts, setDistricts] = useState<District[]>([]);
  const [wards, setWards] = useState<Ward[]>([]);

  const [selectedProvince, setSelectedProvince] = useState<Province | null>(null);
  const [selectedDistrict, setSelectedDistrict] = useState<District | null>(null);
  const [selectedWard, setSelectedWard] = useState<Ward | null>(null);

  // Xác định danh sách sản phẩm cần thanh toán (từ giỏ hàng hoặc mua ngay)
  const selectedItemIds = location.state?.selectedItemIds;

  const checkoutItems = buyNowItem
    ? [buyNowItem]
    : (selectedItemIds
      ? items.filter(item => selectedItemIds.includes(Number(item.id)))
      : items);

  // Tính tổng tiền
  const checkoutTotal = buyNowItem
    ? buyNowItem.price * buyNowItem.quantity
    : checkoutItems.reduce((sum, item) => sum + item.price * item.quantity, 0);

  // Load coupon từ localStorage
  useEffect(() => {
    const savedCouponCode = localStorage.getItem("coupon_code");
    const savedDiscount = localStorage.getItem("coupon_discount");
    if (savedCouponCode) {
      setCouponCode(savedCouponCode);
    }
    if (savedDiscount) {
      setDiscount(Number(savedDiscount));
    }
  }, []);

  // Fetch Available Coupons for Buy Now or Selected Items
  useEffect(() => {
    const fetchCoupons = async () => {
      try {
        setLoadingCoupons(true);
        const data = await getAvailableCoupons(checkoutTotal);
        setAvailableCoupons(data.coupons || []);
      } catch (error) {
        console.error("Failed to fetch coupons:", error);
      } finally {
        setLoadingCoupons(false);
      }
    };
    if (checkoutTotal > 0) {
      fetchCoupons();
    }
  }, [checkoutTotal]);

  const handleApplyCoupon = async (code: string) => {
    try {
      const result = await applyCoupon(code, checkoutTotal);
      if (result.ok) {
        setCouponCode(code);
        setDiscount(result.discount || 0);
        toast.success(`Đã áp dụng mã: ${code}`);
      } else {
        toast.error(result.message || 'Mã không hợp lệ');
      }
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Lỗi khi áp dụng mã giảm giá');
    }
  };

  const handleRemoveCoupon = () => {
    setCouponCode(null);
    setDiscount(0);
    localStorage.removeItem("coupon_code");
    localStorage.removeItem("coupon_discount");
    localStorage.removeItem("applied_coupon");
  };

  // Fetch Saved Addresses
  useEffect(() => {
    if (user) {
      const fetchAddresses = async () => {
        try {
          const res = await api.get('/addresses')
          const list = res.data.data || res.data
          setSavedAddresses(list)

          // Auto select default address
          const defaultAddr = list.find((a: Address) => a.is_default) || list[0]
          if (defaultAddr) {
            setSelectedAddressId(defaultAddr.id)
            applySelectedAddress(defaultAddr)
          } else {
            setAddressType('new')
          }
        } catch (error) {
          console.error("Failed to fetch addresses:", error)
          setAddressType('new')
        }
      }
      fetchAddresses()
    }
  }, [user])

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
          // Only clear if not switching from saved address or if the province actually changed manually
          if (addressType === 'new') {
            setSelectedDistrict(null);
            setSelectedWard(null);
          }
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
          if (addressType === 'new') {
            setSelectedWard(null);
          }
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
      toast.error('Vui lòng điền đầy đủ thông tin bắt buộc!')
      return
    }

    const checkoutData: CheckoutPayload = {
      address_id: (addressType === 'saved' && selectedAddressId) ? selectedAddressId : undefined,
      full_name: formData.fullName.trim(),
      phone: formData.phone.trim(),
      address: formData.address.trim(),
      city: formData.city.trim(),
      province: formData.province?.trim() || undefined,
      province_id: selectedProvince?.id,
      district_id: selectedDistrict?.id,
      ward_id: selectedWard?.id,
      payment_method: formData.paymentMethod as 'bank_transfer' | 'cod'
    }

    // Thêm coupon_code nếu có
    if (couponCode) {
      checkoutData.coupon_code = couponCode.trim();
    }

    // Thêm cart_item_ids nếu có
    if (!buyNowItem && selectedItemIds) {
      checkoutData.cart_item_ids = selectedItemIds;
    }

    setIsSubmitting(true)
    try {
      console.log('Sending checkout data:', checkoutData)

      let response;
      let order;

      if (buyNowItem) {
        // Xử lý tạo đơn hàng trực tiếp
        const orderData = {
          items: [{
            product_id: buyNowItem.product_id,
            variant_id: buyNowItem.variant_id,
            quantity: buyNowItem.quantity,
            price: buyNowItem.price
          }],
          address: {
            receiver_name: checkoutData.full_name,
            receiver_phone: checkoutData.phone,
            line1: checkoutData.address,
            province_id: checkoutData.province_id,
            district_id: checkoutData.district_id,
            ward_id: checkoutData.ward_id,
          },
          address_id: checkoutData.address_id,
          payment_method: checkoutData.payment_method,
          total_price: checkoutTotal,
          coupon_code: checkoutData.coupon_code
        };
        console.log('Creating direct order:', orderData);
        const result = await createOrder(orderData);

        // Handle result (createOrder returns res.data.data)
        if (result && result.order) {
          order = result.order;
          response = result;
        } else {
          order = result;
          response = { order, qr_code: order?.qr_code };
        }
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

      // Xử lý theo phương thức thanh toán
      if (formData.paymentMethod === 'bank_transfer') {
        // Chuyển khoản ngân hàng - hiển thị QR code
        if (response.qr_code) {
          setQrData(response.qr_code);
          setCurrentOrder(order);
          setShowQRModal(true);
          setIsCheckingPayment(true);
          
          // Clear cart và coupon
          if (!buyNowItem) {
            await reloadCart()
          }
          localStorage.removeItem("applied_coupon");
          localStorage.removeItem("coupon_discount");
          localStorage.removeItem("coupon_code");
        } else {
          throw new Error('Không tạo được QR code thanh toán');
        }
      } else {
        // COD - chuyển đến trang thành công
        if (!buyNowItem) {
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
      Swal.fire({
        title: 'Lỗi đặt hàng',
        html: `<div style="text-align: left; font-size: 14px;">
                <p><strong>Chi tiết:</strong> ${errorMessage}</p>
                <p>Vui lòng kiểm tra:</p>
                <ul>
                  <li>Thông tin đã điền đầy đủ chưa</li>
                  <li>Sản phẩm còn tồn kho không</li>
                  <li>Kết nối mạng</li>
                </ul>
               </div>`,
        icon: 'error'
      })
    } finally {
      setIsSubmitting(false)
    }
  }

  const applySelectedAddress = (addr: Address) => {
    setFormData(prev => ({
      ...prev,
      fullName: addr.receiver_name,
      phone: addr.receiver_phone,
      address: addr.line1,
      province: addr.province.name,
      city: addr.district.name
    }))
    setSelectedProvince({ id: addr.province.id, name: addr.province.name } as Province)
    setSelectedDistrict({ id: addr.district.id, name: addr.district.name } as District)
    setSelectedWard({ id: addr.ward.id, name: addr.ward.name } as Ward)
  }

  const handleAddressTypeChange = (type: 'saved' | 'new') => {
    setAddressType(type)
    if (type === 'new') {
      setSelectedAddressId(null)
      setFormData({
        ...formData,
        fullName: user?.name || '',
        phone: '',
        address: '',
        province: '',
        city: '',
      })
      setSelectedProvince(null)
      setSelectedDistrict(null)
      setSelectedWard(null)
    } else {
      const addr = savedAddresses.find(a => a.id === selectedAddressId) || savedAddresses[0]
      if (addr) {
        setSelectedAddressId(addr.id)
        applySelectedAddress(addr)
      }
    }
  }

  const handleSavedAddressChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const id = Number(e.target.value)
    setSelectedAddressId(id)
    const addr = savedAddresses.find(a => a.id === id)
    if (addr) {
      applySelectedAddress(addr)
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

            // Xóa giỏ hàng trên server khi thanh toán thành công
            try {
              // Nếu dùng selective checkout, ta xoá các item đã chọn. 
              // Ở đây đơn giản nhất là clear toàn bộ hoặc gọi delete từng item.
              // Vì backend không có endpoint xoá list ID, ta dùng clear() hoặc delete cho đơn giản.
              const cartRes = await fetch(`http://127.0.0.1:8000/api/cart`, {
                method: 'DELETE',
                headers: {
                  'Authorization': `Bearer ${token}`,
                  'Accept': 'application/json'
                }
              });
              if (!cartRes.ok) console.error('Failed to clear cart after payment');
            } catch (cartErr) {
              console.error('Error clearing cart:', cartErr);
            }

            // reloadCart sẽ lấy giỏ hàng mới
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
            {/* Address Selection */}
            {user && savedAddresses.length > 0 && (
              <div style={{ padding: "16px", background: "#f8fafc", borderRadius: "8px", border: "1px solid #e2e8f0", marginBottom: "8px" }}>
                <div style={{ display: "flex", gap: "20px", marginBottom: "16px" }}>
                  <label style={{ display: "flex", alignItems: "center", gap: "8px", cursor: "pointer", fontSize: "15px", fontWeight: "600" }}>
                    <input
                      type="radio"
                      name="addressType"
                      checked={addressType === 'saved'}
                      onChange={() => handleAddressTypeChange('saved')}
                    />
                    Dùng địa chỉ đã lưu
                  </label>
                  <label style={{ display: "flex", alignItems: "center", gap: "8px", cursor: "pointer", fontSize: "15px", fontWeight: "600" }}>
                    <input
                      type="radio"
                      name="addressType"
                      checked={addressType === 'new'}
                      onChange={() => handleAddressTypeChange('new')}
                    />
                    Nhập địa chỉ mới
                  </label>
                </div>

                {addressType === 'saved' && (
                  <select
                    value={selectedAddressId || ''}
                    onChange={handleSavedAddressChange}
                    style={{
                      width: "100%",
                      padding: "12px",
                      borderRadius: "8px",
                      border: "1px solid #cbd5e1",
                      fontSize: "15px"
                    }}
                  >
                    {savedAddresses.map(addr => (
                      <option key={addr.id} value={addr.id}>
                        {addr.receiver_name} - {addr.receiver_phone} ({addr.line1}, {addr.ward.name}, {addr.district.name}, {addr.province.name})
                      </option>
                    ))}
                  </select>
                )}
              </div>
            )}

            <div style={{
              display: addressType === 'saved' ? 'none' : 'block'
            }}>
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
                required={addressType === 'new'}
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

            <div style={{
              display: addressType === 'saved' ? 'none' : 'block'
            }}>
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
                required={addressType === 'new'}
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

            <div style={{
              display: addressType === 'saved' ? 'none' : 'block'
            }}>
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
                required={addressType === 'new'}
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

            <div style={{
              display: addressType === 'saved' ? 'none' : 'block'
            }}>
              <label style={{
                display: "block",
                marginBottom: "8px",
                fontSize: "14px",
                fontWeight: "600",
                color: "#374151"
              }}>
                Địa chỉ chi tiết *
              </label>
              <input
                name="address"
                placeholder="Số nhà, tên đường..."
                value={formData.address}
                onChange={handleChange}
                required={addressType === 'new'}
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

            <div style={{
              display: addressType === 'saved' ? 'none' : 'grid',
              gridTemplateColumns: "1fr",
              gap: "20px"
            }}>
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
                <select
                  name="province"
                  value={selectedProvince?.id || ''}
                  onChange={handleProvinceChange}
                  required={addressType === 'new'}
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

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "16px" }}>
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
                    required={addressType === 'new'}
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
                    required={addressType === 'new'}
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

            {/* Mã giảm giá */}
            <div style={{ marginBottom: "16px" }}>
              <label style={{ display: "block", marginBottom: "8px", fontSize: "14px", fontWeight: "600", color: "#374151" }}>
                Mã giảm giá
              </label>
              {couponCode ? (
                <div style={{
                  padding: "12px",
                  background: "#d1fae5",
                  borderRadius: "8px",
                  border: "1px solid #10b981",
                  display: "flex",
                  justifyContent: "space-between",
                  alignItems: "center"
                }}>
                  <div>
                    <div style={{ fontWeight: "700", color: "#065f46" }}>{couponCode}</div>
                    <div style={{ fontSize: "12px", color: "#047857" }}>Tiết kiệm được {formatPrice(discount)}</div>
                  </div>
                  <button
                    onClick={handleRemoveCoupon}
                    style={{ background: "#ef4444", color: "white", border: "none", borderRadius: "4px", padding: "4px 8px", cursor: "pointer", fontSize: "12px" }}
                  >
                    Xóa
                  </button>
                </div>
              ) : (
                <div style={{ display: "flex", flexDirection: "column", gap: "8px" }}>
                  {loadingCoupons ? (
                    <div style={{ fontSize: "12px", color: "#6b7280" }}>Đang tải mã giảm giá...</div>
                  ) : availableCoupons.length > 0 ? (
                    <div style={{ maxHeight: "150px", overflowY: "auto", display: "flex", flexDirection: "column", gap: "6px" }}>
                      {availableCoupons.map((coupon) => (
                        <button
                          key={coupon.id}
                          onClick={() => handleApplyCoupon(coupon.code)}
                          disabled={!coupon.is_applicable}
                          style={{
                            textAlign: "left",
                            padding: "8px 12px",
                            borderRadius: "6px",
                            border: "1px solid #e5e7eb",
                            background: coupon.is_applicable ? "white" : "#f9fafb",
                            cursor: coupon.is_applicable ? "pointer" : "not-allowed",
                            fontSize: "13px",
                            opacity: coupon.is_applicable ? 1 : 0.6
                          }}
                        >
                          <div style={{ fontWeight: "600" }}>{coupon.code}</div>
                          <div style={{ fontSize: "11px", color: "#6b7280" }}>{coupon.description}</div>
                        </button>
                      ))}
                    </div>
                  ) : (
                    <div style={{ fontSize: "12px", color: "#6b7280" }}>Không có mã giảm giá khả dụng</div>
                  )}
                </div>
              )}
            </div>

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
                {formatPrice(Math.max(0, checkoutTotal - discount))}
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
                  onClick={async () => {
                    if (!currentOrder?.id) return;
                    
                    setIsConfirmingPayment(true);
                    try {
                      const response = await api.post(`/orders/${currentOrder.id}/confirm-payment`, {
                        transaction_id: `QR_${Date.now()}`
                      });
                      
                      toast.success('Xác nhận thanh toán thành công!');
                      setShowQRModal(false);
                      setIsCheckingPayment(false);
                      setIsConfirmingPayment(false);
                      
                      // Lấy đơn hàng đã cập nhật từ response
                      const updatedOrder = response.data?.order || currentOrder;
                      
                      // Chuyển đến trang thành công
                      navigate('/dat-hang-thanh-cong', {
                        state: { order: updatedOrder }
                      });
                    } catch (err: any) {
                      console.error('Lỗi xác nhận thanh toán:', err);
                      toast.error(err?.response?.data?.message || 'Có lỗi xảy ra khi xác nhận thanh toán');
                      setIsConfirmingPayment(false);
                    }
                  }}
                  disabled={isConfirmingPayment}
                  style={{
                    flex: 1,
                    padding: "12px 24px",
                    background: isConfirmingPayment ? "#9ca3af" : "#10b981",
                    color: "white",
                    border: "none",
                    borderRadius: "8px",
                    fontSize: "16px",
                    fontWeight: "600",
                    cursor: isConfirmingPayment ? "not-allowed" : "pointer",
                    transition: "all 0.2s"
                  }}
                  onMouseEnter={(e) => {
                    if (!isConfirmingPayment) {
                      e.currentTarget.style.background = "#059669";
                    }
                  }}
                  onMouseLeave={(e) => {
                    if (!isConfirmingPayment) {
                      e.currentTarget.style.background = "#10b981";
                    }
                  }}
                >
                  {isConfirmingPayment ? "Đang xử lý..." : "Thanh toán thành công"}
                </button>

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
