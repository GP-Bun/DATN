import { useState, useEffect } from 'react'
import { useAuth } from '../store/AuthContext'
import { useNavigate } from 'react-router-dom'
import axios from 'axios'
import { vietnamProvinces } from '../data/vietnam.provinces'
import { vietnamDistricts } from '../data/vietnam.districts'

interface Address {
  id: number
  receiver_name: string
  receiver_phone: string
  line1: string
  city: string
  province: string
  zip?: string
  is_default: boolean
}

const userApi = axios.create({ baseURL: "http://127.0.0.1:8000/api" })

// Interceptor để thêm token
userApi.interceptors.request.use((config) => {
  const token = localStorage.getItem("user_token") || sessionStorage.getItem("user_token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

export default function ProfilePage() {
  const { user, logoutUser } = useAuth()
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [addresses, setAddresses] = useState<Address[]>([])
  const [isEditingProfile, setIsEditingProfile] = useState(false)
  const [isEditingAddress, setIsEditingAddress] = useState(false)
  const [editingAddressId, setEditingAddressId] = useState<number | null>(null)
  const [avatarPreview, setAvatarPreview] = useState<string | null>(null)
  
  const [profileData, setProfileData] = useState({
    name: (user as any).name || '',
    email: (user as any).email || '',
    phone: (user as any).phone || '',
  })

  const [addressData, setAddressData] = useState({
    receiver_name: '',
    receiver_phone: '',
    line1: '',
    city: '',
    province: '',
    zip: '',
    is_default: false,
  })

  useEffect(() => {
    if (!user) {
      navigate('/dang-nhap')
      return
    }
    setProfileData({
      name: (user as any).name || '',
      email: (user as any).email || '',
      phone: (user as any).phone || '',
    })
    loadAddresses()
    loadUserProfile()
  }, [user])

  const loadUserProfile = async () => {
    try {
      const res = await userApi.get('/user-profile')
      const userData = res.data.user
      setProfileData({
        name: (userData as any).name || '',
        email: (userData as any).email || '',
        phone: (userData as any).phone || '',
      })
      if ((userData as any).avatar_url) {
        setAvatarPreview((userData as any) .avatar_url)
      }
    } catch (err) {
      console.error('Lỗi tải thông tin người dùng:', err)
    }
  }

  const loadAddresses = async () => {
    try {
      const res = await userApi.get('/addresses')
      setAddresses(res.data)
    } catch (err) {
      console.error('Lỗi tải địa chỉ:', err)
    }
  }

  const handleAvatarChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (file) {
      if (file.size > 2 * 1024 * 1024) {
        alert('Kích thước ảnh không được vượt quá 2MB')
        return
      }
      const reader = new FileReader()
      reader.onloadend = () => {
        setAvatarPreview(reader.result as string)
      }
      reader.readAsDataURL(file)
    }
  }

  const handleUpdateProfile = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    try {
      const formData = new FormData()
      formData.append('name', profileData.name)
      if (profileData.phone) formData.append('phone', profileData.phone)
      
      const avatarInput = document.getElementById('avatar-input') as HTMLInputElement
      if (avatarInput?.files?.[0]) {
        formData.append('avatar', avatarInput.files[0])
      }

      await userApi.post('/user-profile', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      
      alert('Cập nhật thông tin thành công!')
      setIsEditingProfile(false)
      await loadUserProfile()
      window.location.reload() // Reload để cập nhật user trong context
    } catch (err: any) {
      console.error(err)
      alert(err.response?.data?.message || 'Có lỗi xảy ra khi cập nhật thông tin')
    } finally {
      setLoading(false)
    }
  }

  const handleSaveAddress = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    try {
      if (editingAddressId) {
        await userApi.put(`/addresses/${editingAddressId}`, addressData)
        alert('Cập nhật địa chỉ thành công!')
      } else {
        await userApi.post('/addresses', addressData)
        alert('Thêm địa chỉ thành công!')
      }
      setIsEditingAddress(false)
      setEditingAddressId(null)
      setAddressData({
        receiver_name: '',
        receiver_phone: '',
        line1: '',
        city: '',
        province: '',
        zip: '',
        is_default: false,
      })
      await loadAddresses()
    } catch (err: any) {
      console.error(err)
      alert(err.response?.data?.message || 'Có lỗi xảy ra')
    } finally {
      setLoading(false)
    }
  }

  const handleEditAddress = (address: Address) => {
    setAddressData({
      receiver_name: address.receiver_name,
      receiver_phone: address.receiver_phone,
      line1: address.line1,
      city: address.city,
      province: address.province,
      zip: address.zip || '',
      is_default: address.is_default,
    })
    setEditingAddressId(address.id)
    setIsEditingAddress(true)
  }

  const handleDeleteAddress = async (id: number) => {
    if (!confirm('Bạn có chắc chắn muốn xóa địa chỉ này?')) return
    try {
      await userApi.delete(`/addresses/${id}`)
      alert('Xóa địa chỉ thành công!')
      await loadAddresses()
    } catch (err: any) {
      console.error(err)
      alert(err.response?.data?.message || 'Có lỗi xảy ra')
    }
  }

  const getImageUrl = (image: string | undefined | null) => {
    if (!image) return "https://via.placeholder.com/150?text=No+Avatar";
    if (image.startsWith("http")) return image;
    if (image.startsWith("/")) return `http://127.0.0.1:8000${image}`;
    return `http://127.0.0.1:8000/storage/${image}`;
  };

  if (!user) {
    return null
  }

  return (
    <div className="main" style={{ maxWidth: "1200px", margin: "0 auto", padding: "40px 20px" }}>
      <h1 style={{
        marginBottom: "32px",
        fontSize: "32px",
        fontWeight: "700",
        color: "#1f2937"
      }}>
        Thông tin tài khoản
      </h1>

      {/* THÔNG TIN CÁ NHÂN */}
      <div style={{
        background: "white",
        borderRadius: "12px",
        padding: "32px",
        boxShadow: "0 1px 3px rgba(0,0,0,0.1)",
        border: "1px solid #e5e7eb",
        marginBottom: "32px"
      }}>
        <div style={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          marginBottom: "24px",
          borderBottom: "2px solid #e5e7eb",
          paddingBottom: "16px"
        }}>
          <h2 style={{
            margin: 0,
            fontSize: "24px",
            fontWeight: "700",
            color: "#1f2937"
          }}>
            Thông tin cá nhân
          </h2>
          {!isEditingProfile && (
            <button
              onClick={() => setIsEditingProfile(true)}
              style={{
                padding: "8px 16px",
                background: "#3b82f6",
                color: "white",
                border: "none",
                borderRadius: "8px",
                cursor: "pointer",
                fontSize: "14px",
                fontWeight: "600"
              }}
            >
              Chỉnh sửa
            </button>
          )}
        </div>

        {!isEditingProfile ? (
          <div style={{ display: "flex", gap: "32px", alignItems: "flex-start" }}>
            <div style={{
              width: "120px",
              height: "120px",
              borderRadius: "50%",
              overflow: "hidden",
              border: "3px solid #e5e7eb",
              flexShrink: 0
            }}>
              <img
                src={getImageUrl(avatarPreview || (user as any).avatar_url)}
                alt="Avatar"
                style={{
                  width: "100%",
                  height: "100%",
                  objectFit: "cover"
                }}
              />
            </div>
            <div style={{ flex: 1 }}>
              <div style={{ marginBottom: "16px" }}>
                <label style={{
                  display: "block",
                  fontSize: "12px",
                  fontWeight: "600",
                  color: "#6b7280",
                  marginBottom: "4px",
                  textTransform: "uppercase"
                }}>
                  Họ và tên
                </label>
                <p style={{
                  margin: 0,
                  fontSize: "18px",
                  fontWeight: "600",
                  color: "#1f2937"
                }}>
                  {profileData.name || 'Chưa cập nhật'}
                </p>
              </div>
              <div style={{ marginBottom: "16px" }}>
                <label style={{
                  display: "block",
                  fontSize: "12px",
                  fontWeight: "600",
                  color: "#6b7280",
                  marginBottom: "4px",
                  textTransform: "uppercase"
                }}>
                  Email
                </label>
                <p style={{
                  margin: 0,
                  fontSize: "18px",
                  fontWeight: "600",
                  color: "#1f2937"
                }}>
                  {profileData.email}
                </p>
              </div>
              <div>
                <label style={{
                  display: "block",
                  fontSize: "12px",
                  fontWeight: "600",
                  color: "#6b7280",
                  marginBottom: "4px",
                  textTransform: "uppercase"
                }}>
                  Số điện thoại
                </label>
                <p style={{
                  margin: 0,
                  fontSize: "18px",
                  fontWeight: "600",
                  color: "#1f2937"
                }}>
                  {profileData.phone || 'Chưa cập nhật'}
                </p>
              </div>
            </div>
          </div>
        ) : (
          <form onSubmit={handleUpdateProfile} style={{ display: "flex", flexDirection: "column", gap: "20px" }}>
            <div style={{ display: "flex", gap: "32px", alignItems: "flex-start" }}>
              <div>
                <label style={{
                  display: "block",
                  marginBottom: "8px",
                  fontSize: "14px",
                  fontWeight: "600",
                  color: "#374151"
                }}>
                  Ảnh đại diện
                </label>
                <div style={{
                  width: "120px",
                  height: "120px",
                  borderRadius: "50%",
                  overflow: "hidden",
                  border: "3px solid #e5e7eb",
                  position: "relative",
                  cursor: "pointer"
                }}>
                  <img
                    src={getImageUrl(avatarPreview || (user as any).avatar_url)}
                    alt="Avatar"
                    style={{
                      width: "100%",
                      height: "100%",
                      objectFit: "cover"
                    }}
                  />
                  <input
                    id="avatar-input"
                    type="file"
                    accept="image/*"
                    onChange={handleAvatarChange}
                    style={{
                      position: "absolute",
                      top: 0,
                      left: 0,
                      width: "100%",
                      height: "100%",
                      opacity: 0,
                      cursor: "pointer"
                    }}
                  />
                </div>
                <p style={{ fontSize: "12px", color: "#6b7280", marginTop: "4px" }}>
                  Nhấp để thay đổi (tối đa 2MB)
                </p>
              </div>
              <div style={{ flex: 1, display: "flex", flexDirection: "column", gap: "20px" }}>
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
                    type="text"
                    value={profileData.name}
                    onChange={(e) => setProfileData({ ...profileData, name: e.target.value })}
                    required
                    style={{
                      width: "100%",
                      padding: "12px 16px",
                      border: "1px solid #d1d5db",
                      borderRadius: "8px",
                      fontSize: "16px",
                      boxSizing: "border-box"
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
                    Email
                  </label>
                  <input
                    type="email"
                    value={profileData.email}
                    disabled
                    style={{
                      width: "100%",
                      padding: "12px 16px",
                      border: "1px solid #d1d5db",
                      borderRadius: "8px",
                      fontSize: "16px",
                      background: "#f3f4f6",
                      color: "#6b7280",
                      boxSizing: "border-box"
                    }}
                  />
                  <p style={{ fontSize: "12px", color: "#6b7280", marginTop: "4px" }}>
                    Email không thể thay đổi
                  </p>
                </div>
                <div>
                  <label style={{
                    display: "block",
                    marginBottom: "8px",
                    fontSize: "14px",
                    fontWeight: "600",
                    color: "#374151"
                  }}>
                    Số điện thoại
                  </label>
                  <input
                    type="tel"
                    value={profileData.phone}
                    onChange={(e) => setProfileData({ ...profileData, phone: e.target.value })}
                    style={{
                      width: "100%",
                      padding: "12px 16px",
                      border: "1px solid #d1d5db",
                      borderRadius: "8px",
                      fontSize: "16px",
                      boxSizing: "border-box"
                    }}
                  />
                </div>
              </div>
            </div>
            <div style={{ display: "flex", gap: "12px", justifyContent: "flex-end" }}>
              <button
                type="button"
                onClick={() => {
                  setIsEditingProfile(false)
                  setAvatarPreview(null)
                  loadUserProfile()
                }}
                style={{
                  padding: "12px 24px",
                  background: "white",
                  color: "#374151",
                  border: "1px solid #d1d5db",
                  borderRadius: "8px",
                  cursor: "pointer",
                  fontSize: "14px",
                  fontWeight: "600"
                }}
              >
                Hủy
              </button>
              <button
                type="submit"
                disabled={loading}
                style={{
                  padding: "12px 24px",
                  background: loading ? "#9ca3af" : "#3b82f6",
                  color: "white",
                  border: "none",
                  borderRadius: "8px",
                  cursor: loading ? "not-allowed" : "pointer",
                  fontSize: "14px",
                  fontWeight: "600"
                }}
              >
                {loading ? "Đang lưu..." : "Lưu thay đổi"}
              </button>
            </div>
          </form>
        )}
      </div>

      {/* ĐỊA CHỈ */}
      <div style={{
        background: "white",
        borderRadius: "12px",
        padding: "32px",
        boxShadow: "0 1px 3px rgba(0,0,0,0.1)",
        border: "1px solid #e5e7eb"
      }}>
        <div style={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          marginBottom: "24px",
          borderBottom: "2px solid #e5e7eb",
          paddingBottom: "16px"
        }}>
          <h2 style={{
            margin: 0,
            fontSize: "24px",
            fontWeight: "700",
            color: "#1f2937"
          }}>
            Địa chỉ giao hàng
          </h2>
          {!isEditingAddress && (
            <button
              onClick={() => {
                setIsEditingAddress(true)
                setEditingAddressId(null)
                setAddressData({
                  receiver_name: '',
                  receiver_phone: '',
                  line1: '',
                  city: '',
                  province: '',
                  zip: '',
                  is_default: false,
                })
              }}
              style={{
                padding: "8px 16px",
                background: "#3b82f6",
                color: "white",
                border: "none",
                borderRadius: "8px",
                cursor: "pointer",
                fontSize: "14px",
                fontWeight: "600"
              }}
            >
              + Thêm địa chỉ
            </button>
          )}
        </div>

        {isEditingAddress ? (
          <form onSubmit={handleSaveAddress} style={{ display: "flex", flexDirection: "column", gap: "20px" }}>
            <div>
              <label style={{
                display: "block",
                marginBottom: "8px",
                fontSize: "14px",
                fontWeight: "600",
                color: "#374151"
              }}>
                Tên người nhận *
              </label>
              <input
                type="text"
                value={addressData.receiver_name}
                onChange={(e) => setAddressData({ ...addressData, receiver_name: e.target.value })}
                required
                style={{
                  width: "100%",
                  padding: "12px 16px",
                  border: "1px solid #d1d5db",
                  borderRadius: "8px",
                  fontSize: "16px",
                  boxSizing: "border-box"
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
                type="tel"
                value={addressData.receiver_phone}
                onChange={(e) => setAddressData({ ...addressData, receiver_phone: e.target.value })}
                required
                style={{
                  width: "100%",
                  padding: "12px 16px",
                  border: "1px solid #d1d5db",
                  borderRadius: "8px",
                  fontSize: "16px",
                  boxSizing: "border-box"
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
                Địa chỉ cụ thể *
              </label>
              <input
                type="text"
                value={addressData.line1}
                onChange={(e) => setAddressData({ ...addressData, line1: e.target.value })}
                required
                style={{
                  width: "100%",
                  padding: "12px 16px",
                  border: "1px solid #d1d5db",
                  borderRadius: "8px",
                  fontSize: "16px",
                  boxSizing: "border-box"
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
                  Quận/Huyện *
                </label>
                <select
                  value={addressData.city}
                  onChange={(e) => setAddressData({ ...addressData, city: e.target.value })}
                  required
                  style={{
                    width: "100%",
                    padding: "12px 16px",
                    border: "1px solid #d1d5db",
                    borderRadius: "8px",
                    fontSize: "16px",
                    background: "white",
                    cursor: "pointer",
                    boxSizing: "border-box"
                  }}
                >
                  <option value="">-- Chọn quận / huyện --</option>
                  {vietnamDistricts.map((d) => (
                    <option key={d} value={d}>{d}</option>
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
                  Tỉnh/Thành phố *
                </label>
                <select
                  value={addressData.province}
                  onChange={(e) => setAddressData({ ...addressData, province: e.target.value })}
                  required
                  style={{
                    width: "100%",
                    padding: "12px 16px",
                    border: "1px solid #d1d5db",
                    borderRadius: "8px",
                    fontSize: "16px",
                    background: "white",
                    cursor: "pointer",
                    boxSizing: "border-box"
                  }}
                >
                  <option value="">-- Chọn tỉnh / thành phố --</option>
                  {vietnamProvinces.map((p) => (
                    <option key={p} value={p}>{p}</option>
                  ))}
                </select>
              </div>
            </div>
            <div>
              <label style={{
                display: "block",
                marginBottom: "8px",
                fontSize: "14px",
                fontWeight: "600",
                color: "#374151"
              }}>
                Mã bưu điện
              </label>
              <input
                type="text"
                value={addressData.zip}
                onChange={(e) => setAddressData({ ...addressData, zip: e.target.value })}
                style={{
                  width: "100%",
                  padding: "12px 16px",
                  border: "1px solid #d1d5db",
                  borderRadius: "8px",
                  fontSize: "16px",
                  boxSizing: "border-box"
                }}
              />
            </div>
            <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
              <input
                type="checkbox"
                id="is_default"
                checked={addressData.is_default}
                onChange={(e) => setAddressData({ ...addressData, is_default: e.target.checked })}
                style={{ width: "18px", height: "18px", cursor: "pointer" }}
              />
              <label htmlFor="is_default" style={{ fontSize: "14px", color: "#374151", cursor: "pointer" }}>
                Đặt làm địa chỉ mặc định
              </label>
            </div>
            <div style={{ display: "flex", gap: "12px", justifyContent: "flex-end" }}>
              <button
                type="button"
                onClick={() => {
                  setIsEditingAddress(false)
                  setEditingAddressId(null)
                  setAddressData({
                    receiver_name: '',
                    receiver_phone: '',
                    line1: '',
                    city: '',
                    province: '',
                    zip: '',
                    is_default: false,
                  })
                }}
                style={{
                  padding: "12px 24px",
                  background: "white",
                  color: "#374151",
                  border: "1px solid #d1d5db",
                  borderRadius: "8px",
                  cursor: "pointer",
                  fontSize: "14px",
                  fontWeight: "600"
                }}
              >
                Hủy
              </button>
              <button
                type="submit"
                disabled={loading}
                style={{
                  padding: "12px 24px",
                  background: loading ? "#9ca3af" : "#3b82f6",
                  color: "white",
                  border: "none",
                  borderRadius: "8px",
                  cursor: loading ? "not-allowed" : "pointer",
                  fontSize: "14px",
                  fontWeight: "600"
                }}
              >
                {loading ? "Đang lưu..." : editingAddressId ? "Cập nhật" : "Thêm địa chỉ"}
              </button>
            </div>
          </form>
        ) : (
          <div style={{ display: "flex", flexDirection: "column", gap: "16px" }}>
            {addresses.length === 0 ? (
              <p style={{ color: "#6b7280", textAlign: "center", padding: "40px" }}>
                Bạn chưa có địa chỉ nào. Hãy thêm địa chỉ mới!
              </p>
            ) : (
              addresses.map((address) => (
                <div
                  key={address.id}
                  style={{
                    padding: "20px",
                    border: address.is_default ? "2px solid #3b82f6" : "1px solid #e5e7eb",
                    borderRadius: "8px",
                    background: address.is_default ? "#eff6ff" : "white"
                  }}
                >
                  <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start" }}>
                    <div style={{ flex: 1 }}>
                      {address.is_default && (
                        <span style={{
                          display: "inline-block",
                          padding: "4px 12px",
                          background: "#3b82f6",
                          color: "white",
                          borderRadius: "4px",
                          fontSize: "12px",
                          fontWeight: "600",
                          marginBottom: "8px"
                        }}>
                          Mặc định
                        </span>
                      )}
                      <p style={{ margin: "8px 0", fontSize: "16px", fontWeight: "600", color: "#1f2937" }}>
                        {address.receiver_name}
                      </p>
                      <p style={{ margin: "4px 0", fontSize: "14px", color: "#6b7280" }}>
                        {address.receiver_phone}
                      </p>
                      <p style={{ margin: "4px 0", fontSize: "14px", color: "#374151" }}>
                        {address.line1}, {address.city}, {address.province}
                        {address.zip && ` - ${address.zip}`}
                      </p>
                    </div>
                    <div style={{ display: "flex", gap: "8px" }}>
                      <button
                        onClick={() => handleEditAddress(address)}
                        style={{
                          padding: "8px 16px",
                          background: "white",
                          color: "#3b82f6",
                          border: "1px solid #3b82f6",
                          borderRadius: "6px",
                          cursor: "pointer",
                          fontSize: "14px",
                          fontWeight: "600"
                        }}
                      >
                        Sửa
                      </button>
                      <button
                        onClick={() => handleDeleteAddress(address.id)}
                        style={{
                          padding: "8px 16px",
                          background: "white",
                          color: "#ef4444",
                          border: "1px solid #ef4444",
                          borderRadius: "6px",
                          cursor: "pointer",
                          fontSize: "14px",
                          fontWeight: "600"
                        }}
                      >
                        Xóa
                      </button>
                    </div>
                  </div>
                </div>
              ))
            )}
          </div>
        )}
      </div>

      {/* NÚT ĐĂNG XUẤT */}
      <div style={{ marginTop: "32px", textAlign: "center" }}>
        <button
          onClick={async () => {
            if (window.confirm('Bạn có chắc chắn muốn đăng xuất không?')) {
              await logoutUser()
              navigate('/')
            }
          }}
          style={{
            padding: "12px 24px",
            background: "#ef4444",
            color: "white",
            border: "none",
            borderRadius: "8px",
            cursor: "pointer",
            fontSize: "16px",
            fontWeight: "600"
          }}
        >
          Đăng xuất
        </button>
      </div>
    </div>
  )
}

