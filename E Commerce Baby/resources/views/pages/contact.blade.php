@extends('layouts.app')

@section('title', 'Contact Us | ' . \App\Models\Setting::get('site_title', 'Growth Agro'))

@section('content')
<div class="container" style="padding: 3rem 1rem 5rem;">
    <div style="text-align: center; margin-bottom: 2.5rem;">
        <span class="hero-tag" style="background: var(--color-primary-light); color: var(--color-primary);">Customer Support</span>
        <h1 style="font-family: var(--font-heading); font-size: 2.2rem; margin-top: 0.4rem;">Get in Touch With Us</h1>
        <p style="color: var(--color-text-muted);">Have questions about product details, delivery status or bulk orders? We are here to assist you!</p>
    </div>

    <div class="checkout-grid" style="max-width: 960px; margin: 0 auto;">
        <!-- Contact Information -->
        <div class="checkout-card">
            <h3>Contact Information</h3>
            <div style="display: flex; flex-direction: column; gap: 1.5rem; font-size: 0.95rem; margin-top: 1rem;">
                <div style="display: flex; gap: 1rem; align-items: flex-start;">
                    <div style="font-size: 1.5rem;">📍</div>
                    <div>
                        <strong>Store Address:</strong>
                        <p style="color: var(--color-text-muted);">{{ \App\Models\Setting::get('store_address', 'Mirpur, Dhaka-1216, Bangladesh') }}</p>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; align-items: flex-start;">
                    <div style="font-size: 1.5rem;">📞</div>
                    <div>
                        <strong>Helpline (10:00 AM - 10:00 PM):</strong>
                        <p><a href="tel:{{ preg_replace('/[^0-9+]/', '', \App\Models\Setting::get('support_phone', '01560-016740')) }}" style="color: var(--color-primary); font-weight:700;">{{ \App\Models\Setting::get('support_phone', '01560-016740') }}</a></p>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; align-items: flex-start;">
                    <div style="font-size: 1.5rem;">💬</div>
                    <div>
                        <strong>Direct WhatsApp Support:</strong>
                        <p><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', \App\Models\Setting::get('whatsapp_number', '8801560016740')) }}" target="_blank" style="color: #25D366; font-weight:700;">Chat on WhatsApp &rarr;</a></p>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; align-items: flex-start;">
                    <div style="font-size: 1.5rem;">✉️</div>
                    <div>
                        <strong>Official Email:</strong>
                        <p style="color: var(--color-text-muted);">{{ \App\Models\Setting::get('support_email', 'support@growthagro.shop') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Message Form -->
        <div class="checkout-card">
            <h3>Send a Message</h3>
            <form onsubmit="event.preventDefault(); showToast('Thank you! Your message has been received.'); this.reset();">
                <div class="form-group">
                    <label>Your Name*</label>
                    <input type="text" class="form-control" placeholder="e.g. Nusrat Jahan" required>
                </div>
                <div class="form-group">
                    <label>Phone Number*</label>
                    <input type="tel" class="form-control" placeholder="01XXXXXXXXX" required>
                </div>
                <div class="form-group">
                    <label>Message / Order Inquiry*</label>
                    <textarea class="form-control" rows="4" placeholder="How can we help you?" required></textarea>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                    Send Message &rarr;
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
