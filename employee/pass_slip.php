<?php
include './../config.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>


    <script>
        function updateDropdown() {
            const isOfficial = document.getElementById('official');
            const selectDropdown = document.getElementById('purpose_select');

            if (isOfficial && selectDropdown) {
                selectDropdown.innerHTML = '';
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
        .form-card {
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: none;
        }

        .form-header {
            background: linear-gradient(135deg, #800000, #a00000);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 25px;
            text-align: center;
        }

        .btn-check:checked+.btn-outline-maroon {
            background-color: #800000;
            color: white;
            border-color: #800000;
        }

        .btn-outline-maroon {
            color: #800000;
            border-color: #800000;
            font-weight: 600;
        }

        .btn-outline-maroon:hover {
            background-color: #f8f9fa;
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
    <main id="main" class="main">
        <div class="pagetitle mb-4">
            <h1 class="fw-bold" style="color: #2c3e50;">Pass Slip Request</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Employee</li>
                    <li class="breadcrumb-item active">Pass Slip</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card form-card">
                        <div class="form-header">
                            <h4 class="fw-bold mb-0"><i class="bi bi-ticket-detailed me-2"></i>E-Pass Slip</h4>
                            <p class="small text-white-50 mb-0 mt-1">For same-day localized travel only</p>
                        </div>
                        <div class="card-body p-4">
                            <form action="process_pass_slip.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary text-uppercase">Date</label>
                                    <input type="text" class="form-control bg-light fw-bold text-maroon"
                                        value="<?= date('F d, Y') ?>" readonly>
                                    <input type="hidden" name="ps_date" value="<?= $today ?>">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-secondary text-uppercase">Issued
                                        For</label>
                                    <div class="d-flex gap-2">
                                        <input type="radio" class="btn-check" name="issued_for" id="official"
                                            value="Official Activity" checked onchange="updateDropdown()">
                                        <label class="btn btn-outline-maroon w-100 rounded-pill" for="official"><i
                                                class="bi bi-briefcase me-2"></i>Official</label>

                                        <input type="radio" class="btn-check" name="issued_for" id="personal"
                                            value="Personal Reason" onchange="updateDropdown()">
                                        <label class="btn btn-outline-maroon w-100 rounded-pill" for="personal"><i
                                                class="bi bi-person-lines-fill me-2"></i>Personal</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label
                                        class="form-label small fw-bold text-secondary text-uppercase">Destination</label>
                                    <input type="text" class="form-control" name="destination"
                                        placeholder="Specific place to be visited" required>
                                </div>

                                <div class="mb-4">
                                    <label
                                        class="form-label small fw-bold text-secondary text-uppercase">Purpose</label>
                                    <select class="form-select mb-2" name="purpose_type" id="purpose_select"
                                        required></select>
                                    <input type="text" class="form-control" name="specific_purpose"
                                        placeholder="Provide specific details..." required>
                                </div>

                                <button type="submit" name="submit_pass_slip"
                                    class="btn w-100 rounded-pill fw-bold py-2 text-white"
                                    style="background-color: #800000; box-shadow: 0 4px 10px rgba(128,0,0,0.3);">
                                    Submit Request <i class="bi bi-send ms-1"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card form-card h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4" style="color: #2c3e50;"><i
                                    class="bi bi-clock-history me-2 text-maroon"></i>My Pass Slip History</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Destination & Date</th>
                                            <th>Status</th>
                                            <th>Time Logs</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($history_query) > 0): ?>
                                            <?php while ($ps = mysqli_fetch_assoc($history_query)):
                                                $badge = ($ps['status'] == 0) ? 'bg-warning text-dark' : (($ps['status'] == 1) ? 'bg-success' : 'bg-danger');
                                                $lbl = ($ps['status'] == 0) ? 'Pending Admin' : (($ps['status'] == 1) ? 'Approved' : 'Declined');

                                                $dep_time = ($ps['time_departure'] && $ps['time_departure'] != '00:00:00') ? date("h:i A", strtotime($ps['time_departure'])) : 'Not Started';
                                                $ret_time = ($ps['time_return'] && $ps['time_return'] != '00:00:00') ? date("h:i A", strtotime($ps['time_return'])) : '--:--';
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold text-dark" style="font-size: 14px;">
                                                            <?= $ps['destination'] ?>
                                                        </div>
                                                        <div class="small fw-bold text-maroon">
                                                            <?= date("M d, Y", strtotime($ps['ps_date'])) ?>
                                                        </div>
                                                        <div class="text-muted" style="font-size: 11px;"><span
                                                                class="badge bg-light text-secondary border me-1"><?= $ps['issued_for'] ?></span><?= $ps['specific_purpose'] ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= $badge ?> rounded-pill mb-1"><?= $lbl ?></span>
                                                        <?php if (isset($ps['is_tracking_active']) && $ps['is_tracking_active'] == 1): ?>
                                                            <br><span class="badge bg-danger pulse-live" style="font-size: 10px;"><i
                                                                    class="bi bi-geo-alt"></i> Live</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="small"><i
                                                                class="bi bi-arrow-up-right-circle text-success me-1"></i>Out:
                                                            <b><?= $dep_time ?></b>
                                                        </div>
                                                        <div class="small"><i
                                                                class="bi bi-arrow-down-left-circle text-danger me-1"></i>In:
                                                            <b><?= $ret_time ?></b>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($ps['status'] == 1 && $ps['ps_date'] == $today && ($ps['time_return'] == null || $ps['time_return'] == '00:00:00')): ?>

                                                            <?php if (!isset($ps['is_tracking_active']) || $ps['is_tracking_active'] == 0): ?>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm w-100 mb-1"
                                                                    onclick="invokeStartPS(<?= $ps['ps_id'] ?>)">
                                                                    <i class="bi bi-play-circle me-1"></i> Start Travel
                                                                </button>
                                                            <?php else: ?>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm w-100 mb-1"
                                                                    onclick="invokeStopPS(<?= $ps['ps_id'] ?>)">
                                                                    <i class="bi bi-stop-circle me-1"></i> End Travel
                                                                </button>
                                                            <?php endif; ?>

                                                        <?php elseif ($ps['status'] == 1): ?>
                                                            <span class="badge bg-light text-success border"><i
                                                                    class="bi bi-check-circle me-1"></i>Completed</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No pass slips requested
                                                    yet.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
        // Pinalitan ko ang pangalan para iwas clash
        function invokeStartPS(psId) {
            if (!navigator.geolocation) {
                alert('Error: Location tracking is not supported by your browser.');
                return;
            }

            if (confirm("Start your journey? This will record your departure time and track your location.")) {
                // Kunin muna ang location para sure bago mag-save sa database
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        // I-save ang Start Time at Location
                        fetch('ps_tracking_handler.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `ps_id=${psId}&action=start`
                        }).then(res => {
                            if (res.ok) {
                                // Pagkatapos ma-save ang time, i-save ang initial location
                                fetch('ps_tracking_handler.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: `ps_id=${psId}&action=location&lat=${lat}&lng=${lng}`
                                }).then(() => {
                                    alert("Travel Started successfully!");
                                    window.location.reload();
                                });
                            }
                        }).catch(err => {
                            alert("Network Error: Could not start travel.");
                        });
                    },
                    function (error) {
                        alert("Please ALLOW Location Access in your browser to start traveling.");
                    },
                    { enableHighAccuracy: true }
                );
            }
        }

        function invokeStartPS(psId) {
            if (!navigator.geolocation) {
                alert('Error: Location tracking is not supported by your browser.');
                return;
            }

            if (confirm("Start your journey? This will record your departure time and initial location.")) {
                // Kunin ang location BAGO mag-start
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        // Isahang POST request: Ipasa ang ID, start action, at location nang sabay!
                        fetch('ps_tracking_handler.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `ps_id=${psId}&action=start&lat=${lat}&lng=${lng}`
                        }).then(res => {
                            if (res.ok) {
                                alert("Travel Started successfully! Your location is now live.");
                                window.location.reload();
                            }
                        }).catch(err => {
                            alert("Network Error: Could not start travel.");
                        });
                    },
                    function (error) {
                        alert("Please ALLOW Location Access in your browser so we can start your tracking.");
                    },
                    { enableHighAccuracy: true }
                );
            }
        }

        // SWEETALERT PARA SA SUBMIT FORM (Notification lang)
        document.addEventListener("DOMContentLoaded", () => {
            const type = sessionStorage.getItem('swal_type');
            const msg = sessionStorage.getItem('swal_msg');
            if (type && msg && typeof Swal !== 'undefined') {
                Swal.fire({ icon: type, title: msg, timer: 3000, showConfirmButton: false });
                sessionStorage.removeItem('swal_type');
                sessionStorage.removeItem('swal_msg');
            }
        });

        // BACKGROUND LOCATION UPDATER
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