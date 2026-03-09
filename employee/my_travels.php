<?php
include './../config.php';

// Helper function para i-parse ang data na manggagaling sa database query
function parseStep($data)
{
    if (!$data)
        return ['time' => '--:--', 'lat' => null, 'lng' => null];
    $parts = explode('|', $data);
    if (count($parts) < 2)
        return ['time' => '--:--', 'lat' => null, 'lng' => null];
    $coords = explode(',', $parts[0]);
    return ['time' => $parts[1], 'lat' => $coords[0], 'lng' => $coords[1]];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include '../template/header.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <style>
        :root {
            --marsu-maroon: #800000;
            --marsu-gold: #ce9d06;
        }

        body {
            background-color: #f8fafc;
        }

        /* Search Box */
        .search-box {
            background: #fff;
            border-radius: 16px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            transition: 0.3s;
        }

        .search-box:focus-within {
            border-color: var(--marsu-maroon);
            box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.1);
        }

        .search-box input {
            border: none;
            outline: none;
            width: 100%;
            margin-left: 10px;
            font-size: 15px;
        }

        /* Smart Travel Cards */
        .travel-card {
            background: #fff;
            border-radius: 20px;
            padding: 20px;
            border: 1px solid #edf2f7;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .travel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        /* Status Accents */
        .accent-pending {
            border-top: 5px solid #f59e0b;
        }

        .accent-approved {
            border-top: 5px solid #10b981;
        }

        .accent-rejected {
            border-top: 5px solid #ef4444;
        }

        .accent-info {
            border-top: 5px solid #3b82f6;
        }

        .memo-badge {
            background: #f1f5f9;
            color: #475569;
            font-family: monospace;
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 12px;
        }

        /* 2x2 Timeline Grid (Pang-Location) */
        .timeline-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 16px;
            margin-top: 15px;
            border: 1px solid #e2e8f0;
        }

        .time-box {
            display: flex;
            align-items: start;
            gap: 8px;
            font-size: 12px;
        }

        .get-address {
            font-size: 10px;
            line-height: 1.2;
            display: inline-block;
            margin-top: 4px;
            text-decoration: none;
            padding: 3px 8px;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            transition: 0.3s;
        }

        .get-address:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        /* Milestone Buttons */
        .action-btn-area {
            margin-top: auto;
            padding-top: 15px;
        }

        .btn-milestone {
            border-radius: 14px;
            font-weight: 700;
            padding: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .btn-milestone:active {
            transform: scale(0.96);
        }

        /* Pulse Animation for Live */
        .pulse-live {
            animation: pulseLive 2s infinite;
        }

        @keyframes pulseLive {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.5);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(220, 53, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        /* Approval Timeline Modal Styles */
        .tracking-list {
            position: relative;
            padding: 20px 10px;
        }

        .tracking-item {
            position: relative;
            padding-left: 45px;
            padding-bottom: 30px;
        }

        .tracking-item::before {
            content: "";
            position: absolute;
            left: 14px;
            top: 5px;
            width: 2px;
            height: 100%;
            background: #e2e8f0;
            z-index: 0;
        }

        .tracking-item:last-child::before {
            display: none;
        }

        .tracking-item.completed::before {
            background: #10b981;
        }

        .tracking-item.rejected::before {
            background: #ef4444;
        }

        .tracking-icon {
            position: absolute;
            left: -2px;
            top: 0;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #cbd5e1;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #94a3b8;
            transition: 0.3s;
        }

        .tracking-item.completed .tracking-icon {
            background: #10b981;
            border-color: #10b981;
            color: #fff;
        }

        .tracking-item.rejected .tracking-icon {
            background: #ef4444;
            border-color: #ef4444;
            color: #fff;
        }

        .tracking-item.active .tracking-icon {
            border-color: var(--marsu-maroon);
            color: var(--marsu-maroon);
            box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.1);
        }

        .tracking-date {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        .tracking-content {
            font-weight: 700;
            font-size: 15px;
            color: #1e293b;
            margin-top: 3px;
        }
    </style>

    <?php
    // In-update ang query para kunin ang 4-Step Milestones tulad sa Pass Slip
    $query = "SELECT t.*, 
                (SELECT COUNT(*) FROM ta_participants_tbl WHERE ta_id = t.ta_id) as total_pax,
                (SELECT CONCAT(lat, ',', lng, '|', DATE_FORMAT(logged_at, '%h:%i %p')) FROM travel_milestones_tbl WHERE ta_id = t.ta_id AND step_number = 1 ORDER BY logged_at DESC LIMIT 1) as step1_data,
                (SELECT CONCAT(lat, ',', lng, '|', DATE_FORMAT(logged_at, '%h:%i %p')) FROM travel_milestones_tbl WHERE ta_id = t.ta_id AND step_number = 2 ORDER BY logged_at DESC LIMIT 1) as step2_data,
                (SELECT CONCAT(lat, ',', lng, '|', DATE_FORMAT(logged_at, '%h:%i %p')) FROM travel_milestones_tbl WHERE ta_id = t.ta_id AND step_number = 3 ORDER BY logged_at DESC LIMIT 1) as step3_data,
                (SELECT CONCAT(lat, ',', lng, '|', DATE_FORMAT(logged_at, '%h:%i %p')) FROM travel_milestones_tbl WHERE ta_id = t.ta_id AND step_number = 4 ORDER BY logged_at DESC LIMIT 1) as step4_data
              FROM ta_tbl t 
              JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id 
              WHERE tp.employee_id = '$employee_id' 
              ORDER BY t.submitted_at DESC";
    $result = mysqli_query($conn, $query);
    $today = date("Y-m-d");
    ?>

    <main id="main" class="main">
        <div class="pagetitle d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="fw-bolder" style="color: #1e3c72; font-size: 1.8rem;">My Travel Management</h1>
                <nav>
                    <ol class="breadcrumb bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-muted">Employee</a></li>
                        <li class="breadcrumb-item active fw-bold" style="color: var(--marsu-maroon);">Travel History
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="search-box flex-grow-1" style="max-width: 400px;">
                <i class="bi bi-search text-muted"></i>
                <input type="text" id="searchInput" placeholder="Search destination or memo no..."
                    onkeyup="filterCards()">
            </div>
        </div>

        <section class="section">
            <div class="row g-4" id="travelCardContainer">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)):
                        $status = (int) $row['status'];

                        $status_config = [
                            0 => ['badge' => 'bg-warning text-dark', 'label' => 'Pending', 'accent' => 'accent-pending'],
                            1 => ['badge' => 'bg-info text-white', 'label' => 'Head Confirmed', 'accent' => 'accent-info'],
                            2 => ['badge' => 'bg-success', 'label' => 'Fully Approved', 'accent' => 'accent-approved'],
                            3 => ['badge' => 'bg-primary', 'label' => 'Certificate Uploaded', 'accent' => 'accent-info'],
                            4 => ['badge' => 'bg-success', 'label' => 'Completed', 'accent' => 'accent-approved'],
                            11 => ['badge' => 'bg-danger', 'label' => 'Needs Correction', 'accent' => 'accent-rejected'],
                            99 => ['badge' => 'bg-dark', 'label' => 'Declined', 'accent' => 'accent-rejected'],
                            'default' => ['badge' => 'bg-secondary', 'label' => 'Unknown', 'accent' => '']
                        ];
                        $config = $status_config[$status] ?? $status_config['default'];

                        $is_travel_day = ($today >= $row['travel_date'] && $today <= $row['return_date']);
                        $step = isset($row['tracking_step']) ? (int) $row['tracking_step'] : 0;

                        // Parse ang mga location data
                        $s1 = parseStep($row['step1_data']);
                        $s2 = parseStep($row['step2_data']);
                        $s3 = parseStep($row['step3_data']);
                        $s4 = parseStep($row['step4_data']);
                        ?>
                        <div class="col-12 col-lg-6 col-xl-4 filterable-card">
                            <div class="travel-card <?= $config['accent'] ?>">

                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span
                                        class="badge <?= $config['badge'] ?> rounded-pill px-3 py-2 shadow-sm"><?= $config['label'] ?></span>
                                    <span class="memo-badge"><?= $row['memo_no'] ?: 'NO MEMO YET' ?></span>
                                </div>

                                <h4 class="fw-bolder text-dark mb-1 card-dest"><?= $row['destination'] ?></h4>

                                <div class="d-flex align-items-center text-muted small fw-bold mb-3 card-date">
                                    <i class="bi bi-calendar-range me-2 text-primary"></i>
                                    <?= date("M d", strtotime($row['travel_date'])) ?> -
                                    <?= date("M d, Y", strtotime($row['return_date'])) ?>
                                </div>

                                <?php if ($row['is_tracking_active'] == 1): ?>
                                    <div class="mb-2">
                                        <span class="badge bg-danger pulse-live rounded-pill px-3 py-1"><i
                                                class="bi bi-geo-alt-fill me-1"></i> Live Tracking Active</span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($status >= 2 && $status != 11 && $status != 99): ?>
                                    <div class="timeline-grid mb-3">
                                        <div class="time-box align-items-start">
                                            <i class="bi bi-box-arrow-right text-primary fs-5 mt-1"></i>
                                            <div>
                                                <span class="d-block text-muted"
                                                    style="font-size: 10px; font-weight: 700;">DEPARTED</span>
                                                <b class="text-dark d-block"><?= $s1['time'] ?></b>
                                                <?php if ($s1['lat']): ?>
                                                    <a href="http://maps.google.com/maps?q=<?= $s1['lat'] ?>,<?= $s1['lng'] ?>"
                                                        target="_blank" class="text-primary get-address" data-lat="<?= $s1['lat'] ?>"
                                                        data-lng="<?= $s1['lng'] ?>">
                                                        <i class="bi bi-geo-alt"></i> Fetching location...
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="time-box align-items-start">
                                            <i class="bi bi-geo-alt-fill text-success fs-5 mt-1"></i>
                                            <div>
                                                <span class="d-block text-muted"
                                                    style="font-size: 10px; font-weight: 700;">ARRIVED</span>
                                                <b class="text-dark d-block"><?= $s2['time'] ?></b>
                                                <?php if ($s2['lat']): ?>
                                                    <a href="http://maps.google.com/maps?q=<?= $s2['lat'] ?>,<?= $s2['lng'] ?>"
                                                        target="_blank" class="text-success get-address" data-lat="<?= $s2['lat'] ?>"
                                                        data-lng="<?= $s2['lng'] ?>">
                                                        <i class="bi bi-geo-alt"></i> Fetching location...
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="time-box align-items-start">
                                            <i class="bi bi-box-arrow-left text-warning fs-5 mt-1"></i>
                                            <div>
                                                <span class="d-block text-muted" style="font-size: 10px; font-weight: 700;">LEFT
                                                    DEST</span>
                                                <b class="text-dark d-block"><?= $s3['time'] ?></b>
                                                <?php if ($s3['lat']): ?>
                                                    <a href="http://maps.google.com/maps?q=<?= $s3['lat'] ?>,<?= $s3['lng'] ?>"
                                                        target="_blank" class="text-warning get-address" data-lat="<?= $s3['lat'] ?>"
                                                        data-lng="<?= $s3['lng'] ?>">
                                                        <i class="bi bi-geo-alt"></i> Fetching location...
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="time-box align-items-start">
                                            <i class="bi bi-house-check-fill text-danger fs-5 mt-1"></i>
                                            <div>
                                                <span class="d-block text-muted"
                                                    style="font-size: 10px; font-weight: 700;">RETURNED</span>
                                                <b class="text-dark d-block"><?= $s4['time'] ?></b>
                                                <?php if ($s4['lat']): ?>
                                                    <a href="http://maps.google.com/maps?q=<?= $s4['lat'] ?>,<?= $s4['lng'] ?>"
                                                        target="_blank" class="text-danger get-address" data-lat="<?= $s4['lat'] ?>"
                                                        data-lng="<?= $s4['lng'] ?>">
                                                        <i class="bi bi-geo-alt"></i> Fetching location...
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="action-btn-area">
                                    <?php if ($status == 2 && $is_travel_day): ?>
                                        <?php if ($step == 0): ?>
                                            <button class="btn btn-outline-primary w-100 btn-milestone shadow-sm"
                                                onclick="logTravelMilestone(<?= $row['ta_id'] ?>, 1, 'Departed Campus')">
                                                <i class="bi bi-box-arrow-right fs-5"></i> Log Departure
                                            </button>
                                        <?php elseif ($step == 1): ?>
                                            <button class="btn btn-success shadow w-100 btn-milestone"
                                                onclick="logTravelMilestone(<?= $row['ta_id'] ?>, 2, 'Arrived at Destination')">
                                                <i class="bi bi-geo-alt-fill fs-5"></i> I Have Arrived
                                            </button>
                                        <?php elseif ($step == 2): ?>
                                            <button class="btn btn-warning shadow w-100 btn-milestone text-dark"
                                                onclick="logTravelMilestone(<?= $row['ta_id'] ?>, 3, 'Left Destination')">
                                                <i class="bi bi-sign-turn-left-fill fs-5"></i> Leaving Dest.
                                            </button>
                                        <?php elseif ($step == 3): ?>
                                            <button class="btn btn-danger shadow w-100 btn-milestone"
                                                onclick="logTravelMilestone(<?= $row['ta_id'] ?>, 4, 'Returned to Campus')">
                                                <i class="bi bi-house-check-fill fs-5"></i> Back to Campus
                                            </button>
                                        <?php elseif ($step == 4): ?>
                                            <div class="alert alert-success m-0 p-2 text-center rounded-pill fw-bold small"><i
                                                    class="bi bi-check-circle-fill me-1"></i> Travel Completed</div>
                                        <?php endif; ?>

                                    <?php elseif ($status == 11): ?>
                                        <button class="btn btn-warning shadow-sm w-100 btn-milestone text-dark"
                                            onclick='openResubmitModal(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                            <i class="bi bi-tools fs-5"></i> Fix & Resubmit
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-light border w-100 btn-milestone text-secondary"
                                            onclick='trackTravel(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                            <i class="bi bi-file-earmark-check fs-5"></i> Approval Tracker
                                        </button>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-folder-x text-muted" style="font-size: 4rem;"></i>
                        <h4 class="fw-bold mt-3 text-secondary">No Travel Records</h4>
                        <p class="text-muted">You haven't requested any Travel Authority yet.</p>
                        <a href="request_ta" class="btn btn-primary rounded-pill px-4 mt-2 shadow-sm">Create Request
                            Now</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <div class="modal fade" id="resubmitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-warning border-0 text-dark" style="border-radius: 20px 20px 0 0;">
                    <h5 class="modal-title fw-bolder"><i class="bi bi-tools me-2"></i>Correction Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_resubmit.php" method="POST">
                    <div class="modal-body p-4 bg-white">
                        <input type="hidden" name="ta_id" id="res_ta_id">

                        <div class="alert alert-danger mb-4 py-3 shadow-sm border-0" style="border-radius: 12px;">
                            <strong class="d-block mb-1 text-danger"><i
                                    class="bi bi-exclamation-triangle-fill me-1"></i> Admin Remarks:</strong>
                            <span id="res_decline_text" class="fst-italic small"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Destination</label>
                            <input type="text" name="destination" id="res_destination"
                                class="form-control form-control-lg bg-light" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Purpose</label>
                            <textarea name="task" id="res_task" class="form-control bg-light" rows="3"
                                required></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Travel Date</label>
                                <input type="date" name="travel_date" id="res_travel_date" class="form-control bg-light"
                                    required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Return Date</label>
                                <input type="date" name="return_date" id="res_return_date" class="form-control bg-light"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-white" style="border-radius: 0 0 20px 20px;">
                        <button type="submit" name="btn_resubmit"
                            class="btn btn-warning fw-bold px-4 py-2 shadow-sm w-100 rounded-pill">Update & Resubmit <i
                                class="bi bi-send ms-1"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 p-4 text-white"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 me-3 shadow-sm d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px;">
                            <i class="bi bi-clipboard2-check fs-4 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Approval Tracker</h5>
                            <small id="pv-memo-no" class="opacity-75" style="letter-spacing: 1px;"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0 bg-white">
                    <div class="p-3 bg-light border-bottom">
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <label class="text-muted fw-bold text-uppercase mb-1"
                                    style="font-size:10px; letter-spacing: 1px;">Destination</label>
                                <p id="pv-dest-text" class="fw-bolder mb-0 text-dark small"></p>
                            </div>
                            <div class="col-6">
                                <label class="text-muted fw-bold text-uppercase mb-1"
                                    style="font-size:10px; letter-spacing: 1px;">Travel Date</label>
                                <p id="pv-date-text" class="fw-bolder mb-0 text-dark small"></p>
                            </div>
                        </div>
                    </div>

                    <div id="decline-reason-section" class="p-3 pb-0 d-none">
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

                <div class="modal-footer border-0 p-3 pt-0 text-center bg-white">
                    <button type="button" class="btn btn-light border w-100 rounded-pill fw-bold"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../template/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php include '../template/script.php'; ?>

    <script>
        // 1. REVERSE GEOCODING (Converts Lat/Lng to Place Name)
        async function loadLocationNames() {
            const locationElements = document.querySelectorAll('.get-address');

            for (let i = 0; i < locationElements.length; i++) {
                const el = locationElements[i];
                const lat = el.getAttribute('data-lat');
                const lng = el.getAttribute('data-lng');

                if (lat && lng) {
                    try {
                        await new Promise(resolve => setTimeout(resolve, 800)); // Delay to respect OpenStreetMap limits

                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=14`);
                        const data = await response.json();

                        if (data && data.address) {
                            let placeName = data.address.village || data.address.town || data.address.city || data.address.county || "Open Map";
                            el.innerHTML = `<i class="bi bi-geo-alt"></i> ${placeName} <i class="bi bi-box-arrow-up-right ms-1" style="font-size:8px;"></i>`;
                        } else {
                            el.innerHTML = `<i class="bi bi-geo-alt"></i> View Map`;
                        }
                    } catch (error) {
                        el.innerHTML = `<i class="bi bi-map"></i> Open Map`;
                    }
                }
            }
        }
        document.addEventListener("DOMContentLoaded", loadLocationNames);

        // 2. Simple JS Filter for Cards (Replaces Datatable Search)
        function filterCards() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cards = document.querySelectorAll('.filterable-card');

            cards.forEach(card => {
                let dest = card.querySelector('.card-dest').innerText.toLowerCase();
                let memo = card.querySelector('.memo-badge').innerText.toLowerCase();

                if (dest.includes(input) || memo.includes(input)) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        }

        // 3. Milestone Tracking Logic (SweetAlert2)
        function logTravelMilestone(taId, stepNumber, actionName) {
            if (!navigator.geolocation) {
                Swal.fire('Error', 'Your browser does not support location tracking.', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm ' + actionName,
                text: "Your exact location and timestamp will be saved and sent to the Admin Map.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#800000',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, Record it!',
                borderRadius: '20px'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Acquiring GPS...',
                        text: 'Please wait a moment.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            fetch('update_milestone.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `ta_id=${taId}&step=${stepNumber}&action_name=${encodeURIComponent(actionName)}&lat=${pos.coords.latitude}&lng=${pos.coords.longitude}`
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: actionName + ' logged.',
                                            icon: 'success',
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(() => location.reload());
                                    } else {
                                        Swal.fire('Error', data.message, 'error');
                                    }
                                });
                        },
                        (err) => {
                            Swal.fire('GPS Error', 'Please enable location services or check your connection.', 'error');
                        },
                        { enableHighAccuracy: true, maximumAge: 0 }
                    );
                }
            });
        }

        // 4. Resubmit Form Handling
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

        // 5. Approval Timeline Popup Handling
        function trackTravel(data) {
            const fmtFull = (d) => d ? new Date(d).toLocaleString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
            }) : "Pending";

            const status = parseInt(data.status);

            const memoText = document.getElementById('pv-memo-no');
            if (memoText) memoText.innerText = data.memo_no ? `MEMO: ${data.memo_no}` : "STATUS: PROCESSING";

            const destText = document.getElementById('pv-dest-text');
            if (destText) destText.innerText = data.destination;

            const dateText = document.getElementById('pv-date-text');
            if (dateText) dateText.innerText = data.travel_date;

            const reasonSection = document.getElementById('decline-reason-section');
            if (reasonSection) {
                if (status == 99 || status == 11) {
                    reasonSection.classList.remove('d-none');
                    document.getElementById('decline-author').innerText = (status == 11) ? "Returned for Correction:" : "Declined:";
                    document.getElementById('decline-msg').innerText = data.admin_remarks || data.decline_reason || "No specific reason provided.";
                } else {
                    reasonSection.classList.add('d-none');
                }
            }

            const steps = [
                { label: "Request Submitted", date: data.submitted_at, isDone: true, isRejected: false, icon: "bi-file-earmark-plus" },
                { label: "Head Confirmation", date: data.head_confirmed_at, isDone: (status >= 1 && status != 99) || (status == 11) || (status == 99 && data.head_confirmed_at != null), isRejected: status == 99 && (data.head_confirmed_at == null || data.head_confirmed_at == ""), isActive: status == 0, icon: "bi-person-check" },
                { label: "Admin Approval", date: data.admin_approved_at, isDone: status == 2 || status == 4, isRejected: status == 11 || (status == 99 && data.head_confirmed_at != null), isActive: status == 1, icon: "bi-shield-check" }
            ];

            let html = '';
            steps.forEach((step) => {
                let sClass = step.isRejected ? 'rejected' : (step.isDone ? 'completed' : (step.isActive ? 'active' : ''));
                let iconMarkup = step.isRejected ? '<i class="bi bi-x-lg"></i>' : (step.isDone ? '<i class="bi bi-check-lg"></i>' : `<i class="bi ${step.icon}"></i>`);

                html += `
                <div class="tracking-item ${sClass}">
                    <div class="tracking-icon shadow-sm">
                        ${iconMarkup}
                    </div>
                    <div class="ps-2 pt-1">
                        <span class="tracking-date">${step.isRejected ? 'Declined' : (step.isDone ? fmtFull(step.date) : (step.isActive ? 'In Progress' : 'Awaiting'))}</span>
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