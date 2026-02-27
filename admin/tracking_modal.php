<style>
    .tracking-list {
        position: relative;
        padding: 10px 5px;
    }

    .tracking-item {
        position: relative;
        padding-left: 55px;
        padding-bottom: 45px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    /* The Connector Line */
    .tracking-item::before {
        content: "";
        position: absolute;
        left: 19px;
        top: 10px;
        width: 2px;
        height: 100%;
        background: #dee2e6;
        z-index: 0;
    }

    .tracking-item:last-child::before {
        display: none;
    }

    .tracking-item.completed::before {
        background: #28a745;
    }

    /* Left Icon Styles */
    .tracking-icon {
        position: absolute;
        left: 0;
        top: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #ddd;
        z-index: 1;
        text-align: center;
        line-height: 38px;
        font-size: 18px;
        color: #adb5bd;
        transition: all 0.3s ease;
    }

    /* Green State (Completed) */
    .tracking-item.completed .tracking-icon {
        background: #28a745;
        border-color: #28a745;
        color: #fff;
        box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
    }

    /* Maroon Pulse State (Ongoing) */
    .tracking-item.active .tracking-icon {
        border-color: #800000;
        color: #800000;
        background: #fff;
        animation: tracking-pulse 2s infinite;
    }

    @keyframes tracking-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(128, 0, 0, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(128, 0, 0, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(128, 0, 0, 0);
        }
    }

    /* Right Side Check Mark */
    .status-check-right {
        font-size: 22px;
        color: #28a745;
        display: none;
        align-self: center;
    }

    .tracking-item.completed .status-check-right {
        display: block;
        animation: fadeInUp 0.4s both;
    }

    .tracking-date {
        display: block;
        font-size: 10px;
        font-weight: 800;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .tracking-content {
        font-weight: 700;
        font-size: 16px;
        color: #212529;
    }

    .tracking-item.completed .tracking-content {
        color: #28a745;
    }
</style>

<div class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px; overflow: hidden;">

            <div class="modal-header border-0 p-4 text-white"
                style="background: linear-gradient(45deg, #800000, #a00000);">
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded-circle p-2 me-3 shadow-sm d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px;">
                        <i class="bi bi-geo-alt-fill fs-4" style="color: #800000;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Travel Journey</h5>
                        <small id="pv-memo-no" class="opacity-75">REF: PENDING</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">
                <div class="p-4 bg-light border-bottom text-center">
                    <div class="row">
                        <div class="col-6 border-end">
                            <label class="text-muted smallest text-uppercase fw-bold">Destination</label>
                            <p id="pv-dest-text" class="fw-bold text-dark mb-0">-</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted smallest text-uppercase fw-bold">Date</label>
                            <p id="pv-date-text" class="fw-bold text-dark mb-0">-</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 px-5">
                    <div class="tracking-list" id="trackingTimeline">
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light w-100 fw-bold py-3 shadow-sm" data-bs-dismiss="modal"
                    style="border-radius: 15px;">Dismiss Tracker</button>
            </div>
        </div>
    </div>
</div>

<script>
    function trackTravel(data) {
        const fmtFull = (d) => d ? new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : "Pending";
        const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : "-";

        // 1. Populate Headers
        document.getElementById('pv-memo-no').innerText = `REF: ${data.memo_no || 'PENDING'}`;
        document.getElementById('pv-dest-text').innerText = data.destination;
        document.getElementById('pv-date-text').innerText = fmtDate(data.travel_date);

        // 2. Step Logic (Status 0=Pending, 1=Confirmed, 2=Approved)
        const steps = [
            { label: "Request Filed", desc: "Successfully submitted to E-TAMS.", date: data.submitted_at, isDone: true, icon: "bi-send" },
            { label: "Dept. Confirmation", desc: "Verified by Department Head.", date: data.head_confirmed_at, isDone: data.status >= 1, isActive: data.status == 0, icon: "bi-person-check" },
            { label: "Final Approval", desc: "Approved by Campus Admin.", date: data.admin_approved_at, isDone: data.status >= 2, isActive: data.status == 1, icon: "bi-shield-check" },
            { label: "Ready to Print", desc: "Document finalized and active.", date: data.admin_approved_at, isDone: data.status >= 2, isActive: false, icon: "bi-printer" }
        ];

        let html = '';
        steps.forEach((step) => {
            let statusClass = step.isDone ? 'completed' : (step.isActive ? 'active' : '');

            html += `
                <div class="tracking-item ${statusClass}">
                    <div class="tracking-icon shadow-sm">
                        <i class="bi ${step.icon}"></i>
                    </div>
                    <div class="ps-2 flex-grow-1">
                        <span class="tracking-date">${step.isDone ? fmtFull(step.date) : (step.isActive ? 'Ongoing' : 'Awaiting')}</span>
                        <div class="tracking-content">${step.label}</div>
                        <p class="text-muted mb-0" style="font-size: 11px;">${step.desc}</p>
                    </div>
                    <div class="status-check-right">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>`;
        });

        document.getElementById('trackingTimeline').innerHTML = html;

        // Show Modal
        const myModal = new bootstrap.Modal(document.getElementById('trackingModal'));
        myModal.show();
    }
</script>