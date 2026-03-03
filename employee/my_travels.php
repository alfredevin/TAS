<?php
include './../config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>

</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <style>
        :root {
            --marsu-maroon: #800000;
            --marsu-gold: #ce9d06;
        }

        .card-stats {
            border-left: 5px solid var(--marsu-maroon);
            transition: 0.3s;
        }

        .tracking-list {
            position: relative;
            padding: 20px 5px;
        }

        .tracking-item {
            position: relative;
            padding-left: 45px;
            padding-bottom: 40px;
        }

        .tracking-item::before {
            content: "";
            position: absolute;
            left: 14px;
            top: 5px;
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

        .tracking-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #ddd;
            z-index: 1;
            text-align: center;
            line-height: 26px;
            font-size: 14px;
        }

        .tracking-item.completed .tracking-icon {
            background: #28a745;
            border-color: #28a745;
            color: #fff;
        }

        .tracking-item.active .tracking-icon {
            border-color: var(--marsu-maroon);
            color: var(--marsu-maroon);
            animation: pulse 2s infinite;
        }

        .tracking-item.rejected::before {
            background: #dc3545;
        }

        @keyframes pulse {
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

        .tracking-date {
            display: block;
            font-size: 11px;
            font-weight: bold;
            color: #6c757d;
        }

        .tracking-content {
            font-weight: 600;
            font-size: 14px;
            color: #212529;
        }

        .pulse-live {
            animation: pulseLive 1.5s infinite;
        }

        @keyframes pulseLive {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.7;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
    <?php
    $query = "SELECT t.*, (SELECT COUNT(*) FROM ta_participants_tbl WHERE ta_id = t.ta_id) as total_pax 
              FROM ta_tbl t 
              JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id 
              WHERE tp.employee_id = '$employee_id' 
              ORDER BY t.submitted_at DESC";
    $result = mysqli_query($conn, $query);
    $today = date("Y-m-d"); // Kukunin ang petsa ngayon
    ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>My Travel Management</h1>
        </div>

        <section class="section">
            <div class="card border-0 shadow-sm">
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover datatable align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Memo No.</th>
                                    <th>Destination & Dates</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)):
                                    $status = (int) $row['status'];

                                    $status_config = [
                                        0 => ['badge' => 'bg-warning text-dark', 'label' => 'Pending'],
                                        1 => ['badge' => 'bg-info', 'label' => 'Head Confirmed'],
                                        2 => ['badge' => 'bg-success', 'label' => 'Fully Approved'],
                                        3 => ['badge' => 'bg-primary', 'label' => 'Certificate Uploaded'],
                                        4 => ['badge' => 'bg-success', 'label' => 'Completed'],
                                        11 => ['badge' => 'bg-danger', 'label' => 'Needs Correction'],
                                        99 => ['badge' => 'bg-dark', 'label' => 'Cancelled/Declined'],
                                        'default' => ['badge' => 'bg-secondary', 'label' => 'Unknown']
                                    ];
                                    $config = $status_config[$status] ?? $status_config['default'];

                                    // Check kung ngayon ang biyahe
                                    $is_travel_day = ($today >= $row['travel_date'] && $today <= $row['return_date']);
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-maroon"><?= $row['memo_no'] ?: '---' ?></td>
                                        <td>
                                            <div class="fw-bold"><?= $row['destination'] ?></div>
                                            <div class="small text-muted"><i
                                                    class="bi bi-calendar-event me-1"></i><?= date("M d", strtotime($row['travel_date'])) ?>
                                                - <?= date("M d, Y", strtotime($row['return_date'])) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $config['badge'] ?> mb-1"><?= $config['label'] ?></span>
                                            <?php if ($row['is_tracking_active'] == 1): ?>
                                                <br><span class="badge bg-danger pulse-live"><i class="bi bi-geo-alt"></i> Live
                                                    Tracking</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">

                                            <?php if ($status == 2 && $is_travel_day): ?>
                                                <?php if ($row['is_tracking_active'] == 0): ?>
                                                    <button class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm me-1"
                                                        onclick="startTracking(<?= $row['ta_id'] ?>)">
                                                        <i class="bi bi-play-circle me-1"></i> Start Travel
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm me-1"
                                                        onclick="stopTracking(<?= $row['ta_id'] ?>)">
                                                        <i class="bi bi-stop-circle me-1"></i> End Travel
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <?php if ($status == 11): ?>
                                                <button class="btn btn-sm btn-warning rounded-pill px-3 shadow-sm"
                                                    onclick='openResubmitModal(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <i class="bi bi-pencil-square me-1"></i> Fix
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"
                                                    onclick='trackTravel(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <i class="bi bi-search"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="modal fade" id="resubmitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-warning border-0 text-dark">
                    <h5 class="modal-title fw-bold"><i class="bi bi-tools me-2"></i>Correction Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_resubmit.php" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="ta_id" id="res_ta_id">

                        <div class="alert alert-secondary mb-3 py-2 small" style="border-left: 4px solid #800000;">
                            <strong>Admin Remarks:</strong> <br>
                            <span id="res_decline_text" class="fst-italic"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Update Destination</label>
                            <input type="text" name="destination" id="res_destination" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Update Purpose</label>
                            <textarea name="task" id="res_task" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Travel Date</label>
                                <input type="date" name="travel_date" id="res_travel_date" class="form-control"
                                    required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Return Date</label>
                                <input type="date" name="return_date" id="res_return_date" class="form-control"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" name="btn_resubmit"
                            class="btn btn-warning fw-bold px-4 shadow-sm w-100">Update & Send to Head</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 p-4 text-white"
                    style="background: linear-gradient(45deg, #800000, #a00000);">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 me-3 shadow-sm">
                            <i class="bi bi-geo-alt-fill text-maroon fs-4" style="color:#800000;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Travel Status Tracker</h5>
                            <small id="pv-memo-no" class="opacity-75"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                    <div class="p-3 bg-light border-bottom">
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <label class="text-muted smallest fw-bold text-uppercase"
                                    style="font-size:10px;">Destination</label>
                                <p id="pv-dest-text" class="fw-bold mb-0 small"></p>
                            </div>
                            <div class="col-6">
                                <label class="text-muted smallest fw-bold text-uppercase" style="font-size:10px;">Travel
                                    Date</label>
                                <p id="pv-date-text" class="fw-bold mb-0 small"></p>
                            </div>
                        </div>
                    </div>

                    <div id="decline-reason-section" class="p-3 d-none">
                        <div class="alert alert-danger border-0 shadow-sm mb-0" style="border-radius: 12px;">
                            <div class="d-flex">
                                <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                                <div>
                                    <strong id="decline-author" class="d-block small">Decision Remark:</strong>
                                    <span id="decline-msg" class="small fst-italic"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="tracking-list" id="trackingTimeline"></div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-3 pt-0 text-center">
                    <button type="button" class="btn btn-light w-100 rounded-pill fw-bold small"
                        data-bs-dismiss="modal">Close Tracker</button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../template/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include '../template/script.php'; ?>

    <script>
        // 1. SAFE PHP INJECTION PARA SA ACTIVE TRACKING
        const activeTaId = <?php
        $active_id = "null";
        // Kukunin ang active TA ng logged in user (safe check)
        $check_active = mysqli_query($conn, "SELECT t.ta_id FROM ta_tbl t JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id WHERE tp.employee_id = '$employee_id' AND t.is_tracking_active = 1 LIMIT 1");
        if ($check_active && mysqli_num_rows($check_active) > 0) {
            $active_id = mysqli_fetch_assoc($check_active)['ta_id'];
        }
        echo $active_id;
        ?>;

        // 2. BACKGROUND LOCATION SENDER (Gagana lang kung may active travel)
        if (activeTaId !== null) {
            setInterval(() => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        sendLocation(activeTaId, pos.coords.latitude, pos.coords.longitude);
                    }, err => console.log('Location access issue:', err), { enableHighAccuracy: true });
                }
            }, 30000); // 30 seconds interval
        }

        // 3. CORE BUTTON FUNCTIONS
        function startTracking(taId) {
            if (!navigator.geolocation) {
                Swal.fire('Error', 'Your browser does not support location tracking.', 'error');
                return;
            }

            Swal.fire({
                title: 'Start Official Travel?',
                text: "Your live location will be shared with the Admin Dashboard.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Yes, Start Tracking'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Update Database (Set to Active 1)
                    fetch('toggle_tracking.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'ta_id=' + taId + '&status=1'
                    }).then(response => {
                        if (response.ok) {
                            // Hihingi ng permission sa browser at kukuha ng unang location
                            navigator.geolocation.getCurrentPosition(
                                (pos) => {
                                    sendLocation(taId, pos.coords.latitude, pos.coords.longitude);
                                    Swal.fire({ title: 'Tracking Started', icon: 'success', timer: 1500, showConfirmButton: false })
                                        .then(() => location.reload());
                                },
                                (err) => {
                                    Swal.fire('Location Access Denied', 'Please allow location access in your browser settings to continue.', 'error');
                                }
                            );
                        } else {
                            Swal.fire('Error', 'Failed to communicate with database.', 'error');
                        }
                    }).catch(error => {
                        console.error('Fetch error:', error);
                    });
                }
            });
        }

        function stopTracking(taId) {
            Swal.fire({
                title: 'End Official Travel?',
                text: "This will stop sharing your location.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, End Travel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('toggle_tracking.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'ta_id=' + taId + '&status=0'
                    }).then(() => location.reload());
                }
            });
        }

        function sendLocation(taId, lat, lng) {
            fetch('update_location.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'ta_id=' + taId + '&lat=' + lat + '&lng=' + lng
            });
        }

        // 4. EXISTING MODALS FOR TRACKING AND RESUBMIT
        function openResubmitModal(data) {
            document.getElementById('res_ta_id').value = data.ta_id;
            document.getElementById('res_destination').value = data.destination;
            document.getElementById('res_task').value = data.task;
            document.getElementById('res_travel_date').value = data.travel_date;
            document.getElementById('res_return_date').value = data.return_date;
            const reasonSectionText = document.getElementById('res_decline_text');
            if (reasonSectionText) {
                reasonSectionText.innerText = data.admin_remarks || data.decline_reason || "Check with admin for details.";
            }
            new bootstrap.Modal(document.getElementById('resubmitModal')).show();
        }

        function trackTravel(data) {
            const fmtFull = (d) => d ? new Date(d).toLocaleString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
            }) : "Pending";

            const status = parseInt(data.status);

            const memoText = document.getElementById('pv-memo-no');
            if (memoText) memoText.innerText = data.memo_no ? `Memo: ${data.memo_no}` : "Status: Processing";

            const destText = document.getElementById('pv-dest-text');
            if (destText) destText.innerText = data.destination;

            const dateText = document.getElementById('pv-date-text');
            if (dateText) dateText.innerText = data.travel_date;

            const reasonSection = document.getElementById('decline-reason-section');
            if (reasonSection) {
                if (status == 99 || status == 11) {
                    reasonSection.classList.remove('d-none');
                    document.getElementById('decline-author').innerText = (status == 11) ? "Returned for Correction (Admin):" : "Declined:";
                    document.getElementById('decline-msg').innerText = data.admin_remarks || data.decline_reason || "No specific reason provided.";
                } else {
                    reasonSection.classList.add('d-none');
                }
            }

            const steps = [
                { label: "Request Submitted", date: data.submitted_at, isDone: true, isRejected: false, icon: "bi-file-earmark-plus" },
                { label: "Head Confirmation", date: data.head_confirmed_at, isDone: (status >= 1 && status != 99) || (status == 11) || (status == 99 && data.head_confirmed_at != null), isRejected: status == 99 && (data.head_confirmed_at == null || data.head_confirmed_at == ""), isActive: status == 0, icon: "bi-person-check" },
                { label: "Admin Approval", date: data.admin_approved_at, isDone: status == 2, isRejected: status == 11 || (status == 99 && data.head_confirmed_at != null), isActive: status == 1, icon: "bi-shield-check" }
            ];

            let html = '';
            steps.forEach((step) => {
                let sClass = step.isRejected ? 'rejected' : (step.isDone ? 'completed' : (step.isActive ? 'active' : ''));
                let iconMarkup = step.isRejected ? '<i class="bi bi-x-lg"></i>' : (step.isDone ? '<i class="bi bi-check-lg"></i>' : `<i class="bi ${step.icon}"></i>`);

                html += `
                <div class="tracking-item ${sClass}">
                    <div class="tracking-icon shadow-sm ${step.isRejected ? 'bg-danger text-white border-danger' : ''}">
                        ${iconMarkup}
                    </div>
                    <div class="ps-2">
                        <span class="tracking-date">${step.isRejected ? 'Declined' : (step.isDone ? fmtFull(step.date) : (step.isActive ? 'In Progress' : 'Upcoming'))}</span>
                        <div class="tracking-content mb-0 ${step.isRejected ? 'text-danger' : ''}">${step.label}</div>
                    </div>
                </div>`;
            });

            document.getElementById('trackingTimeline').innerHTML = html;
            new bootstrap.Modal(document.getElementById('trackingModal')).show();
        }
    </script>
</body>

</html>