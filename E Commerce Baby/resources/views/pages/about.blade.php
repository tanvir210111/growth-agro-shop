@extends('layouts.app')

@section('title', 'About Us | Baby Fashion BD')

@section('content')
<div class="container" style="padding: 3rem 1rem 5rem; max-width: 860px;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 3rem; box-shadow: var(--shadow-sm);">
        <span class="hero-tag" style="background: var(--color-primary-light); color: var(--color-primary);">About Our Brand</span>
        <h1 style="font-family: var(--font-heading); font-size: 2.2rem; margin: 0.5rem 0 1.5rem; color: var(--color-text-main);">
            Welcome to Baby Fashion BD
        </h1>

        <div style="font-size: 1rem; color: var(--color-text-muted); line-height: 1.8; display: flex; flex-direction: column; gap: 1.2rem;">
            <p>
                At <strong>Baby Fashion BD</strong>, we believe every child deserves skin-friendly, comfortable, and charming fashion. Founded in Dhaka, Bangladesh, our mission is to deliver high-quality baby garments made strictly from 100% premium combed organic cotton, ensuring breathability, ultra-soft touch, and zero irritation.
            </p>

            <h3 style="font-family: var(--font-heading); color: var(--color-text-main); margin-top: 1rem;">Why Parents Across Bangladesh Trust Us</h3>
            <ul style="padding-left: 1.5rem; display: flex; flex-direction: column; gap: 0.6rem;">
                <li><strong>Certified Safe Fabrics:</strong> Tested with hypoallergenic, non-toxic organic dyes.</li>
                <li><strong>Snug & Elastic Fits:</strong> Scratch-free labels, soft waistbands, and smooth neck snap buttons.</li>
                <li><strong>Cash on Delivery (COD):</strong> Order with zero risk; pay upon delivery anywhere in Bangladesh.</li>
                <li><strong>7-Day Free Exchange:</strong> Quick, hassle-free size replacement support.</li>
            </ul>

            <div style="margin-top: 1.5rem; background: var(--color-bg-warm); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--color-primary);">
                <strong>Our Flagship Office:</strong><br>
                Kuwaiti Moshjid Road, Dhali Bari, Bashundhara R/A 1229, Dhaka, Bangladesh<br>
                <strong>Helpline:</strong> +880 1560-016740 | <strong>Email:</strong> support@babyfashionbd.com
            </div>
        </div>
    </div>
</div>
@endsection
