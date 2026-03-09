<?php
include './../config.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include '../template/header.php'; ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body style="background-color: #f4f7f6;">
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <?php

    $employee_id = $_SESSION['employee_id'];
    $today = date("Y-m-d");

    // 1. Kunin ang mga Stats para sa Chart at Cards
// TAs
    $ta_q = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM ta_tbl t JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id WHERE tp.employee_id = '$employee_id' GROUP BY status");
    $ta_stats = ['pending' => 0, 'approved' => 0, 'declined' => 0];
    while ($r = mysqli_fetch_assoc($ta_q)) {
        if ($r['status'] == 0 || $r['status'] == 1)
            $ta_stats['pending'] += $r['count'];
        if ($r['status'] == 2 || $r['status'] == 4)
            $ta_stats['approved'] += $r['count'];
        if ($r['status'] == 99 || $r['status'] == 11)
            $ta_stats['declined'] += $r['count'];
    }

    // Pass Slips
    $ps_q = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM pass_slip_tbl WHERE employee_id = '$employee_id' GROUP BY status");
    $ps_stats = ['pending' => 0, 'approved' => 0, 'declined' => 0];
    while ($r = mysqli_fetch_assoc($ps_q)) {
        if ($r['status'] == 0)
            $ps_stats['pending'] += $r['count'];
        if ($r['status'] == 1)
            $ps_stats['approved'] += $r['count'];
        if ($r['status'] == 2)
            $ps_stats['declined'] += $r['count'];
    }

    $total_approved = $ta_stats['approved'] + $ps_stats['approved'];
    $total_pending = $ta_stats['pending'] + $ps_stats['pending'];

    // 2. CHECK FOR ACTIVE TRAVEL TODAY (TA)
    $active_ta_query = mysqli_query($conn, "
    SELECT t.*, 'TA' as travel_type 
    FROM ta_tbl t 
    JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id 
    WHERE tp.employee_id = '$employee_id' 
    AND t.status = 2 
    AND ('$today' BETWEEN t.travel_date AND t.return_date)
    AND (t.tracking_step < 4)
    LIMIT 1
");
    $active_travel = mysqli_fetch_assoc($active_ta_query);

    // 3. IF NO ACTIVE TA, CHECK FOR ACTIVE PASS SLIP TODAY
    if (!$active_travel) {
        $active_ps_query = mysqli_query($conn, "
        SELECT *, 'PS' as travel_type 
        FROM pass_slip_tbl 
        WHERE employee_id = '$employee_id' 
        AND status = 1 
        AND ps_date = '$today'
        AND (tracking_step < 4)
        LIMIT 1
    ");
        $active_travel = mysqli_fetch_assoc($active_ps_query);
    }

    // 4. KUNIN ANG RECENT HISTORY (TA & PS Combined)
    $recent_history_query = mysqli_query($conn, "
    (SELECT 'Travel Authority' as type, destination, travel_date as t_date, status, submitted_at FROM ta_tbl t JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id WHERE tp.employee_id = '$employee_id')
    UNION ALL
    (SELECT 'Pass Slip' as type, destination, ps_date as t_date, status, created_at as submitted_at FROM pass_slip_tbl WHERE employee_id = '$employee_id')
    ORDER BY submitted_at DESC LIMIT 4
");
    ?>
    <style>
        /* Aura & Professional Dashboard Theme */
        .dash-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 24px;
            padding: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(30, 60, 114, 0.2);
            position: relative;
            overflow: hidden;
        }

        .dash-header::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .dash-header h2 {
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        /* Quick Stat Cards */
        .stat-card {
            background: #fff;
            border-radius: 20px;
            border: none;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.03);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            padding: 20px;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.06);
        }

        .stat-icon {
            width: 55px;
            height: 55px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
            flex-shrink: 0;
        }

        /* Modern Container Cards */
        .glass-card {
            background: #fff;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            border: 1px solid #edf2f7;
            height: 100%;
        }

        .card-title-custom {
            font-size: 16px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Interactive Active Travel Highlight */
        .active-travel-box {
            border: 2px solid #10b981;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.15);
            background: #fff;
        }

        .active-header {
            background: #10b981;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* INTERACTIVE MAP CONTAINER STYLES */
        .mini-map-wrapper {
            position: relative;
            height: 280px;
            width: 100%;
            background: #e2e8f0;
            overflow: hidden;
        }

        #miniMap {
            height: 100%;
            width: 100%;
            z-index: 1;
        }

        /* Floating Recenter Button */
        .btn-recenter {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 400;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e3c72;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            font-size: 18px;
        }

        .btn-recenter:active {
            transform: scale(0.9);
        }

        /* Glassmorphism Floating Address */
        .floating-address {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 400;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 800;
            color: #1e293b;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            white-space: nowrap;
            border: 1px solid rgba(255, 255, 255, 0.5);
            max-width: 90%;
            overflow: hidden;
        }

        /* Pulse Radar Map Marker */
        .radar-marker {
            width: 20px;
            height: 20px;
            background: #10b981;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .radar-marker::after {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border-radius: 50%;
            border: 2px solid #10b981;
            animation: radarPulse 2s linear infinite;
            opacity: 0;
        }

        @keyframes radarPulse {
            0% {
                transform: scale(0.5);
                opacity: 1;
                border-width: 3px;
            }

            100% {
                transform: scale(2);
                opacity: 0;
                border-width: 0px;
            }
        }

        /* Action Buttons */
        .btn-update-loc {
            border-radius: 16px;
            font-weight: 800;
            padding: 15px;
            font-size: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: 0.2s;
        }

        .btn-update-loc:active {
            transform: scale(0.96);
        }

        .pulse-indicator {
            width: 12px;
            height: 12px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.3);
            animation: pulseWhite 2s infinite;
        }

        @keyframes pulseWhite {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.6);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(255, 255, 255, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }

        /* Recent History Timeline */
        .recent-timeline {
            border-left: 2px solid #e2e8f0;
            margin-left: 10px;
            padding-left: 20px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-marker {
            position: absolute;
            left: -27px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #3b82f6;
        }

        .timeline-date {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
        }

        .timeline-content {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
        }

        .timeline-desc {
            font-size: 12px;
            color: #64748b;
        }
    </style>

    <main id="main" class="main">
        <section class="section dashboard">

            <div class="dash-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="m-0">Dashboard Overview</h2>
                        <p class="text-white-50 m-0 mt-1"><i class="bi bi-clock me-1"></i> Today is
                            <?= date('l, F d, Y') ?>
                        </p>
                    </div>
                    <div class="d-none d-md-block">
                        <a href="request_ta"
                            class="btn btn-light rounded-pill fw-bold text-primary px-4 shadow-sm"><i
                                class="bi bi-plus-lg me-1"></i> New Request</a>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-7 col-lg-12">
                    <div class="row g-3">
                        <div class="col-sm-6 col-12">
                            <div class="stat-card">
                                <div class="stat-icon bg-success bg-opacity-10 text-success"><i
                                        class="bi bi-check-circle-fill"></i></div>
                                <div>
                                    <span class="text-muted small fw-bold text-uppercase"
                                        style="letter-spacing: 1px;">Approved Travels</span>
                                    <h3 class="fw-bolder m-0 text-dark"><?= $total_approved ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-12">
                            <div class="stat-card">
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i
                                        class="bi bi-hourglass-split"></i></div>
                                <div>
                                    <span class="text-muted small fw-bold text-uppercase"
                                        style="letter-spacing: 1px;">Pending Approval</span>
                                    <h3 class="fw-bolder m-0 text-dark"><?= $total_pending ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex d-md-none gap-2 mt-3">
                        <a href="create_ta.php" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm"><i
                                class="bi bi-briefcase me-1"></i> File TA</a>
                        <a href="pass_slip.php"
                            class="btn btn-outline-primary w-100 rounded-pill fw-bold bg-white shadow-sm"><i
                                class="bi bi-ticket me-1"></i> Pass Slip</a>
                    </div>
                </div>

                <div class="col-xl-5 col-lg-12">
                    <div class="glass-card d-flex flex-column">
                        <h6 class="card-title-custom"><i class="bi bi-pie-chart-fill text-primary me-2"></i>My Request
                            Analytics</h6>
                        <div class="flex-grow-1" style="position: relative; height: 180px;">
                            <canvas id="travelChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">

                <div class="col-xl-7 col-lg-12">
                    <div class="glass-card p-0 overflow-hidden border-0">
                        <div class="p-4 pb-3">
                            <h6 class="card-title-custom m-0"><i class="bi bi-broadcast me-2 text-danger"></i>Live
                                Action Center</h6>
                        </div>

                        <?php if ($active_travel):
                            $is_ta = ($active_travel['travel_type'] == 'TA');
                            $travel_id = $is_ta ? $active_travel['ta_id'] : $active_travel['ps_id'];
                            $step = (int) $active_travel['tracking_step'];
                            $destination = $active_travel['destination'];
                            $handler_url = $is_ta ? 'update_milestone.php' : 'ps_tracking_handler.php';
                            ?>
                            <div class="active-travel-box m-3 mt-0 border-0 shadow-lg">
                                <div class="active-header">
                                    <div>
                                        <h6 class="m-0 fw-bolder" style="font-size: 14px; letter-spacing: 0.5px;">
                                            <?= $is_ta ? 'TRAVEL AUTHORITY' : 'PASS SLIP' ?> ACTIVE
                                        </h6>
                                        <small style="opacity: 0.9;"><i class="bi bi-pin-map-fill me-1"></i>Target:
                                            <?= $destination ?></small>
                                    </div>
                                    <div class="pulse-indicator"></div>
                                </div>

                                <div class="mini-map-wrapper">
                                    <div id="miniMap"></div>

                                    <button class="btn-recenter" onclick="recenterMap()" title="Recenter to my location">
                                        <i class="bi bi-crosshair"></i>
                                    </button>

                                    <div class="floating-address">
                                        <i class="bi bi-geo-alt-fill text-danger me-2 fs-6"></i>
                                        <span id="current-address" class="text-truncate">Acquiring signal...</span>
                                    </div>
                                </div>

                                <div class="p-4 text-center" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                                    <p class="text-muted small fw-bold mb-3 text-uppercase" style="letter-spacing: 1px;">
                                        Update Your Travel Status</p>

                                    <?php if ($step == 0): ?>
                                        <button class="btn btn-primary w-100 btn-update-loc"
                                            onclick="logAction(<?= $travel_id ?>, 1, 'depart', '<?= $handler_url ?>', '<?= $active_travel['travel_type'] ?>')"><i
                                                class="bi bi-box-arrow-right me-2 fs-5"></i> Log Departure from Campus</button>
                                    <?php elseif ($step == 1): ?>
                                        <button class="btn btn-success w-100 btn-update-loc"
                                            onclick="logAction(<?= $travel_id ?>, 2, 'arrive', '<?= $handler_url ?>', '<?= $active_travel['travel_type'] ?>')"><i
                                                class="bi bi-geo-alt-fill me-2 fs-5"></i> I Have Arrived at Destination</button>
                                    <?php elseif ($step == 2): ?>
                                        <button class="btn btn-warning text-dark w-100 btn-update-loc"
                                            onclick="logAction(<?= $travel_id ?>, 3, 'leave_dest', '<?= $handler_url ?>', '<?= $active_travel['travel_type'] ?>')"><i
                                                class="bi bi-sign-turn-left-fill me-2 fs-5"></i> Leaving Destination</button>
                                    <?php elseif ($step == 3): ?>
                                        <button class="btn btn-danger w-100 btn-update-loc"
                                            onclick="logAction(<?= $travel_id ?>, 4, 'return', '<?= $handler_url ?>', '<?= $active_travel['travel_type'] ?>')"><i
                                                class="bi bi-house-check-fill me-2 fs-5"></i> Returned to Campus</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 m-3 mt-0 border border-dashed rounded-4"
                                style="border-color: #cbd5e1 !important; background: #f8fafc;">
                                <i class="bi bi-cup-hot text-muted" style="font-size: 3.5rem;"></i>
                                <h5 class="mt-3 fw-bold text-secondary">No Travel Scheduled Today</h5>
                                <p class="text-muted small px-4">Your calendar is clear. You are currently stationed at the
                                    office.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-xl-5 col-lg-12">
                    <div class="glass-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="card-title-custom m-0"><i
                                    class="bi bi-clock-history text-secondary me-2"></i>Recent Activity</h6>
                            <a href="my_travels.php" class="text-primary small fw-bold text-decoration-none">View
                                All</a>
                        </div>

                        <div class="recent-timeline">
                            <?php if (mysqli_num_rows($recent_history_query) > 0): ?>
                                <?php while ($hist = mysqli_fetch_assoc($recent_history_query)):
                                    $hist_status = (int) $hist['status'];
                                    $badge_color = ($hist_status == 2 || $hist_status == 4 || $hist_status == 1) ? 'text-success' : (($hist_status == 0) ? 'text-warning' : 'text-danger');
                                    $icon_color = ($hist['type'] == 'Pass Slip') ? '#f59e0b' : '#3b82f6';
                                    ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker" style="border-color: <?= $icon_color ?>;"></div>
                                        <div class="timeline-date"><?= date('M d, Y', strtotime($hist['submitted_at'])) ?></div>
                                        <div class="timeline-content"><?= $hist['destination'] ?></div>
                                        <div class="timeline-desc">
                                            <span class="badge bg-light text-secondary border me-1"><?= $hist['type'] ?></span>
                                            For: <span class="fw-bold"><?= date('M d', strtotime($hist['t_date'])) ?></span> •
                                            <i class="bi bi-circle-fill <?= $badge_color ?>" style="font-size: 8px;"></i>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted small fst-italic">No past travel requests found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const ctx = document.getElementById('travelChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Pending', 'Declined'],
                    datasets: [{
                        data: [<?= $total_approved ?>, <?= $total_pending ?>, <?= ($ta_stats['declined'] + $ps_stats['declined']) ?>],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 5
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 10, font: { size: 11, family: 'Inter' } } }
                    }
                }
            });
        });
    </script>

    <?php if ($active_travel): ?>
        <script>
            // Zoom control hidden for cleaner mobile look
            var map = L.map('miniMap', { zoomControl: false, attributionControl: false }).setView([13.3858, 121.9563], 15);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(map);

            var userMarker;
            var currentUserLat = null;
            var currentUserLng = null;

            // Modern Glowing Radar Marker
            var radarIcon = L.divIcon({
                className: 'custom-radar',
                html: `<div class="radar-marker"></div>`,
                iconSize: [20, 20], iconAnchor: [10, 10]
            });

            // Function para i-center ang map kapag pinindot ang Recenter Button
            function recenterMap() {
                if (currentUserLat && currentUserLng) {
                    map.flyTo([currentUserLat, currentUserLng], 17, { animate: true, duration: 1 });
                } else {
                    Swal.fire({ title: 'Locating...', text: 'Still acquiring your GPS signal.', timer: 1500, showConfirmButton: false, icon: 'info' });
                }
            }

            // Automatic Background Update
            function updateMiniMap() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(async pos => {
                        currentUserLat = pos.coords.latitude;
                        currentUserLng = pos.coords.longitude;

                        if (!userMarker) {
                            userMarker = L.marker([currentUserLat, currentUserLng], { icon: radarIcon }).addTo(map);
                            map.setView([currentUserLat, currentUserLng], 16, { animate: true });
                        } else {
                            userMarker.setLatLng([currentUserLat, currentUserLng]);
                        }

                        // Reverse Geocoding para sa Address
                        try {
                            let res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${currentUserLat}&lon=${currentUserLng}&zoom=16`);
                            let data = await res.json();
                            let address = data.address.road || data.address.village || data.address.town || data.address.city || "Unknown Street";
                            document.getElementById('current-address').innerText = address;
                        } catch (e) {
                            document.getElementById('current-address').innerText = "GPS Fixed - Locating Name...";
                        }
                    });
                }
            }

            updateMiniMap();
            setInterval(updateMiniMap, 8000); // 8 seconds refresh

            // UNIVERSAL SUBMIT LOGGER
            function logAction(id, stepNum, actionString, urlHandler, type) {
                if (!navigator.geolocation) { Swal.fire('Error', 'No GPS support on this device.', 'error'); return; }

                let btnNames = ['Log Departure', 'Log Arrival', 'Leave Destination', 'Return to Campus'];
                Swal.fire({
                    title: btnNames[stepNum - 1] + '?',
                    text: "Your location and time will be recorded instantly.",
                    icon: 'question', showCancelButton: true,
                    confirmButtonColor: '#10b981', confirmButtonText: 'Confirm & Submit',
                    borderRadius: '20px'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({ title: 'Acquiring Signal...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                let bodyData = (type === 'TA')
                                    ? `ta_id=${id}&step=${stepNum}&action_name=${encodeURIComponent(actionString)}&lat=${pos.coords.latitude}&lng=${pos.coords.longitude}`
                                    : `ps_id=${id}&action=${actionString}&lat=${pos.coords.latitude}&lng=${pos.coords.longitude}`;

                                fetch(urlHandler, {
                                    method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: bodyData
                                })
                                    .then(res => type === 'TA' ? res.json() : res.text())
                                    .then(data => {
                                        let isSuccess = (type === 'TA') ? (data.status === 'success') : data.includes('success');
                                        if (isSuccess) {
                                            Swal.fire({ title: 'Recorded!', text: 'Your status has been updated.', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload());
                                        } else Swal.fire('Error', 'Failed to log.', 'error');
                                    });
                            },
                            (err) => Swal.fire('GPS Error', 'Please enable location services in your phone settings.', 'error'),
                            { enableHighAccuracy: true }
                        );
                    }
                });
            }
        </script>
    <?php endif; ?>
</body>

</html>