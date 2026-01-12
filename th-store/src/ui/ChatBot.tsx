import { useState, useRef, useEffect } from 'react';
import api from '../api/api';
import { toast } from 'react-hot-toast';
import { useAuth } from '../store/AuthContext';

interface Message {
    id: string;
    text: string;
    sender: 'user' | 'bot' | 'admin';
    timestamp: Date;
}

export default function ChatBot() {
    const { user } = useAuth();
    const [isOpen, setIsOpen] = useState(false);
    const [messages, setMessages] = useState<Message[]>([
        {
            id: 'initial',
            text: 'Chào bạn! Tôi là trợ lý AI của TH Store. Tôi có thể giúp gì được cho bạn?',
            sender: 'bot',
            timestamp: new Date()
        }
    ]);
    const [inputValue, setInputValue] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const pollingRef = useRef<any>(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    useEffect(() => {
        if (isOpen) {
            scrollToBottom();
            if (user) {
                fetchHistory();
                // Bắt đầu polling khi mở chat
                pollingRef.current = setInterval(fetchHistory, 5000);
            }
        } else {
            if (pollingRef.current) {
                clearInterval(pollingRef.current);
            }
        }
        return () => {
            if (pollingRef.current) clearInterval(pollingRef.current);
        };
    }, [isOpen, user]);

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const fetchHistory = async () => {
        try {
            const res = await api.get('/messages');
            const history = res.data.map((m: any) => ({
                id: m.id.toString(),
                text: m.content,
                sender: m.sender_id === user?.id ? 'user' : 'admin',
                timestamp: new Date(m.created_at)
            }));

            if (history.length > 0) {
                // Giữ lại tin nhắn chào mừng đầu tiên nếu muốn, hoặc thay thế hoàn toàn
                setMessages([
                    {
                        id: 'initial',
                        text: 'Chào bạn! Tôi là trợ lý của TH Store. Bạn có thể chat với AI hoặc đợi Admin trả lời nhé.',
                        sender: 'bot',
                        timestamp: history[0] ? new Date(history[0].timestamp.getTime() - 1000) : new Date()
                    },
                    ...history
                ]);
            }
        } catch (error) {
            console.error('Fetch History Error:', error);
        }
    };

    const handleSend = async () => {
        if (!inputValue.trim()) return;

        const userMsg: Message = {
            id: Date.now().toString(),
            text: inputValue,
            sender: 'user',
            timestamp: new Date()
        };

        setMessages(prev => [...prev, userMsg]);
        const currentInput = inputValue;
        setInputValue('');
        setIsLoading(true);

        try {
            // Gửi tới chatbot (Backend đã được cập nhật để lưu vào DB nếu có user)
            const response = await api.post('/chatbot', {
                message: currentInput
            });

            // Nếu không dùng polling thì cập nhật cục bộ tin nhắn bot
            const botMsg: Message = {
                id: (Date.now() + 1).toString(),
                text: response.data.reply,
                sender: 'bot',
                timestamp: new Date()
            };

            setMessages(prev => [...prev, botMsg]);

            // Nếu có user thì fetch lại phát cuối cho chắc đồng bộ
            if (user) fetchHistory();

        } catch (error) {
            console.error('Chat Error:', error);
            // Nếu chưa đăng nhập thì vẫn cho chat với bot bình thường (state-less)
            toast.error('Có lỗi xảy ra khi gửi tin nhắn.');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <>
            {/* Floating Button */}
            <button
                onClick={() => setIsOpen(!isOpen)}
                style={{
                    position: 'fixed',
                    bottom: '30px',
                    right: '30px',
                    width: '60px',
                    height: '60px',
                    borderRadius: '50%',
                    background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    color: 'white',
                    border: 'none',
                    boxShadow: '0 10px 25px rgba(0,0,0,0.2)',
                    cursor: 'pointer',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: '24px',
                    zIndex: 1000,
                    transition: 'transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
                }}
                onMouseEnter={(e) => e.currentTarget.style.transform = 'scale(1.1) rotate(5deg)'}
                onMouseLeave={(e) => e.currentTarget.style.transform = 'scale(1) rotate(0deg)'}
            >
                {isOpen ? '✕' : '💬'}
            </button>

            {/* Chat Window */}
            {isOpen && (
                <div
                    style={{
                        position: 'fixed',
                        bottom: '100px',
                        right: '30px',
                        width: '380px',
                        height: '550px',
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdropFilter: 'blur(10px)',
                        borderRadius: '24px',
                        boxShadow: '0 20px 50px rgba(0,0,0,0.15)',
                        display: 'flex',
                        flexDirection: 'column',
                        overflow: 'hidden',
                        zIndex: 1000,
                        border: '1px solid rgba(255, 255, 255, 0.3)',
                        animation: 'slideIn 0.3s ease-out',
                    }}
                >
                    {/* Header */}
                    <div
                        style={{
                            padding: '20px',
                            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                            color: 'white',
                            display: 'flex',
                            alignItems: 'center',
                            gap: '12px'
                        }}
                    >
                        <div style={{ width: '10px', height: '10px', borderRadius: '50%', background: '#4ade80', boxShadow: '0 0 10px #4ade80' }}></div>
                        <div>
                            <h3 style={{ margin: 0, fontSize: '16px', fontWeight: '700' }}>TH Store Support</h3>
                            <p style={{ margin: 0, fontSize: '12px', opacity: 0.8 }}>
                                {user ? `Đang chat: ${user.name}` : 'Chế độ khách (AI hỗ trợ)'}
                            </p>
                        </div>
                    </div>

                    {/* Messages */}
                    <div
                        style={{
                            flex: 1,
                            padding: '20px',
                            overflowY: 'auto',
                            display: 'flex',
                            flexDirection: 'column',
                            gap: '15px'
                        }}
                    >
                        {messages.map((msg, index) => (
                            <div
                                key={`${msg.id}-${index}`}
                                style={{
                                    alignSelf: msg.sender === 'user' ? 'flex-end' : 'flex-start',
                                    maxWidth: '80%',
                                    display: 'flex',
                                    flexDirection: 'column',
                                    gap: '4px'
                                }}
                            >
                                <div
                                    style={{
                                        padding: '12px 16px',
                                        borderRadius: msg.sender === 'user' ? '18px 18px 4px 18px' : '18px 18px 18px 4px',
                                        background: msg.sender === 'user' ? '#667eea' : '#f1f5f9',
                                        color: msg.sender === 'user' ? 'white' : '#1e293b',
                                        fontSize: '14px',
                                        lineHeight: '1.5',
                                        boxShadow: '0 2px 5px rgba(0,0,0,0.05)'
                                    }}
                                >
                                    {msg.text}
                                </div>
                                <span style={{ fontSize: '10px', color: '#94a3b8', textAlign: msg.sender === 'user' ? 'right' : 'left' }}>
                                    {msg.sender === 'admin' ? 'Admin | ' : ''}
                                    {msg.timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                </span>
                            </div>
                        ))}
                        {isLoading && (
                            <div style={{ alignSelf: 'flex-start', background: '#f1f5f9', padding: '12px 16px', borderRadius: '18px 18px 18px 4px', display: 'flex', gap: '4px' }}>
                                <div className="dot" style={{ width: '6px', height: '6px', background: '#94a3b8', borderRadius: '50%', animation: 'bounce 1.4s infinite ease-in-out' }}></div>
                                <div className="dot" style={{ width: '6px', height: '6px', background: '#94a3b8', borderRadius: '50%', animation: 'bounce 1.4s infinite ease-in-out 0.2s' }}></div>
                                <div className="dot" style={{ width: '6px', height: '6px', background: '#94a3b8', borderRadius: '50%', animation: 'bounce 1.4s infinite ease-in-out 0.4s' }}></div>
                            </div>
                        )}
                        <div ref={messagesEndRef} />
                    </div>

                    {/* Input */}
                    {!user && (
                        <div style={{ padding: '8px 20px', background: '#fffbeb', borderTop: '1px solid #fef3c7', fontSize: '11px', color: '#92400e', textAlign: 'center' }}>
                            Đăng nhập để lưu lịch sử chat và được Admin hỗ trợ trực tiếp.
                        </div>
                    )}
                    <div style={{ padding: '20px', borderTop: '1px solid #f1f5f9', display: 'flex', gap: '10px' }}>
                        <input
                            type="text"
                            value={inputValue}
                            onChange={(e) => setInputValue(e.target.value)}
                            onKeyPress={(e) => e.key === 'Enter' && handleSend()}
                            placeholder="Nhập tin nhắn..."
                            disabled={isLoading}
                            style={{
                                flex: 1,
                                padding: '12px 16px',
                                borderRadius: '12px',
                                border: '1px solid #e2e8f0',
                                outline: 'none',
                                fontSize: '14px',
                                transition: 'border-color 0.2s'
                            }}
                            onFocus={(e) => e.currentTarget.style.borderColor = '#667eea'}
                            onBlur={(e) => e.currentTarget.style.borderColor = '#e2e8f0'}
                        />
                        <button
                            onClick={handleSend}
                            disabled={isLoading || !inputValue.trim()}
                            style={{
                                width: '45px',
                                height: '45px',
                                borderRadius: '12px',
                                background: inputValue.trim() ? '#667eea' : '#e2e8f0',
                                color: 'white',
                                border: 'none',
                                cursor: 'pointer',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                transition: 'all 0.2s'
                            }}
                        >
                            🚀
                        </button>
                    </div>
                </div>
            )}

            <style>{`
        @keyframes slideIn {
          from { opacity: 0; transform: translateY(20px); }
          to { opacity: 1; transform: translateY(0); }
        }
        @keyframes bounce {
          0%, 80%, 100% { transform: scale(0); }
          40% { transform: scale(1.0); }
        }
      `}</style>
        </>
    );
}
