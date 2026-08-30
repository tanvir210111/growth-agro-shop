@extends('layouts.front')

@section('contents')

{{-- ১. হিরো সেকশন ও সার্চ বক্স --}}
<section class="page-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1>আপনার পছন্দের <span>ডোমেইন</span> খুঁজুন</h1>
                <p class="mb-5">আপনার ব্র্যান্ডের জন্য সেরা নামটি আজই বুক করুন</p>
                
                <div class="search-card bg-white p-2 rounded-pill shadow-lg">
                    <form id="domainForm">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="domain_name" id="domainInput" class="form-control border-0 rounded-pill ps-4" placeholder="শুধুমাত্র ডোমেইন নাম লিখুন (যেমন: mycompany)" required style="height: 55px; font-size: 18px;">
                            
                            <div class="input-group-append d-flex align-items-center bg-light rounded-pill ms-2 p-1">
                                <select name="extension" id="extSelect" class="form-control border-0 bg-transparent fw-bold" style="width: 100px; height: 45px; cursor: pointer;">
                                    @foreach($extensions as $ext)
                                        <option value="{{ $ext->extension }}">{{ $ext->extension }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 ms-2 fw-bold" id="searchBtn">
                                    <i class="fas fa-search"></i> খুঁজুন
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <small class="text-white-50 mt-2 d-block">* অনুগ্রহ করে ইনপুট বক্সে কোনো এক্সটেনশন (.com/.net) লিখবেন না।</small>

                <div id="resultArea" class="mt-4 text-start" style="display: none;"></div>
            </div>
        </div>
    </div>
</section>

{{-- ২. ডোমেইন প্রাইস লিস্ট টেবিল --}}
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h6 class="text-primary fw-bold text-uppercase">প্রাইসিং</h6>
            <h2 class="fw-bold">জনপ্রিয় ডোমেইন প্রাইস লিস্ট</h2>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center domain-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 text-start ps-5">Domain</th>
                            <th class="py-3">New Price</th>
                            <th class="py-3">Transfer</th>
                            <th class="py-3">Renewal</th>
                            <th class="py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($extensions as $ext)
                        <tr>
                            <td class="text-start ps-5 py-4">
                                <span class="fw-bold fs-5 text-dark">{{ $ext->extension }}</span>
                            </td>
                            <td><div class="fw-bold text-dark fs-6">৳{{ $ext->registration_price }}</div></td>
                            <td><div class="fw-bold text-muted fs-6">৳{{ $ext->transfer_price }}</div></td>
                            <td><div class="fw-bold text-muted fs-6">৳{{ $ext->renewal_price }}</div></td>
                            <td>
                                {{-- সরাসরি টেবিল থেকে সার্চ করার জন্য ফাংশন --}}
                                <button onclick="quickSearch('{{ $ext->extension }}')" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">Buy Now</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<style>
    .text-primary { color: #ff6600 !important; }
    .btn-primary { background-color: #ff6600 !important; border-color: #ff6600 !important; color: #fff !important; }
    .btn-outline-primary { color: #ff6600 !important; border-color: #ff6600 !important; }
    .btn-outline-primary:hover { background-color: #ff6600 !important; color: #fff !important; }
    .result-card { border-left: 5px solid #28a745; background: #fff; border-radius: 10px; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ডোমেইন ইনপুট ভ্যালিডেশন (বাংলা বা স্পেশাল ক্যারেক্টার রিমুভ)
    $('#domainInput').on('input', function() {
        var inputVal = $(this).val();

        // রেগুলার এক্সপ্রেশন: যা ইংরেজি অক্ষর (a-z), সংখ্যা (0-9) এবং হাইফেন (-) ছাড়া অন্য কিছু
        var regex = /[^a-zA-Z0-9-]/g;

        if (regex.test(inputVal)) {
            // ১. অবৈধ ক্যারেক্টার (যেমন বাংলা) সাথে সাথে মুছে ফেলা হবে
            $(this).val(inputVal.replace(regex, ''));

            // ২. ওয়ার্নিং মেসেজ দেখানো হবে
            Swal.fire({
                icon: 'warning',
                title: 'শুধুমাত্র ইংরেজি!',
                text: 'ডোমেইন নাম অবশ্যই ইংরেজিতে লিখতে হবে। বাংলা বা স্পেশাল ক্যারেক্টার গ্রহণযোগ্য নয়।',
                confirmButtonColor: '#ff6600',
                timer: 3000 // ৩ সেকেন্ড পর অটো বন্ধ হয়ে যাবে
            });
        }
    });
$(document).ready(function() {
    // কন্ট্রোলার থেকে আসা plan_id জাভাস্ক্রিপ্ট ভেরিয়েবলে ধরা
    let currentPlanId = "{{ $plan_id ?? '' }}";

    $('#domainInput').on('input', function() {
        if ($(this).val().includes('.')) {
            $(this).val($(this).val().replace(/\./g, ''));
            Swal.fire({ icon: 'warning', text: 'এক্সটেনশন লিখবেন না। ড্রপডাউন ব্যবহার করুন।', confirmButtonColor: '#ff6600' });
        }
    });

    $('#domainForm').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#searchBtn');
        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        $('#resultArea').hide();

        $.ajax({
            url: "{{ route('front.domain.check') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                let html = '';
                if (response.status === 'available') {
                    
                    // [FIXED] এখানে plan_id চেক করে লিঙ্ক তৈরি করা হচ্ছে
                    let nextStepUrl = "{{ route('front.domain.config') }}?domain=" + response.domain;
                    if(currentPlanId) {
                        nextStepUrl += "&plan_id=" + currentPlanId;
                    }

                    html = `
                        <div class="result-card p-4 shadow d-flex justify-content-between align-items-center bg-white border-start border-success border-5">
                            <div>
                                <h4 class="fw-bold mb-1 text-success"><i class="fas fa-check-circle"></i> ${response.domain}</h4>
                                <p class="mb-0 text-muted">${response.message}</p>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0 text-primary fw-bold">৳ ${response.price}</h3>
                                <a href="${nextStepUrl}" class="btn btn-primary rounded-pill mt-2 px-4 shadow-sm">পরবর্তী ধাপ <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    `;
                } else {
                    html = `<div class="alert alert-danger shadow-sm border-0 p-4"><b>${response.message}</b></div>`;
                }
                $('#resultArea').html(html).fadeIn();
            },
            error: function() {
                Swal.fire({ icon: 'error', text: 'সার্ভারে সমস্যা হয়েছে। আবার চেষ্টা করুন।' });
            },
            complete: function() { btn.html('<i class="fas fa-search"></i> খুঁজুন').prop('disabled', false); }
        });
    });

    // টেবিল বাটন ফাংশন
    window.quickSearch = function(ext) {
        let domain = $('#domainInput').val();
        if(!domain) {
            Swal.fire({ text: 'আগে বক্সে ডোমেইন নামটি লিখুন', icon: 'info' });
            $('#domainInput').focus();
            return;
        }
        $('#extSelect').val(ext);
        $('#domainForm').submit();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});
</script>

@endsection