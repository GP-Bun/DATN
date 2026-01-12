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
            // Ensure data is array
            const data = Array.isArray(res.data) ? res.data : (res.data?.data || []);
            setConversations(data);
        } catch (error) {
            console.error('Fetch Conversations Error:', error);
        }
    };

    const fetchMessages = async (convId: number) => {
        try {
            // Re-fetch all to get updated messages structure
            // In a real app we might have a specific endpoint like /admin/conversations/:id/messages
            const res = await api.get('/admin/conversations');
            const data = Array.isArray(res.data) ? res.data : (res.data?.data || []);
            const conv = data.find((c: any) => c.id === convId);
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
            // Optimistic update or refetch
            fetchMessages(selectedId);
        } catch (error) {
            toast.error('Gửi tin nhắn thất bại');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div style={{ padding: '24px', fontFamily: 'Inter, sans-serif', height: '100vh', display: 'flex', flexDirection: 'column' }}>
            {/* Header Section */}
            <div style={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                marginBottom: '20px',
                background: 'white',
                padding: '20px',
                borderRadius: '12px',
                boxShadow: '0 2px 4px rgba(0,0,0,0.05)',
                flexShrink: 0
            }}>
                <div>
                    <h1 style={{ fontSize: '24px', fontWeight: '700', color: '#111827', margin: 0 }}>Hỗ trợ trực tuyến</h1>
                    <p style={{ color: '#6b7280', margin: '4px 0 0 0', fontSize: '14px' }}>
                        Chat trực tiếp với khách hàng của bạn
                    </p>
                </div>
            </div>

            {/* Chat Container */}
            <div style={{ display: 'flex', flex: 1, gap: '20px', minHeight: 0 }}>
                {/* Conversations List */}
                <div style={{ width: '300px', background: 'white', borderRadius: '12px', boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)', overflowY: 'auto', display: 'flex', flexDirection: 'column' }}>
                    <div style={{ padding: '20px', borderBottom: '1px solid #f3f4f6', flexShrink: 0 }}>
                        <h2 style={{ fontSize: '16px', fontWeight: '700', margin: 0, color: '#374151' }}>Danh sách hội thoại</h2>
                    </div>
                    <div style={{ flex: 1, overflowY: 'auto' }}>
                        {conversations.map(conv => (
                            <div
                                key={conv.id}
                                onClick={() => setSelectedId(conv.id)}
                                style={{
                                    padding: '16px 20px',
                                    cursor: 'pointer',
                                    background: selectedId === conv.id ? '#eff6ff' : 'transparent',
                                    borderLeft: selectedId === conv.id ? '4px solid #3b82f6' : '4px solid transparent',
                                    borderBottom: '1px solid #f9fafb',
                                    transition: 'all 0.2s'
                                }}
                            >
                                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px' }}>
                                    <div style={{ fontWeight: '600', color: '#1f2937', fontSize: '14px' }}>{conv.user?.name || 'Unknown User'}</div>
                                    {conv.unread_count > 0 && <span style={{ width: '8px', height: '8px', background: '#ef4444', borderRadius: '50%' }}></span>}
                                </div>
                                <div style={{ fontSize: '12px', color: '#6b7280', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                                    {conv.user?.email}
                                </div>
                                <div style={{ fontSize: '11px', color: '#9ca3af', marginTop: '4px', textAlign: 'right' }}>
                                    {new Date(conv.last_message_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Chat Messages */}
                <div style={{ flex: 1, background: 'white', borderRadius: '12px', boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)', display: 'flex', flexDirection: 'column', overflow: 'hidden' }}>
                    {selectedId ? (
                        <>
                            <div style={{ padding: '20px', borderBottom: '1px solid #f3f4f6', flexShrink: 0, display: 'flex', alignItems: 'center', gap: '12px' }}>
                                <div style={{ width: '40px', height: '40px', borderRadius: '50%', background: '#e5e7eb', display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 'bold', color: '#6b7280' }}>
                                    {conversations.find(c => c.id === selectedId)?.user?.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <h2 style={{ fontSize: '16px', fontWeight: '700', margin: 0, color: '#111827' }}>
                                        {conversations.find(c => c.id === selectedId)?.user?.name}
                                    </h2>
                                    <div style={{ fontSize: '12px', color: '#10b981', display: 'flex', alignItems: 'center', gap: '4px' }}>
                                        <span style={{ width: '6px', height: '6px', borderRadius: '50%', background: '#10b981' }}></span> Online
                                    </div>
                                </div>
                            </div>

                            <div style={{ flex: 1, padding: '24px', overflowY: 'auto', display: 'flex', flexDirection: 'column', gap: '20px', backgroundColor: '#f3f4f6' }}>
                                {messages.map((msg, index) => {
                                    const currentConv = conversations.find(c => c.id === selectedId);
                                    // Use loose equality or explicit conversion to handle potential string/number mismatches from API
                                    const isIncoming = currentConv && (Number(msg.sender_id) === Number(currentConv.user_id)); // Message from Customer
                                    const showAvatar = index === 0 || messages[index - 1].sender_id !== msg.sender_id;

                                    return (
                                        <div
                                            key={msg.id}
                                            style={{
                                                alignSelf: isIncoming ? 'flex-start' : 'flex-end',
                                                display: 'flex',
                                                flexDirection: isIncoming ? 'row' : 'row-reverse',
                                                alignItems: 'flex-end',
                                                gap: '8px',
                                                maxWidth: '80%'
                                            }}
                                        >
                                            {/* Avatar */}
                                            <div style={{
                                                width: '32px',
                                                height: '32px',
                                                borderRadius: '50%',
                                                background: isIncoming ? '#e5e7eb' : '#3b82f6',
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center',
                                                fontSize: '12px',
                                                fontWeight: 'bold',
                                                color: isIncoming ? '#6b7280' : 'white',
                                                flexShrink: 0,
                                                opacity: showAvatar ? 1 : 0 // Preserve space if grouped
                                            }}>
                                                {isIncoming
                                                    ? (currentConv?.user?.name?.charAt(0).toUpperCase() || 'C')
                                                    : 'A' // Admin
                                                }
                                            </div>

                                            {/* Message Bubble */}
                                            <div style={{ display: 'flex', flexDirection: 'column', alignItems: isIncoming ? 'flex-start' : 'flex-end' }}>
                                                {showAvatar && (
                                                    <span style={{ fontSize: '12px', color: '#9ca3af', marginBottom: '4px', marginLeft: isIncoming ? '4px' : 0, marginRight: !isIncoming ? '4px' : 0 }}>
                                                        {isIncoming ? currentConv?.user?.name : 'Bạn'}
                                                    </span>
                                                )}
                                                <div style={{
                                                    padding: '12px 16px',
                                                    borderRadius: isIncoming ? '16px 16px 16px 4px' : '16px 16px 4px 16px',
                                                    background: isIncoming ? 'white' : 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
                                                    color: isIncoming ? '#1f2937' : 'white',
                                                    boxShadow: '0 2px 4px rgba(0,0,0,0.05)',
                                                    fontSize: '14px',
                                                    lineHeight: '1.5',
                                                    position: 'relative',
                                                    border: isIncoming ? '1px solid #e5e7eb' : 'none'
                                                }}>
                                                    {msg.content}
                                                </div>
                                                <div style={{ fontSize: '11px', color: '#9ca3af', marginTop: '4px', opacity: 0.8 }}>
                                                    {new Date(msg.created_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                                <div ref={messagesEndRef} />
                            </div>

                            <div style={{ padding: '20px', borderTop: '1px solid #f3f4f6', display: 'flex', gap: '12px', backgroundColor: 'white' }}>
                                <input
                                    value={inputValue}
                                    onChange={e => setInputValue(e.target.value)}
                                    onKeyPress={e => e.key === 'Enter' && handleSend()}
                                    placeholder="Nhập tin nhắn..."
                                    style={{
                                        flex: 1,
                                        padding: '12px 16px',
                                        borderRadius: '12px',
                                        border: '1px solid #d1d5db',
                                        fontSize: '14px',
                                        outline: 'none',
                                        transition: 'border-color 0.2s'
                                    }}
                                />
                                <button
                                    onClick={handleSend}
                                    disabled={loading}
                                    style={{
                                        padding: '12px 24px',
                                        background: '#3b82f6',
                                        color: 'white',
                                        border: 'none',
                                        borderRadius: '12px',
                                        cursor: 'pointer',
                                        fontWeight: '600',
                                        transition: 'opacity 0.2s',
                                        opacity: loading || !inputValue.trim() ? 0.7 : 1
                                    }}
                                >
                                    Gửi
                                </button>
                            </div>
                        </>
                    ) : (
                        <div style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', color: '#9ca3af' }}>
                            <div style={{ fontSize: '48px', marginBottom: '16px' }}>💬</div>
                            <div style={{ fontSize: '16px', fontWeight: '500' }}>Chọn một cuộc hội thoại để bắt đầu hỗ trợ</div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
