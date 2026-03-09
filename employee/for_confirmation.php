<?php include './../config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include '../template/header.php'; ?>


</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <style>
        body {
            background-color: #f8fafc;
        }

        /* Modern Tabs / Pills */
        .nav-pills {
            background: #fff;
            padding: 8px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            display: flex;
            overflow-x: auto;
            flex-wrap: nowrap;
            /* Forces horizontal scroll on very small screens */
        }

        .nav-pills::-webkit-scrollbar {
            display: none;
        }

        .nav-pills .nav-link {
            color: #64748b;
            font-weight: 600;
            border-radius: 50px;
            padding: 10px 24px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .nav-pills .nav-link:hover {
            color: #800000;
            background: #f8fafc;
        }

        .nav-pills .nav-link.active {
            background: #800000;
            color: #fff;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(128, 0, 0, 0.2);
        }

        .badge-pending {
            background: #fff1f2;
            color: #ef4444;
            border: 1px solid #fecdd3;
            margin-left: 8px;
            padding: 4px 8px;
            font-size: 10px;
        }

        .nav-link.active .badge-pending {
            background: #fff;
            color: #800000;
            border: none;
        }

        /* Mobile-Friendly Smart Cards (Replaces Table) */
        .request-card {
            background: #fff;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            border: 1px solid #edf2f7;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .request-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
        }

        .emp-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #94a3b8;
            border: 2px solid #e2e8f0;
            flex-shrink: 0;
        }

        /* Action Buttons */
        .btn-confirm {
            background-color: #10b981;
            color: white;
            border-radius: 12px;
            padding: 10px;
            font-weight: 700;
            border: none;
            flex-grow: 1;
            transition: 0.2s;
        }

        .btn-confirm:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }

        .btn-decline {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            background: #fff1f2;
            color: #ef4444;
            border: 1px solid transparent;
            transition: 0.2s;
        }

        .btn-decline:hover {
            background: #ef4444;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
        }

        .card-details-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px;
            margin-top: 15px;
        }

        /* Status Accents */
        .card-confirmed {
            border-left: 5px solid #10b981;
        }

        .card-declined {
            border-left: 5px solid #ef4444;
        }
    </style>
    <?php
    $my_id = $_SESSION['employee_id'];

    // 1. Kunin ang department_id ng logged-in Head
    $get_head_info = mysqli_query($conn, "SELECT department_id FROM employee_tbl WHERE employee_id = '$my_id'");
    $head_info = mysqli_fetch_assoc($get_head_info);
    $my_dept_id = $head_info['department_id'] ?? 0;

    // 2. Logic para sa Approval o Rejection
    if (isset($_POST['action_ta'])) {
        $ta_id = mysqli_real_escape_string($conn, $_POST['ta_id']);
        $action = $_POST['action_type'];
        $target_emp_id = mysqli_real_escape_string($conn, $_POST['requester_id']);

        if ($action == 'confirm') {
            mysqli_query($conn, "UPDATE ta_tbl SET status = 1, head_confirmed_at = NOW() WHERE ta_id = '$ta_id'");

            // Notify Admin
            $admin_query = mysqli_query($conn, "SELECT userid FROM user_tbl WHERE usertype = 1 LIMIT 1");
            $admin = mysqli_fetch_assoc($admin_query);
            $admin_id = $admin['userid'] ?? 0;
            $msg_admin = "A travel request from your department is ready for final approval.";
            mysqli_query($conn, "INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, link) 
                            VALUES ('$admin_id', '$my_id', 'TA Awaiting Approval', '$msg_admin', 'pending_approval.php')");

            // Notify Employee
            $msg_emp = "Good news! Your TA request has been confirmed by your Department Head.";
            mysqli_query($conn, "INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, link) 
                            VALUES ('$target_emp_id', '$my_id', 'TA Confirmed', '$msg_emp', 'my_travels.php')");

            $alert_type = "success";
            $alert_msg = "Travel request has been confirmed!";
        } else if ($action == 'reject') {
            $reason = mysqli_real_escape_string($conn, $_POST['decline_reason']);
            mysqli_query($conn, "UPDATE ta_tbl SET status = 99, decline_reason = '$reason' WHERE ta_id = '$ta_id'");

            $msg_rej = "Your TA request was declined by your Department Head. Reason: " . $reason;
            mysqli_query($conn, "INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, link) 
                            VALUES ('$target_emp_id', '$my_id', 'TA Declined', '$msg_rej', 'my_travels.php')");

            $alert_type = "error";
            $alert_msg = "Travel request has been declined.";
        }

        echo "<script>
        sessionStorage.setItem('swal_type', '$alert_type');
        sessionStorage.setItem('swal_msg', '$alert_msg');
        window.location.href='for_confirmation.php';
        </script>";
    }

    // 3. Queries for the 3 Tabs
    function getRequests($conn, $dept_id, $status)
    {
        return mysqli_query($conn, "SELECT t.*, e.employee_id as requester_id, e.first_name, e.last_name, e.position_name 
          FROM ta_tbl t 
          JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id 
          JOIN employee_tbl e ON tp.employee_id = e.employee_id 
          WHERE t.status = '$status' AND e.department_id = '$dept_id'
          GROUP BY t.ta_id ORDER BY t.submitted_at DESC");
    }

    $pending_res = getRequests($conn, $my_dept_id, 0);
    $confirmed_res = getRequests($conn, $my_dept_id, 1);
    $declined_res = getRequests($conn, $my_dept_id, 99);

    $count_pending = mysqli_num_rows($pending_res);
    ?>

    <main id="main" class="main">
        <div class="pagetitle mb-4">
            <h1 class="fw-bolder" style="color: #1e293b; font-size: 1.8rem;">Department Confirmation</h1>
            <nav>
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-muted">Head</a></li>
                    <li class="breadcrumb-item active fw-bold" style="color: #800000;">Pending Requests</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-10 col-xl-8 mx-auto">

                    <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active d-flex align-items-center" id="pills-pending-tab"
                                data-bs-toggle="pill" data-bs-target="#pills-pending" type="button">
                                <i class="bi bi-hourglass-split me-2 d-none d-sm-block"></i> Pending <span
                                    class="badge badge-pending rounded-pill"><?= $count_pending ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center" id="pills-confirmed-tab"
                                data-bs-toggle="pill" data-bs-target="#pills-confirmed" type="button">
                                <i class="bi bi-check-circle me-2 d-none d-sm-block"></i> Confirmed
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center" id="pills-declined-tab"
                                data-bs-toggle="pill" data-bs-target="#pills-declined" type="button">
                                <i class="bi bi-x-octagon me-2 d-none d-sm-block"></i> Declined
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent">

                        <div class="tab-pane fade show active" id="pills-pending" role="tabpanel">
                            <?php renderCards($pending_res, true, ''); ?>
                        </div>

                        <div class="tab-pane fade" id="pills-confirmed" role="tabpanel">
                            <?php renderCards($confirmed_res, false, 'card-confirmed', 'Confirmed'); ?>
                        </div>

                        <div class="tab-pane fade" id="pills-declined" role="tabpanel">
                            <?php renderCards($declined_res, false, 'card-declined', 'Declined'); ?>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php
    // Reusable Card Function (Mobile Friendly replacement for Table)
    function renderCards($result, $isActionable, $accentClass = '', $statusText = '')
    {
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <div class="request-card <?= $accentClass ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center">
                            <div class="emp-avatar me-3">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bolder m-0 text-dark"><?= $row['first_name'] . ' ' . $row['last_name'] ?></h6>
                                <span class="text-muted small" style="font-size: 11px;"><i
                                        class="bi bi-tag-fill me-1"></i><?= $row['position_name'] ?></span>
                            </div>
                        </div>
                        <?php if (!$isActionable): ?>
                            <span
                                class="badge <?= ($statusText == 'Confirmed') ? 'bg-success' : 'bg-danger' ?> rounded-pill px-3 py-2 shadow-sm"><?= $statusText ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="card-details-box">
                        <div class="mb-2">
                            <span class="text-muted small d-block mb-1 text-uppercase"
                                style="font-size: 10px; font-weight: 700;">Destination</span>
                            <div class="fw-bold text-dark" style="color: #800000 !important;"><?= $row['destination'] ?></div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 mb-2 mb-sm-0">
                                <span class="text-muted small d-block mb-1 text-uppercase"
                                    style="font-size: 10px; font-weight: 700;">Task / Purpose</span>
                                <div class="small text-truncate" style="max-width: 100%;"><?= $row['task'] ?></div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="text-muted small d-block mb-1 text-uppercase"
                                    style="font-size: 10px; font-weight: 700;">Schedule</span>
                                <div class="small fw-bold">
                                    <?= date("M d", strtotime($row['travel_date'])) ?> -
                                    <?= date("M d, Y", strtotime($row['return_date'])) ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!$isActionable && $statusText == 'Declined' && !empty($row['decline_reason'])): ?>
                            <div class="mt-3 pt-3 border-top border-danger border-opacity-25 text-danger small">
                                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Decline Reason:</strong>
                                <?= $row['decline_reason'] ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($isActionable): ?>
                        <div class="d-flex gap-2 mt-3 pt-2 border-top">
                            <form method="POST" id="form-<?= $row['ta_id'] ?>" class="w-100 d-flex gap-2">
                                <input type="hidden" name="ta_id" value="<?= $row['ta_id'] ?>">
                                <input type="hidden" name="requester_id" value="<?= $row['requester_id'] ?>">
                                <input type="hidden" name="action_type" id="action-<?= $row['ta_id'] ?>" value="">
                                <input type="hidden" name="decline_reason" id="reason-<?= $row['ta_id'] ?>" value="">

                                <button type="button" class="btn btn-confirm" onclick="confirmAction('confirm', <?= $row['ta_id'] ?>)">
                                    <i class="bi bi-check-lg me-1"></i> Confirm Request
                                </button>
                                <button type="button" class="btn btn-decline" onclick="confirmAction('reject', <?= $row['ta_id'] ?>)">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                <button type="submit" name="action_ta" id="submit-<?= $row['ta_id'] ?>" style="display:none;"></button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
            }
        } else {
            echo '<div class="text-center py-5 bg-white rounded-4 shadow-sm border border-light">
                    <i class="bi bi-folder2-open text-muted" style="font-size: 3.5rem;"></i>
                    <h5 class="mt-3 fw-bold text-secondary">Folder Empty</h5>
                    <p class="text-muted small">There are no records to show in this category.</p>
                  </div>';
        }
    }
    ?>

    <?php include '../template/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include '../template/script.php'; ?>

    <script>
        function confirmAction(type, id) {
            if (type === 'confirm') {
                Swal.fire({
                    title: 'Confirm Travel?',
                    text: 'This will forward the request to the Admin for final processing.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Yes, Confirm it!',
                    borderRadius: '20px'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                        document.getElementById('action-' + id).value = type;
                        document.getElementById('submit-' + id).click();
                    }
                });
            } else {
                Swal.fire({
                    title: 'Decline Request',
                    text: 'Please state the reason for declining:',
                    icon: 'warning',
                    input: 'textarea',
                    inputPlaceholder: 'Type your reason here...',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Submit Decision',
                    borderRadius: '20px',
                    preConfirm: (reason) => {
                        if (!reason) { Swal.showValidationMessage('A reason is required to decline a request!'); }
                        return reason;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                        document.getElementById('action-' + id).value = type;
                        document.getElementById('reason-' + id).value = result.value;
                        document.getElementById('submit-' + id).click();
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const type = sessionStorage.getItem('swal_type');
            const msg = sessionStorage.getItem('swal_msg');
            if (type && msg) {
                Swal.fire({ icon: type, title: msg, timer: 3000, showConfirmButton: false });
                sessionStorage.removeItem('swal_type');
                sessionStorage.removeItem('swal_msg');
            }
        });
    </script>
</body>

</html>