<?php
include '../config.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>
    <style>
        .history-card {
            border: none;
            border-radius: 15px;
            transition: 0.3s;
        }

        .history-card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .report-box {
            background: #fdfdfd;
            border-left: 4px solid #800000;
            padding: 15px;
            border-radius: 5px;
            font-style: italic;
        }

        .cert-link {
            text-decoration: none;
            color: #800000;
            font-weight: 600;
            font-size: 13px;
            transition: 0.2s;
        }

        .cert-link:hover {
            color: #b00000;
        }

        .badge-memo {
            background: #800000;
            font-family: monospace;
        }
    </style>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <?php

    $my_id = $employee_id;

    // Query: Kunin lahat ng COMPLETED travels (Status 3) at ang kanilang reports
    $query = "SELECT t.*, c.accomplishment_text, c.submitted_at as report_date 
          FROM ta_tbl t 
          JOIN travel_completions_tbl c ON t.ta_id = c.ta_id 
          WHERE t.status = 3 AND c.employee_id = '$my_id'
          ORDER BY c.submitted_at DESC";
    $result = mysqli_query($conn, $query);
    ?>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Travel Accomplishments</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">History</li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm" style="border-radius: 15px;">
                        <div class="card-body pt-4">
                            <h5 class="card-title">My Travel Portfolio</h5>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Memo & Date</th>
                                            <th>Destination</th>
                                            <th>Report Summary</th>
                                            <th class="text-center">Attachments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)):
                                            $ta_id = $row['ta_id'];
                                            // Sub-query para sa multiple attachments ng TA na ito
                                            $att_q = mysqli_query($conn, "SELECT * FROM travel_attachments_tbl WHERE ta_id = '$ta_id'");
                                            ?>
                                            <tr>
                                                <td>
                                                    <span class="badge badge-memo mb-1"><?= $row['memo_no'] ?></span>
                                                    <div class="small text-muted">
                                                        <?= date("M d, Y", strtotime($row['travel_date'])) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= $row['destination'] ?></div>
                                                    <div class="smallest text-muted"><?= $row['task'] ?></div>
                                                </td>
                                                <td style="max-width: 300px;">
                                                    <div class="report-box small text-truncate"
                                                        title="<?= $row['accomplishment_text'] ?>">
                                                        "<?= $row['accomplishment_text'] ?>"
                                                    </div>
                                                    <button class="btn btn-link btn-sm p-0 mt-1 cert-link"
                                                        onclick="viewFullReport('<?= addslashes($row['accomplishment_text']) ?>', '<?= $row['memo_no'] ?>')">
                                                        Read Full Report
                                                    </button>
                                                </td>
                                                <td class="text-center">
                                                    <div class="dropdown">
                                                        <button
                                                            class="btn btn-outline-secondary btn-sm rounded-pill px-3 dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bi bi-paperclip"></i> Files
                                                            (<?= mysqli_num_rows($att_q) ?>)
                                                        </button>
                                                        <ul class="dropdown-menu shadow border-0"
                                                            style="border-radius: 10px;">
                                                            <?php if (mysqli_num_rows($att_q) > 0): ?>
                                                                <?php while ($att = mysqli_fetch_assoc($att_q)): ?>
                                                                    <li>
                                                                        <a class="dropdown-item small py-2"
                                                                            href="../uploads/completions/<?= $att['file_path'] ?>"
                                                                            target="_blank">
                                                                            <i
                                                                                class="bi bi-file-earmark-check text-success me-2"></i>
                                                                            View Certificate
                                                                        </a>
                                                                    </li>
                                                                <?php endwhile; ?>
                                                            <?php else: ?>
                                                                <li class="dropdown-item small text-muted">No files attached
                                                                </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
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

    <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">Travel Accomplishment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="smallest text-uppercase fw-bold text-muted mb-2 d-block">Reference: <span
                            id="modal-memo" class="text-maroon"></span></label>
                    <div id="modal-report-text" class="p-3 bg-light rounded"
                        style="white-space: pre-wrap; font-size: 14px; line-height: 1.6;"></div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>
    <script>
        function viewFullReport(text, memo) {
            document.getElementById('modal-report-text').innerText = text;
            document.getElementById('modal-memo').innerText = memo;
            var myModal = new bootstrap.Modal(document.getElementById('reportModal'));
            myModal.show();
        }
    </script>
</body>

</html>