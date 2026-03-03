<?php
include './../config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>
    <style>
        .card-confirm {
            border-radius: 15px;
            border: none;
            transition: 0.3s;
        }

        .text-maroon {
            color: rgb(128, 0, 36);
        }

        .btn-confirm {
            background-color: #28a745;
            color: white;
            border-radius: 50px;
            padding: 5px 20px;
            font-weight: 600;
            border: none;
        }

        .btn-confirm:hover {
            background-color: #218838;
            color: white;
        }

        .btn-decline {
            border-radius: 50px;
            padding: 5px 20px;
            font-weight: 600;
        }

        .nav-pills .nav-link.active {
            background-color: #800000;
        }

        .nav-pills .nav-link {
            color: #800000;
            font-weight: 600;
        }

        .badge-pending {
            background: #fff3f3;
            color: #800000;
            border: 1px solid #800000;
        }
    </style>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
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
            // BAGO: Kunin ang reason at i-sanitize para iwas SQL Injection
            $reason = mysqli_real_escape_string($conn, $_POST['decline_reason']);

            // BAGO: I-update ang status at i-save ang reason
            mysqli_query($conn, "UPDATE ta_tbl SET status = 99, decline_reason = '$reason' WHERE ta_id = '$ta_id'");

            // BAGO: Isama ang reason sa notification ng employee
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
        <div class="pagetitle">
            <h1>Department Confirmation</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">E-TAMS</li>
                    <li class="breadcrumb-item active">Approvals</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-pending-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-pending" type="button">
                        Pending <span class="badge badge-pending ml-2"><?= $count_pending ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-confirmed-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-confirmed" type="button">History: Confirmed</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-declined-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-declined" type="button">History: Declined</button>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-pending" role="tabpanel">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                        <div class="card-body pt-4">
                            <?php renderTable($pending_res, true); ?>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pills-confirmed" role="tabpanel">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                        <div class="card-body pt-4">
                            <?php renderTable($confirmed_res, false, 'text-success', 'Confirmed'); ?>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pills-declined" role="tabpanel">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                        <div class="card-body pt-4">
                            <?php renderTable($declined_res, false, 'text-danger', 'Declined'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php
    // Reusable Table Function
    function renderTable($result, $isActionable, $statusClass = '', $statusText = '')
    {
        ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th>Faculty Details</th>
                        <th>Travel Information</th>
                        <th>Dates</th>
                        <th class="text-center"><?= $isActionable ? 'Action' : 'Status' ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2"
                                            style="width: 35px; height: 35px;">
                                            <i class="bi bi-person text-secondary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= $row['first_name'] . ' ' . $row['last_name'] ?></div>
                                            <small class="text-muted text-uppercase"
                                                style="font-size: 10px;"><?= $row['position_name'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-maroon fw-bold mb-0"><?= $row['destination'] ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 250px;">
                                        <i class="bi bi-info-circle me-1"></i><?= $row['task'] ?>
                                    </div>
                                    <?php if (!$isActionable && $statusText == 'Declined' && !empty($row['decline_reason'])): ?>
                                        <div class="text-danger small mt-1">
                                            <strong>Reason:</strong> <?= $row['decline_reason'] ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small fw-bold"><i
                                            class="bi bi-calendar-event me-1"></i><?= date("M d", strtotime($row['travel_date'])) ?>
                                    </div>
                                    <div class="smallest text-muted">Returns: <?= date("M d, Y", strtotime($row['return_date'])) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($isActionable): ?>
                                        <form method="POST" id="form-<?= $row['ta_id'] ?>">
                                            <input type="hidden" name="ta_id" value="<?= $row['ta_id'] ?>">
                                            <input type="hidden" name="requester_id" value="<?= $row['requester_id'] ?>">
                                            <input type="hidden" name="action_type" id="action-<?= $row['ta_id'] ?>" value="">

                                            <input type="hidden" name="decline_reason" id="reason-<?= $row['ta_id'] ?>" value="">

                                            <button type="button" class="btn btn-confirm btn-sm shadow-sm"
                                                onclick="confirmAction('confirm', <?= $row['ta_id'] ?>)">
                                                <i class="bi bi-check2"></i> Confirm
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-decline ms-1"
                                                onclick="confirmAction('reject', <?= $row['ta_id'] ?>)">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <button type="submit" name="action_ta" id="submit-<?= $row['ta_id'] ?>"
                                                style="display:none;"></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge rounded-pill <?= $statusClass ?> bg-light border"><?= $statusText ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    ?>

    <?php include '../template/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include '../template/script.php'; ?>

    <script>
        // BAGO: Updated SweetAlert Logic para humingi ng reason
        function confirmAction(type, id) {
            if (type === 'confirm') {
                Swal.fire({
                    title: 'Confirm Travel?',
                    text: 'This will forward the request to the Admin.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Yes, Confirm it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('action-' + id).value = type;
                        document.getElementById('submit-' + id).click();
                    }
                });
            } else {
                Swal.fire({
                    title: 'Decline Request?',
                    text: 'Please state the reason for declining:',
                    icon: 'warning',
                    input: 'textarea', // Gumawa ng text area
                    inputPlaceholder: 'Type your reason here...',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, Decline it',
                    preConfirm: (reason) => {
                        if (!reason) {
                            Swal.showValidationMessage('A reason is required to decline a request!');
                        }
                        return reason;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Ipasa yung nilagay sa textarea papunta sa hidden form input
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