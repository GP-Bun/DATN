import { useState, useEffect, useRef } from 'react';
import api from '../../api/api';
import { toast } from 'react-hot-toast';

interface Conversation {
    id: number;
    user_id: number;
    admin_id: number;
    last_message_at: string;
    unread_count: number;
    user: {
        id: number;
        name: string;
        email: string;
    };
    messages?: Message[];
}

interface Message {
    id: number;
    conversation_id: number;
    sender_id: number;
    receiver_id: number;
    content: string;
    created_at: string;
    read_at: string | null;
}

export default function ChatSupport() {
    const [conversations, setConversations] = useState<Conversation[]>([]);
    const [selectedId, setSelectedId] = useState<number | null>(null);
    const [messages, setMessages] = useState<Message[]>([]);
    const [inputValue, setInputValue] = useState('');
    const [loading, setLoading] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    useEffect(() => {
        fetchConversations();
        const interval = setInterval(fetchConversations, 10000);
        return () => clearInterval(interval);
    }, []);

    useEffect(() => {
        if (selectedId) {
            fetchMessages(selectedId);
            const interval = setInterval(() => fetchMessages(selectedId), 5000);
            return () => clearInterval(interval);
        }
    }, [selectedId]);

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const fetchConversations = async () => {
        try {
            const res = await api.get('/admin/conversations');
            setConversations(res.data);
        } catch (error) {
            console.error('Fetch Conversations Error:', error);
        }
    };

    const fetchMessages = async (convId: number) => {
        try {
            // Trong Backend chưa có route lấy message theo conv_id riêng biệt cho admin?
            // Nhưng index/show order đã có. Ở đây MessageController mang tính global.
            // Tạm thời fetch tất cả và filter hoặc giả định Backend trả về messages trong conversation object.
            const res = await api.get('/admin/conversations');
            const conv = res.data.find((c: any) => c.id === convId);
            if (conv && conv.messages) {
                setMessages(conv.messages);
            }
        } catch (error) {
            console.error('Fetch Messages Error:', error);
        }
    };

    const handleSend = async () => {
        if (!selectedId || !inputValue.trim()) return;

        const currentConv = conversations.find(c => c.id === selectedId);
        if (!currentConv) return;

        setLoading(true);
        try {
            await api.post('/messages', {
                receiver_id: currentConv.user_id,
                content: inputValue
            });
            setInputValue('');
            fetchMessages(selectedId);
        } catch (error) {
            toast.error('Gửi tin nhắn thất bại');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div style={{ display: 'flex', height: 'calc(100vh - 120px)', gap: '20px', padding: '20px' }}>
            {/* Conversations List */}
            <div style={{ width: '300px', background: 'white', borderRadius: '12px', border: '1px solid #e5e7eb', overflowY: 'auto' }}>
                <div style={{ padding: '20px', borderBottom: '1px solid #f3f4f6' }}>
                    <h2 style={{ fontSize: '18px', fontWeight: '700', margin: 0 }}>Hội thoại</h2>
                </div>
                {conversations.map(conv => (
                    <div
                        key={conv.id}
                        onClick={() => setSelectedId(conv.id)}
                        style={{
                            padding: '15px 20px',
                            cursor: 'pointer',
                            background: selectedId === conv.id ? '#f0f9ff' : 'transparent',
                            borderBottom: '1px solid #f9fafb',
                            transition: 'all 0.2s'
                        }}
                    >
                        <div style={{ fontWeight: '600', color: '#1f2937' }}>{conv.user.name}</div>
                        <div style={{ fontSize: '12px', color: '#6b7280', marginTop: '4px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                            {conv.last_message_at}
                        </div>
                    </div>
                ))}
            </div>

            {/* Chat Messages */}
            <div style={{ flex: 1, background: 'white', borderRadius: '12px', border: '1px solid #e5e7eb', display: 'flex', flexDirection: 'column' }}>
                {selectedId ? (
                    <>
                        <div style={{ padding: '20px', borderBottom: '1px solid #f3f4f6' }}>
                            <h2 style={{ fontSize: '18px', fontWeight: '700', margin: 0 }}>
                                Chat với {conversations.find(c => c.id === selectedId)?.user.name}
                            </h2>
                        </div>
                        <div style={{ flex: 1, padding: '20px', overflowY: 'auto', display: 'flex', flexDirection: 'column', gap: '12px' }}>
                            {messages.map(msg => (
                                <div
                                    key={msg.id}
                                    style={{
                                        alignSelf: msg.sender_id === conversations.find(c => c.id === selectedId)?.user_id ? 'flex-start' : 'flex-end',
                                        maxWidth: '70%',
                                        padding: '10px 15px',
                                        borderRadius: '12px',
                                        background: msg.sender_id === conversations.find(c => c.id === selectedId)?.user_id ? '#f3f4f6' : '#3b82f6',
                                        color: msg.sender_id === conversations.find(c => c.id === selectedId)?.user_id ? '#1f2937' : 'white'
                                    }}
                                >
                                    {msg.content}
                                </div>
                            ))}
                            <div ref={messagesEndRef} />
                        </div>
                        <div style={{ padding: '20px', borderTop: '1px solid #f3f4f6', display: 'flex', gap: '10px' }}>
                            <input
                                value={inputValue}
                                onChange={e => setInputValue(e.target.value)}
                                onKeyPress={e => e.key === 'Enter' && handleSend()}
                                placeholder="Nhập câu trả lời..."
                                style={{ flex: 1, padding: '10px', borderRadius: '8px', border: '1px solid #d1d5db' }}
                            />
                            <button
                                onClick={handleSend}
                                disabled={loading}
                                style={{ padding: '10px 20px', background: '#3b82f6', color: 'white', border: 'none', borderRadius: '8px', cursor: 'pointer' }}
                            >
                                Gửi
                            </button>
                        </div>
                    </>
                ) : (
                    <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#9ca3af' }}>
                        Chọn một hội thoại để bắt đầu hỗ trợ
                    </div>
                )}
            </div>
        </div>
    );
}
