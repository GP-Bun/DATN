<?php
return [
    'vnp_TmnCode'     => env('VNPAY_TMN_CODE', ''),
    'vnp_HashSecret'  => env('VNPAY_HASH_SECRET', ''),
    'vnp_Url'         => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
    'vnp_ReturnUrl'   => env('VNPAY_RETURN_URL', env('APP_URL', 'http://localhost') . '/api/vnpay/return'),
    'frontend_url'    => env('FRONTEND_URL', 'http://localhost:5000'),
    // Response codes mapping
    'responseCodes' => [
        '00' => 'Giao dịch thành công',
        '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ',
        '09' => 'Tài khoản/chưa đăng ký InternetBanking',
        '11' => 'Hết hạn chờ thanh toán',
        '12' => 'Thẻ/Tài khoản bị khóa',
        '13' => 'Sai mật khẩu OTP',
        '24' => 'Khách hàng hủy giao dịch',
        '51' => 'Không đủ số dư',
        '65' => 'Vượt hạn mức giao dịch',
        '75' => 'Ngân hàng bảo trì',
        '79' => 'Sai mật khẩu quá số lần',
        '99' => 'Lỗi khác',
    ],
];
