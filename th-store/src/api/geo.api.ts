import api from "./api";

export interface Province {
  id: number;
  code: string;
  name: string;
  slug: string;
  type: string;
}

export interface District {
  id: number;
  code: string;
  name: string;
  slug: string;
  type: string;
  province_id: number;
}

export interface Ward {
  id: number;
  code: string;
  name: string;
  slug: string;
  type: string;
  district_id: number;
}

export const geoApi = {
  // Lấy danh sách tỉnh/thành
  getProvinces: async (): Promise<Province[]> => {
    const res = await api.get("/geo/provinces");
    return res.data.data;
  },

  // Lấy danh sách quận/huyện theo province_id
  getDistricts: async (provinceId: number): Promise<District[]> => {
    const res = await api.get(`/geo/districts?province_id=${provinceId}`);
    return res.data.data;
  },

  // Lấy danh sách xã/phường theo district_id
  getWards: async (districtId: number): Promise<Ward[]> => {
    const res = await api.get(`/geo/wards?district_id=${districtId}`);
    return res.data.data;
  },
};