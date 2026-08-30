@extends('layouts.admin')

@section('styles')
<style>
    /* প্রিমিয়াম ব্যাকগ্রাউন্ড ও কন্টেইনার */
    .content-wrapper { background: #f8fafc; min-height: 100vh; padding: 30px 15px; }
    
    /* চ্যাট বক্স ডিজাইন - মডার্ন লুক */
    .chat-box { 
        height: 500px; 
        overflow-y: auto; 
        padding: 20px; 
        background: #ffffff; 
        border-radius: 12px; 
        border: 1px solid #edf2f7;
        scrollbar-width: thin;
        scroll-behavior: smooth;
    }
    .chat-box::-webkit-scrollbar { width: 6px; }
    .chat-box::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }

    .message-row { margin-bottom: 25px; display: flex; align-items: flex-end; gap: 10px; }
    .message-row.admin-row { flex-direction: row-reverse; }

    /* প্রোফাইল অবতার */
    .chat-avatar {
        width: 35px; height: 35px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; font-size: 14px; color: white; flex-shrink: 0;
    }
    .avatar-customer { background: #6366f1; }
    .avatar-admin { background: #10b981; }

    /* বাবল ডিজাইন */
    .bubble { 
        max-width: 70%; padding: 12px 16px; font-size: 14px; 
        line-height: 1.6; position: relative; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .bubble-customer { 
        background: #f1f5f9; color: #334155; 
        border-radius: 18px 18px 18px 4px; 
    }
    .bubble-admin { 
        background: #4f46e5; color: #ffffff; 
        border-radius: 18px 18px 4px 18px; 
    }

    .msg-meta { font-size: 10px; margin-top: 6px; opacity: 0.7; font-weight: 500; }
    .bubble-admin .msg-meta { text-align: right; color: #e0e7ff; }

    /* ইনপুট এরিয়া */
    .reply-section { background: #fff; border-radius: 15px; padding: 20px; border: 1px solid #edf2f7; }
    .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

    /* স্ট্যাটাস ব্যাজ */
    .status-select { font-weight: 600; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer; }

    /* সাউন্ড টগল বাটন ডিজাইন */
    .sound-control {
        display: flex; align-items: center; gap: 8px; 
        background: #eef2ff; color: #4f46e5; 
        padding: 6px 15px; border-radius: 20px; 
        font-weight: 600; font-size: 13px; cursor: pointer;
        border: 1px solid #c7d2fe; transition: 0.3s;
    }
    .sound-control:hover { background: #e0e7ff; }
    .sound-control.muted { background: #fee2e2; color: #ef4444; border-color: #fecaca; }
</style>
@endsection

@section('content')
<div class="container-fluid content-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="d-md-flex justify-content-between align-items-center">
                        <div class="mb-3 mb-md-0">
                            <h4 class="font-weight-bold text-dark mb-2">{{ $ticket->subject }}</h4>
                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                <span class="badge badge-primary px-3 py-2">ID: #{{ $ticket->id }}</span>
                                <span class="text-muted small"><i class="far fa-user mr-1"></i> Client: <b>{{ $ticket->user_name }}</b></span>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center gap-3">
                            {{-- [NEW] সাউন্ড কন্ট্রোল বাটন --}}
                            <div class="sound-control" id="soundToggleBtn" title="Click to mute/unmute">
                                <i class="fas fa-volume-up" id="soundIcon"></i> 
                                <span id="soundText">Sound ON</span>
                            </div>

                            <div class="d-flex flex-column align-items-end">
                                <select class="form-control status-select mb-2 form-control-sm" id="updateStatus">
                                    <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>🕒 Pending</option>
                                    <option value="replied" {{ $ticket->status == 'replied' ? 'selected' : '' }}>✅ Replied</option>
                                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>🔒 Closed</option>
                                </select>
                                <a href="{{ route('admin.tickets.index') }}" class="btn btn-light btn-sm px-3 border"> <i class="fas fa-arrow-left"></i> Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-0">
                    <div class="chat-box" id="chatContainer">
                        
                        {{-- মূল টিকেট মেসেজ --}}
                        <div class="message-row">
                            <div class="chat-avatar avatar-customer" title="{{ $ticket->user_name }}">
                                {{ substr($ticket->user_name, 0, 1) }}
                            </div>
                            <div class="bubble bubble-customer">
                                <strong class="small d-block mb-1">Issue Description</strong>
                                {{ $ticket->message }}
                                <div class="msg-meta">{{ \Carbon\Carbon::parse($ticket->created_at)->format('h:i A') }}</div>
                            </div>
                        </div>

                        {{-- রিপ্লাইসমূহ --}}
                        @foreach($replies as $reply)
                        @php $isAdmin = !empty($reply->admin_id); @endphp
                        <div class="message-row {{ $isAdmin ? 'admin-row' : '' }}">
                            <div class="chat-avatar {{ $isAdmin ? 'avatar-admin' : 'avatar-customer' }}">
                                {{ $isAdmin ? 'A' : substr($reply->customer_name ?? 'C', 0, 1) }}
                            </div>
                            <div class="bubble {{ $isAdmin ? 'bubble-admin' : 'bubble-customer' }}">
                                {{ $reply->message }}
                                <div class="msg-meta">{{ \Carbon\Carbon::parse($reply->created_at)->diffForHumans() }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="reply-section p-4">
                        <form id="replyForm">
                            @csrf
                            <div class="form-group position-relative">
                                {{-- [IMPORTANT] ক্লিক করলে সাউন্ড থামবে --}}
                                <textarea name="message" id="replyMessage" class="form-control p-3" rows="3" 
                                    placeholder="Write your professional response here..." required style="border-radius: 12px;"></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-muted small"><i class="fas fa-info-circle"></i> Customer will be notified via email.</span>
                                <button type="submit" class="btn btn-primary px-5 py-2 font-weight-bold shadow-sm" id="sendBtn" style="border-radius: 8px;">
                                    Send Reply <i class="fas fa-paper-plane ml-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- jQuery & SweetAlert --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        
        // ===========================================
        // ১. অ্যাডভান্সড সাউন্ড সিস্টেম
        // ===========================================
        var notificationSound = new Audio("{{ asset('assets/images/ring.mp3') }}");
        notificationSound.loop = true; // [গুরুত্বপূর্ণ] সাউন্ড লুপ হবে (বাজতেই থাকবে)
        notificationSound.volume = 1.0;

        var isSoundEnabled = true;

        // সাউন্ড টগল বাটন ক্লিক ইভেন্ট
        $('#soundToggleBtn').click(function() {
            isSoundEnabled = !isSoundEnabled;
            if(isSoundEnabled) {
                $(this).removeClass('muted');
                $('#soundIcon').removeClass('fa-volume-mute').addClass('fa-volume-up');
                $('#soundText').text('Sound ON');
            } else {
                $(this).addClass('muted');
                $('#soundIcon').removeClass('fa-volume-up').addClass('fa-volume-mute');
                $('#soundText').text('Sound OFF');
                // সাথে সাথে সাউন্ড বন্ধ করা
                notificationSound.pause();
                notificationSound.currentTime = 0;
            }
        });

        // [গুরুত্বপূর্ণ] মেসেজ বক্সে ক্লিক বা টাইপ করলে সাউন্ড থামবে
        $('#replyMessage').on('focus click keydown', function() {
            if(!notificationSound.paused) {
                notificationSound.pause();
                notificationSound.currentTime = 0;
            }
        });


        // ===========================================
        // ২. সাধারণ ভেরিয়েবল ও স্ক্রল
        // ===========================================
        var container = document.getElementById("chatContainer");
        if(container) container.scrollTop = container.scrollHeight;

        var lastReplyId = "{{ $replies->count() > 0 ? $replies->last()->id : 0 }}";
        var baseCheckUrl = "{{ route('admin.tickets.check_new', $ticket->id) }}";

        // ===========================================
        // ৩. স্ট্যাটাস আপডেট
        // ===========================================
        $('#updateStatus').on('change', function() {
            let status = $(this).val();
            $.ajax({
                url: "{{ route('admin.tickets.status', $ticket->id) }}",
                type: "POST",
                data: { _token: "{{ csrf_token() }}", status: status },
                success: function(res) {
                    Swal.fire({ icon: 'success', title: 'Status Updated', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                }
            });
        });

        // ===========================================
        // ৪. রিপ্লাই পাঠানো (AJAX)
        // ===========================================
        $('#replyForm').on('submit', function(e) {
            e.preventDefault();
            
            // পাঠানোর আগে সাউন্ড নিশ্চিতভাবে বন্ধ করা
            notificationSound.pause();
            notificationSound.currentTime = 0;

            var message = $('#replyMessage').val();
            if(message.trim() == '') return;

            var formData = $(this).serialize();
            $('#replyMessage').val(''); // বক্স খালি করা
            
            var btnOriginal = $('#sendBtn').html();
            $('#sendBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

            // ইনস্ট্যান্ট শো (নিজ মেসেজ)
            var myMessageHtml = `
                <div class="message-row admin-row">
                    <div class="chat-avatar avatar-admin">A</div>
                    <div class="bubble bubble-admin">
                        ${message}
                        <div class="msg-meta">Just now</div>
                    </div>
                </div>
            `;
            $('#chatContainer').append(myMessageHtml);
            container.scrollTop = container.scrollHeight;

            $.ajax({
                url: "{{ route('admin.tickets.reply', $ticket->id) }}",
                type: "POST",
                data: formData,
                success: function(res) {
                    $('#sendBtn').prop('disabled', false).html(btnOriginal);
                    if(res.last_id) lastReplyId = res.last_id;
                },
                error: function() {
                    $('#sendBtn').prop('disabled', false).html(btnOriginal);
                    Swal.fire('Error!', 'Message sending failed.', 'error');
                }
            });
        });

        // ===========================================
        // ৫. অটোমেটিক চেক (লুপ সাউন্ড সহ)
        // ===========================================
        function checkNewMessages() {
            var finalUrl = baseCheckUrl + "?t=" + new Date().getTime();

            $.ajax({
                url: finalUrl,
                type: "GET",
                data: { last_id: lastReplyId },
                success: function(res) {
                    if(res.has_new) {
                        $('#chatContainer').append(res.html);
                        container.scrollTop = container.scrollHeight;
                        lastReplyId = res.last_id;

                        // যদি কাস্টমার মেসেজ হয় এবং সাউন্ড অন থাকে
                        if(res.html.includes('bubble-customer') && isSoundEnabled) {
                            notificationSound.play().catch(e => console.log('Autoplay blocked'));
                        }
                    }
                }
            });
        }

        setInterval(checkNewMessages, 3000);

        // প্রথম ক্লিকে সাউন্ড পারমিশন নিয়ে রাখা
        $(document).one('click', function() {
            notificationSound.play().then(() => {
                notificationSound.pause();
                notificationSound.currentTime = 0;
            }).catch(e => {});
        });
    });
</script>
@endsection