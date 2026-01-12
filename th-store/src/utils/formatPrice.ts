/**
 * Format số tiền theo chuẩn Việt Nam (không có phần thập phân)
 * Ví dụ: 100000 -> "100.000đ"
 */
export const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'decimal',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(price) + 'đ';
};

/**
 * Format số tiền không có đơn vị (chỉ số, không có phần thập phân)
 * Ví dụ: 100000 -> "100.000"
 */
export const formatNumber = (num: number): string => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'decimal',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num);
};

