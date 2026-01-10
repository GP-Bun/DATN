import React, { useState } from 'react';
import { toast } from 'react-hot-toast';

export default function AboutPage() {
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        subject: '',
        message: ''
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        toast.success('Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.');
        setFormData({ name: '', email: '', subject: '', message: '' });
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    return (
        <div className="about-page" style={{ paddingBottom: '80px' }}>
            {/* Hero Section */}
            <section style={{
                background: 'linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url("https://images.unsplash.com/photo-1552346154-21d32810aba3?q=80&w=1470&auto=format&fit=crop") center/cover no-repeat',
                height: '400px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                color: 'white',
                textAlign: 'center',
                borderRadius: '24px',
                marginBottom: '60px',
                marginTop: '20px'
            }}>
                <div>
                    <h1 style={{ fontSize: '4rem', fontWeight: '800', margin: '0 0 16px' }}>Về Chúng Tôi</h1>
                    <p style={{ fontSize: '1.4rem', fontWeight: '400', maxWidth: '600px', margin: '0 auto' }}>
                        Nâng tầm phong cách của bạn với những đôi giày sneaker đẳng cấp nhất từ TH Store.
                    </p>
                </div>
            </section>

            <div className="grid" style={{ gridTemplateColumns: '1fr 1fr', gap: '60px', alignItems: 'center', marginBottom: '80px' }}>
                <div>
                    <h2 style={{ fontSize: '2.5rem', fontWeight: '700', marginBottom: '24px', color: '#1f2937' }}>Câu chuyện của TH Store</h2>
                    <p style={{ fontSize: '1.1rem', color: '#6b7280', lineHeight: '1.8', marginBottom: '20px' }}>
                        Khởi nguồn từ niềm đam mê mãnh liệt dành cho những đôi giày sneaker, TH Store được thành lập với sứ mệnh mang đến cho khách hàng những sản phẩm chất lượng, thời thượng và phản ánh cá tính riêng.
                    </p>
                    <p style={{ fontSize: '1.1rem', color: '#6b7280', lineHeight: '1.8' }}>
                        Chúng tôi tin rằng đôi giày không chỉ là vật dụng đi lại, mà còn là người bạn đồng hành trong từng bước chân chinh phục những đỉnh cao mới. Tại TH Store, mỗi sản phẩm đều được tuyển chọn kỹ lưỡng từ những thương hiệu hàng đầu thế giới.
                    </p>
                </div>
                <div style={{ borderRadius: '24px', overflow: 'hidden', boxShadow: 'var(--shadow-lg)' }}>
                    <img
                        src="https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=1000&auto=format&fit=crop"
                        alt="Shoe collection"
                        style={{ width: '100%', height: '500px', objectFit: 'cover' }}
                    />
                </div>
            </div>

            <section style={{ background: '#f8fafc', padding: '80px 40px', borderRadius: '32px', marginBottom: '80px' }}>
                <h2 style={{ textAlign: 'center', fontSize: '2.5rem', fontWeight: '700', marginBottom: '48px' }}>Sứ mệnh & Tầm nhìn</h2>
                <div className="grid" style={{ gridTemplateColumns: 'repeat(3, 1fr)', gap: '32px' }}>
                    <div style={{ background: 'white', padding: '40px', borderRadius: '24px', boxShadow: 'var(--shadow)', textAlign: 'center' }}>
                        <div style={{ fontSize: '3rem', marginBottom: '20px' }}>🎯</div>
                        <h3 style={{ fontSize: '1.4rem', fontWeight: '600', marginBottom: '16px' }}>Chất lượng</h3>
                        <p style={{ color: '#6b7280' }}>Cam kết 100% sản phẩm chính hãng, được kiểm định nghiêm ngặt về chất lượng.</p>
                    </div>
                    <div style={{ background: 'white', padding: '40px', borderRadius: '24px', boxShadow: 'var(--shadow)', textAlign: 'center' }}>
                        <div style={{ fontSize: '3rem', marginBottom: '20px' }}>🤝</div>
                        <h3 style={{ fontSize: '1.4rem', fontWeight: '600', marginBottom: '16px' }}>Khách hàng</h3>
                        <p style={{ color: '#6b7280' }}>Luôn đặt sự hài lòng của khách hàng lên hàng đầu với dịch vụ hỗ trợ tận tâm.</p>
                    </div>
                    <div style={{ background: 'white', padding: '40px', borderRadius: '24px', boxShadow: 'var(--shadow)', textAlign: 'center' }}>
                        <div style={{ fontSize: '3rem', marginBottom: '20px' }}>🚀</div>
                        <h3 style={{ fontSize: '1.4rem', fontWeight: '600', marginBottom: '16px' }}>Đổi mới</h3>
                        <p style={{ color: '#6b7280' }}>Luôn cập nhật những xu hướng thời trang mới nhất để mang lại sự khác biệt.</p>
                    </div>
                </div>
            </section>

            {/* Contact Section */}
            <section id="contact" style={{ display: 'grid', gridTemplateColumns: 'minmax(0, 1fr) minmax(0, 1.5fr)', gap: '60px' }}>
                <div>
                    <h2 style={{ fontSize: '2.5rem', fontWeight: '700', marginBottom: '24px' }}>Liên hệ với chúng tôi</h2>
                    <p style={{ color: '#6b7280', marginBottom: '40px', fontSize: '1.1rem' }}>
                        Bạn có thắc mắc hay cần hỗ trợ? Đừng ngần ngại gửi tin nhắn cho chúng tôi. Đội ngũ TH Store luôn sẵn sàng giúp đỡ bạn.
                    </p>

                    <div style={{ display: 'flex', flexDirection: 'column', gap: '24px' }}>
                        <div style={{ display: 'flex', gap: '16px', alignItems: 'center' }}>
                            <div style={{ width: '50px', height: '50px', borderRadius: '12px', background: 'var(--primary)', color: 'white', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px' }}>📍</div>
                            <div>
                                <h4 style={{ margin: 0, fontWeight: '600' }}>Địa chỉ</h4>
                                <p style={{ margin: 0, color: '#6b7280' }}>240 Phú Mỹ Quận Cầu Giấy, Hà Nội</p>
                            </div>
                        </div>
                        <div style={{ display: 'flex', gap: '16px', alignItems: 'center' }}>
                            <div style={{ width: '50px', height: '50px', borderRadius: '12px', background: 'var(--primary)', color: 'white', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px' }}>📞</div>
                            <div>
                                <h4 style={{ margin: 0, fontWeight: '600' }}>Điện thoại</h4>
                                <p style={{ margin: 0, color: '#6b7280' }}>0964232666</p>
                            </div>
                        </div>
                        <div style={{ display: 'flex', gap: '16px', alignItems: 'center' }}>
                            <div style={{ width: '50px', height: '50px', borderRadius: '12px', background: 'var(--primary)', color: 'white', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px' }}>📧</div>
                            <div>
                                <h4 style={{ margin: 0, fontWeight: '600' }}>Email</h4>
                                <p style={{ margin: 0, color: '#6b7280' }}>contact@thstore.vn</p>
                            </div>
                        </div>
                    </div>

                    <div style={{ marginTop: '40px', height: '250px', borderRadius: '20px', overflow: 'hidden', border: '1px solid var(--border)' }}>
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3723.8638558814227!2d105.74459841541012!3d21.03813279283311!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x313454b991d80fd5%3A0x530c0de1d2a84961!2zVHLGsOG7nW5nIENhbyDEkeG6s25nIEZQVCBQb2x5dGVjaG5pYw!5e0!3m2!1svi!2s!4v1655000000000!5m2!1svi!2s"
                            width="100%" height="100%" style={{ border: 0 }} allowFullScreen loading="lazy"></iframe>
                    </div>
                </div>

                <div style={{ background: 'white', padding: '40px', borderRadius: '32px', boxShadow: 'var(--shadow-lg)', border: '1px solid var(--border)' }}>
                    <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }}>
                            <div>
                                <label style={{ display: 'block', marginBottom: '8px', fontWeight: '600', color: '#374151' }}>Họ và tên</label>
                                <input
                                    type="text"
                                    name="name"
                                    value={formData.name}
                                    onChange={handleChange}
                                    placeholder="Nhập họ và tên"
                                    required
                                    style={{ width: '100%', padding: '14px', borderRadius: '12px', border: '1px solid #d1d5db', fontSize: '16px' }}
                                />
                            </div>
                            <div>
                                <label style={{ display: 'block', marginBottom: '8px', fontWeight: '600', color: '#374151' }}>Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    value={formData.email}
                                    onChange={handleChange}
                                    placeholder="name@example.com"
                                    required
                                    style={{ width: '100%', padding: '14px', borderRadius: '12px', border: '1px solid #d1d5db', fontSize: '16px' }}
                                />
                            </div>
                        </div>
                        <div>
                            <label style={{ display: 'block', marginBottom: '8px', fontWeight: '600', color: '#374151' }}>Chủ đề</label>
                            <input
                                type="text"
                                name="subject"
                                value={formData.subject}
                                onChange={handleChange}
                                placeholder="Bạn cần hỗ trợ điều gì?"
                                required
                                style={{ width: '100%', padding: '14px', borderRadius: '12px', border: '1px solid #d1d5db', fontSize: '16px' }}
                            />
                        </div>
                        <div>
                            <label style={{ display: 'block', marginBottom: '8px', fontWeight: '600', color: '#374151' }}>Lời nhắn</label>
                            <textarea
                                name="message"
                                rows={5}
                                value={formData.message}
                                onChange={handleChange}
                                placeholder="Nhập lời nhắn của bạn ở đây..."
                                required
                                style={{ width: '100%', padding: '14px', borderRadius: '12px', border: '1px solid #d1d5db', fontSize: '16px', resize: 'vertical' }}
                            ></textarea>
                        </div>
                        <button
                            type="submit"
                            style={{
                                background: 'var(--gradient)',
                                color: 'white',
                                padding: '16px',
                                borderRadius: '16px',
                                border: 'none',
                                fontSize: '18px',
                                fontWeight: '600',
                                cursor: 'pointer',
                                transition: 'all 0.3s ease',
                                marginTop: '10px'
                            }}
                            onMouseEnter={(e) => {
                                e.currentTarget.style.transform = 'translateY(-2px)';
                                e.currentTarget.style.boxShadow = '0 10px 15px -3px rgba(102, 126, 234, 0.4)';
                            }}
                            onMouseLeave={(e) => {
                                e.currentTarget.style.transform = 'translateY(0)';
                                e.currentTarget.style.boxShadow = 'none';
                            }}
                        >
                            Gửi tin nhắn 📨
                        </button>
                    </form>
                </div>
            </section>
        </div>
    );
}
