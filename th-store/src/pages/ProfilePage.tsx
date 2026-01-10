import { useState, useEffect } from 'react'
import { useAuth } from '../store/AuthContext'
import { useNavigate } from 'react-router-dom'
import api from '../api/api'
import { geoApi } from '../api/geo.api'
import type { Province, District, Ward } from '../api/geo.api'
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

export default function ProfilePage() {
  const { user, loading: authLoading, refreshUser } = useAuth()
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [addresses, setAddresses] = useState<Address[]>([])
  const [isEditingProfile, setIsEditingProfile] = useState(false)
  const [isEditingAddress, setIsEditingAddress] = useState(false)
  const [editingAddressId, setEditingAddressId] = useState<number | null>(null)
  const [avatarPreview, setAvatarPreview] = useState<string | null>(null)

  const [provinces, setProvinces] = useState<Province[]>([])
  const [districts, setDistricts] = useState<District[]>([])
  const [wards, setWards] = useState<Ward[]>([])

  const [profileData, setProfileData] = useState({
    name: (user as any)?.name || '',
    email: (user as any)?.email || '',
    phone: (user as any)?.phone || '',
  })

  const [addressData, setAddressData] = useState({
    receiver_name: '',
    receiver_phone: '',
    line1: '',
    province_id: 0,
    district_id: 0,
    ward_id: 0,
    zip: '',
    is_default: false,
  })

  useEffect(() => {
    if (!authLoading && !user) {
      navigate('/dang-nhap')
      return
    }
    if (user) {
      loadAddresses()
      loadUserProfile()
      loadProvinces()
    }
  }, [user, authLoading])

  useEffect(() => {
    if (addressData.province_id) {
      loadDistricts(addressData.province_id)
    } else {
      setDistricts([])
      setWards([])
    }
  }, [addressData.province_id])

  useEffect(() => {
    if (addressData.district_id) {
      loadWards(addressData.district_id)
    } else {
      setWards([])
    }
  }, [addressData.district_id])

  const loadProvinces = async () => {
    try {
      const data = await geoApi.getProvinces()
      setProvinces(data)
    } catch (err) {
      console.error('Lỗi tải tỉnh/thành:', err)
    }
  }

  const loadDistricts = async (provinceId: number) => {
    try {
      const data = await geoApi.getDistricts(provinceId)
      setDistricts(data)
    } catch (err) {
      console.error('Lỗi tải quận/huyện:', err)
    }
  }

  const loadWards = async (districtId: number) => {
    try {
      const data = await geoApi.getWards(districtId)
      setWards(data)
    } catch (err) {
      console.error('Lỗi tải phường/xã:', err)
    }
  }

  const loadUserProfile = async () => {
    try {
      const res = await api.get('/user-profile')
      const userData = res.data.user
      setProfileData({
        name: userData.name || '',
        email: userData.email || '',
        phone: userData.phone || '',
      })
      if (userData.avatar_url) {
        setAvatarPreview(userData.avatar_url)
      }
    } catch (err) {
      console.error('Lỗi tải thông tin người dùng:', err)
    }
  }

  const loadAddresses = async () => {
    try {
      const res = await api.get('/addresses')
      // AddressResource wraps data in 'data' key when using collection()
      setAddresses(res.data.data || res.data)
    } catch (err) {
      console.error('Lỗi tải địa chỉ:', err)
    }
  }

  const handleAvatarChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (file) {
      if (file.size > 2 * 1024 * 1024) {
        toast.error('Kích thước ảnh không được vượt quá 2MB')
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
      formData.append('email', profileData.email)
      if (profileData.phone) formData.append('phone', profileData.phone)

      const avatarInput = document.getElementById('avatar-input') as HTMLInputElement
      if (avatarInput?.files?.[0]) {
        formData.append('avatar', avatarInput.files[0])
      }

      await api.post('/user-profile', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })

      toast.success('Cập nhật thông tin thành công!')
      setIsEditingProfile(false)
      await refreshUser()
      await loadUserProfile()
    } catch (err: any) {
      console.error(err)
      toast.error(err.response?.data?.message || 'Có lỗi xảy ra khi cập nhật thông tin')
    } finally {
      setLoading(false)
    }
  }

  const handleSaveAddress = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!addressData.province_id || !addressData.district_id || !addressData.ward_id) {
      toast.error('Vui lòng chọn đầy đủ Tỉnh/Thành, Quận/Huyện, Phường/Xã')
      return
    }
    setLoading(true)
    try {
      if (editingAddressId) {
        await api.put(`/addresses/${editingAddressId}`, addressData)
        toast.success('Cập nhật địa chỉ thành công!')
      } else {
        await api.post('/addresses', addressData)
        toast.success('Thêm địa chỉ thành công!')
      }
      setIsEditingAddress(false)
      setEditingAddressId(null)
      setAddressData({
        receiver_name: '',
        receiver_phone: '',
        line1: '',
        province_id: 0,
        district_id: 0,
        ward_id: 0,
        zip: '',
        is_default: false,
      })
      await loadAddresses()
    } catch (err: any) {
      console.error(err)
      toast.error(err.response?.data?.message || 'Có lỗi xảy ra')
    } finally {
      setLoading(false)
    }
  }

  const handleEditAddress = (address: Address) => {
    setAddressData({
      receiver_name: address.receiver_name,
      receiver_phone: address.receiver_phone,
      line1: address.line1,
      province_id: address.province.id,
      district_id: address.district.id,
      ward_id: address.ward.id,
      zip: address.zip || '',
      is_default: address.is_default,
    })
    setEditingAddressId(address.id)
    setIsEditingAddress(true)
  }

  const handleDeleteAddress = async (id: number) => {
    const result = await Swal.fire({
      title: 'Xóa địa chỉ?',
      text: "Bạn có chắc chắn muốn xóa địa chỉ này không?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Đồng ý xóa',
      cancelButtonText: 'Hủy'
    })

    if (result.isConfirmed) {
      try {
        await api.delete(`/addresses/${id}`)
        toast.success('Xóa địa chỉ thành công!')
        await loadAddresses()
      } catch (err: any) {
        console.error(err)
        toast.error(err.response?.data?.message || 'Có lỗi xảy ra')
      }
    }
  }

  const getImageUrl = (image: string | undefined | null) => {
    if (!image) return "https://via.placeholder.com/150?text=No+Avatar";
    if (image.startsWith("http") || image.startsWith("data:")) return image;
    if (image.startsWith("/")) return `http://127.0.0.1:8000${image}`;
    return `http://127.0.0.1:8000/storage/${image}`;
  };

  if (authLoading) return <div className="main" style={{ textAlign: 'center', padding: '100px' }}>Đang tải...</div>;
  if (!user) return null;

  return (
    <div className="main" style={{ maxWidth: "1200px", margin: "0 auto", padding: "40px 20px" }}>
      <h1 style={{ marginBottom: "32px", fontSize: "32px", fontWeight: "700", color: "#1f2937" }}>
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
          <h2 style={{ margin: 0, fontSize: "24px", fontWeight: "700", color: "#1f2937" }}>
            Thông tin cá nhân
          </h2>
          <div style={{ display: "flex", gap: "12px" }}>
            {!isEditingProfile && (
              <button
                onClick={() => setIsEditingProfile(true)}
                style={{
                  padding: "8px 16px",
                  background: "white",
                  color: "#3b82f6",
                  border: "1px solid #3b82f6",
                  borderRadius: "8px",
                  cursor: "pointer",
                  fontSize: "14px",
                  fontWeight: "600"
                }}
              >
                Chỉnh sửa profile
              </button>
            )}
            {!isEditingAddress && (
              <button
                onClick={() => { setEditingAddressId(null); setAddressData({ receiver_name: '', receiver_phone: '', line1: '', province_id: 0, district_id: 0, ward_id: 0, zip: '', is_default: false }); setIsEditingAddress(true); }}
                style={{ padding: "8px 16px", background: "#3b82f6", color: "white", border: "none", borderRadius: "8px", cursor: "pointer", fontSize: "14px", fontWeight: "600" }}
              >
                + Thêm địa chỉ mới
              </button>
            )}
          </div>
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
                style={{ width: "100%", height: "100%", objectFit: "cover" }}
              />
            </div>
            <div style={{ flex: 1 }}>
              <div style={{ marginBottom: "16px" }}>
                <label style={{ display: "block", fontSize: "12px", fontWeight: "600", color: "#6b7280", marginBottom: "4px", textTransform: "uppercase" }}>Họ và tên</label>
                <p style={{ margin: 0, fontSize: "18px", fontWeight: "600", color: "#1f2937" }}>{profileData.name || 'Chưa cập nhật'}</p>
              </div>
              <div style={{ marginBottom: "16px" }}>
                <label style={{ display: "block", fontSize: "12px", fontWeight: "600", color: "#6b7280", marginBottom: "4px", textTransform: "uppercase" }}>Email</label>
                <p style={{ margin: 0, fontSize: "18px", fontWeight: "600", color: "#1f2937" }}>{profileData.email}</p>
              </div>
              <div>
                <label style={{ display: "block", fontSize: "12px", fontWeight: "600", color: "#6b7280", marginBottom: "4px", textTransform: "uppercase" }}>Số điện thoại</label>
                <p style={{ margin: 0, fontSize: "18px", fontWeight: "600", color: "#1f2937" }}>{profileData.phone || 'Chưa cập nhật'}</p>
              </div>
            </div>
          </div>
        ) : (
          <form onSubmit={handleUpdateProfile} style={{ display: "flex", flexDirection: "column", gap: "20px" }}>
            <div style={{ display: "flex", gap: "32px", alignItems: "flex-start" }}>
              <div>
                <label style={{ display: "block", marginBottom: "8px", fontSize: "14px", fontWeight: "600", color: "#374151" }}>Ảnh đại diện</label>
                <div style={{
                  width: "120px", height: "120px", borderRadius: "50%", overflow: "hidden", border: "3px solid #e5e7eb", position: "relative", cursor: "pointer"
                }}>
                  <img
                    src={getImageUrl(avatarPreview || (user as any).avatar_url)}
                    alt="Avatar"
                    style={{ width: "100%", height: "100%", objectFit: "cover" }}
                  />
                  <input id="avatar-input" type="file" accept="image/*" onChange={handleAvatarChange}
                    style={{ position: "absolute", top: 0, left: 0, width: "100%", height: "100%", opacity: 0, cursor: "pointer" }}
                  />
                </div>
              </div>
              <div style={{ flex: 1, display: "flex", flexDirection: "column", gap: "20px" }}>
                <div>
                  <label style={{ display: "block", fontSize: "12px", fontWeight: "600", color: "#6b7280", marginBottom: "4px" }}>HỌ VÀ TÊN</label>
                  <input type="text" placeholder="Họ và tên" value={profileData.name} onChange={(e) => setProfileData({ ...profileData, name: e.target.value })} required
                    style={{ width: "100%", padding: "12px 16px", border: "1px solid #d1d5db", borderRadius: "8px" }}
                  />
                </div>
                <div>
                  <label style={{ display: "block", fontSize: "12px", fontWeight: "600", color: "#6b7280", marginBottom: "4px" }}>EMAIL</label>
                  <input type="email" placeholder="Email" value={profileData.email} onChange={(e) => setProfileData({ ...profileData, email: e.target.value })} required
                    style={{ width: "100%", padding: "12px 16px", border: "1px solid #d1d5db", borderRadius: "8px" }}
                  />
                </div>
                <div>
                  <label style={{ display: "block", fontSize: "12px", fontWeight: "600", color: "#6b7280", marginBottom: "4px" }}>SỐ ĐIỆN THOẠI</label>
                  <input type="tel" placeholder="Số điện thoại" value={profileData.phone} onChange={(e) => setProfileData({ ...profileData, phone: e.target.value })}
                    style={{ width: "100%", padding: "12px 16px", border: "1px solid #d1d5db", borderRadius: "8px" }}
                  />
                </div>
              </div>
            </div>
            <div style={{ display: "flex", gap: "12px", justifyContent: "flex-end" }}>
              <button type="button" onClick={() => setIsEditingProfile(false)} style={{ padding: "12px 24px", background: "white", border: "1px solid #d1d5db", borderRadius: "8px" }}>Hủy</button>
              <button type="submit" disabled={loading} style={{ padding: "12px 24px", background: "#3b82f6", color: "white", border: "none", borderRadius: "8px" }}>Lưu thay đổi</button>
            </div>
          </form>
        )}
        {/* Divider and Addressing section merged inside */}
        <div style={{ marginTop: "40px", paddingTop: "32px", borderTop: "2px solid #f3f4f6" }}>
          <h2 style={{ margin: "0 0 24px 0", fontSize: "22px", fontWeight: "700", color: "#1f2937", display: "flex", alignItems: "center", gap: "8px" }}>
            📍 Danh sách địa chỉ
          </h2>

          {isEditingAddress ? (
            <form onSubmit={handleSaveAddress} style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "20px", background: "#f9fafb", padding: "24px", borderRadius: "12px", border: "1px solid #e5e7eb" }}>
              <div style={{ gridColumn: "span 2", marginBottom: "8px" }}>
                <h3 style={{ margin: 0, fontSize: "16px", fontWeight: "600" }}>{editingAddressId ? 'Cập nhật địa chỉ' : 'Thêm địa chỉ mới'}</h3>
              </div>
              <input type="text" placeholder="Tên người nhận" value={addressData.receiver_name} onChange={(e) => setAddressData({ ...addressData, receiver_name: e.target.value })} required style={{ padding: "12px", border: "1px solid #d1d5db", borderRadius: "8px" }} />
              <input type="tel" placeholder="Số điện thoại" value={addressData.receiver_phone} onChange={(e) => setAddressData({ ...addressData, receiver_phone: e.target.value })} required style={{ padding: "12px", border: "1px solid #d1d5db", borderRadius: "8px" }} />
              <input type="text" placeholder="Địa chỉ chi tiết (Số nhà, tên đường...)" value={addressData.line1} onChange={(e) => setAddressData({ ...addressData, line1: e.target.value })} required style={{ gridColumn: "span 2", padding: "12px", border: "1px solid #d1d5db", borderRadius: "8px" }} />

              <select value={addressData.province_id} onChange={(e) => setAddressData({ ...addressData, province_id: Number(e.target.value), district_id: 0, ward_id: 0 })} required style={{ padding: "12px", border: "1px solid #d1d5db", borderRadius: "8px", background: "white" }}>
                <option value="0">Chọn Tỉnh/Thành phố</option>
                {provinces.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
              </select>

              <select value={addressData.district_id} onChange={(e) => setAddressData({ ...addressData, district_id: Number(e.target.value), ward_id: 0 })} required disabled={!addressData.province_id} style={{ padding: "12px", border: "1px solid #d1d5db", borderRadius: "8px", background: !addressData.province_id ? "#f3f4f6" : "white" }}>
                <option value="0">Chọn Quận/Huyện</option>
                {districts.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
              </select>

              <select value={addressData.ward_id} onChange={(e) => setAddressData({ ...addressData, ward_id: Number(e.target.value) })} required disabled={!addressData.district_id} style={{ padding: "12px", border: "1px solid #d1d5db", borderRadius: "8px", background: !addressData.district_id ? "#f3f4f6" : "white" }}>
                <option value="0">Chọn Phường/Xã</option>
                {wards.map(w => <option key={w.id} value={w.id}>{w.name}</option>)}
              </select>

              <div style={{ gridColumn: "span 1" }}></div>

              <label style={{ gridColumn: "span 2", display: "flex", alignItems: "center", gap: "8px", cursor: "pointer" }}>
                <input type="checkbox" checked={addressData.is_default} onChange={(e) => setAddressData({ ...addressData, is_default: e.target.checked })} /> Đặt làm địa chỉ mặc định
              </label>
              <div style={{ gridColumn: "span 2", display: "flex", gap: "12px", justifyContent: "flex-end" }}>
                <button type="button" onClick={() => setIsEditingAddress(false)} style={{ padding: "12px 24px", background: "white", border: "1px solid #d1d5db", borderRadius: "8px" }}>Hủy</button>
                <button type="submit" style={{ padding: "12px 24px", background: "#3b82f6", color: "white", border: "none", borderRadius: "8px" }}>Lưu địa chỉ</button>
              </div>
            </form>
          ) : (
            <div style={{ display: "flex", flexDirection: "column", gap: "16px" }}>
              {addresses.length === 0 ? (
                <p style={{ color: "#6b7280", textAlign: "center", padding: "40px", background: "#f9fafb", borderRadius: "12px", border: "1px dashed #d1d5db" }}>Bạn chưa có địa chỉ nào.</p>
              ) : (
                addresses.map(addr => (
                  <div key={addr.id} style={{ padding: "20px", border: "1px solid #e5e7eb", borderRadius: "12px", display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                    <div>
                      <div style={{ display: "flex", alignItems: "center", gap: "12px", marginBottom: "4px" }}>
                        <span style={{ fontWeight: "700", fontSize: "16px" }}>{addr.receiver_name}</span>
                        <span style={{ color: "#6b7280", fontSize: "14px" }}>| {addr.receiver_phone}</span>
                        {addr.is_default && <span style={{ padding: "2px 8px", background: "#fef2f2", color: "#ef4444", border: "1px solid #ef4444", borderRadius: "4px", fontSize: "12px" }}>Mặc định</span>}
                      </div>
                      <p style={{ margin: 0, color: "#4b5563" }}>{addr.line1}</p>
                      <p style={{ margin: 0, color: "#4b5563" }}>{addr.ward.name}, {addr.district.name}, {addr.province.name}</p>
                    </div>
                    <div style={{ display: "flex", gap: "16px" }}>
                      <button onClick={() => handleEditAddress(addr)} style={{ background: "none", border: "none", color: "#3b82f6", cursor: "pointer", fontWeight: "600" }}>Sửa</button>
                      {!addr.is_default && <button onClick={() => handleDeleteAddress(addr.id)} style={{ background: "none", border: "none", color: "#ef4444", cursor: "pointer", fontWeight: "600" }}>Xóa</button>}
                    </div>
                  </div>
                ))
              )}
            </div>
          )}
        </div>
      </div>

    </div>
  )
}
