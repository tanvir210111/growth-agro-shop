/* ==========================================================================
   Baby Fashion BD - Admin Panel JS
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
    // Sidebar toggle for mobile
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebar = document.querySelector('.main-sidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            sidebar.classList.toggle('open');
        });
    }

    // CSRF Setup for Fetch
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Quick Order Status Changer
    document.querySelectorAll('.order-status-changer').forEach(function (select) {
        select.addEventListener('change', async function () {
            const orderId = this.dataset.orderId;
            const newStatus = this.value;
            const originalStatus = this.dataset.currentStatus;

            // Visual feedback
            this.className = 'status-select status-' + newStatus + ' order-status-changer';

            try {
                const response = await fetch(`/admin/orders/${orderId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                const data = await response.json();

                if (data.success) {
                    this.dataset.currentStatus = newStatus;
                    showToast('success', data.message || 'Status updated!');
                } else {
                    this.value = originalStatus;
                    this.className = 'status-select status-' + originalStatus + ' order-status-changer';
                    showToast('error', 'Failed to update order status.');
                }
            } catch (err) {
                console.error(err);
                this.value = originalStatus;
                this.className = 'status-select status-' + originalStatus + ' order-status-changer';
                showToast('error', 'Server error while updating status.');
            }
        });
    });

    // Simple toast notification helper
    window.showToast = function (type, message) {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        } else {
            alert(message);
        }
    };
});
