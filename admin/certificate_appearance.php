<?php
include '../config.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>
    <style>
        .verify-card {
            border: none;
            border-radius: 15px;
        }

        .text-maroon {
            color: #800000;
        }

        .attachment-pill {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 12px;
            display: inline-block;
            margin: 2px;
            transition: 0.3s;
        }

        .attachment-pill:hover {
            background: #800000;
            color: white !important;
        }
    </style>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <?php

    // 2. Query: Kunin lahat ng COMPLETED travels (Status 3)
    $query = "SELECT t.*, c.accomplishment_text, c.submitted_at as completion_date, 
          e.first_name, e.last_name, d.department_name
          FROM ta_tbl t 
          JOIN travel_completions_tbl c ON t.ta_id = c.ta_id 
          JOIN employee_tbl e ON c.employee_id = e.employee_id 
          JOIN department_tbl d ON e.department_id = d.department_id
          WHERE t.status = 3
          ORDER BY c.submitted_at DESC";
    $result = mysqli_query($conn, $query);
    ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Completion Verification</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Admin</li>
                    <li class="breadcrumb-item active">Verify Reports</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="card verify-card shadow-sm">
                <div class="card-body pt-4">
                    <h5 class="card-title px-2">Submissions for Review</h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>Personnel</th>
                                    <th>Ref / Destination</th>
                                    <th>Completion Date</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)):
                                    $ta_id = $row['ta_id'];
                                    // Kunin ang bilang ng in-upload na files
                                    $files_q = mysqli_query($conn, "SELECT * FROM travel_attachments_tbl WHERE ta_id = '$ta_id'");
                                    $file_count = mysqli_num_rows($files_q);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <?= $row['first_name'] . ' ' . $row['last_name'] ?>
                                            </div>
                                            <small class="text-muted"><?= $row['department_name'] ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary mb-1"
                                                style="font-family: monospace;"><?= $row['memo_no'] ?></span>
                                            <div class="small fw-bold"><?= $row['destination'] ?></div>
                                        </td>
                                        <td>
                                            <div class="small"><?= date("M d, Y", strtotime($row['completion_date'])) ?>
                                            </div>
                                            <div class="smallest text-muted">
                                                <?= date("h:i A", strtotime($row['completion_date'])) ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm"
                                                onclick="openReviewModal(<?= htmlspecialchars(json_encode($row)) ?>, <?= $file_count ?>)">
                                                <i class="bi bi-search me-1"></i> Review
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

    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">Review Travel Outcome</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <label class="smallest text-uppercase fw-bold text-muted mb-2">Personnel Details</label>
                            <h6 id="rev-name" class="fw-bold mb-0"></h6>
                            <p id="rev-dept" class="text-muted small mb-3"></p>

                            <label class="smallest text-uppercase fw-bold text-muted mb-2">Accomplishment Report</label>
                            <div id="rev-report" class="p-3 bg-light rounded small italic mb-3"
                                style="white-space: pre-wrap; border-left: 3px solid #800000;"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="smallest text-uppercase fw-bold text-muted mb-2">Uploaded Evidence (<span
                                    id="rev-file-count">0</span>)</label>
                            <div id="attachment-list" class="mt-2">
                            </div>

                            <div class="alert alert-info mt-4 border-0 smallest">
                                <i class="bi bi-info-circle-fill me-1"></i> Verify if the uploaded certificates are
                                clear and valid.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close
                        Review</button>
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" onclick="markVerified()">
                        <i class="bi bi-check-all"></i> Mark as Verified
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>
    <script>
        let selectedTaId = null;

        function openReviewModal(data) {
            selectedTaId = data.ta_id;
            document.getElementById('rev-name').innerText = data.first_name + ' ' + data.last_name;
            document.getElementById('rev-dept').innerText = data.department_name;
            document.getElementById('rev-report').innerText = data.accomplishment_text;

            const container = document.getElementById('attachment-list');
            container.innerHTML = '<div class="spinner-border spinner-border-sm text-danger"></div>';

            fetch(`get_attachments.php?ta_id=${data.ta_id}`)
                .then(res => res.json())
                .then(files => {
                    container.innerHTML = '';
                    if (files.length > 0) {
                        files.forEach(f => {
                            container.innerHTML += `<a href="../uploads/completions/${f.file_path}" target="_blank" class="attachment-pill"><i class="bi bi-file-earmark-image me-2"></i>View Attachment</a>`;
                        });
                    } else {
                        container.innerHTML = '<p class="text-muted small italic">No files uploaded.</p>';
                    }
                });

            new bootstrap.Modal(document.getElementById('reviewModal')).show();
        }

        function markVerified() {
            Swal.fire({
                title: 'Archive this record?',
                text: "This will mark the travel as officially completed.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Yes, Verify It'
            }).then((result) => {
                if (result.isConfirmed) {
                    let fd = new FormData();
                    fd.append('ta_id', selectedTaId);

                    fetch('mark_verified.php', { method: 'POST', body: fd })
                        .then(r => r.text())
                        .then(res => {
                            if (res === 'success') {
                                Swal.fire('Saved!', 'Travel archived successfully.', 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Error', 'Failed to save verification.', 'error');
                            }
                        });
                }
            });
        }
    </script>
</body>

</html>