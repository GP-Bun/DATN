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
                        <button class="btn btn-sm text-danger border-0 ms-2"
                            onclick="deleteConversation(event, {{ $conv->id }})">
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

                <div id="messages-container" class="flex-grow-1 p-3 overflow-auto d-flex flex-column gap-2"
                    style="background: #f1f5f9;">
                    <!-- Tin nhắn sẽ được load ở đây -->
                    <div class="text-center text-muted mt-5">Vui lòng chọn một cuộc trò chuyện</div>
                </div>

                <div class="p-3 border-top bg-light">
                    <form id="chat-form" onsubmit="sendMessage(event)" class="d-flex gap-2 align-items-center">

                        <!-- Nút chọn file -->
                        <button type="button" class="btn btn-light" id="attach-btn"
                            onclick="document.getElementById('chat-file').click()" disabled>
                            📎
                        </button>

                        <!-- Input file (ẩn) -->
                        <input type="file" id="chat-file" hidden accept="image/*,.pdf,.doc,.docx,.zip">

                        <!-- Input text -->
                        <input type="text" id="chat-input" class="form-control" placeholder="Nhập tin nhắn..." disabled>

                        <!-- Nút gửi -->
                        <button type="submit" id="send-btn" class="btn btn-primary" disabled>
                            Gửi
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <style>
        .chat-item:hover {
            background-color: #f8fafc;
            cursor: pointer;
            transition: background 0.2s;
        }

        .active-chat {
            background-color: #eff6ff !important;
            border-left: 4px solid #667eea;
        }

        .message-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message {
            padding: 8px 12px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.5;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);

            max-width: 55%;
            white-space: pre-wrap;
            /* ✅ giữ xuống dòng đúng */
            word-break: break-word;
            /* ✅ chỉ bẻ khi quá dài */
            overflow-wrap: anywhere;
            /* ✅ KHÔNG bẻ từng chữ */
        }


        .message-sent {
            background: #667eea;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-bot {
            background: #10b981;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-received {
            background-color: #f1f5f9;
            color: #1e293b;
            border-bottom-left-radius: 4px;
        }

        .message-wrapper {
            display: flex;
            flex-direction: column;
        }

        .message-label {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .col-md-8 {
            position: sticky;
            top: 0;
            height: calc(100vh - 150px);
            overflow-y: auto;
        }



        #messages-container::-webkit-scrollbar {
            width: 6px;
        }

        #messages-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        #messages-container {
            scroll-behavior: smooth;
        }
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
                document.getElementById('attach-btn').disabled = false;


                // ✅ mark as read
                fetch(`/admin/chat/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
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

                    const activeConv = {!! json_encode($conversations) !!}.find(c => c.id == currentConvId);
                    const customerId = activeConv ? activeConv.user_id : null;

                    const container = document.getElementById('messages-container');

                    // ✅ kiểm tra trước khi render
                    const wasAtBottom = isAtBottom(container);

                    container.innerHTML = messages.map(msg => {
                        const isFromStore = msg.is_bot || (customerId && msg.sender_id != customerId);
                        const time = new Date(msg.created_at).toLocaleTimeString('vi-VN', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        let label = isFromStore ? (msg.is_bot ? 'AI Assistant' : 'Bạn') : 'Khách';
                        let bubbleClass = isFromStore ?
                            (msg.is_bot ? 'message-bot' : 'message-sent') :
                            'message-received';

                        return `
                <div class="d-flex ${isFromStore ? 'justify-content-end' : 'justify-content-start'} mb-3">
                    <div class="message-wrapper ${isFromStore ? 'align-items-end' : 'align-items-start'}" style="max-width: 85%;">
                        <div class="message ${bubbleClass}">
                            ${msg.content}
                        </div>
                        <div class="message-label">
                            ${label} | ${time}
                        </div>
                    </div>
                </div>
            `;
                    }).join('');

                    // ✅ CHỈ cuộn nếu đang ở đáy
                    if (wasAtBottom) {
                        container.scrollTop = container.scrollHeight;
                    }

                } catch (error) {
                    console.error('Error loading messages:', error);
                }
            }


            async function sendMessage(e) {
                e.preventDefault();

                if (!currentConvId) return;

                const input = document.getElementById('chat-input');
                const fileInput = document.getElementById('chat-file');

                const content = input.value.trim();
                const file = fileInput.files[0];

                // ❗ Phải có text hoặc file
                if (!content && !file) return;

                const formData = new FormData();
                formData.append('conversation_id', currentConvId);

                if (content) {
                    formData.append('content', content);
                }

                if (file) {
                    formData.append('file', file);
                }

                try {
                    const response = await fetch('/admin/chat/send', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: formData
                    });

                    if (!response.ok) {
                        alert('Gửi tin nhắn thất bại');
                        return;
                    }

                    // reset input
                    input.value = '';
                    fileInput.value = '';

                    loadMessages();

                } catch (error) {
                    console.error('Error sending message:', error);
                    alert('Lỗi gửi tin nhắn');
                }
            }

            async function deleteConversation(e, id) {
                e.stopPropagation(); // Ngăn sự kiện click lan ra ngoài (không chọn hội thoại)
                if (!confirm('Bạn có chắc chắn muốn xóa cuộc hội thoại này? Tất cả tin nhắn sẽ bị mất.')) return;

                try {
                    const response = await fetch(`/admin/chat/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        // Xóa element khỏi danh sách
                        document.getElementById('conv-' + id).remove();

                        // Nếu đang chọn hội thoại này thì reset khung chat
                        if (currentConvId === id) {
                            currentConvId = null;
                            document.getElementById('messages-container').innerHTML =
                                '<div class="text-center text-muted mt-5">Vui lòng chọn một cuộc trò chuyện</div>';
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

            function isAtBottom(container, threshold = 50) {
                return container.scrollTop + container.clientHeight >= container.scrollHeight - threshold;
            }


            let convPoll = null;

            async function loadConversations() {
                try {
                    const res = await fetch('/admin/chat/conversations');
                    const conversations = await res.json();

                    const sidebar = document.querySelector('.col-md-4.border-end');
                    const activeId = currentConvId;

                    let html = `
            <div class="p-3 border-bottom bg-light">
                <h5 class="m-0">Hội thoại</h5>
            </div>
        `;

                    conversations.forEach(conv => {
                        const isActive = conv.id === activeId;
                        const unread = conv.unread_count > 0;

                        html += `
                <div class="chat-item p-3 border-bottom d-flex justify-content-between align-items-center
                    ${isActive ? 'active-chat' : ''}"
                    onclick="selectConversation(${conv.id}, '${conv.user.name}')"
                    id="conv-${conv.id}">
                    
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between">
                            <strong>${conv.user.name}</strong>
                            <small class="text-muted">${conv.last_message_at ?? ''}</small>
                        </div>
                        <div class="text-truncate small ${unread ? 'fw-bold text-primary' : 'text-secondary'}">
                            ${unread ? '● Tin nhắn mới' : 'Đã đọc'}
                        </div>
                    </div>

                    ${unread ? `<span class="badge bg-danger ms-2">${conv.unread_count}</span>` : ''}
                </div>
            `;
                    });

                    sidebar.innerHTML = html;
                } catch (e) {
                    console.error('Load conversations error', e);
                }
            }

            // 🔥 Poll danh sách hội thoại 
            if (convPoll) clearInterval(convPoll);
            convPoll = setInterval(loadConversations, 1000);
        </script>
    @endsection
@endsection
