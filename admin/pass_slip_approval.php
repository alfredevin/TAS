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
    <?php

    // --- BACKEND LOGIC PARA SA APPROVE / DECLINE ---
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_ps'])) {
        $admin_id = $_SESSION['userid']; // ID ng nakalog-in na Admin
        $ps_id = mysqli_real_escape_string($conn, $_POST['ps_id']);
        $action = $_POST['action_type']; // 'approve' or 'decline'
        $target_emp_id = mysqli_real_escape_string($conn, $_POST['employee_id']);

        if ($action == 'approve') {
            $status = 1;
            $msg = "Your Pass Slip for today has been approved.";
            $swal_type = 'success';
            $swal_msg = 'Pass Slip Approved!';
        } else {
            $status = 99; // Declined
            $reason = mysqli_real_escape_string($conn, $_POST['decline_reason']);
            $msg = "Your Pass Slip was declined. Reason: " . $reason;
            $swal_type = 'error';
            $swal_msg = 'Pass Slip Declined.';

            // I-save ang reason sa database
            mysqli_query($conn, "UPDATE pass_slip_tbl SET admin_remarks = '$reason' WHERE ps_id = '$ps_id'");
        }

        // Update status
        $update = mysqli_query($conn, "UPDATE pass_slip_tbl SET status = '$status' WHERE ps_id = '$ps_id'");

        if ($update) {
            // Notify Employee
            mysqli_query($conn, "INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, link) 
                            VALUES ('$target_emp_id', '$admin_id', 'Pass Slip Update', '$msg', 'pass_slip.php')");

            echo "<script>
            sessionStorage.setItem('swal_type', '$swal_type');
            sessionStorage.setItem('swal_msg', '$swal_msg');
            window.location.href='pass_slip_approval.php';
        </script>";
        }
    }

    // --- FETCH DATA: Kunin lahat ng Pending (0) at kasalukuyang araw pataas ---
    $today = date("Y-m-d");
    $query = "SELECT ps.*, e.first_name, e.last_name, d.department_name 
          FROM pass_slip_tbl ps
          JOIN employee_tbl e ON ps.employee_id = e.employee_id
          JOIN department_tbl d ON e.department_id = d.department_id
          WHERE ps.status = 0 AND ps.ps_date >= '$today'
          ORDER BY ps.created_at ASC";
    $result = mysqli_query($conn, $query);

    // Kunin ang History para sa tab
    $history_query = "SELECT ps.*, e.first_name, e.last_name, d.department_name 
                  FROM pass_slip_tbl ps
                  JOIN employee_tbl e ON ps.employee_id = e.employee_id
                  JOIN department_tbl d ON e.department_id = d.department_id
                  WHERE ps.status IN (1, 99)
                  ORDER BY ps.ps_date DESC LIMIT 50";
    $history_result = mysqli_query($conn, $history_query);
    ?>

       <style>
        .admin-card {
            border-radius: 15px;
            border: none;
        }

        .text-maroon {
            color: #800000;
        }

        .nav-pills .nav-link.active {
            background-color: #800000;
        }

        .nav-pills .nav-link {
            color: #800000;
            font-weight: 600;
        }
    </style>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Pass Slip Approvals</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Admin</li>
                    <li class="breadcrumb-item active">Pass Slips</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-pending-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-pending" type="button">
                        Pending Requests
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-history-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-history" type="button">
                        History
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">

                <div class="tab-pane fade show active" id="pills-pending" role="tabpanel">
                    <div class="card admin-card shadow-sm">
                        <div class="card-body pt-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Employee</th>
                                            <th>Travel Details</th>
                                            <th>Time & Date</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?= $row['first_name'] . ' ' . $row['last_name'] ?>
                                                    </div>
                                                    <small class="text-muted"><?= $row['department_name'] ?></small>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= $row['destination'] ?></div>
                                                    <div class="small">
                                                        <span
                                                            class="badge bg-light text-dark border me-1"><?= $row['issued_for'] ?></span>
                                                        <span class="text-muted"><?= $row['specific_purpose'] ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-maroon">
                                                        <?= date("F d, Y", strtotime($row['ps_date'])) ?>
                                                    </div>
                                                    <small class="text-muted"><i
                                                            class="bi bi-clock me-1"></i><?= date("h:i A", strtotime($row['time_departure'])) ?>
                                                        - <?= date("h:i A", strtotime($row['time_return'])) ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <form method="POST" id="form-<?= $row['ps_id'] ?>">
                                                        <input type="hidden" name="ps_id" value="<?= $row['ps_id'] ?>">
                                                        <input type="hidden" name="employee_id"
                                                            value="<?= $row['employee_id'] ?>">
                                                        <input type="hidden" name="action_type"
                                                            id="action-<?= $row['ps_id'] ?>" value="">
                                                        <input type="hidden" name="decline_reason"
                                                            id="reason-<?= $row['ps_id'] ?>" value="">

                                                        <button type="button"
                                                            class="btn btn-sm btn-success rounded-pill px-3 shadow-sm me-1"
                                                            onclick="confirmAction('approve', <?= $row['ps_id'] ?>)">
                                                            <i class="bi bi-check-lg me-1"></i> Approve
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm"
                                                            onclick="confirmAction('decline', <?= $row['ps_id'] ?>)">
                                                            <i class="bi bi-x-lg me-1"></i> Decline
                                                        </button>

                                                        <button type="submit" name="action_ps"
                                                            id="submit-<?= $row['ps_id'] ?>" style="display:none;"></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pills-history" role="tabpanel">
                    <div class="card admin-card shadow-sm">
                        <div class="card-body pt-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Employee</th>
                                            <th>Details</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($h_row = mysqli_fetch_assoc($history_result)):
                                            $badge = ($h_row['status'] == 1) ? 'bg-success' : 'bg-danger';
                                            $lbl = ($h_row['status'] == 1) ? 'Approved' : 'Declined';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">
                                                        <?= $h_row['first_name'] . ' ' . $h_row['last_name'] ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?= $h_row['destination'] ?></div>
                                                    <small class="text-muted"><?= $h_row['specific_purpose'] ?></small>
                                                </td>
                                                <td class="small"><?= date("M d, Y", strtotime($h_row['ps_date'])) ?></td>
                                                <td>
                                                    <span class="badge <?= $badge ?> rounded-pill"><?= $lbl ?></span>
                                                    <?php if ($h_row['status'] == 1): ?>
                                                        <a href="./report/print_pass_slip.php?id=<?= $h_row['ps_id'] ?>" target="_blank"
                                                            class="btn btn-sm btn-light border ms-2 rounded-circle"
                                                            title="Print Pass Slip">
                                                            <i class="bi bi-printer text-dark"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include '../template/script.php'; ?>

    <script>
        // SweetAlert Logic for Approve/Decline with Reason
        function confirmAction(type, id) {
            if (type === 'approve') {
                Swal.fire({
                    title: 'Approve Pass Slip?',
                    text: 'The employee will be notified and can print their slip.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Yes, Approve'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('action-' + id).value = 'approve';
                        document.getElementById('submit-' + id).click();
                    }
                });
            } else {
                Swal.fire({
                    title: 'Decline Pass Slip?',
                    text: 'Please state the reason for declining:',
                    icon: 'warning',
                    input: 'text',
                    inputPlaceholder: 'e.g. Incomplete details, Not allowed today...',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Decline Request',
                    preConfirm: (reason) => {
                        if (!reason) {
                            Swal.showValidationMessage('A reason is required!');
                        }
                        return reason;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('action-' + id).value = 'decline';
                        document.getElementById('reason-' + id).value = result.value;
                        document.getElementById('submit-' + id).click();
                    }
                });
            }
        }

        // Success/Error Messages Handler
        document.addEventListener('DOMContentLoaded', function () {
            const type = sessionStorage.getItem('swal_type');
            const msg = sessionStorage.getItem('swal_msg');
            if (type && msg) {
                Swal.fire({ icon: type, title: msg, timer: 2000, showConfirmButton: false });
                sessionStorage.removeItem('swal_type');
                sessionStorage.removeItem('swal_msg');
            }
        });
    </script>
</body>

</html>