<?php
include './../config.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include '../template/header.php'; ?>

    <script>
        function updateDropdown() {
            const isOfficial = document.getElementById('official');
            const selectDropdown = document.getElementById('purpose_select');

            if (isOfficial && selectDropdown) {
                selectDropdown.innerHTML = '';
                // Add smooth fade animation
                selectDropdown.style.opacity = 0;
                setTimeout(() => {
                    if (isOfficial.checked) {
                        selectDropdown.innerHTML = `
                            <option value="To coordinate with">To coordinate with</option>
                            <option value="To attend Meeting/Conference">To attend Meeting/Conference</option>
                            <option value="To Secure Documents">To Secure Documents</option>
                            <option value="Others">Others (Specify below)</option>
                        `;
                    } else {
                        selectDropdown.innerHTML = `
                            <option value="To attend Personal Matter">To attend Personal Matter</option>
                            <option value="Others">Others (Specify below)</option>
                        `;
                    }
                    selectDropdown.style.opacity = 1;
                }, 150);
            }
        }
        window.addEventListener('DOMContentLoaded', updateDropdown);
    </script>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <?php
    $employee_id = $_SESSION['employee_id'];
    $today = date("Y-m-d");

    // Kunin ang History ng Pass Slips ng employee
    $history_query = mysqli_query($conn, "SELECT * FROM pass_slip_tbl WHERE employee_id = '$employee_id' ORDER BY created_at DESC");
    ?>

    <style>
        /* AURA & DRAMATIC THEME (Professional, Non-Gaming) */
        :root {
            --primary-aura: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            --accent-glow: rgba(42, 82, 152, 0.4);
            --card-radius: 24px;
        }

        .main {
            background-color: #f8f9fa;
        }

        .form-card {
            border-radius: var(--card-radius);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            border: none;
            background: #ffffff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .form-card:hover {
            box-shadow: 0 20px 40px var(--accent-glow);
        }

        .form-header {
            background: var(--primary-aura);
            color: white;
            padding: 30px 20px;
            text-align: center;
            position: relative;
        }

        .form-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
        }

        /* Interactive Radio Buttons */
        .btn-check:checked+.btn-outline-custom {
            background: var(--primary-aura);
            color: white;
            border-color: transparent;
            box-shadow: 0 8px 20px var(--accent-glow);
            transform: translateY(-2px);
        }

        .btn-outline-custom {
            color: #1e3c72;
            border: 2px solid #e2e8f0;
            font-weight: 600;
            border-radius: 16px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-outline-custom:hover {
            background-color: #f1f5f9;
            transform: translateY(-1px);
        }

        /* Input Fields */
        .form-control,
        .form-select {
            border-radius: 14px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #fff;
            border-color: #2a5298;
            box-shadow: 0 0 0 4px rgba(42, 82, 152, 0.1);
        }

        /* Submit Button */
        .btn-submit-aura {
            background: var(--primary-aura);
            border-radius: 16px;
            padding: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-submit-aura:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px var(--accent-glow);
        }

        /* History Cards (Mobile Friendly instead of Table) */
        .history-item {
            background: #fff;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 16px;
            border: 1px solid #edf2f7;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .history-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06);
            border-color: #e2e8f0;
        }

        /* Status Border Accents */
        .status-accent-0 {
            border-left: 6px solid #f59e0b;
        }

        /* Pending */
        .status-accent-1 {
            border-left: 6px solid #10b981;
        }

        /* Approved */
        .status-accent-2 {
            border-left: 6px solid #ef4444;
        }

        /* Declined */

        .timeline-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 16px;
            margin-top: 15px;
        }

        .time-box {
            display: flex;
            align-items: start;
            gap: 8px;
            font-size: 12px;
        }

        .pulse-live {
            animation: pulseLive 1.5s infinite;
        }

        @keyframes pulseLive {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }

            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        .get-address {
            font-size: 10px;
            line-height: 1.2;
            display: inline-block;
            margin-top: 4px;
            text-decoration: none;
            padding: 2px 6px;
            background: rgba(0, 0, 0, 0.04);
            border-radius: 4px;
        }

        .get-address:hover {
            background: rgba(0, 0, 0, 0.08);
        }
    </style>

    <main id="main" class="main">
        <div class="pagetitle mb-4 px-2">
            <h1 class="fw-bolder" style="color: #1e3c72; font-size: 1.8rem;">Pass Slip Request</h1>
            <nav>
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-muted">Employee</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary">E-Pass Slip</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-xl-4 col-lg-5 mb-4">
                    <div class="form-card">
                        <div class="form-header">
                            <h3 class="fw-bolder mb-1"><i class="bi bi-send-check me-2"></i>Apply Pass Slip</h3>
                            <p class="small text-white-50 mb-0">Quick & secure campus exit</p>
                        </div>
                        <div class="card-body p-4 pt-5">
                            <form action="process_pass_slip.php" method="POST">
                                <div class="mb-3">
                                    <label
                                        class="form-label small fw-bold text-muted text-uppercase tracking-wider">Date
                                        of Application</label>
                                    <input type="text" class="form-control fw-bold text-primary border-0"
                                        value="<?= date('F d, Y') ?>" readonly style="background-color: #eff6ff;">
                                    <input type="hidden" name="ps_date" value="<?= $today ?>">
                                </div>

                                <div class="mb-4">
                                    <label
                                        class="form-label small fw-bold text-muted text-uppercase tracking-wider">Transaction
                                        Type</label>
                                    <div class="d-flex gap-2">
                                        <input type="radio" class="btn-check" name="issued_for" id="official"
                                            value="Official Activity" checked onchange="updateDropdown()">
                                        <label class="btn btn-outline-custom w-100 py-3" for="official"><i
                                                class="bi bi-briefcase-fill me-2"></i>Official</label>

                                        <input type="radio" class="btn-check" name="issued_for" id="personal"
                                            value="Personal Reason" onchange="updateDropdown()">
                                        <label class="btn btn-outline-custom w-100 py-3" for="personal"><i
                                                class="bi bi-person-badge-fill me-2"></i>Personal</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label
                                        class="form-label small fw-bold text-muted text-uppercase tracking-wider">Where
                                        to?</label>
                                    <input type="text" class="form-control" name="destination"
                                        placeholder="e.g. Boac Municipality" required>
                                </div>

                                <div class="mb-4">
                                    <label
                                        class="form-label small fw-bold text-muted text-uppercase tracking-wider">Specific
                                        Purpose</label>
                                    <select class="form-select mb-3 shadow-sm" name="purpose_type" id="purpose_select"
                                        required style="transition: opacity 0.2s;"></select>
                                    <textarea class="form-control shadow-sm" name="specific_purpose"
                                        placeholder="Provide more details here..." rows="2" required></textarea>
                                </div>

                                <button type="submit" name="submit_pass_slip"
                                    class="btn w-100 btn-submit-aura text-white">
                                    Submit Request <i class="bi bi-arrow-right-circle ms-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8 col-lg-7">
                    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                        <h5 class="fw-bolder mb-0" style="color: #1e3c72;"><i class="bi bi-clock-history me-2"></i>My
                            History</h5>
                        <span class="badge bg-primary rounded-pill"><?= mysqli_num_rows($history_query) ?>
                            Records</span>
                    </div>

                    <div class="history-container">
                        <?php if (mysqli_num_rows($history_query) > 0): ?>
                            <?php while ($ps = mysqli_fetch_assoc($history_query)):
                                $status_class = $ps['status'];
                                $badge = ($status_class == 0) ? 'bg-warning text-dark' : (($status_class == 1) ? 'bg-success' : 'bg-danger');
                                $lbl = ($status_class == 0) ? 'Pending Review' : (($status_class == 1) ? 'Approved' : 'Declined');

                                $dep_time = ($ps['time_departure'] && $ps['time_departure'] != '00:00:00') ? date("h:i A", strtotime($ps['time_departure'])) : '--:--';
                                $arr_time = ($ps['time_arrived'] && $ps['time_arrived'] != '00:00:00') ? date("h:i A", strtotime($ps['time_arrived'])) : '--:--';
                                $lv_time = ($ps['time_leaving'] && $ps['time_leaving'] != '00:00:00') ? date("h:i A", strtotime($ps['time_leaving'])) : '--:--';
                                $ret_time = ($ps['time_return'] && $ps['time_return'] != '00:00:00') ? date("h:i A", strtotime($ps['time_return'])) : '--:--';

                                $step = isset($ps['tracking_step']) ? (int) $ps['tracking_step'] : 0;

                                // KUNIN ANG LAT & LNG MULA SA LOGS
                                $current_ps_id = $ps['ps_id'];
                                $logs_query = mysqli_query($conn, "SELECT step_number, latitude, longitude FROM ps_tracking_logs_tbl WHERE ps_id = '$current_ps_id'");
                                $coords = [1 => null, 2 => null, 3 => null, 4 => null];
                                while ($log = mysqli_fetch_assoc($logs_query)) {
                                    $coords[$log['step_number']] = ['lat' => $log['latitude'], 'lng' => $log['longitude']];
                                }
                                ?>
                                <div class="history-item status-accent-<?= $status_class ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="fw-bold text-dark mb-1"><?= $ps['destination'] ?></h5>
                                            <p class="text-muted small mb-0"><i
                                                    class="bi bi-calendar-event me-1"></i><?= date("F d, Y", strtotime($ps['ps_date'])) ?>
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <span
                                                class="badge <?= $badge ?> rounded-pill px-3 py-2 shadow-sm"><?= $lbl ?></span>
                                            <?php if (isset($ps['is_tracking_active']) && $ps['is_tracking_active'] == 1): ?>
                                                <div class="mt-2"><span class="badge bg-danger pulse-live rounded-pill"><i
                                                            class="bi bi-geo-alt me-1"></i>Live</span></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <span class="badge bg-light text-secondary border me-1"><?= $ps['issued_for'] ?></span>
                                        <span class="text-secondary small"><?= $ps['specific_purpose'] ?></span>
                                    </div>

                                    <div class="timeline-grid mb-3">
                                        <div class="time-box">
                                            <i class="bi bi-box-arrow-right text-primary fs-5 mt-1"></i>
                                            <div>
                                                <span class="d-block text-muted"
                                                    style="font-size: 10px; font-weight: 700;">DEPARTED</span>
                                                <b class="text-dark d-block"><?= $dep_time ?></b>
                                                <?php if ($coords[1]): ?>
                                                    <a href="https://www.google.com/maps?q=<?= $coords[1]['lat'] ?>,<?= $coords[1]['lng'] ?>"
                                                        target="_blank" class="text-primary get-address"
                                                        data-lat="<?= $coords[1]['lat'] ?>" data-lng="<?= $coords[1]['lng'] ?>">
                                                        <i class="bi bi-geo-alt"></i> Loading map...
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="time-box">
                                            <i class="bi bi-geo-alt-fill text-success fs-5 mt-1"></i>
                                            <div>
                                                <span class="d-block text-muted"
                                                    style="font-size: 10px; font-weight: 700;">ARRIVED</span>
                                                <b class="text-dark d-block"><?= $arr_time ?></b>
                                                <?php if ($coords[2]): ?>
                                                    <a href="https://www.google.com/maps?q=<?= $coords[2]['lat'] ?>,<?= $coords[2]['lng'] ?>"
                                                        target="_blank" class="text-success get-address"
                                                        data-lat="<?= $coords[2]['lat'] ?>" data-lng="<?= $coords[2]['lng'] ?>">
                                                        <i class="bi bi-geo-alt"></i> Loading map...
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="time-box">
                                            <i class="bi bi-box-arrow-left text-warning fs-5 mt-1"></i>
                                            <div>
                                                <span class="d-block text-muted" style="font-size: 10px; font-weight: 700;">LEFT
                                                    DEST</span>
                                                <b class="text-dark d-block"><?= $lv_time ?></b>
                                                <?php if ($coords[3]): ?>
                                                    <a href="https://www.google.com/maps?q=<?= $coords[3]['lat'] ?>,<?= $coords[3]['lng'] ?>"
                                                        target="_blank" class="text-warning get-address"
                                                        data-lat="<?= $coords[3]['lat'] ?>" data-lng="<?= $coords[3]['lng'] ?>">
                                                        <i class="bi bi-geo-alt"></i> Loading map...
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="time-box">
                                            <i class="bi bi-house-check-fill text-danger fs-5 mt-1"></i>
                                            <div>
                                                <span class="d-block text-muted"
                                                    style="font-size: 10px; font-weight: 700;">RETURNED</span>
                                                <b class="text-dark d-block"><?= $ret_time ?></b>
                                                <?php if ($coords[4]): ?>
                                                    <a href="https://www.google.com/maps?q=<?= $coords[4]['lat'] ?>,<?= $coords[4]['lng'] ?>"
                                                        target="_blank" class="text-danger get-address"
                                                        data-lat="<?= $coords[4]['lat'] ?>" data-lng="<?= $coords[4]['lng'] ?>">
                                                        <i class="bi bi-geo-alt"></i> Loading map...
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="action-section">
                                        <?php if ($ps['status'] == 1 && $ps['ps_date'] == $today): ?>
                                            <?php if ($step == 0): ?>
                                                <button type="button" class="btn btn-outline-primary rounded-pill w-100 fw-bold py-2"
                                                    onclick="updateTravelStep(<?= $ps['ps_id'] ?>, 'depart', 'Record Departure? This logs your exit from campus.')">
                                                    <i class="bi bi-box-arrow-right me-2"></i> Log Departure
                                                </button>
                                            <?php elseif ($step == 1): ?>
                                                <button type="button" class="btn btn-success rounded-pill w-100 fw-bold py-2 shadow-sm"
                                                    onclick="updateTravelStep(<?= $ps['ps_id'] ?>, 'arrive', 'Record Arrival? This logs that you reached your destination.')">
                                                    <i class="bi bi-geo-alt-fill me-2"></i> I Have Arrived
                                                </button>
                                            <?php elseif ($step == 2): ?>
                                                <button type="button" class="btn btn-warning rounded-pill w-100 fw-bold py-2 shadow-sm"
                                                    onclick="updateTravelStep(<?= $ps['ps_id'] ?>, 'leave_dest', 'Leaving destination? This logs your departure back to campus.')">
                                                    <i class="bi bi-box-arrow-left me-2"></i> Leave Destination
                                                </button>
                                            <?php elseif ($step == 3): ?>
                                                <button type="button" class="btn btn-danger rounded-pill w-100 fw-bold py-2 shadow-sm"
                                                    onclick="updateTravelStep(<?= $ps['ps_id'] ?>, 'return', 'Record Return? This ends your pass slip tracking.')">
                                                    <i class="bi bi-house-check-fill me-2"></i> Back to Campus
                                                </button>
                                            <?php elseif ($step == 4): ?>
                                                <div class="alert alert-success m-0 py-2 text-center rounded-pill" role="alert"><i
                                                        class="bi bi-check-circle-fill me-2"></i>Travel Completed</div>
                                            <?php endif; ?>
                                        <?php elseif ($ps['status'] == 1 && $step == 4): ?>
                                            <div class="alert alert-success m-0 py-2 text-center rounded-pill" role="alert"><i
                                                    class="bi bi-check-circle-fill me-2"></i>Travel Completed</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5 bg-white rounded-4 shadow-sm border border-light">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 fw-bold text-secondary">No Pass Slips Yet</h5>
                                <p class="text-muted small">Your submitted requests will appear here.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../template/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include '../template/script.php'; ?>

    <script>
        // SMART TRACKING SCRIPT
        function updateTravelStep(psId, actionType, confirmMessage) {
            if (!navigator.geolocation) {
                Swal.fire('Error', 'Location tracking is not supported by your browser.', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Action',
                text: confirmMessage,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1e3c72',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Yes, proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Acquiring Location...', text: 'Please wait', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });

                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;

                            fetch('ps_tracking_handler.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `ps_id=${psId}&action=${actionType}&lat=${lat}&lng=${lng}`
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        sessionStorage.setItem('swal_type', 'success');
                                        sessionStorage.setItem('swal_msg', data.message);
                                        window.location.reload();
                                    } else {
                                        Swal.fire('Error', data.message, 'error');
                                    }
                                })
                                .catch(err => Swal.fire('Error', 'Network Error: Could not record travel step.', 'error'));
                        },
                        function (error) {
                            Swal.fire('Warning', 'Please ALLOW Location Access in your browser settings to log this step.', 'warning');
                        },
                        { enableHighAccuracy: true }
                    );
                }
            });
        }

        // Handle success notifications cleanly
        document.addEventListener("DOMContentLoaded", () => {
            const type = sessionStorage.getItem('swal_type');
            const msg = sessionStorage.getItem('swal_msg');
            if (type && msg && typeof Swal !== 'undefined') {
                Swal.fire({ icon: type, title: 'Success', text: msg, timer: 3000, showConfirmButton: false });
                sessionStorage.removeItem('swal_type');
                sessionStorage.removeItem('swal_msg');
            }
        });

        // REVERSE GEOCODING API (Babasahin ang Lat/Lng para maging address)
        async function loadLocationNames() {
            const locationElements = document.querySelectorAll('.get-address');

            for (let i = 0; i < locationElements.length; i++) {
                const el = locationElements[i];
                const lat = el.getAttribute('data-lat');
                const lng = el.getAttribute('data-lng');

                if (lat && lng) {
                    try {
                        // 1-sec delay to respect openstreetmap API limits
                        await new Promise(resolve => setTimeout(resolve, 1000));

                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=14`);
                        const data = await response.json();

                        if (data && data.address) {
                            let placeName = data.address.village || data.address.town || data.address.city || data.address.county || "View Map";
                            el.innerHTML = `<i class="bi bi-geo-alt"></i> ${placeName} <i class="bi bi-box-arrow-up-right ms-1" style="font-size:8px;"></i>`;
                        } else {
                            el.innerHTML = `<i class="bi bi-geo-alt"></i> View on Map`;
                        }
                    } catch (error) {
                        el.innerHTML = `<i class="bi bi-map"></i> Open Maps`;
                    }
                }
            }
        }
        document.addEventListener("DOMContentLoaded", loadLocationNames);

        // Background location updater
        <?php
        $active_ps = mysqli_query($conn, "SELECT ps_id FROM pass_slip_tbl WHERE employee_id = '$employee_id' AND is_tracking_active = 1 LIMIT 1");
        if ($active_ps && mysqli_num_rows($active_ps) > 0) {
            $ps_data = mysqli_fetch_assoc($active_ps)['ps_id'];
            echo "
            setInterval(() => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        fetch('ps_tracking_handler.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'ps_id={$ps_data}&action=location&lat=' + pos.coords.latitude + '&lng=' + pos.coords.longitude
                        });
                    });
                }
            }, 30000);
            ";
        }
        ?>
    </script>
</body>

</html>