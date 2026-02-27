<?php
include '../config.php';


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>
    <style>
        .ta-card {
            border: none;
            border-radius: 20px;
            transition: 0.3s;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .ta-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(128, 0, 36, 0.15);
        }

        .card-maroon-top {
            height: 8px;
            background: #800000;
        }

        .btn-maroon {
            background: #800000;
            color: white;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-maroon:hover {
            background: #600000;
            color: white;
        }

        .dropzone-area {
            border: 2px dashed #ddd;
            border-radius: 15px;
            padding: 20px;
            background: #f9f9f9;
            cursor: pointer;
        }

        .dropzone-area:hover {
            border-color: #800000;
            background: #fffafa;
        }
    </style>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <?php

    $my_id = $employee_id;

    $query = "SELECT * FROM ta_tbl WHERE status = 2 AND ta_id IN (SELECT ta_id FROM ta_participants_tbl WHERE employee_id = '$my_id')";
    $result = mysqli_query($conn, $query);
    ?>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Travel Completion</h1>
            <p class="text-muted small">Select a completed travel to submit your accomplishment report and certificates.
            </p>
        </div>

        <section class="section">
            <div class="row">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card ta-card h-100">
                                <div class="card-maroon-top"></div>
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <span class="badge bg-light text-dark border">Ref: <?= $row['memo_no'] ?></span>
                                        <i class="bi bi-airplane-engines text-maroon fs-4"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1"><?= $row['destination'] ?></h5>
                                    <p class="text-muted small mb-3"><i class="bi bi-calendar-event me-1"></i>
                                        <?= date("M d, Y", strtotime($row['travel_date'])) ?></p>

                                    <hr class="opacity-25">

                                    <button class="btn btn-maroon w-100 py-2"
                                        onclick="openSubmitModal(<?= $row['ta_id'] ?>, '<?= $row['memo_no'] ?>', '<?= addslashes($row['destination']) ?>')">
                                        Submit Report <i class="bi bi-arrow-right-short ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-check2-circle text-success" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 fw-bold">All caught up!</h4>
                        <p class="text-muted">You have no pending travel reports to submit.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <div class="modal fade" id="submitReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="process_completion.php" method="POST" enctype="multipart/form-data"
                class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modal-title-text">Travel Report</h5>
                        <small class="text-muted" id="modal-memo-text"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="ta_id" id="modal-ta-id">

                    <div class="row">
                        <div class="col-md-7">
                            <label class="small fw-bold text-uppercase text-muted mb-2">Accomplishment Summary</label>
                            <textarea name="accomplishment_report" class="form-control border-0 bg-light p-3" rows="10"
                                placeholder="Explain what happened during the trip..." required
                                style="border-radius: 15px;"></textarea>
                        </div>
                        <div class="col-md-5">
                            <label class="small fw-bold text-uppercase text-muted mb-2">Attach Files
                                (Certs/Photos)</label>
                            <div class="dropzone-area" onclick="document.getElementById('fileInput').click()">
                                <i class="bi bi-cloud-arrow-up text-maroon fs-2"></i>
                                <p class="small fw-bold mb-0">Click to Upload</p>
                                <p class="smallest text-muted mb-0">Multiple files allowed</p>
                                <input type="file" name="cert_files[]" id="fileInput" class="d-none" multiple
                                    onchange="showFiles(this)">
                            </div>
                            <div id="filePreview" class="mt-3 small text-muted"></div>

                            <div class="alert alert-warning mt-3 border-0 smallest" style="border-radius: 12px;">
                                <i class="bi bi-info-circle-fill"></i> Status will be updated to <b>Completed</b>.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" name="submit_report" class="btn btn-maroon px-5 py-2 shadow w-100">Confirm
                        Submission</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>
    <script>
        function openSubmitModal(id, memo, dest) {
            document.getElementById('modal-ta-id').value = id;
            document.getElementById('modal-title-text').innerText = dest;
            document.getElementById('modal-memo-text').innerText = "Memorandum No: " + memo;
            var myModal = new bootstrap.Modal(document.getElementById('submitReportModal'));
            myModal.show();
        }

        function showFiles(input) {
            let list = document.getElementById('filePreview');
            list.innerHTML = "<b>Selected Files:</b><br>";
            for (let i = 0; i < input.files.length; i++) {
                list.innerHTML += "• " + input.files[i].name + "<br>";
            }
        }
    </script>
</body>

</html>