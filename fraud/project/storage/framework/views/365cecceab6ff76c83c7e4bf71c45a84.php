

<?php $__env->startSection('meta'); ?>
    <title>ড্যাশবোর্ড — <?php echo e($gs->title); ?></title>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('contents'); ?>
<?php
    $firstName = explode(' ', Auth::user()->name)[0] ?? Auth::user()->name;
    $paginator = $data['recent_orders'];
?>

<section class="ud-wrap">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3">
                <?php echo $__env->make('partial.user.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>

            <div class="col-lg-9">
                
                <div class="ud-hero">
                    <h2>স্বাগতম, <?php echo e($firstName); ?>!</h2>
                    <p>Fraud Checker দিয়ে অর্ডার কনফার্মের আগেই কাস্টমার যাচাই করুন। আপনার অ্যাকাউন্টের সারাংশ নিচে দেখুন।</p>
                    <div class="ud-hero-actions">
                        <a href="<?php echo e(route('user.fraud.index')); ?>" class="btn-w"><i class="fas fa-search"></i> ফ্রি সার্চ করুন</a>
                        <a href="<?php echo e(route('user.fraud.logs')); ?>" class="btn-ghost-w"><i class="fas fa-history"></i> হিস্ট্রি দেখুন</a>
                    </div>
                </div>

                
                <div class="ud-stats">
                    <div class="ud-stat">
                        <div class="ico p"><i class="fas fa-shield-alt"></i></div>
                        <div class="lbl">মোট চেক</div>
                        <div class="val"><?php echo e(number_format($data['fraud_total'] ?? 0)); ?></div>
                        <div class="hint">সকল সময়ের Fraud Check</div>
                    </div>
                    <div class="ud-stat">
                        <div class="ico g"><i class="fas fa-bolt"></i></div>
                        <div class="lbl">আজকের চেক</div>
                        <div class="val">
                            <?php echo e((int) ($data['fraud_today'] ?? 0)); ?>

                            <?php if(($data['fraud_daily_limit'] ?? 0) > 0): ?>
                                <small style="font-size:.9rem;color:#94a3b8;font-weight:600">/ <?php echo e($data['fraud_daily_limit']); ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="hint">ডেইলি লিমিট ব্যবহার</div>
                    </div>
                    <div class="ud-stat">
                        <div class="ico b"><i class="fas fa-shopping-bag"></i></div>
                        <div class="lbl">মোট অর্ডার</div>
                        <div class="val"><?php echo e(number_format($data['total_orders'] ?? 0)); ?></div>
                        <div class="hint">সার্ভিস অর্ডার</div>
                    </div>
                    <div class="ud-stat">
                        <div class="ico o"><i class="fas fa-ticket-alt"></i></div>
                        <div class="lbl">পেন্ডিং সাপোর্ট</div>
                        <div class="val"><?php echo e(number_format($data['pending_tickets'] ?? 0)); ?></div>
                        <div class="hint">উত্তরের অপেক্ষায়</div>
                    </div>
                </div>

                <div class="ud-grid2">
                    
                    <div class="ud-card">
                        <div class="ud-card-h">
                            <h5>সাম্প্রতিক Fraud Check</h5>
                            <a href="<?php echo e(route('user.fraud.logs')); ?>" class="btn-cd-ghost" style="min-height:2.2rem;padding:.4rem 1rem;font-size:.8rem">সব দেখুন</a>
                        </div>
                        <div class="ud-card-b" style="padding:0">
                            <?php $__empty_1 = true; $__currentLoopData = ($data['fraud_recent'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="d-flex align-items-center justify-content-between px-3 py-3" style="border-bottom:1px solid #f1f5f9">
                                    <div>
                                        <code style="font-weight:700;color:#0f172a;font-size:.95rem"><?php echo e($row->phone); ?></code>
                                        <div class="small text-muted mt-1"><?php echo e(\Carbon\Carbon::parse($row->created_at)->format('d M, Y h:i A')); ?></div>
                                    </div>
                                    <div class="text-end">
                                        <span class="ud-badge ok">Success <?php echo e(number_format($row->success_ratio, 0)); ?>%</span>
                                        <div class="small text-muted mt-1">Cancel <?php echo e(number_format($row->cancel_ratio, 0)); ?>%</div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="ud-empty">
                                    <i class="fas fa-search fa-3x"></i>
                                    এখনো কোনো চেক নেই।
                                    <div class="mt-3">
                                        <a href="<?php echo e(route('user.fraud.index')); ?>" class="btn-cd btn-cd-primary">প্রথম চেক করুন</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="ud-card">
                        <div class="ud-card-h">
                            <h5>কুইক অ্যাকশন</h5>
                        </div>
                        <div class="ud-card-b">
                            <div class="ud-quick">
                                <a href="<?php echo e(route('user.fraud.index')); ?>">
                                    <span class="ico" style="background:#ede9fe;color:#7c3aed"><i class="fas fa-search"></i></span>
                                    <span>
                                        <strong>নতুন চেক</strong>
                                        <span>ফোন নম্বর দিয়ে রিস্ক স্কোর দেখুন</span>
                                    </span>
                                    <i class="fas fa-chevron-right go"></i>
                                </a>
                                <a href="<?php echo e(route('user.fraud.logs')); ?>">
                                    <span class="ico" style="background:#d1fae5;color:#059669"><i class="fas fa-history"></i></span>
                                    <span>
                                        <strong>চেক হিস্ট্রি</strong>
                                        <span>আগের সব রিপোর্ট দেখুন</span>
                                    </span>
                                    <i class="fas fa-chevron-right go"></i>
                                </a>
                                <a href="<?php echo e(route('user.support')); ?>">
                                    <span class="ico" style="background:#ffedd5;color:#ea580c"><i class="fas fa-headset"></i></span>
                                    <span>
                                        <strong>সাপোর্ট টিকেট</strong>
                                        <span>সাহায্যের জন্য টিকেট খুলুন</span>
                                    </span>
                                    <i class="fas fa-chevron-right go"></i>
                                </a>
                                <a href="<?php echo e(route('user.profile')); ?>">
                                    <span class="ico" style="background:#dbeafe;color:#2563eb"><i class="fas fa-user-cog"></i></span>
                                    <span>
                                        <strong>প্রোফাইল</strong>
                                        <span>অ্যাকাউন্ট তথ্য আপডেট করুন</span>
                                    </span>
                                    <i class="fas fa-chevron-right go"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="ud-card">
                    <div class="ud-card-h">
                        <h5>সাম্প্রতিক সার্ভিস অর্ডার</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="ud-table">
                            <thead>
                                <tr>
                                    <th>অর্ডার আইডি</th>
                                    <th>তারিখ</th>
                                    <th>পেমেন্ট</th>
                                    <th>পরিমাণ</th>
                                    <th class="text-center">স্ট্যাটাস</th>
                                    <th class="text-end">ইনভয়েস</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $paginator; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ud-oid">#<?php echo e($order->order_number); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($order->created_at)->format('d M, Y')); ?></td>
                                    <td><i class="fas fa-wallet me-1 text-muted"></i><?php echo e($order->payment_method); ?></td>
                                    <td class="fw-bold">৳ <?php echo e(number_format($order->total_amount)); ?></td>
                                    <td class="text-center">
                                        <?php if($order->due_amount <= 0): ?>
                                            <span class="ud-badge ok">পরিশোধিত</span>
                                        <?php else: ?>
                                            <span class="ud-badge due">বকেয়া</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 view-invoice-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#invoiceModal"
                                            data-order="<?php echo e($order->order_number); ?>"
                                            data-date="<?php echo e(\Carbon\Carbon::parse($order->created_at)->format('d M, Y h:i A')); ?>"
                                            data-method="<?php echo e($order->payment_method); ?>"
                                            data-total="<?php echo e(number_format($order->total_amount)); ?>"
                                            data-paid="<?php echo e(number_format($order->paid_amount ?? 0)); ?>"
                                            data-due="<?php echo e(number_format($order->due_amount ?? 0)); ?>"
                                            data-desc="<?php echo e($order->description ?? '—'); ?>"
                                            data-link="<?php echo e(route('frontend.invoice.live', ['order_number' => $order->order_number, 'token' => $order->hash_token ?? ''])); ?>"
                                        >
                                            <i class="fas fa-eye me-1"></i> বিস্তারিত
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="ud-empty">
                                            <i class="fas fa-folder-open fa-3x"></i>
                                            এখনো কোনো অর্ডার নেই।
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if($paginator->hasPages()): ?>
                    <div class="d-flex justify-content-center py-3">
                        <nav>
                            <ul class="pagination ud-pagination mb-0">
                                <?php if($paginator->onFirstPage()): ?>
                                    <li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left" style="font-size:11px"></i></span></li>
                                <?php else: ?>
                                    <li class="page-item"><a class="page-link" href="<?php echo e($paginator->previousPageUrl()); ?>"><i class="fas fa-chevron-left" style="font-size:11px"></i></a></li>
                                <?php endif; ?>

                                <?php $__currentLoopData = range(1, $paginator->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($i >= $paginator->currentPage() - 2 && $i <= $paginator->currentPage() + 2): ?>
                                        <?php if($i == $paginator->currentPage()): ?>
                                            <li class="page-item active"><span class="page-link"><?php echo e($i); ?></span></li>
                                        <?php else: ?>
                                            <li class="page-item"><a class="page-link" href="<?php echo e($paginator->url($i)); ?>"><?php echo e($i); ?></a></li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php if($paginator->hasMorePages()): ?>
                                    <li class="page-item"><a class="page-link" href="<?php echo e($paginator->nextPageUrl()); ?>"><i class="fas fa-chevron-right" style="font-size:11px"></i></a></li>
                                <?php else: ?>
                                    <li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right" style="font-size:11px"></i></span></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>


<div class="modal fade ud-modal" id="invoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold mb-0">ইনভয়েস ডিটেইলস</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted text-uppercase">অর্ডার আইডি</small>
                        <h6 class="fw-bold text-primary" id="modalOrderNo">#INV-0000</h6>
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-muted text-uppercase">তারিখ</small>
                        <h6 class="fw-bold" id="modalDate">---</h6>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold text-muted mb-1">অর্ডার আইটেমসমূহ</label>
                    <div class="invoice-item-list" id="modalDesc">---</div>
                </div>
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span class="text-muted">পেমেন্ট মেথড</span>
                    <span class="fw-bold" id="modalMethod">---</span>
                </div>
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span class="text-muted">মোট বিল</span>
                    <span class="fw-bold">৳ <span id="modalTotal">0</span></span>
                </div>
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span class="text-success">জমা</span>
                    <span class="fw-bold text-success">৳ <span id="modalPaid">0</span></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-danger">বকেয়া</span>
                    <span class="fw-bold text-danger">৳ <span id="modalDue">0</span></span>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">বন্ধ</button>
                <a href="#" id="modalPrintLink" target="_blank" class="btn btn-primary rounded-pill">
                    <i class="fas fa-print me-1"></i> প্রিন্ট
                </a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('.view-invoice-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('modalOrderNo').textContent = '#' + this.dataset.order;
        document.getElementById('modalDate').textContent = this.dataset.date;
        document.getElementById('modalTotal').textContent = this.dataset.total;
        document.getElementById('modalPaid').textContent = this.dataset.paid;
        document.getElementById('modalDue').textContent = this.dataset.due;
        document.getElementById('modalMethod').textContent = this.dataset.method;
        document.getElementById('modalDesc').textContent = this.dataset.desc;
        document.getElementById('modalPrintLink').setAttribute('href', this.dataset.link || '#');
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.front', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\project\resources\views/user/client/dashboard.blade.php ENDPATH**/ ?>