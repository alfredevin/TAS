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

    // --- BACKEND LOGIC: ACTIONS ---
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $admin_id = $_SESSION['userid'];
        $ta_id = mysqli_real_escape_string($conn, $_POST['ta_id']);
        $target_emp_id = mysqli_real_escape_string($conn, $_POST['requester_id']);

        // 1. FINAL APPROVAL LOGIC
        if (isset($_POST['approve_final'])) {
            $current_year = date("Y");
            $count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM ta_tbl WHERE status = 2 AND YEAR(admin_approved_at) = '$current_year'");
            $row_count = mysqli_fetch_assoc($count_query);
            $next_number = $row_count['total'] + 1;
            $generated_memo = "TA" . $current_year . "-" . str_pad($next_number, 2, "0", STR_PAD_LEFT);

            $update = mysqli_query($conn, "UPDATE ta_tbl SET status = 2, memo_no = '$generated_memo', admin_approved_at = NOW() WHERE ta_id = '$ta_id'");

            if ($update) {
                $msg = "Your TA (Ref: $generated_memo) is now approved and ready for printing.";
                mysqli_query($conn, "INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, link) VALUES ('$target_emp_id', '$admin_id', 'Ready to Print', '$msg', 'my_travels.php')");

                // Success Sequence using SweetAlert
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Generating Document...',
                        timer: 1500,
                        didOpen: () => { Swal.showLoading(); }
                    }).then(() => {
                        Swal.fire({
                            title: 'Approved!',
                            html: 'Assigned: <b>$generated_memo</b>',
                            icon: 'success',
                            confirmButtonText: 'Print Now'
                        }).then(() => { 
                            window.open('./report/print_ta.php?id=$ta_id', '_blank');
                            window.location.href='pending_approval.php'; 
                        });
                    });
                });
            </script>";
            }
        }

        // 2. REJECT OR DECLINE LOGIC
        if (isset($_POST['admin_action'])) {
            $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
            $type = $_POST['action_type']; // 'reject' or 'decline'
    
            if ($type == 'reject') {
                $status = 11; // Return to Faculty for Correction
                $title = "TA Needs Correction";
                $alert = "Request returned to faculty member.";
            } else {
                $status = 99; // Final Decline
                $title = "TA Declined (Final)";
                $alert = "Request has been permanently cancelled.";
            }

            $update = mysqli_query($conn, "UPDATE ta_tbl SET status = $status, admin_remarks = '$remarks' WHERE ta_id = '$ta_id'");
            if ($update) {
                mysqli_query($conn, "INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, link) VALUES ('$target_emp_id', '$admin_id', '$title', '$remarks', 'my_travels.php')");
                echo "<script>
                sessionStorage.setItem('swal_type', 'info');
                sessionStorage.setItem('swal_msg', '$alert');
                window.location.href='pending_approval.php';
            </script>";
            }
        }
    }

    // FETCH DATA
    $query = "SELECT t.*, e.employee_id as requester_id, e.first_name, e.last_name, d.department_name
          FROM ta_tbl t 
          JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id 
          JOIN employee_tbl e ON tp.employee_id = e.employee_id 
          JOIN department_tbl d ON e.department_id = d.department_id
          WHERE t.status = 1 
          GROUP BY t.ta_id ORDER BY t.head_confirmed_at ASC";
    $result = mysqli_query($conn, $query);
    ?>

    <style>
        .admin-card {
            border-radius: 15px;
            border: none;
        }

        .text-maroon {
            color: #800000;
        }

        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }

        .btn-approve {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .btn-reject {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .btn-decline {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn-action:hover {
            transform: scale(1.1);
            filter: brightness(0.9);
        }
    </style>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Final TA Approval</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Admin</li>
                    <li class="breadcrumb-item active">Pending Review</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="card admin-card shadow-sm">
                <div class="card-body pt-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>Personnel</th>
                                    <th>Travel Details</th>
                                    <th>Dates</th>
                                    <th class="text-center">Review Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)):
                                    $year = date("Y");
                                    $get_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM ta_tbl WHERE status = 2 AND YEAR(admin_approved_at) = '$year'");
                                    $res_count = mysqli_fetch_assoc($get_count);
                                    $suggested_memo = "TA" . $year . "-" . str_pad($res_count['total'] + 1, 2, "0", STR_PAD_LEFT);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= $row['first_name'] . ' ' . $row['last_name'] ?></div>
                                            <small class="text-muted"><?= $row['department_name'] ?></small>
                                        </td>
                                        <td>
                                            <div class="text-maroon fw-bold mb-0"><?= $row['destination'] ?></div>
                                            <small class="text-muted"><?= $row['task'] ?></small>
                                        </td>
                                        <td>
                                            <div class="small fw-bold"><?= date("M d, Y", strtotime($row['travel_date'])) ?>
                                            </div>
                                            <div class="smallest text-muted">Confirmed by Head</div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-light btn-sm rounded-pill px-3 border me-2"
                                                onclick='trackTravel(<?= json_encode($row); ?>)'>
                                                <i class="bi bi-geo"></i>
                                            </button>

                                            <button class="btn-action btn-approve" title="Final Approve"
                                                onclick="openApprovalModal(<?= $row['ta_id'] ?>, '<?= $row['first_name'] ?>', <?= $row['requester_id'] ?>, '<?= $suggested_memo ?>')">
                                                <i class="bi bi-check-lg"></i>
                                            </button>

                                            <button class="btn-action btn-reject" title="Return for Correction"
                                                onclick="openActionModal('reject', <?= $row['ta_id'] ?>, <?= $row['requester_id'] ?>)">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>

                                            <button class="btn-action btn-decline" title="Final Decline"
                                                onclick="openActionModal('decline', <?= $row['ta_id'] ?>, <?= $row['requester_id'] ?>)">
                                                <i class="bi bi-x-lg"></i>
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

    <div class="modal fade" id="approvalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0 text-center">
                    <h5 class="modal-title fw-bold w-100">Final Confirmation</h5>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small text-center mb-4">You are about to approve the TA for <span
                            id="modal-name" class="fw-bold text-dark"></span>.</p>
                    <input type="hidden" name="ta_id" id="modal-ta-id">
                    <input type="hidden" name="requester_id" id="modal-req-id">
                    <div class="bg-light p-3 rounded-3 border mb-3">
                        <label class="smallest text-uppercase fw-bold text-secondary d-block mb-1"
                            style="font-size: 10px;">Memorandum Number</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-hash text-maroon me-2 fs-4"></i>
                            <input type="text" name="memo_no" id="modal-memo-no"
                                class="form-control border-0 bg-transparent fw-bold fs-5 p-0" style="color: #800000;"
                                readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="approve_final"
                        class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">Finalize TA</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold" id="actionModalTitle">Reason</h5>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="ta_id" id="action-ta-id">
                    <input type="hidden" name="requester_id" id="action-req-id">
                    <input type="hidden" name="action_type" id="action-type-input">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">ADMIN REMARKS</label>
                        <textarea name="remarks" class="form-control border-2" rows="4"
                            placeholder="Explain the decision..." required></textarea>
                    </div>
                    <div id="action-hint" class="alert small border-0 py-2"></div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" name="admin_action" id="action-submit-btn"
                        class="btn w-100 rounded-pill fw-bold py-2 shadow-sm">Submit Decision</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'tracking_modal.php'; ?>
    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function openApprovalModal(taId, name, reqId, memo) {
            document.getElementById('modal-ta-id').value = taId;
            document.getElementById('modal-name').innerText = name;
            document.getElementById('modal-req-id').value = reqId;
            document.getElementById('modal-memo-no').value = memo;
            new bootstrap.Modal(document.getElementById('approvalModal')).show();
        }

        function openActionModal(type, taId, reqId) {
            const title = document.getElementById('actionModalTitle');
            const hint = document.getElementById('action-hint');
            const btn = document.getElementById('action-submit-btn');

            document.getElementById('action-ta-id').value = taId;
            document.getElementById('action-req-id').value = reqId;
            document.getElementById('action-type-input').value = type;

            if (type === 'reject') {
                title.innerText = "Return to Faculty";
                title.className = "modal-title fw-bold text-warning";
                hint.className = "alert alert-warning border-0 small";
                hint.innerHTML = "<b>Reject:</b> Allows user to edit and resubmit.";
                btn.className = "btn btn-warning rounded-pill fw-bold shadow-sm";
            } else {
                title.innerText = "Final Decline";
                title.className = "modal-title fw-bold text-danger";
                hint.className = "alert alert-danger border-0 small";
                hint.innerHTML = "<b>Decline:</b> Permanent cancellation.";
                btn.className = "btn btn-danger rounded-pill fw-bold shadow-sm";
            }
            new bootstrap.Modal(document.getElementById('actionModal')).show();
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