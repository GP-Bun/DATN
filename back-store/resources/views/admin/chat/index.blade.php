@extends('layouts.app')

@section('title', 'Hỗ trợ chat')

@section('content')
<div class="container-fluid">
    <div class="row" style="height: calc(100vh - 150px);">
        <!-- Sidebar: Danh sách hội thoại -->
        <div class="col-md-4 border-end bg-white p-0 overflow-auto">
            <div class="p-3 border-bottom bg-light">
                <h5 class="m-0">Hội thoại</h5>
            </div>
            @forelse($conversations as $conv)
            <div class="chat-item p-3 border-bottom cursor-pointer {{ $loop->first ? 'active-chat' : '' }} d-flex justify-content-between align-items-center" 
                 onclick="selectConversation({{ $conv->id }}, '{{ $conv->user->name }}')"
                 id="conv-{{ $conv->id }}">
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong class="text-dark">{{ $conv->user->name }}</strong>
                        <small class="text-muted">{{ $conv->last_message_at }}</small>
                    </div>
                    <div class="text-truncate small text-secondary">
                        {{ $conv->messages->last()->content ?? 'Chưa có tin nhắn' }}
                    </div>
                </div>
                <button class="btn btn-sm text-danger border-0 ms-2" onclick="deleteConversation(event, {{ $conv->id }})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            @empty
            <div class="p-4 text-center text-muted">
                <i class="bi bi-chat-left-dots" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                Chưa có cuộc hội thoại nào từ khách hàng.
            </div>
            @endforelse
        </div>

        <!-- Khung chat -->
        <div class="col-md-8 d-flex flex-column bg-white p-0">
            <div id="chat-header" class="p-3 border-bottom bg-light">
                <h5 class="m-0" id="selected-user-name">Chọn khách hàng để hỗ trợ</h5>
            </div>
            
            <div id="messages-container" class="flex-grow-1 p-3 overflow-auto d-flex flex-column gap-2" style="background: #f1f5f9;">
                <!-- Tin nhắn sẽ được load ở đây -->
                <div class="text-center text-muted mt-5">Vui lòng chọn một cuộc trò chuyện</div>
            </div>

            <div class="p-3 border-top bg-light">
                <form id="chat-form" onsubmit="sendMessage(event)" class="d-flex gap-2">
                    <input type="text" id="chat-input" class="form-control" placeholder="Nhập tin nhắn..." disabled>
                    <button type="submit" id="send-btn" class="btn btn-primary" disabled>Gửi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .chat-item:hover { background-color: #f8fafc; cursor: pointer; }
    .active-chat { background-color: #e2e8f0 !important; border-left: 4px solid #3b82f6; }
    .message { max-width: 75%; padding: 10px 15px; border-radius: 15px; margin-bottom: 5px; }
    .message-sent { align-self: flex-end; background-color: #3b82f6; color: white; border-bottom-right-radius: 2px; }
    .message-received { align-self: flex-start; background-color: #e2e8f0; color: #1e293b; border-bottom-left-radius: 2px; }
</style>

@section('scripts')
<script>
    let currentConvId = null;
    let pollInterval = null;

    function selectConversation(id, name) {
        currentConvId = id;
        document.getElementById('selected-user-name').innerText = `Chat với: ${name}`;
        document.getElementById('chat-input').disabled = false;
        document.getElementById('send-btn').disabled = false;
        
        // Update UI
        document.querySelectorAll('.chat-item').forEach(el => el.classList.remove('active-chat'));
        document.getElementById('conv-' + id).classList.add('active-chat');

        loadMessages();
        
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(loadMessages, 5000);
    }

    async function loadMessages() {
        if (!currentConvId) return;
        try {
            const response = await fetch(`/admin/chat/${currentConvId}/messages`);
            const messages = await response.json();
            
            const container = document.getElementById('messages-container');
            container.innerHTML = messages.map(msg => `
                <div class="message ${msg.sender_id != {{ auth()->id() ?? 0 }} ? 'message-received' : 'message-sent'}">
                    ${msg.content}
                    <div style="font-size: 10px; opacity: 0.7; text-align: right;">
                        ${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                    </div>
                </div>
            `).join('');
            
            container.scrollTop = container.scrollHeight;
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    async function sendMessage(e) {
        e.preventDefault();
        const input = document.getElementById('chat-input');
        const content = input.value.trim();
        if (!content || !currentConvId) return;

        try {
            const response = await fetch('/admin/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    conversation_id: currentConvId,
                    content: content
                })
            });

            if (response.ok) {
                input.value = '';
                loadMessages();
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    async function deleteConversation(e, id) {
        e.stopPropagation(); // Ngăn sự kiện click lan ra ngoài (không chọn hội thoại)
        if (!confirm('Bạn có chắc chắn muốn xóa cuộc hội thoại này? Tất cả tin nhắn sẽ bị mất.')) return;

        try {
            const response = await fetch(`/admin/chat/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                // Xóa element khỏi danh sách
                document.getElementById('conv-' + id).remove();
                
                // Nếu đang chọn hội thoại này thì reset khung chat
                if (currentConvId === id) {
                    currentConvId = null;
                    document.getElementById('messages-container').innerHTML = '<div class="text-center text-muted mt-5">Vui lòng chọn một cuộc trò chuyện</div>';
                    document.getElementById('chat-input').disabled = true;
                    document.getElementById('send-btn').disabled = true;
                    document.getElementById('selected-user-name').innerText = 'Chọn khách hàng để hỗ trợ';
                    if (pollInterval) clearInterval(pollInterval);
                }
            } else {
                alert('Có lỗi xảy ra khi xóa hội thoại');
            }
        } catch (error) {
            console.error('Error deleting conversation:', error);
            alert('Lỗi kết nối');
        }
    }
</script>
@endsection
@endsection
