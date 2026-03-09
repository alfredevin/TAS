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

    // Dynamic Greeting Logic
    $hour = date('H');
    $greeting = ($hour < 12) ? 'Good morning' : (($hour < 17) ? 'Good afternoon' : 'Good evening');

    // 1. Kunin ang mga Stats para sa Chart at Cards
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
    $total_declined = $ta_stats['declined'] + $ps_stats['declined'];

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

    // 4. KUNIN ANG RECENT HISTORY
    $recent_history_query = mysqli_query($conn, "
    (SELECT 'Travel Authority' as type, destination, travel_date as t_date, status, submitted_at FROM ta_tbl t JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id WHERE tp.employee_id = '$employee_id')
    UNION ALL
    (SELECT 'Pass Slip' as type, destination, ps_date as t_date, status, created_at as submitted_at FROM pass_slip_tbl WHERE employee_id = '$employee_id')
    ORDER BY submitted_at DESC LIMIT 4
");
    ?>
    <style>
        :root {
            --primary-maroon: #800000;
            --gradient-maroon: linear-gradient(135deg, #800000 0%, #b91c1c 100%);
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        }

        /* Premium Welcome Banner */
        .welcome-banner {
            background: var(--gradient-maroon);
            border-radius: 24px;
            padding: 35px 30px;
            color: white;
            box-shadow: 0 15px 35px rgba(128, 0, 36, 0.2);
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
        }

        /* Abstract shapes for banner */
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -30px;
            right: -20px;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            bottom: -50px;
            right: 80px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .welcome-title {
            font-weight: 800;
            font-size: 1.8rem;
            letter-spacing: -0.5px;
            z-index: 1;
            position: relative;
        }

        .welcome-subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
            z-index: 1;
            position: relative;
        }

        /* Sleek Glass Cards */
        .premium-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: var(--card-shadow);
            padding: 25px;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .premium-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06);
        }

        .card-heading {
            font-size: 15px;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .card-heading i {
            font-size: 18px;
            margin-right: 10px;
        }

        /* Modern Stat Item */
        .stat-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px dashed #e2e8f0;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-icon-box {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-right: 15px;
            flex-shrink: 0;
        }

        /* Active Tracker Widget */
        .active-tracker-container {
            border-radius: 20px;
            overflow: hidden;
            border: 2px solid #10b981;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.15);
        }

        .tracker-header {
            background: #10b981;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .map-box {
            height: 260px;
            width: 100%;
            background: #e2e8f0;
            position: relative;
        }

        #liveMap {
            height: 100%;
            width: 100%;
            z-index: 1;
        }

        /* Frosted Glass Address Overlay */
        .glass-address {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            color: #1e293b;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
            white-space: nowrap;
            z-index: 10;
            max-width: 90%;
        }

        /* Action Button */
        .btn-action-giant {
            border-radius: 16px;
            font-weight: 800;
            padding: 18px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-action-giant:active {
            transform: scale(0.97);
        }

        /* Pulse Animations */
        .live-dot {
            width: 12px;
            height: 12px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.3);
            animation: pulseLive 2s infinite;
        }

        @keyframes pulseLive {
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

        .radar-pin {
            width: 22px;
            height: 22px;
            background: #10b981;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .radar-pin::after {
            content: '';
            position: absolute;
            top: -12px;
            left: -12px;
            right: -12px;
            bottom: -12px;
            border-radius: 50%;
            border: 2px solid #10b981;
            animation: radarWave 2s linear infinite;
            opacity: 0;
        }

        @keyframes radarWave {
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

        /* Empty State */
        .empty-state-box {
            text-align: center;
            padding: 40px 20px;
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 20px;
        }

        .empty-icon-wrapper {
            width: 80px;
            height: 80px;
            background: #fff1f2;
            color: var(--primary-maroon);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            margin: 0 auto 20px auto;
            box-shadow: 0 5px 15px rgba(128, 0, 36, 0.1);
        }

        /* Timeline */
        .timeline-wrapper {
            border-left: 2px solid #e2e8f0;
            margin-left: 15px;
            padding-left: 20px;
        }

        .tl-item {
            position: relative;
            margin-bottom: 25px;
        }

        .tl-item:last-child {
            margin-bottom: 0;
        }

        .tl-dot {
            position: absolute;
            left: -27px;
            top: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #cbd5e1;
        }

        .tl-date {
            font-size: 10px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            display: block;
        }

        .tl-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
            margin-bottom: 3px;
        }

        .tl-subtitle {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }
    </style>

    <main id="main" class="main">
        <section class="section dashboard">

            <div class="welcome-banner">
                <h2 class="welcome-title"><?= $greeting ?>, <?= explode(' ', $_SESSION['fullname'] ?? 'User')[0] ?>! 👋
                </h2>
                <p class="welcome-subtitle mb-0"><i class="bi bi-calendar-event me-1"></i> Today is
                    <?= date('l, F d, Y') ?>
                </p>
            </div>

            <div class="row g-4">

                <div class="col-xl-7 col-lg-12">
                    <div class="premium-card p-0 overflow-hidden border-0">
                        <div class="p-4 pb-0">
                            <h6 class="card-heading"><i class="bi bi-broadcast text-danger"></i> Live Action Center</h6>
                        </div>

                        <?php if ($active_travel):
                            $is_ta = ($active_travel['travel_type'] == 'TA');
                            $travel_id = $is_ta ? $active_travel['ta_id'] : $active_travel['ps_id'];
                            $step = (int) $active_travel['tracking_step'];
                            $destination = $active_travel['destination'];
                            $handler_url = $is_ta ? 'update_milestone.php' : 'ps_tracking_handler.php';
                            ?>
                            <div class="active-tracker-container m-4 mt-2">
                                <div class="tracker-header">
                                    <div>
                                        <h6 class="m-0 fw-bolder" style="font-size: 13px; letter-spacing: 0.5px;">
                                            <?= $is_ta ? 'TRAVEL AUTHORITY' : 'PASS SLIP' ?> ACTIVE
                                        </h6>
                                        <div class="small mt-1" style="opacity: 0.9; font-weight: 600;"><i
                                                class="bi bi-pin-map-fill me-1"></i><?= $destination ?></div>
                                    </div>
                                    <div class="live-dot"></div>
                                </div>

                                <div class="map-box">
                                    <div id="liveMap"></div>
                                    <button class="btn-recenter" onclick="recenterMap()"
                                        style="position: absolute; top:10px; right:10px; z-index:400; background:white; border:none; border-radius:50%; width:36px; height:36px; box-shadow:0 2px 5px rgba(0,0,0,0.2);">
                                        <i class="bi bi-crosshair text-primary"></i>
                                    </button>
                                    <div class="glass-address text-truncate">
                                        <i class="bi bi-geo-alt-fill text-danger me-2"></i> <span
                                            id="current-address">Detecting Location...</span>
                                    </div>
                                </div>

                                <div class="p-4" style="background: #f8fafc;">
                                    <?php if ($step == 0): ?>
                                        <button class="btn btn-primary w-100 btn-action-giant shadow-sm"
                                            onclick="logAction(<?= $travel_id ?>, 1, 'depart', '<?= $handler_url ?>', '<?= $active_travel['travel_type'] ?>')">
                                            <i class="bi bi-box-arrow-right"></i> Log Departure
                                        </button>
                                    <?php elseif ($step == 1): ?>
                                        <button class="btn btn-success w-100 btn-action-giant shadow-sm"
                                            onclick="logAction(<?= $travel_id ?>, 2, 'arrive', '<?= $handler_url ?>', '<?= $active_travel['travel_type'] ?>')">
                                            <i class="bi bi-geo-alt-fill"></i> Arrived at Target
                                        </button>
                                    <?php elseif ($step == 2): ?>
                                        <button class="btn btn-warning w-100 btn-action-giant shadow-sm text-dark"
                                            onclick="logAction(<?= $travel_id ?>, 3, 'leave_dest', '<?= $handler_url ?>', '<?= $active_travel['travel_type'] ?>')">
                                            <i class="bi bi-sign-turn-left-fill"></i> Leaving Target
                                        </button>
                                    <?php elseif ($step == 3): ?>
                                        <button class="btn btn-danger w-100 btn-action-giant shadow-sm"
                                            onclick="logAction(<?= $travel_id ?>, 4, 'return', '<?= $handler_url ?>', '<?= $active_travel['travel_type'] ?>')">
                                            <i class="bi bi-house-check-fill"></i> Returned to Campus
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="empty-state-box m-4 mt-2">
                                <div class="empty-icon-wrapper">
                                    <i class="bi bi-building-check"></i>
                                </div>
                                <h5 class="fw-bolder text-dark mb-2">You are at the Office</h5>
                                <p class="text-muted small px-3 mb-4">No travels scheduled for today. Need to step out for
                                    official business?</p>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="request_ta.php" class="btn btn-primary rounded-pill fw-bold px-4 shadow-sm"><i
                                            class="bi bi-briefcase me-1"></i> File TA</a>
                                    <a href="pass_slip.php"
                                        class="btn btn-outline-primary rounded-pill fw-bold px-4 bg-white"><i
                                            class="bi bi-ticket me-1"></i> Pass Slip</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-xl-5 col-lg-12 d-flex flex-column gap-4">

                    <div class="premium-card">
                        <h6 class="card-heading"><i class="bi bi-pie-chart-fill text-primary"></i> Travel Overview</h6>

                        <div class="row align-items-center">
                            <div class="col-6">
                                <div class="stat-item">
                                    <div class="stat-icon-box bg-success bg-opacity-10 text-success"><i
                                            class="bi bi-check-circle-fill"></i></div>
                                    <div>
                                        <div class="fw-bolder text-dark fs-4 lh-1"><?= $total_approved ?></div>
                                        <div class="text-muted small fw-bold text-uppercase mt-1"
                                            style="font-size:9px;">Approved</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon-box bg-warning bg-opacity-10 text-warning"><i
                                            class="bi bi-hourglass-split"></i></div>
                                    <div>
                                        <div class="fw-bolder text-dark fs-4 lh-1"><?= $total_pending ?></div>
                                        <div class="text-muted small fw-bold text-uppercase mt-1"
                                            style="font-size:9px;">Pending</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 position-relative" style="height: 140px;">
                                <canvas id="travelChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="premium-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="card-heading m-0"><i class="bi bi-clock-history text-secondary"></i> Recent Logs
                            </h6>
                            <a href="my_travels.php" class="text-primary small fw-bold text-decoration-none">See All</a>
                        </div>

                        <div class="timeline-wrapper">
                            <?php if (mysqli_num_rows($recent_history_query) > 0): ?>
                                <?php while ($hist = mysqli_fetch_assoc($recent_history_query)):
                                    $status = (int) $hist['status'];
                                    $is_success = ($status == 2 || $status == 4 || $status == 1);
                                    $borderColor = $is_success ? '#10b981' : ($status == 0 ? '#f59e0b' : '#ef4444');
                                    ?>
                                    <div class="tl-item">
                                        <div class="tl-dot" style="border-color: <?= $borderColor ?>;"></div>
                                        <span class="tl-date"><?= date('M d, Y', strtotime($hist['submitted_at'])) ?></span>
                                        <div class="tl-title text-truncate" title="<?= $hist['destination'] ?>">
                                            <?= $hist['destination'] ?>
                                        </div>
                                        <div class="tl-subtitle">
                                            <span class="badge bg-light text-dark border me-1"><?= $hist['type'] ?></span>
                                            <?= $is_success ? '<span class="text-success fw-bold"><i class="bi bi-check"></i> Approved</span>' : ($status == 0 ? '<span class="text-warning fw-bold"><i class="bi bi-hourglass"></i> Pending</span>' : '<span class="text-danger fw-bold"><i class="bi bi-x"></i> Declined</span>') ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-muted small fst-italic">No past travel history available.</div>
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
                        data: [<?= $total_approved ?>, <?= $total_pending ?>, <?= $total_declined ?>],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 5
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '75%',
                    plugins: { legend: { display: false } } // Hidden legend for cleaner look, data is beside it
                }
            });
        });
    </script>

    <?php if ($active_travel): ?>
        <script>
            var map = L.map('liveMap', { zoomControl: false, attributionControl: false }).setView([13.3858, 121.9563], 15);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(map);

            var userMarker;
            var currentLat = null, currentLng = null;

            var radarIcon = L.divIcon({
                className: 'custom-radar', html: `<div class="radar-pin"></div>`, iconSize: [22, 22], iconAnchor: [11, 11]
            });

            function recenterMap() {
                if (currentLat && currentLng) map.flyTo([currentLat, currentLng], 17, { animate: true });
            }

            function updateMapLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(async pos => {
                        currentLat = pos.coords.latitude; currentLng = pos.coords.longitude;

                        if (!userMarker) {
                            userMarker = L.marker([currentLat, currentLng], { icon: radarIcon }).addTo(map);
                            map.setView([currentLat, currentLng], 16);
                        } else {
                            userMarker.setLatLng([currentLat, currentLng]);
                        }

                        try {
                            let res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${currentLat}&lon=${currentLng}&zoom=16`);
                            let data = await res.json();
                            document.getElementById('current-address').innerText = data.address.road || data.address.village || data.address.town || "Location Acquired";
                        } catch (e) {
                            document.getElementById('current-address').innerText = "GPS Fixed";
                        }
                    });
                }
            }

            updateMapLocation();
            setInterval(updateMapLocation, 8000);

            // SWEETALERT ACTION LOGGER
            function logAction(id, stepNum, actionString, urlHandler, type) {
                if (!navigator.geolocation) { Swal.fire('Error', 'GPS not supported on your device.', 'error'); return; }

                let titles = ['Log Departure', 'Log Arrival', 'Leave Destination', 'Return to Campus'];
                Swal.fire({
                    title: titles[stepNum - 1],
                    text: "Proceed to update your official travel status?",
                    icon: 'question', showCancelButton: true,
                    confirmButtonColor: '#10b981', cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Yes, Update Now', borderRadius: '20px'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({ title: 'Acquiring GPS...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                let bodyData = (type === 'TA')
                                    ? `ta_id=${id}&step=${stepNum}&action_name=${encodeURIComponent(actionString)}&lat=${pos.coords.latitude}&lng=${pos.coords.longitude}`
                                    : `ps_id=${id}&action=${actionString}&lat=${pos.coords.latitude}&lng=${pos.coords.longitude}`;

                                fetch(urlHandler, {
                                    method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: bodyData
                                }).then(res => type === 'TA' ? res.json() : res.text())
                                    .then(data => {
                                        let success = (type === 'TA') ? (data.status === 'success') : data.includes('success');
                                        if (success) Swal.fire({ title: 'Success!', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload());
                                        else Swal.fire('Error', 'Update failed.', 'error');
                                    });
                            },
                            (err) => Swal.fire('GPS Error', 'Please enable location services.', 'error'),
                            { enableHighAccuracy: true }
                        );
                    }
                });
            }
        </script>
    <?php endif; ?>
</body>

</html>