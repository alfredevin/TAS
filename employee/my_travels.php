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

        .card-stats:hover {
            transform: scale(1.02);
        }

        /* Timeline Styling */
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

        .tracking-item:last-child {
            padding-bottom: 0;
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
            margin-bottom: 2px;
        }

        .tracking-content {
            font-weight: 600;
            font-size: 14px;
            color: #212529;
        }

        .badge-status {
            font-size: 0.75rem;
            padding: 0.5em 1em;
            border-radius: 50px;
        }
    </style>
    <?php


    // 2. Query para sa Summary Cards
    $summary_query = mysqli_query($conn, "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as approved
    FROM ta_tbl t JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id 
    WHERE tp.employee_id = '$employee_id'");
    $summary = mysqli_fetch_assoc($summary_query);

    // 3. Query para sa Table
    $query = "SELECT t.*, 
          (SELECT COUNT(*) FROM ta_participants_tbl WHERE ta_id = t.ta_id) as total_pax
          FROM ta_tbl t 
          JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id 
          WHERE tp.employee_id = '$employee_id' 
          ORDER BY t.submitted_at DESC";
    $result = mysqli_query($conn, $query);
    ?>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>My Travel Management</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item active">Travels</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card card-stats border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="btn btn-light btn-sm rounded-circle me-3"><i
                                        class="bi bi-files text-primary"></i></div>
                                <div>
                                    <h6 class="mb-0 text-muted small">Total Requests</h6>
                                    <h4 class="mb-0 fw-bold"><?= $summary['total'] ?? 0 ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-stats border-0 shadow-sm" style="border-left-color: #ffc107;">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="btn btn-light btn-sm rounded-circle me-3"><i
                                        class="bi bi-hourglass-split text-warning"></i></div>
                                <div>
                                    <h6 class="mb-0 text-muted small">Pending Approval</h6>
                                    <h4 class="mb-0 fw-bold"><?= $summary['pending'] ?? 0 ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-stats border-0 shadow-sm" style="border-left-color: #198754;">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="btn btn-light btn-sm rounded-circle me-3"><i
                                        class="bi bi-check-all text-success"></i></div>
                                <div>
                                    <h6 class="mb-0 text-muted small">Approved Travels</h6>
                                    <h4 class="mb-0 fw-bold"><?= $summary['approved'] ?? 0 ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover datatable align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date Filed</th>
                                    <th>Memo No.</th>
                                    <th>Destination</th>
                                    <th>Status</th>
                                    <th class="text-center">Track</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)):
                                    $status = $row['status'];

                                    // Mas malinis na mapping para sa Badge Colors
                                    $status_config = [
                                        0 => ['badge' => 'bg-warning text-dark', 'label' => 'Pending'],
                                        1 => ['badge' => 'bg-info', 'label' => 'Confirmed'],
                                        2 => ['badge' => 'bg-success', 'label' => 'Approved'],
                                        3 => ['badge' => 'bg-primary', 'label' => 'Completed'], // Submitted report
                                        4 => ['badge' => 'bg-dark', 'label' => 'Verified'],  // Admin Archived
                                        'default' => ['badge' => 'bg-danger', 'label' => 'Rejected']
                                    ];

                                    $config = $status_config[$status] ?? $status_config['default'];
                                    $badge = $config['badge'];
                                    $label = $config['label'];
                                    ?>
                                    <tr>
                                        <td class="small"><?= date("M d, Y", strtotime($row['submitted_at'])) ?></td>
                                        <td class="fw-bold text-maroon"><?= $row['memo_no'] ?></td>

                                        <td>
                                            <div class="fw-bold text-dark"><?= $row['destination'] ?></div>
                                            <div class="text-muted small" style="line-height: 1.2;">
                                                <i class="bi bi-info-circle-fill me-1" style="font-size: 10px;"></i>
                                                <?= (strlen($row['task']) > 50) ? substr($row['task'], 0, 50) . '...' : $row['task']; ?>
                                            </div>
                                        </td>

                                        <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>

                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"
                                                onclick='trackTravel(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                <i class="bi bi-search me-1"></i> Track
                                            </button>
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

    <div class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">

                <div class="modal-header border-0 p-4 text-white"
                    style="background: linear-gradient(45deg, #800000, #a00000);">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 me-3 shadow-sm">
                            <i class="bi bi-geo-alt-fill text-maroon fs-4" style="color: #800000;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Travel Journey</h5>
                            <small id="pv-memo-no" class="opacity-75">Memo: PENDING</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                    <div class="p-4 bg-light border-bottom">
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <label class="text-muted small text-uppercase fw-bold">Destination</label>
                                <p id="pv-dest-text" class="fw-bold text-dark mb-0">-</p>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small text-uppercase fw-bold">Travel Date</label>
                                <p id="pv-date-text" class="fw-bold text-dark mb-0">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="tracking-list" id="trackingTimeline">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light w-100 fw-bold py-2 shadow-sm" data-bs-dismiss="modal"
                        style="border-radius: 10px;">Close Tracker</button>
                </div>
            </div>
        </div>
    </div>


    <?php include '../template/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php include '../template/script.php'; ?>


    <script>
        function trackTravel(data) {
            const fmtDateOnly = (d) => d ? new Date(d).toLocaleDateString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric'
            }) : "-";

            const fmtFull = (d) => d ? new Date(d).toLocaleString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
            }) : "Pending";

            document.getElementById('pv-memo-no').innerText = `Memo: ${data.memo_no || 'N/A'}`;
            document.getElementById('pv-dest-text').innerText = data.destination;
            document.getElementById('pv-date-text').innerText = fmtDateOnly(data.travel_date);

            let html = '';
            const status = parseInt(data.status);

            // CONFIGURATION WITH REJECTION LOGIC
            const steps = [
                {
                    label: "Request Submitted",
                    desc: "Filing of TA by the faculty member",
                    date: data.submitted_at,
                    isDone: true,
                    isRejected: false,
                    icon: "bi-file-earmark-plus"
                },
                {
                    label: "Head Confirmation",
                    desc: status == 99 && !data.head_confirmed_at ? "Rejected by Department Head" : "Approval from the Department Head",
                    date: data.head_confirmed_at,
                    isDone: status >= 1 && status != 99,
                    // Na-reject dito kung status 99 at walang timestamp ang head
                    isRejected: status == 99 && (!data.head_confirmed_at || data.head_confirmed_at === null),
                    isActive: status == 0,
                    icon: "bi-person-check"
                },
                {
                    label: "Admin Approval",
                    desc: status == 99 && data.head_confirmed_at ? "Rejected by Admin" : "Final review and assignment of Memo No.",
                    date: data.admin_approved_at,
                    isDone: status >= 2 && status != 99,
                    // Na-reject dito kung status 99 pero naka-confirm na ang head
                    isRejected: status == 99 && (data.head_confirmed_at && !data.admin_approved_at),
                    isActive: status == 1,
                    icon: "bi-shield-check"
                },
                {
                    label: "Ready to Print",
                    desc: "Document is now available",
                    date: data.admin_approved_at,
                    isDone: status >= 2 && status != 99,
                    isRejected: false, // Hindi na aabot dito kung rejected sa taas
                    isActive: false,
                    icon: "bi-printer"
                }
            ];

            steps.forEach((step) => {
                let statusClass = '';
                let iconHtml = `<i class="bi ${step.icon}"></i>`;

                if (step.isRejected) {
                    statusClass = 'rejected'; // Gagawa tayo ng CSS para dito
                    iconHtml = `<i class="bi bi-x-lg"></i>`;
                } else if (step.isDone) {
                    statusClass = 'completed';
                    iconHtml = `<i class="bi bi-check-lg"></i>`;
                } else if (step.isActive) {
                    statusClass = 'active';
                }

                html += `
        <div class="tracking-item ${statusClass}">
            <div class="tracking-icon shadow-sm ${step.isRejected ? 'bg-danger border-danger text-white' : ''}">
                ${iconHtml}
            </div>
            <div class="ps-2">
                <span class="tracking-date">
                    ${step.isRejected ? 'Declined' : (step.isDone ? fmtFull(step.date) : (step.isActive ? 'Action Required' : 'Upcoming'))}
                </span>
                <div class="tracking-content mb-0 ${step.isRejected ? 'text-danger' : ''}">${step.label}</div>
                <p class="text-muted small mb-0">${step.desc}</p>
            </div>
        </div>`;
            });

            document.getElementById('trackingTimeline').innerHTML = html;
            new bootstrap.Modal(document.getElementById('trackingModal')).show();
        }
    </script>
</body>

</html>