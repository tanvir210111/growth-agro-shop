@extends('layouts.front')
@section('title', 'Support Tickets')
@section('contents')

<style>
    .ticket-card { background: #fff; border-radius: 1rem; padding: 1.35rem; margin-bottom: .9rem; border: 1px solid #eef2f7; transition: .25s; }
    .ticket-card:hover { border-color: #ddd6fe; box-shadow: 0 10px 24px rgba(124,58,237,.08); }
    .status-badge { font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 5px 12px; border-radius: 50px; }
    .badge-pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
    .badge-replied { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
    .badge-closed { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }
</style>

<section class="ud-wrap">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3">
                @include('partial.user.sidebar')
            </div>

            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-dark m-0">সাপোর্ট টিকেট সমূহ</h4>
                    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#newTicket">
                        <i class="fa-solid fa-circle-plus me-1"></i> নতুন টিকেট
                    </button>
                </div>

                @forelse($tickets as $ticket)
                <div class="ticket-card">
                    <div class="d-md-flex justify-content-between align-items-center">
                        <div>
                            <div class="d-flex align-items-center mb-1">
                                <h6 class="fw-bold text-dark mb-0 me-3">{{ $ticket->subject }}</h6>
                                <span class="status-badge badge-{{ strtolower($ticket->status) }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </div>
                            <div class="text-muted small">
                                <i class="fa-regular fa-calendar-check me-1"></i> 
                                {{ \Carbon\Carbon::parse($ticket->created_at)->format('d M, Y h:i A') }}
                                <span class="mx-2">|</span>
                                <i class="fa-solid fa-hashtag me-1"></i> Ticket #{{ $ticket->id }}
                            </div>
                        </div>
                        <div class="mt-3 mt-md-0">
                            {{-- বিস্তারিত ও রিপ্লাই বাটন --}}
                            <a href="{{ route('user.support.view', $ticket->id) }}" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-bold">
                                বিস্তারিত ও রিপ্লাই <i class="fa-solid fa-chevron-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    <p class="mt-3 text-secondary small mb-0">
                        {{ Str::limit($ticket->message, 150) }}
                    </p>
                </div>
                @empty
                <div class="text-center py-5 bg-white rounded-4 border shadow-sm">
                    <i class="fa-solid fa-envelope-open-text fa-4x text-light mb-3"></i>
                    <h5 class="fw-bold text-dark">কোন টিকেট পাওয়া যায়নি</h5>
                    <p class="text-muted small">সাহায্যের জন্য একটি নতুন টিকেট খুলুন।</p>
                </div>
                @endforelse

                @if ($tickets->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $tickets->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- নতুন টিকেট খোলার Modal --}}
<div class="modal fade" id="newTicket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark">নতুন টিকেট খুলুন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('user.support.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">বিষয়</label>
                        <input type="text" name="subject" class="form-control rounded-3" placeholder="সমস্যার সংক্ষিপ্ত নাম..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">বিস্তারিত বর্ণনা</label>
                        <textarea name="message" class="form-control rounded-3" rows="5" placeholder="আপনার সমস্যাটি বিস্তারিত এখানে লিখুন..." required></textarea>
                    </div>
                    <input type="hidden" name="priority" value="medium">
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow">
                        টিকিট সাবমিট করুন
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection