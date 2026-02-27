<?php
include './../config.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>
    <style>
        .admin-card {
            border-radius: 15px;
            border: none;
        }

        .text-maroon {
            color: rgb(128, 0, 36);
        }

        .memo-input {
            border-radius: 10px;
            border: 2px solid #eee;
            padding: 10px;
            font-weight: bold;
        }

        .memo-input:focus {
            border-color: #800000;
            box-shadow: none;
        }
    </style>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <?php


    // 2. Query: Kunin lahat ng 'Confirmed' (status 1) mula sa lahat ng departments
    $query = "SELECT t.*, e.employee_id as requester_id, e.first_name, e.last_name, e.position_name, d.department_name
          FROM ta_tbl t 
          JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id 
          JOIN employee_tbl e ON tp.employee_id = e.employee_id 
          JOIN department_tbl d ON e.department_id = d.department_id
          WHERE t.status = 1 
          GROUP BY t.ta_id ORDER BY t.head_confirmed_at ASC";
    $result = mysqli_query($conn, $query);
    // 3. Logic para sa Auto-Generated Final Approval
    if (isset($_POST['approve_final'])) {
        $ta_id = mysqli_real_escape_string($conn, $_POST['ta_id']);
        $target_emp_id = mysqli_real_escape_string($conn, $_POST['requester_id']);
        $admin_id = $_SESSION['userid'];

        $current_year = date("Y");

        // 1. Bilangin ang total approvals para sa auto-numbering
        $count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM ta_tbl WHERE status = 2 AND YEAR(admin_approved_at) = '$current_year'");
        $row_count = mysqli_fetch_assoc($count_query);

        $next_number = $row_count['total'] + 1;
        // HUWAG i-overwrite ang $ta_id dito!
    
        $generated_memo = "TA" . $current_year . "-" . str_pad($next_number, 2, "0", STR_PAD_LEFT);

        // 2. I-update ang TA record
        $update = mysqli_query($conn, "UPDATE ta_tbl SET 
                status = 2, 
                memo_no = '$generated_memo', 
                admin_approved_at = NOW() 
              WHERE ta_id = '$ta_id'");

        if ($update) {

            mysqli_query($conn, "UPDATE notifications_tbl SET is_read = 1 
                        WHERE recipient_id = '$admin_id' 
                        AND title LIKE '%Awaiting Approval%' 
                        AND is_read = 0");
            // 1. Mag-insert ng notification para sa Faculty
            $msg = "Your TA (Ref: $generated_memo) is now approved and ready for printing.";
            mysqli_query($conn, "INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, link) 
                        VALUES ('$target_emp_id', '$admin_id', 'Ready to Print', '$msg', 'my_travels.php')");

            // 2. Interactive SweetAlert Sequence
            echo "<script>
        // Unang Step: Loading Animation
        Swal.fire({
            title: 'Generating TA Document...',
            text: 'Please wait while we finalize the reference number.',
            allowOutsideClick: false,
            timer: 2000, // 2 seconds na loading
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
            }
        }).then(() => {
            // Pangalawang Step: Success Message
            Swal.fire({
                title: 'TA Approved Successfully!',
                html: 'Assigned Reference: <b style=\"color: #800000;\">$generated_memo</b>',
                icon: 'success',
                confirmButtonColor: '#800000',
                confirmButtonText: '<i class=\"bi bi-printer\"></i> Print Document Now',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Dito lang magbubukas ang bagong tab pag-click ng OK
                    window.open('./report/print_ta.php?id=$ta_id', '_blank');
                    
                    // I-refresh ang main list
                    window.location.href = 'pending_approval.php';
                }
            });
        });
    </script>";
            exit();
        }
    }
    ?>

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
                    <h5 class="card-title px-2">Requests for Final Review</h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>Personnel & Dept</th>
                                    <th>Destination</th>
                                    <th>Travel Dates</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)):
                                        $year = date("Y");
                                        $get_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM ta_tbl WHERE status = 2 AND YEAR(admin_approved_at) = '$year'");
                                        $res_count = mysqli_fetch_assoc($get_count);
                                        $current_total = $res_count['total'];
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">
                                                    <?= $row['first_name'] . ' ' . $row['last_name'] ?>
                                                </div>
                                                <div class="badge bg-light text-secondary border small" style="font-size: 9px;">
                                                    <?= $row['department_name'] ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-maroon fw-bold mb-0"><?= $row['destination'] ?></div>
                                                <small class="text-muted small"><?= $row['task'] ?></small>
                                            </td>
                                            <td>
                                                <div class="small fw-bold text-dark">
                                                    <?= date("M d", strtotime($row['travel_date'])) ?> -
                                                    <?= date("M d, Y", strtotime($row['return_date'])) ?>
                                                </div>
                                                <div class="smallest text-muted">Confirmed:
                                                    <?= date("M d, h:i A", strtotime($row['head_confirmed_at'])) ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-info rounded-pill px-3 me-1"
                                                    onclick='trackTravel(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <i class="bi bi-geo"></i>
                                                </button>

                                                <?php
                                                $next_val = $current_total + 1; // "Pang-ilan" siya sa database
                                                $suggested_memo = "TA" . $year . "-" . str_pad($next_val, 2, "0", STR_PAD_LEFT);
                                                ?>
                                                <button class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm"
                                                    onclick="openApprovalModal(<?= $row['ta_id'] ?>, '<?= addslashes($row['first_name']) ?>', <?= $row['requester_id'] ?>, '<?= $suggested_memo ?>')">
                                                    <i class="bi bi-pencil-square me-1"></i> Approve
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted italic small">No confirmed
                                            requests awaiting final approval.</td>
                                    </tr>
                                <?php endif; ?>
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
                            style="font-size: 10px;">Assigned Memorandum Number</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-hash text-maroon me-2 fs-4"></i>
                            <input type="text" name="memo_no" id="modal-memo-no"
                                class="form-control border-0 bg-transparent fw-bold fs-5 p-0" style="color: #800000;"
                                readonly>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 small mb-0" style="background: #f0f7ff; color: #00529b;">
                        <i class="bi bi-info-circle-fill me-2"></i> This number is automatically generated based on the
                        current year and total approvals.
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="approve_final"
                        class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">Confirm & Approve</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'tracking_modal.php'; ?>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Idinagdag ang 'suggestedMemo' sa parameters
        function openApprovalModal(taId, name, reqId, suggestedMemo) {
            document.getElementById('modal-ta-id').value = taId;
            document.getElementById('modal-name').innerText = name;
            document.getElementById('modal-req-id').value = reqId;

            // Dito natin ilalagay ang value sa Read-Only input
            document.getElementById('modal-memo-no').value = suggestedMemo;

            // Siguraduhing initialized ang Bootstrap Modal
            var myModal = new bootstrap.Modal(document.getElementById('approvalModal'));
            myModal.show();
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