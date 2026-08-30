<!-- Floating WhatsApp Widget -->
<div class="whatsapp-floating-container" id="whatsappContainer">
    <!-- WhatsApp Chat Popup Box -->
    <div class="whatsapp-popup" id="whatsappPopup">
        <div class="whatsapp-popup-header">
            <div class="whatsapp-avatar-wrap">
                <img src="{{ asset('images/logo.png') }}" alt="Baby Fashion BD Support" class="whatsapp-avatar">
                <span class="whatsapp-online-dot"></span>
            </div>
            <div class="whatsapp-header-info">
                <div class="whatsapp-title">Baby Fashion BD</div>
                <div class="whatsapp-status">Typically replies within minutes</div>
            </div>
            <button type="button" class="whatsapp-popup-close" id="whatsappCloseBtn" aria-label="Close Chat">&times;</button>
        </div>

        <div class="whatsapp-popup-body">
            <div class="whatsapp-message-bubble">
                <p>Hello! 👋 Welcome to Baby Fashion BD.</p>
                <p style="margin-top: 5px;">How can we assist you today? Let us know if you need help with sizing, orders, or delivery!</p>
                <span class="whatsapp-msg-time">{{ date('h:i A') }}</span>
            </div>
        </div>

        <div class="whatsapp-popup-footer">
            <a href="https://wa.me/8801560016740?text=Hello%20Baby%20Fashion%20BD,%20I%20have%20an%20inquiry." target="_blank" rel="noopener noreferrer" class="whatsapp-send-btn">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19.01L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 14.99 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.53 7.34C9.36 7.34 9.09 7.4 8.87 7.65C8.65 7.89 8.02 8.48 8.02 9.69C8.02 10.9 8.9 12.07 9.02 12.23C9.15 12.4 10.74 14.85 13.18 15.91C13.76 16.16 14.21 16.31 14.57 16.42C15.15 16.61 15.68 16.58 16.1 16.52C16.57 16.45 17.54 15.93 17.75 15.34C17.95 14.75 17.95 14.25 17.89 14.15C17.83 14.05 17.67 13.99 17.43 13.87C17.19 13.75 16 13.17 15.78 13.09C15.56 13 15.4 12.96 15.24 13.2C15.08 13.45 14.61 14.01 14.47 14.17C14.33 14.34 14.19 14.36 13.95 14.24C13.71 14.12 12.93 13.86 12.01 13.04C11.29 12.4 10.8 11.61 10.66 11.37C10.52 11.13 10.65 11 10.77 10.88C10.88 10.77 11.02 10.59 11.14 10.45C11.26 10.31 11.3 10.21 11.38 10.05C11.46 9.89 11.42 9.75 11.36 9.63C11.3 9.5 10.82 8.33 10.62 7.84C10.42 7.37 10.22 7.43 10.07 7.42C9.93 7.42 9.77 7.34 9.53 7.34Z"/></svg>
                <span>Chat on WhatsApp</span>
            </a>
        </div>
    </div>

    <!-- Floating Circular Button -->
    <button type="button" class="whatsapp-floating-btn" id="whatsappToggleBtn" aria-label="Open WhatsApp Chat">
        <span class="whatsapp-btn-pulse"></span>
        <svg class="whatsapp-icon" width="34" height="34" fill="#ffffff" viewBox="0 0 24 24">
            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19.01L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 14.99 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.53 7.34C9.36 7.34 9.09 7.4 8.87 7.65C8.65 7.89 8.02 8.48 8.02 9.69C8.02 10.9 8.9 12.07 9.02 12.23C9.15 12.4 10.74 14.85 13.18 15.91C13.76 16.16 14.21 16.31 14.57 16.42C15.15 16.61 15.68 16.58 16.1 16.52C16.57 16.45 17.54 15.93 17.75 15.34C17.95 14.75 17.95 14.25 17.89 14.15C17.83 14.05 17.67 13.99 17.43 13.87C17.19 13.75 16 13.17 15.78 13.09C15.56 13 15.4 12.96 15.24 13.2C15.08 13.45 14.61 14.01 14.47 14.17C14.33 14.34 14.19 14.36 13.95 14.24C13.71 14.12 12.93 13.86 12.01 13.04C11.29 12.4 10.8 11.61 10.66 11.37C10.52 11.13 10.65 11 10.77 10.88C10.88 10.77 11.02 10.59 11.14 10.45C11.26 10.31 11.3 10.21 11.38 10.05C11.46 9.89 11.42 9.75 11.36 9.63C11.3 9.5 10.82 8.33 10.62 7.84C10.42 7.37 10.22 7.43 10.07 7.42C9.93 7.42 9.77 7.34 9.53 7.34Z"/>
        </svg>
        <span class="whatsapp-tooltip">Chat with us</span>
    </button>
</div>

<style>
/* WhatsApp Floating Widget Styles */
.whatsapp-floating-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 99999;
    font-family: 'Poppins', sans-serif;
}

.whatsapp-floating-btn {
    position: relative;
    width: 60px;
    height: 60px;
    background: #25D366;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(37, 211, 102, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.whatsapp-floating-btn:hover {
    transform: scale(1.1);
}

.whatsapp-btn-pulse {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: #25D366;
    opacity: 0.7;
    animation: waPulse 2s infinite cubic-bezier(0.25, 0.46, 0.45, 0.94);
    z-index: -1;
}

@keyframes waPulse {
    0% { transform: scale(1); opacity: 0.7; }
    70% { transform: scale(1.4); opacity: 0; }
    100% { transform: scale(1.4); opacity: 0; }
}

.whatsapp-tooltip {
    position: absolute;
    right: 70px;
    background: #1f2937;
    color: #ffffff;
    font-size: 13px;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 6px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transform: translateX(10px);
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.whatsapp-floating-btn:hover .whatsapp-tooltip {
    opacity: 1;
    transform: translateX(0);
}

/* Popup Box */
.whatsapp-popup {
    position: absolute;
    bottom: 75px;
    right: 0;
    width: 320px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.18);
    overflow: hidden;
    display: none;
    flex-direction: column;
    animation: waPopupSlide 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    transform-origin: bottom right;
}

.whatsapp-popup.active {
    display: flex;
}

@keyframes waPopupSlide {
    0% { opacity: 0; transform: scale(0.85) translateY(20px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}

.whatsapp-popup-header {
    background: #075E54;
    color: #ffffff;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

.whatsapp-avatar-wrap {
    position: relative;
    width: 44px;
    height: 44px;
    flex-shrink: 0;
}

.whatsapp-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #ffffff;
    padding: 3px;
    object-fit: contain;
}

.whatsapp-online-dot {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 12px;
    height: 12px;
    background: #25D366;
    border: 2px solid #075E54;
    border-radius: 50%;
}

.whatsapp-header-info {
    flex: 1;
}

.whatsapp-title {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.2;
}

.whatsapp-status {
    font-size: 11px;
    opacity: 0.85;
    margin-top: 2px;
}

.whatsapp-popup-close {
    background: none;
    border: none;
    color: #ffffff;
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.2s;
}

.whatsapp-popup-close:hover {
    opacity: 1;
}

.whatsapp-popup-body {
    background: #ECE5DD;
    background-image: radial-gradient(#d1c7bc 1px, transparent 1px);
    background-size: 16px 16px;
    padding: 18px 14px;
    min-height: 120px;
    display: flex;
    flex-direction: column;
}

.whatsapp-message-bubble {
    background: #ffffff;
    padding: 12px 14px;
    border-radius: 12px 12px 12px 2px;
    font-size: 13px;
    line-height: 1.4;
    color: #111827;
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    position: relative;
    max-width: 90%;
}

.whatsapp-msg-time {
    display: block;
    text-align: right;
    font-size: 10px;
    color: #9ca3af;
    margin-top: 6px;
}

.whatsapp-popup-footer {
    padding: 12px 14px;
    background: #ffffff;
    border-top: 1px solid #f3f4f6;
}

.whatsapp-send-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    background: #25D366;
    color: #ffffff;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    padding: 11px;
    border-radius: 25px;
    transition: background 0.2s, transform 0.15s;
    box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);
}

.whatsapp-send-btn:hover {
    background: #20BA56;
    transform: translateY(-1px);
}

@media (max-width: 576px) {
    .whatsapp-floating-container {
        bottom: 20px;
        right: 20px;
    }
    .whatsapp-popup {
        width: 290px;
        bottom: 70px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('whatsappToggleBtn');
    const popup = document.getElementById('whatsappPopup');
    const closeBtn = document.getElementById('whatsappCloseBtn');

    if (toggleBtn && popup) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            popup.classList.toggle('active');
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                popup.classList.remove('active');
            });
        }

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!popup.contains(e.target) && !toggleBtn.contains(e.target)) {
                popup.classList.remove('active');
            }
        });
    }
});
</script>
