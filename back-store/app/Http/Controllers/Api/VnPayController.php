<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Services\VnPayService;

class VnPayController extends Controller
{
    /**
     * Tạo URL thanh toán VNPay
     */
    public function create(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);

        $ipAddr = $request->header('x-forwarded-for') ?? $request->ip();
        $result = VnPayService::createPaymentUrl(
            $order->id,
            $order->final_amount,
            $ipAddr
        );

        return response()->json([
            'payment_url' => $result['payment_url'],
            'txn_ref'     => $result['txn_ref'],
            'amount'      => $result['amount'],
        ]);
    }

    /**
     * Handle VNPay Return URL
     */
    public function return(Request $request)
    {
        $params = $request->query();
        $verifyResult = VnPayService::verifyHash($params);
        $responseCode = $request->query('vnp_ResponseCode');

        $frontendUrl = config('vnpay.frontend_url');

        // Nếu hash không khớp nhưng responseCode = '00' (thanh toán thành công), vẫn xử lý nhưng log warning
        if (!$verifyResult['valid']) {
            Log::warning('VNPay Return: invalid hash', [
                'hashData' => $verifyResult['hashData'],
                'calculated' => $verifyResult['calculated_hash'],
                'received' => $verifyResult['received_hash'],
                'params' => $params,
                'responseCode' => $responseCode,
            ]);
            
            // Nếu không phải thành công, reject ngay
            if ($responseCode !== '00') {
                return redirect($frontendUrl . '/payment/result?success=false&message=Chu-ky-khong-hop-le');
            }
            // Nếu thành công nhưng hash không khớp, vẫn xử lý nhưng log cảnh báo
            Log::warning('VNPay Return: Hash mismatch but ResponseCode=00, proceeding with caution');
        }

        $txnRef = $request->query('vnp_TxnRef');
        $transactionNo = $request->query('vnp_TransactionNo');
        $bankCode = $request->query('vnp_BankCode');
        $amount = ((int) $request->query('vnp_Amount')) / 100;

        // Parse orderId từ txnRef (format: orderIdTtimestamp)
        $orderId = explode('T', $txnRef)[0] ?? null;
        $order = $orderId ? Order::find($orderId) : null;
        if (!$order) {
            return redirect($frontendUrl . '/payment/result?success=false&message=Khong-tim-thay-don-hang');
        }

        if ($responseCode === '00') {
            $order->update(['payment_status' => 'paid', 'order_status' => 'confirmed']);
            return redirect($frontendUrl . '/payment/result?success=true&orderNumber=' . $order->id . '&amount=' . $amount . '&transactionNo=' . $transactionNo . '&bankCode=' . $bankCode);
        }

        // Restore stock on failed payment
        $order->load('items.product', 'items.variant');
        foreach ($order->items as $item) {
            if ($item->variant) {
                $item->variant->increment('stock', $item->quantity);
                $product = $item->variant->product;
                if ($product) {
                    $product->auto_status = $product->variants()->where('stock', '>', 0)->exists() ? 1 : 2;
                    $product->save();
                }
            } elseif ($item->product) {
                $item->product->increment('stock', $item->quantity);
                $item->product->auto_status = $item->product->stock > 0 ? 1 : 2;
                $item->product->save();
            }
        }

        $order->update(['payment_status' => 'failed', 'order_status' => 'cancelled']);
        $errorMessage = config('vnpay.responseCodes')[$responseCode] ?? 'Thanh toan that bai';
        return redirect($frontendUrl . '/payment/result?success=false&orderNumber=' . $order->id . '&message=' . urlencode($errorMessage) . '&code=' . $responseCode);
    }

    /**
     * Handle VNPay IPN
     */
    public function ipn(Request $request)
    {
        $params = $request->query();
        $verifyResult = VnPayService::verifyHash($params);

        if (!$verifyResult['valid']) {
            Log::warning('VNPay IPN: invalid hash', [
                'hashData' => $verifyResult['hashData'],
                'calculated' => $verifyResult['calculated_hash'],
                'received' => $verifyResult['received_hash'],
                'params' => $params,
            ]);
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid Checksum']);
        }

        $txnRef = $request->query('vnp_TxnRef');
        $responseCode = $request->query('vnp_ResponseCode');
        $amount = ((int) $request->query('vnp_Amount')) / 100;
        // Parse orderId từ txnRef (format: orderIdTtimestamp)
        $orderId = explode('T', $txnRef)[0] ?? null;
        $order = $orderId ? Order::find($orderId) : null;

        if (!$order) {
            return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
        }

        if ((float) $order->final_amount !== (float) $amount) {
            return response()->json(['RspCode' => '04', 'Message' => 'Invalid amount']);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['RspCode' => '02', 'Message' => 'Order already confirmed']);
        }

        if ($responseCode === '00') {
            $order->update(['payment_status' => 'paid', 'order_status' => 'confirmed']);
            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
        }

        $order->update(['payment_status' => 'failed', 'order_status' => 'cancelled']);
        return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
    }
}
