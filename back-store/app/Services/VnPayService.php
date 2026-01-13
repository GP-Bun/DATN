<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VnPayService
{
    /**
     * Tạo URL thanh toán VNPay
     *
     * @param int $orderId ID đơn hàng
     * @param float $amount Số tiền thanh toán (VND)
     * @param string|null $ipAddr Địa chỉ IP (nếu null sẽ lấy từ request)
     * @param string|null $orderInfo Thông tin đơn hàng (nếu null sẽ tự động tạo)
     * @return array ['payment_url' => string, 'txn_ref' => string]
     */
    public static function createPaymentUrl($orderId, $amount, $ipAddr = null, $orderInfo = null)
    {
        // Set cứng để test (có thể thay bằng env sau)
        $tmnCode    = '9JRKWAQ9';
        $secretKey  = 'UJTLKILS5Z9JZDETR8DHCJSB2K0NH2MT';
        $vnpUrl     = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
        $returnUrl  = 'http://127.0.0.1:8000/api/vnpay/return';

        // Fallback: đọc từ env/config nếu cần
        // $tmnCode    = env('VNPAY_TMN_CODE') ?: config('vnpay.vnp_TmnCode') ?: '9JRKWAQ9';
        // $secretKey  = env('VNPAY_HASH_SECRET') ?: config('vnpay.vnp_HashSecret') ?: 'UJTLKILS5Z9JZDETR8DHCJSB2K0NH2MT';
        // $vnpUrl     = config('vnpay.vnp_Url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        // $returnUrl  = config('vnpay.vnp_ReturnUrl', env('APP_URL', 'http://localhost') . '/api/vnpay/return');

        // Log config values for debugging (without exposing secret)
        \Log::info('VNPay Config Check', [
            'tmnCode' => $tmnCode ? substr($tmnCode, 0, 3) . '...' : 'EMPTY',
            'secretKey' => $secretKey ? 'SET (' . strlen($secretKey) . ' chars)' : 'EMPTY',
            'vnpUrl' => $vnpUrl,
            'returnUrl' => $returnUrl,
            'env_VNPAY_TMN_CODE' => env('VNPAY_TMN_CODE') ? 'SET' : 'NOT_SET',
            'env_VNPAY_HASH_SECRET' => env('VNPAY_HASH_SECRET') ? 'SET' : 'NOT_SET',
        ]);

        // Validate required config
        if (empty($tmnCode) || empty($secretKey) || trim($tmnCode) === '' || trim($secretKey) === '') {
            \Log::error('VNPay config missing', [
                'tmnCode' => $tmnCode ?: 'empty',
                'secretKey' => $secretKey ? '***' : 'empty',
                'tmnCode_length' => strlen($tmnCode ?? ''),
                'secretKey_length' => strlen($secretKey ?? ''),
                'env_VNPAY_TMN_CODE' => env('VNPAY_TMN_CODE', 'NOT_SET'),
                'env_VNPAY_HASH_SECRET' => env('VNPAY_HASH_SECRET', 'NOT_SET') ? 'SET' : 'NOT_SET',
            ]);
            throw new \Exception('VNPay configuration is missing. Please check VNPAY_TMN_CODE and VNPAY_HASH_SECRET in .env file and restart the server.');
        }

        // Set timezone
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $createDate = now()->format('YmdHis');

        // Xử lý IP address
        if (!$ipAddr) {
            $ipAddr = request()->header('x-forwarded-for') ?? request()->ip();
        }
        if (str_contains($ipAddr, ',')) {
            $ipAddr = trim(explode(',', $ipAddr)[0]);
        }
        if ($ipAddr === '::1' || $ipAddr === '::ffff:127.0.0.1') {
            $ipAddr = '127.0.0.1';
        }

        // Tạo TxnRef: orderId + timestamp
        $orderIdStamp = now()->format('dHis');
        $txnRef = $orderId . 'T' . $orderIdStamp;

        // Tạo OrderInfo nếu chưa có
        if (!$orderInfo) {
            $orderInfo = 'Thanh toan don hang ' . $orderId;
        }

        // Chuẩn bị params theo đúng chuẩn VNPay
        $vnpParams = [
            'vnp_Version'   => '2.1.0',
            'vnp_Command'   => 'pay',
            'vnp_TmnCode'   => $tmnCode,
            'vnp_Locale'    => 'vn',
            'vnp_CurrCode'  => 'VND',
            'vnp_TxnRef'    => $txnRef,
            'vnp_OrderInfo' => $orderInfo,
            'vnp_OrderType' => 'billpayment',
            'vnp_Amount'    => (int) round($amount * 100), // Chuyển sang xu (VND * 100)
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_IpAddr'    => $ipAddr,
            'vnp_CreateDate'=> $createDate,
        ];

        // Sắp xếp params theo key
        ksort($vnpParams);

        // Tạo hashData và query string (cả hai đều URL encode)
        $hashData = '';
        $query = '';
        $i = 0;
        foreach ($vnpParams as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        // Tạo secure hash
        $secureHash = hash_hmac('sha512', $hashData, $secretKey);

        // Tạo payment URL
        $paymentUrl = $vnpUrl . '?' . $query . 'vnp_SecureHash=' . $secureHash;

        // Log để debug
        Log::info('VNPay Create Payment URL', [
            'order_id' => $orderId,
            'amount' => $amount,
            'txn_ref' => $txnRef,
            'hashData' => $hashData,
            'secureHash' => $secureHash,
            'paymentUrl' => $paymentUrl,
        ]);

        return [
            'payment_url' => $paymentUrl,
            'txn_ref'     => $txnRef,
            'amount'      => $amount,
        ];
    }

    /**
     * Xác thực hash từ VNPay callback
     *
     * @param array $params Các tham số từ VNPay callback
     * @return array ['valid' => bool, 'calculated_hash' => string, 'received_hash' => string]
     */
    public static function verifyHash($params)
    {
        $receivedHash = $params['vnp_SecureHash'] ?? '';
        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);

        // VNPay gửi về params đã được Laravel decode rồi
        // Cần tạo hashData giống hệt cách tạo khi gửi đi (urlencode từng key-value)
        ksort($params);
        $hashData = '';
        $i = 0;
        foreach ($params as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
        }

        // Dùng secret key đã set cứng
        $secretKey = 'UJTLKILS5Z9JZDETR8DHCJSB2K0NH2MT';
        $calculated = hash_hmac('sha512', $hashData, $secretKey);

        return [
            'valid' => $receivedHash === $calculated,
            'calculated_hash' => $calculated,
            'received_hash' => $receivedHash,
            'hashData' => $hashData,
        ];
    }
}
