@extends('layouts.front')
@section('contents')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
{{-- jQuery নিশ্চিত করা --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    
    /* সাইডবার ডিজাইন */
    .sidebar-card { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #edf2f7; }
    .user-profile-box { text-align: center; padding: 35px 20px; background: #fff; border-bottom: 1px solid #f1f5f9; }
    .user-avatar { width: 90px; height: 90px; border-radius: 50%; border: 4px solid #f8fafc; box-shadow: 0 5px 15px rgba(0,0,0,0.1); object-fit: cover; background-color: #eee; }
    .menu-link { display: flex; align-items: center; padding: 14px 20px; color: #64748b; text-decoration: none; border-radius: 12px; margin: 5px 15px; transition: 0.3s; font-weight: 500; }
    .menu-link i { width: 30px; font-size: 18px; }
    .menu-link:hover, .menu-link.active { background: #f1f5f9; color: #4e73df; }

    /* চ্যাট বক্স ডিজাইন */
    .chat-box { height: 450px; overflow-y: auto; padding: 20px; background: #f8fafc; border-radius: 15px; border: 1px solid #e2e8f0; scroll-behavior: smooth; }
    .bubble { max-width: 80%; padding: 12px 18px; border-radius: 15px; margin-bottom: 15px; font-size: 14px; position: relative; line-height: 1.5; }
    
    /* ইউজারের মেসেজ (ডান দিকে) */
    .bubble-user { background: #4e73df; color: white; margin-left: auto; border-bottom-right-radius: 2px; box-shadow: 0 4px 6px rgba(78, 115, 223, 0.1); }
    
    /* অ্যাডমিনের মেসেজ (বাম দিকে) */
    .bubble-admin { background: white; color: #334155; border: 1px solid #e2e8f0; border-bottom-left-radius: 2px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    
    .msg-meta { font-size: 10px; opacity: 0.8; display: block; margin-top: 5px; }

    /* সাউন্ড বাটন ডিজাইন */
    .sound-control {
        display: flex; align-items: center; gap: 8px; 
        background: #f1f5f9; color: #4e73df; 
        padding: 5px 15px; border-radius: 20px; 
        font-weight: 600; font-size: 12px; cursor: pointer;
        border: 1px solid #e2e8f0; transition: 0.3s;
    }
    .sound-control:hover { background: #e0e7ff; }
    .sound-control.muted { background: #fee2e2; color: #ef4444; border-color: #fecaca; }
</style>

<div class="section-padding py-5">
    <div class="container">
        <div class="row g-4">
            {{-- সাইডবার --}}
            <div class="col-lg-3">
                <div class="sidebar-card">
                    <div class="user-profile-box">
                        <img src="{{ Auth::user()->photo ? asset('assets/images/users/'.Auth::user()->photo) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png' }}" class="user-avatar mb-3">
                        <h6 class="fw-bold text-dark mb-0">{{ Auth::user()->name }}</h6>
                    </div>
                    <div class="py-3">
                        <a href="{{ route('user.dashboard') }}" class="menu-link"><i class="fa-solid fa-house"></i> ড্যাশবোর্ড</a>
                        			
									
															<a href="{{ route('user.fraud.index') }}" class="menu-link {{ request()->routeIs('user.fraud.*') ? 'active' : '' }}">
    <i class="fa-solid fa-shield-halved"></i> Fraud Checker
</a>
<style>
    .submenu-container {
    background: #fdfdfd; /* সাব-মেনুর জন্য হালকা আলাদা ব্যাকগ্রাউন্ড */
    border-radius: 12px;
    margin: 2px 15px;
}

.ps-5 {
    padding-left: 45px !important; /* সাব-মেনু আইটেমগুলোকে একটু ভেতরে সরানোর জন্য */
    font-size: 14px !important;    /* সাব-মেনু ফন্ট একটু ছোট */
}

.menu-link i.fa-chevron-down {
    transition: transform 0.3s ease;
}

.menu-link.open i.fa-chevron-down {
    transform: rotate(180deg);
}
</style>
<script>
    function toggleSubMenu(event, menuId) {
        event.preventDefault();
        const menu = document.getElementById(menuId);
        const link = event.currentTarget;
        
        if (menu.style.display === "none") {
            menu.style.display = "block";
            link.classList.add('open');
        } else {
            menu.style.display = "none";
            link.classList.remove('open');
        }
    }
</script>

                        <a href="{{ route('user.support') }}" class="menu-link active"><i class="fa-solid fa-headset"></i> সাপোর্ট টিকেট</a>
                        <a href="{{ route('user.profile') }}" class="menu-link"><i class="fa-solid fa-user-gear"></i> প্রোফাইল সেটিংস</a>
                        <a href="{{ route('user.logout') }}" class="menu-link text-danger mt-4"><i class="fa-solid fa-right-from-bracket"></i> লগআউট</a>
                    </div>
                </div>
            </div>

            {{-- চ্যাট এরিয়া --}}
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                        <div>
                            <h6 class="m-0 fw-bold text-dark">{{ $ticket->subject }}</h6>
                            <small class="text-muted">ID: #{{ $ticket->id }}</small>
                        </div>
                        
                        <div class="d-flex align-items-center gap-3">
                            {{-- [NEW] সাউন্ড বাটন --}}
                            <div class="sound-control" id="soundToggleBtn" title="Click to mute/unmute">
                                <i class="fas fa-volume-up" id="soundIcon"></i> 
                                <span id="soundText">Sound ON</span>
                            </div>

                            <a href="{{ route('user.support') }}" class="btn btn-sm btn-light border px-3 rounded-pill">ফিরে যান</a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="chat-box mb-4" id="chatBox">
                            {{-- মূল টিকেট মেসেজ --}}
                            <div class="bubble bubble-user">
                                <strong>আপনি:</strong><br>
                                {{ $ticket->message }}
                                <span class="msg-meta text-end">
                                    {{ \Carbon\Carbon::parse($ticket->created_at)->diffForHumans() }}
                                </span>
                            </div>

                            {{-- আগের রিপ্লাইসমূহ --}}
                            @foreach($replies as $reply)
                                <div class="bubble {{ $reply->user_id ? 'bubble-user' : 'bubble-admin' }}">
                                    <strong>{{ $reply->user_id ? 'আপনি' : 'অ্যাডমিন সাপোর্ট' }}:</strong><br>
                                    {{ $reply->message }}
                                    <span class="msg-meta">
                                        {{ \Carbon\Carbon::parse($reply->created_at)->diffForHumans() }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        {{-- রিপ্লাই ফর্ম --}}
                        @if($ticket->status != 'closed')
                        <form action="{{ route('user.support.reply', $ticket->id) }}" method="POST" id="replyForm">
                            @csrf
                            <div class="mb-3">
                                {{-- [IMPORTANT] এখানে ক্লিক করলে সাউন্ড থামবে --}}
                                <textarea name="message" id="replyMessage" class="form-control shadow-sm" rows="3" required placeholder="আপনার রিপ্লাই এখানে লিখুন..." style="border-radius: 12px;"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" id="sendBtn" class="btn btn-primary px-5 rounded-pill fw-bold shadow">
                                    পাঠান <i class="fa-solid fa-paper-plane ms-1"></i>
                                </button>
                            </div>
                        </form>
                        @else
                        <div class="alert alert-secondary text-center rounded-pill small">
                            <i class="fa-solid fa-lock me-1"></i> এই টিকেটটি বর্তমানে বন্ধ (Closed) আছে।
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        
        // =======================================================
        // ১. অ্যাডভান্সড সাউন্ড সিস্টেম
        // =======================================================
        // অনলাইন রিংটোন (নির্ভরযোগ্য লিংক)
        var notificationSound = new Audio("{{ asset('assets/images/ring.mp3') }}");
        
        notificationSound.loop = true; // [গুরুত্বপূর্ণ] বাজতেই থাকবে
        notificationSound.volume = 1.0; 

        var isSoundEnabled = true;

        // সাউন্ড টগল বাটন
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
                stopRingtone(); // বাটন অফ করলেই সাউন্ড থামবে
            }
        });

        // [গুরুত্বপূর্ণ] সাউন্ড থামানোর ফাংশন
        function stopRingtone() {
            if(!notificationSound.paused) {
                notificationSound.pause();
                notificationSound.currentTime = 0;
            }
        }

        // মেসেজ বক্সে ক্লিক বা টাইপ করলেই সাউন্ড থামবে
        $('#replyMessage').on('focus click keydown', function() {
            stopRingtone();
        });


        // =======================================================
        // ২. সাধারণ ভেরিয়েবল
        // =======================================================
        var chatBox = document.getElementById("chatBox");
        if(chatBox) chatBox.scrollTop = chatBox.scrollHeight;

        var lastReplyId = "{{ $replies->count() > 0 ? $replies->last()->id : 0 }}"; 
        var baseCheckUrl = "{{ route('user.support.check_new', $ticket->id) }}";

// =======================================================
        // ৩. মেসেজ সেন্ড ফাংশন (সেন্ড করার সাথে সাথে দেখাবে)
        // =======================================================
        $('#replyForm').on('submit', function(e) {
            e.preventDefault(); 
            
            stopRingtone(); 

            var message = $('#replyMessage').val();
            if(message.trim() == '') return;

            var formData = $(this).serialize();
            $('#replyMessage').val(''); 
            
            var btnOriginal = $('#sendBtn').html();
            $('#sendBtn').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

            // ইনস্ট্যান্ট শো
            var myMessageHtml = `
                <div class="bubble bubble-user">
                    <strong>আপনি:</strong><br>
                    ${message}
                    <span class="msg-meta text-end">এইমাত্র</span>
                </div>
            `;
            $('#chatBox').append(myMessageHtml);
            chatBox.scrollTop = chatBox.scrollHeight;

            // সার্ভারে পাঠানো
            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: formData,
                success: function(res) {
                    $('#sendBtn').prop('disabled', false).html(btnOriginal);
                    
                    // [ফিক্স] এই লাইনটি আপনার কোডে ভুল ছিল (res.last_id লেখা ছিল)
                    // সঠিক হবে: res.data.id
                    if(res.data && res.data.id) {
                        lastReplyId = res.data.id; 
                    }
                },
                error: function(xhr) {
                    $('#sendBtn').prop('disabled', false).html(btnOriginal);
                    alert('মেসেজ যায়নি। ইন্টারনেট চেক করুন।');
                }
            });
        });

        // =======================================================
        // ৪. অটোমেটিক মেসেজ চেক (লজিক আপডেট)
        // =======================================================
        function checkNewMessages() {
            var finalUrl = baseCheckUrl + "?t=" + new Date().getTime();

            $.ajax({
                url: finalUrl,
                type: "GET",
                data: { last_id: lastReplyId },
                success: function(res) {
                    if(res.has_new) {
                        $('#chatBox').append(res.html); 
                        chatBox.scrollTop = chatBox.scrollHeight;
                        lastReplyId = res.last_id; 
                        
                        // যদি অ্যাডমিনের মেসেজ হয় (bubble-admin ক্লাস থাকে) এবং সাউন্ড অন থাকে
                        if(res.html.includes('bubble-admin') && isSoundEnabled) {
                            var promise = notificationSound.play();
                            if (promise !== undefined) {
                                promise.catch(error => {
                                    console.log('Autoplay prevented. Click page once.');
                                });
                            }
                        }
                    }
                }
            });
        }

        setInterval(checkNewMessages, 3000);

        // =======================================================
        // ৫. ব্রাউজার পারমিশন (সাউন্ড রেডি করা)
        // =======================================================
        $(document).one('click keydown touchstart', function() {
            notificationSound.play().then(() => {
                notificationSound.pause();
                notificationSound.currentTime = 0;
            }).catch(error => {});
        });
    });
</script>
@endsection
