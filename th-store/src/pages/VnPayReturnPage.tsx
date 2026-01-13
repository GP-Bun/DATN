import { useEffect, useState } from 'react';
import { useLocation, Link } from 'react-router-dom';
import api from '../api/api';
import { toast } from 'react-hot-toast';
import { formatPrice } from '../utils/formatPrice';

export default function VnPayReturnPage() {
    const location = useLocation();
    const [loading, setLoading] = useState(true);
    const [status, setStatus] = useState<'success' | 'failed' | 'error' | null>(null);
    const [vnpayData, setVnpayData] = useState<any>(null);

    useEffect(() => {
        const params = new URLSearchParams(location.search);
        const responseCode = params.get('vnp_ResponseCode');
        const orderId = params.get('vnp_TxnRef');
        const amount = params.get('vnp_Amount');

        setVnpayData({
            responseCode,
            orderId,
            amount: amount ? parseInt(amount) / 100 : 0,
            bankCode: params.get('vnp_BankCode'),
            payDate: params.get('vnp_PayDate'),
            transactionNo: params.get('vnp_TransactionNo')
        });

        if (responseCode === '00') {
            setStatus('success');
            toast.success('Thanh toán thành công!');
            fetchOrderDetails(orderId);
        } else {
            setStatus('failed');
            toast.error('Thanh toán thất bại hoặc đã bị hủy');
            setLoading(false);
        }
    }, [location]);

    const fetchOrderDetails = async (orderId: string | null) => {
        if (!orderId) return;
        try {
            await api.get(`/orders/${orderId}`);
            // Chúng ta có thể dùng dữ liệu này sau nếu cần
        } catch (err) {
            console.error('Error fetching order details:', err);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="main" style={{ textAlign: 'center', padding: '100px 20px' }}>
                <div style={{
                    width: '50px',
                    height: '50px',
                    border: '5px solid #f3f3f3',
                    borderTop: '5px solid #3498db',
                    borderRadius: '50%',
                    animation: 'spin 1s linear infinite',
                    margin: '0 auto 20px'
                }}></div>
                <h2>Đang xử lý kết quả thanh toán...</h2>
                <style>{`
          @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
        `}</style>
            </div>
        );
    }

    return (
        <div className="main" style={{ maxWidth: '800px', margin: '0 auto', padding: '40px 20px' }}>
            {status === 'success' ? (
                <div style={{ textAlign: 'center' }}>
                    <div style={{
                        background: '#ecfdf5',
                        borderRadius: '16px',
                        padding: '40px',
                        border: '1px solid #10b981',
                        marginBottom: '30px'
                    }}>
                        <div style={{ fontSize: '64px', color: '#10b981', marginBottom: '20px' }}>✓</div>
                        <h1 style={{ color: '#064e3b', marginBottom: '10px' }}>Thanh toán thành công!</h1>
                        <p style={{ color: '#047857', fontSize: '18px' }}>
                            Cảm ơn bạn đã mua sắm tại TH Store. Đơn hàng của bạn đang được xử lý.
                        </p>
                    </div>

                    <div style={{ background: 'white', border: '1px solid #e5e7eb', borderRadius: '12px', padding: '24px', textAlign: 'left', marginBottom: '30px' }}>
                        <h3 style={{ borderBottom: '1px solid #e5e7eb', paddingBottom: '12px', marginBottom: '20px' }}>Chi tiết giao dịch</h3>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                            <div><span style={{ color: '#6b7280' }}>Mã đơn hàng:</span> <strong>#{vnpayData?.orderId}</strong></div>
                            <div><span style={{ color: '#6b7280' }}>Số tiền:</span> <strong style={{ color: '#10b981' }}>{formatPrice(vnpayData?.amount || 0)}</strong></div>
                            <div><span style={{ color: '#6b7280' }}>Ngân hàng:</span> <strong>{vnpayData?.bankCode}</strong></div>
                            <div><span style={{ color: '#6b7280' }}>Mã giao dịch:</span> <strong>{vnpayData?.transactionNo}</strong></div>
                        </div>
                    </div>

                    <div style={{ display: 'flex', gap: '16px', justifyContent: 'center' }}>
                        <Link to="/don-hang" style={{ padding: '12px 24px', background: '#3b82f6', color: 'white', borderRadius: '8px', textDecoration: 'none', fontWeight: 'bold' }}>Xem đơn hàng</Link>
                        <Link to="/" style={{ padding: '12px 24px', background: 'white', color: '#3b82f6', border: '2px solid #3b82f6', borderRadius: '8px', textDecoration: 'none', fontWeight: 'bold' }}>Về trang chủ</Link>
                    </div>
                </div>
            ) : (
                <div style={{ textAlign: 'center' }}>
                    <div style={{
                        background: '#fef2f2',
                        borderRadius: '16px',
                        padding: '40px',
                        border: '1px solid #ef4444',
                        marginBottom: '30px'
                    }}>
                        <div style={{ fontSize: '64px', color: '#ef4444', marginBottom: '20px' }}>✕</div>
                        <h1 style={{ color: '#991b1b', marginBottom: '10px' }}>Thanh toán thất bại</h1>
                        <p style={{ color: '#b91c1c', fontSize: '18px' }}>
                            Giao dịch của bạn không thành công hoặc đã bị hủy.
                        </p>
                    </div>

                    <div style={{ background: 'white', border: '1px solid #e5e7eb', borderRadius: '12px', padding: '24px', textAlign: 'left', marginBottom: '30px' }}>
                        <h3 style={{ borderBottom: '1px solid #e5e7eb', paddingBottom: '12px', marginBottom: '20px' }}>Thông tin đơn hàng</h3>
                        <p>Mã đơn hàng: <strong>#{vnpayData?.orderId}</strong></p>
                        <p>Bạn có thể thử thanh toán lại trong phần lịch sử đơn hàng hoặc liên hệ nếu cần hỗ trợ.</p>
                    </div>

                    <div style={{ display: 'flex', gap: '16px', justifyContent: 'center' }}>
                        <Link to="/don-hang" style={{ padding: '12px 24px', background: '#3b82f6', color: 'white', borderRadius: '8px', textDecoration: 'none', fontWeight: 'bold' }}>Đến danh sách đơn hàng</Link>
                        <Link to="/" style={{ padding: '12px 24px', background: 'white', color: '#3b82f6', border: '2px solid #3b82f6', borderRadius: '8px', textDecoration: 'none', fontWeight: 'bold' }}>Về trang chủ</Link>
                    </div>
                </div>
            )}
        </div>
    );
}
