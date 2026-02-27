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
        .card-confirm {
            border-radius: 15px;
            border: none;
            transition: 0.3s;
        }

        .card-confirm:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
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
        }

        .btn-decline {
            border-radius: 50px;
            padding: 5px 20px;
            font-weight: 600;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: #fff3f3;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #800000;
        }
    </style>
    <?php

    $my_id = $_SESSION['employee_id'];
    // Kunin ang department_id ng logged-in Head
    $get_head_info = mysqli_query($conn, "SELECT department_id FROM employee_tbl WHERE employee_id = '$my_id'");
    $head_info = mysqli_fetch_assoc($get_head_info);
    $my_dept_id = $head_info['department_id'] ?? 0;

    // 2. Query: Kunin lahat ng 'Pending' (status 0) sa department niya
    // Inayos natin ang join para makuha ang tamang Requester ID
    $query = "SELECT t.*, e.employee_id as requester_id, e.first_name, e.last_name, e.position_name 
          FROM ta_tbl t 
          JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id 
          JOIN employee_tbl e ON tp.employee_id = e.employee_id 
          WHERE t.status = 0 AND e.department_id = '$my_dept_id'
          GROUP BY t.ta_id ORDER BY t.submitted_at ASC";
    $result = mysqli_query($conn, $query);

    // Summary count para sa Dashboard Cards
    $count_pending = mysqli_num_rows($result);

    // 3. Logic para sa Approval o Rejection
    if (isset($_POST['action_ta'])) {
        $ta_id = mysqli_real_escape_string($conn, $_POST['ta_id']);
        $action = $_POST['action_type'];
        $target_emp_id = mysqli_real_escape_string($conn, $_POST['requester_id']);

        if ($action == 'confirm') {
            // Update to Status 1 (Confirmed by Head)
            mysqli_query($conn, "UPDATE ta_tbl SET status = 1, head_confirmed_at = NOW() WHERE ta_id = '$ta_id'");

            // NOTIFY ADMIN (Forward)
            $admin_query = mysqli_query($conn, "SELECT userid FROM user_tbl WHERE usertype = 1 LIMIT 1");
            $admin = mysqli_fetch_assoc($admin_query);
            $admin_id = $admin['userid'] ?? 0;

            $msg_admin = "A travel request from your department is ready for final approval.";
            mysqli_query($conn, "INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, link) 
                            VALUES ('$admin_id', '$my_id', 'TA Awaiting Approval', '$msg_admin', 'pending_approval.php')");

            // NOTIFY EMPLOYEE (Feedback)
            $msg_emp = "Good news! Your TA request has been confirmed by your Department Head.";
            mysqli_query($conn, "INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, link) 
                            VALUES ('$target_emp_id', '$my_id', 'TA Confirmed', '$msg_emp', 'my_travels.php')");

            $alert_type = "success";
            $alert_msg = "Travel request has been confirmed!";
        } else {
            // Update to Status 99 (Rejected)
            mysqli_query($conn, "UPDATE ta_tbl SET status = 99 WHERE ta_id = '$ta_id'");

            // NOTIFY EMPLOYEE ONLY
            $msg_rej = "Your TA request was not confirmed. Please visit the office for details.";
            mysqli_query($conn, "INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, link) 
                            VALUES ('$target_emp_id', '$my_id', 'TA Notification', '$msg_rej', 'my_travels.php')");

            $alert_type = "error";
            $alert_msg = "Travel request has been declined.";
        }

        echo "<script>
        sessionStorage.setItem('swal_type', '$alert_type');
        sessionStorage.setItem('swal_msg', '$alert_msg');
        window.location.href='for_confirmation.php';
    </script>";
    }
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
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card card-confirm shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3"><i class="bi bi-hourglass-split"></i></div>
                                <div>
                                    <h6 class="text-muted mb-0 small">Pending for your Action</h6>
                                    <h3 class="mb-0 fw-bold"><?= $count_pending ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body pt-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>Faculty Details</th>
                                    <th>Travel Information</th>
                                    <th>Dates</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                                        <i class="bi bi-person text-secondary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark"><?= $row['first_name'] . ' ' . $row['last_name'] ?></div>
                                                        <small class="text-muted text-uppercase" style="font-size: 10px;"><?= $row['position_name'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-maroon fw-bold mb-0"><?= $row['destination'] ?></div>
                                                <div class="text-muted small text-truncate" style="max-width: 250px;">
                                                    <i class="bi bi-info-circle me-1"></i><?= $row['task'] ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small fw-bold"><i class="bi bi-calendar-event me-1"></i><?= date("M d", strtotime($row['travel_date'])) ?></div>
                                                <div class="smallest text-muted">Returns: <?= date("M d, Y", strtotime($row['return_date'])) ?></div>
                                            </td>
                                            <td class="text-center">
                                                <form method="POST" id="form-<?= $row['ta_id'] ?>">
                                                    <input type="hidden" name="ta_id" value="<?= $row['ta_id'] ?>">
                                                    <input type="hidden" name="requester_id" value="<?= $row['requester_id'] ?>">
                                                    <input type="hidden" name="action_type" id="action-<?= $row['ta_id'] ?>" value="">

                                                    <button type="button" class="btn btn-confirm btn-sm shadow-sm"
                                                        onclick="confirmAction('confirm', <?= $row['ta_id'] ?>)">
                                                        <i class="bi bi-check2"></i> Confirm
                                                    </button>

                                                    <button type="button" class="btn btn-outline-danger btn-sm btn-decline ms-1"
                                                        onclick="confirmAction('reject', <?= $row['ta_id'] ?>)">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>

                                                    <button type="submit" name="action_ta" id="submit-<?= $row['ta_id'] ?>" style="display:none;"></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted small italic">
                                            <i class="bi bi-check-all fs-2 d-block mb-2"></i>
                                            No pending confirmations found in your department.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../template/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include '../template/script.php'; ?>

    <script>
        // SweetAlert function for action verification
        function confirmAction(type, id) {
            const title = (type === 'confirm') ? 'Confirm Travel?' : 'Decline Request?';
            const text = (type === 'confirm') ? 'This will forward the request to the Admin.' : 'The employee will be notified of the rejection.';
            const color = (type === 'confirm') ? '#28a745' : '#dc3545';

            Swal.fire({
                title: title,
                text: text,
                icon: (type === 'confirm') ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: color,
                confirmButtonText: (type === 'confirm') ? 'Yes, Confirm it!' : 'Yes, Decline it'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('action-' + id).value = type;
                    document.getElementById('submit-' + id).click();
                }
            });
        }

        // Success Alert handling
        document.addEventListener('DOMContentLoaded', function() {
            const type = sessionStorage.getItem('swal_type');
            const msg = sessionStorage.getItem('swal_msg');
            if (type && msg) {
                Swal.fire({
                    icon: type,
                    title: msg,
                    timer: 2000,
                    showConfirmButton: false
                });
                sessionStorage.removeItem('swal_type');
                sessionStorage.removeItem('swal_msg');
            }
        });
    </script>
</body>

</html>