import { useEffect, useState } from 'react';
import { useLocation, Link } from 'react-router-dom';
import { formatPrice } from '../utils/formatPrice';

interface PaymentResult {
    success: boolean;
    orderNumber?: string;
    amount?: number;
    transactionNo?: string;
    bankCode?: string;
    message?: string;
    code?: string;
}

export default function PaymentResultPage() {
    const location = useLocation();
    const [result, setResult] = useState<PaymentResult | null>(null);

    useEffect(() => {
        const params = new URLSearchParams(location.search);
        setResult({
            success: params.get('success') === 'true',
            orderNumber: params.get('orderNumber') || undefined,
            amount: params.get('amount') ? Number(params.get('amount')) : undefined,
            transactionNo: params.get('transactionNo') || undefined,
            bankCode: params.get('bankCode') || undefined,
            message: params.get('message') ? decodeURIComponent(params.get('message')!) : undefined,
            code: params.get('code') || undefined,
        });
    }, [location]);

    if (!result) {
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
                <h2>Đang xử lý...</h2>
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
            {result.success ? (
                <div style={{ textAlign: 'center' }}>
                    <div style={{
                        background: '#ecfdf5',
                        borderRadius: '16px',
                        padding: '40px',
                        marginBottom: '30px'
                    }}>
                        <div style={{
                            width: '80px',
                            height: '80px',
                            background: '#10b981',
                            borderRadius: '50%',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            margin: '0 auto 20px',
                            fontSize: '40px'
                        }}>
                            ✓
                        </div>
                        <h1 style={{ color: '#065f46', marginBottom: '10px' }}>
                            Thanh toán thành công!
                        </h1>
                        <p style={{ color: '#047857', fontSize: '18px' }}>
                            Cảm ơn bạn đã mua hàng tại TH Store
                        </p>
                    </div>

                    <div style={{
                        background: '#f8fafc',
                        borderRadius: '12px',
                        padding: '24px',
                        textAlign: 'left',
                        marginBottom: '30px'
                    }}>
                        <h3 style={{ marginBottom: '16px', color: '#1f2937' }}>
                            Thông tin giao dịch
                        </h3>
                        <div style={{ display: 'grid', gap: '12px' }}>
                            {result.orderNumber && (
                                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                    <span style={{ color: '#6b7280' }}>Mã đơn hàng:</span>
                                    <strong style={{ color: '#1f2937' }}>#{result.orderNumber}</strong>
                                </div>
                            )}
                            {result.amount && (
                                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                    <span style={{ color: '#6b7280' }}>Số tiền:</span>
                                    <strong style={{ color: '#10b981' }}>{formatPrice(result.amount)}</strong>
                                </div>
                            )}
                            {result.transactionNo && (
                                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                    <span style={{ color: '#6b7280' }}>Mã giao dịch:</span>
                                    <strong style={{ color: '#1f2937' }}>{result.transactionNo}</strong>
                                </div>
                            )}
                            {result.bankCode && (
                                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                    <span style={{ color: '#6b7280' }}>Ngân hàng:</span>
                                    <strong style={{ color: '#1f2937' }}>{result.bankCode}</strong>
                                </div>
                            )}
                        </div>
                    </div>

                    <div style={{ display: 'flex', gap: '16px', justifyContent: 'center' }}>
                        <Link
                            to="/don-hang"
                            style={{
                                padding: '14px 28px',
                                background: '#10b981',
                                color: 'white',
                                borderRadius: '8px',
                                textDecoration: 'none',
                                fontWeight: '600'
                            }}
                        >
                            Xem đơn hàng
                        </Link>
                        <Link
                            to="/"
                            style={{
                                padding: '14px 28px',
                                background: '#f1f5f9',
                                color: '#475569',
                                borderRadius: '8px',
                                textDecoration: 'none',
                                fontWeight: '600'
                            }}
                        >
                            Về trang chủ
                        </Link>
                    </div>
                </div>
            ) : (
                <div style={{ textAlign: 'center' }}>
                    <div style={{
                        background: '#fef2f2',
                        borderRadius: '16px',
                        padding: '40px',
                        marginBottom: '30px'
                    }}>
                        <div style={{
                            width: '80px',
                            height: '80px',
                            background: '#ef4444',
                            borderRadius: '50%',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            margin: '0 auto 20px',
                            fontSize: '40px',
                            color: 'white'
                        }}>
                            ✕
                        </div>
                        <h1 style={{ color: '#991b1b', marginBottom: '10px' }}>
                            Thanh toán thất bại
                        </h1>
                        <p style={{ color: '#dc2626', fontSize: '18px' }}>
                            {result.message || 'Đã có lỗi xảy ra trong quá trình thanh toán'}
                        </p>
                        {result.code && (
                            <p style={{ color: '#9ca3af', fontSize: '14px', marginTop: '10px' }}>
                                Mã lỗi: {result.code}
                            </p>
                        )}
                    </div>

                    {result.orderNumber && (
                        <div style={{
                            background: '#f8fafc',
                            borderRadius: '12px',
                            padding: '24px',
                            textAlign: 'left',
                            marginBottom: '30px'
                        }}>
                            <h3 style={{ marginBottom: '16px', color: '#1f2937' }}>
                                Thông tin đơn hàng
                            </h3>
                            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                <span style={{ color: '#6b7280' }}>Mã đơn hàng:</span>
                                <strong style={{ color: '#1f2937' }}>#{result.orderNumber}</strong>
                            </div>
                            <p style={{ color: '#6b7280', fontSize: '14px', marginTop: '12px' }}>
                                Đơn hàng đã bị hủy. Bạn có thể đặt lại đơn hàng mới.
                            </p>
                        </div>
                    )}

                    <div style={{ display: 'flex', gap: '16px', justifyContent: 'center' }}>
                        <Link
                            to="/cart"
                            style={{
                                padding: '14px 28px',
                                background: '#ef4444',
                                color: 'white',
                                borderRadius: '8px',
                                textDecoration: 'none',
                                fontWeight: '600'
                            }}
                        >
                            Quay lại giỏ hàng
                        </Link>
                        <Link
                            to="/"
                            style={{
                                padding: '14px 28px',
                                background: '#f1f5f9',
                                color: '#475569',
                                borderRadius: '8px',
                                textDecoration: 'none',
                                fontWeight: '600'
                            }}
                        >
                            Về trang chủ
                        </Link>
                    </div>
                </div>
            )}
        </div>
    );
}
