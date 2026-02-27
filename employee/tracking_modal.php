<style>
    .tracking-list {
        position: relative;
        padding: 10px 5px;
    }

    .tracking-item {
        position: relative;
        padding-left: 50px;
        padding-bottom: 40px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    /* Ang vertical line sa gilid */
    .tracking-item::before {
        content: "";
        position: absolute;
        left: 19px;
        top: 10px;
        width: 2px;
        height: 100%;
        background: #e9ecef;
        z-index: 0;
    }

    .tracking-item:last-child::before {
        display: none;
    }

    .tracking-item.completed::before {
        background: #28a745;
    }

    /* Nagiging green ang line pag tapos na */

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
        line-height: 36px;
        font-size: 18px;
        color: #999;
        transition: 0.3s;
    }

    /* Completed State (Green) */
    .tracking-item.completed .tracking-icon {
        background: #28a745;
        border-color: #28a745;
        color: #fff;
        box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
    }

    /* Active State (Maroon Pulse) */
    .tracking-item.active .tracking-icon {
        border-color: #800000;
        color: #800000;
        background: #fff;
        animation: pulse-tracking 2s infinite;
    }

    @keyframes pulse-tracking {
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

    /* Right Side Check Badge */
    .status-badge-right {
        font-size: 20px;
        color: #28a745;
        display: none;
        align-self: center;
    }

    .tracking-item.completed .status-badge-right {
        display: block;
    }

    .tracking-date {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #6c757d;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .tracking-content {
        font-weight: 700;
        font-size: 15px;
        color: #333;
        line-height: 1.2;
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
                        style="width: 50px; height: 50px;">
                        <i class="bi bi-geo-alt-fill fs-4" style="color: #800000;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">E-TAMS Journey</h5>
                        <small id="pv-memo-no" class="opacity-75 font-monospace">Memo: PENDING</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">
                <div class="p-4 bg-light border-bottom">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <label class="text-muted small text-uppercase fw-bold"
                                style="font-size: 10px; letter-spacing: 1px;">Destination</label>
                            <p id="pv-dest-text" class="fw-bold text-dark mb-0">-</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small text-uppercase fw-bold"
                                style="font-size: 10px; letter-spacing: 1px;">Travel Date</label>
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
                    style="border-radius: 15px; transition: 0.3s;">
                    Dismiss Tracker
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function trackTravel(data) {
        const fmtDateOnly = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : "-";
        const fmtFull = (d) => d ? new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : "Pending";

        document.getElementById('pv-memo-no').innerText = `Ref: ${data.memo_no}`;
        document.getElementById('pv-dest-text').innerText = data.destination;
        document.getElementById('pv-date-text').innerText = fmtDateOnly(data.travel_date);

        // Logic para sa bawat step
        const steps = [
            {
                label: "Application Submitted",
                desc: "TA request successfully filed.",
                date: data.submitted_at,
                isDone: true,
                icon: "bi-send"
            },
            {
                label: "Head Confirmation",
                desc: "Department level verification.",
                date: data.head_confirmed_at,
                isDone: data.status >= 1,
                isActive: data.status == 0,
                icon: "bi-person-badge"
            },
            {
                label: "Admin Approval",
                desc: "Final university-level approval.",
                date: data.admin_approved_at,
                isDone: data.status >= 2,
                isActive: data.status == 1,
                icon: "bi-shield-check"
            },
            {
                label: "Ready for Print",
                desc: "Official document generated.",
                date: data.admin_approved_at,
                isDone: data.status >= 2,
                isActive: false,
                icon: "bi-printer"
            }
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
                        <div class="tracking-content mb-0">${step.label}</div>
                        <p class="text-muted smallest mb-0" style="font-size: 11px;">${step.desc}</p>
                    </div>

                    <div class="status-badge-right ms-3">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>`;
        });

        document.getElementById('trackingTimeline').innerHTML = html;

        const modalEl = document.getElementById('trackingModal');
        const myModal = new bootstrap.Modal(modalEl);
        myModal.show();
    }
</script>