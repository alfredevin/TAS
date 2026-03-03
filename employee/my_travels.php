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
    </style>
    <?php
    // Summary Query
    $summary_query = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as approved FROM ta_tbl t JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id WHERE tp.employee_id = '$employee_id'");
    $summary = mysqli_fetch_assoc($summary_query);

    // Main Table Query - Isinama natin ang decline_reason
    $query = "SELECT t.*, (SELECT COUNT(*) FROM ta_participants_tbl WHERE ta_id = t.ta_id) as total_pax FROM ta_tbl t JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id WHERE tp.employee_id = '$employee_id' ORDER BY t.submitted_at DESC";
    $result = mysqli_query($conn, $query);
    ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>My Travel Management</h1>
        </div>

        <section class="section">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card card-stats border-0 shadow-sm">
                        <div class="card-body py-3">
                            <h6 class="mb-0 text-muted small">Total Requests</h6>
                            <h4 class="mb-0 fw-bold"><?= $summary['total'] ?? 0 ?></h4>
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
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)):
                                    $status = $row['status'];
                                    $status_config = [
                                        0 => ['badge' => 'bg-warning text-dark', 'label' => 'Pending'],
                                        1 => ['badge' => 'bg-info', 'label' => 'Confirmed'],
                                        2 => ['badge' => 'bg-success', 'label' => 'Approved'],
                                        99 => ['badge' => 'bg-danger', 'label' => 'Declined'],
                                        'default' => ['badge' => 'bg-secondary', 'label' => 'Unknown']
                                    ];
                                    $config = $status_config[$status] ?? $status_config['default'];
                                    ?>
                                    <tr>
                                        <td class="small"><?= date("M d, Y", strtotime($row['submitted_at'])) ?></td>
                                        <td class="fw-bold text-maroon"><?= $row['memo_no'] ?: '---' ?></td>
                                        <td>
                                            <div class="fw-bold"><?= $row['destination'] ?></div>
                                            <div class="small text-muted"><?= substr($row['task'], 0, 40) ?>...</div>
                                        </td>
                                        <td><span class="badge <?= $config['badge'] ?>"><?= $config['label'] ?></span></td>
                                        <td class="text-center">
                                            <?php if ($status == 99): ?>
                                                <button class="btn btn-sm btn-warning rounded-pill px-3 shadow-sm"
                                                    onclick='openResubmitModal(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <i class="bi bi-arrow-repeat me-1"></i> Resubmit
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"
                                                    onclick='trackTravel(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <i class="bi bi-search me-1"></i> Track
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
                <div class="modal-header bg-warning border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit & Resubmit Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_resubmit.php" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="ta_id" id="res_ta_id">

                        <div class="alert alert-danger mb-3 py-2 small">
                            <strong>Reason for decline:</strong> <span id="res_decline_text"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Destination</label>
                            <input type="text" name="destination" id="res_destination" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Purpose / Task</label>
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
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="btn_resubmit"
                            class="btn btn-warning fw-bold px-4 shadow-sm">Resubmit Request</button>
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
        function openResubmitModal(data) {
            document.getElementById('res_ta_id').value = data.ta_id;
            document.getElementById('res_destination').value = data.destination;
            document.getElementById('res_task').value = data.task;
            document.getElementById('res_travel_date').value = data.travel_date;
            document.getElementById('res_return_date').value = data.return_date;
            document.getElementById('res_decline_text').innerText = data.decline_reason || "No reason specified.";

            new bootstrap.Modal(document.getElementById('resubmitModal')).show();
        }
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