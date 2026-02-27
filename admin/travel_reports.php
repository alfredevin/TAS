<?php
include '../config.php';
 

// 2. Query: Lahat ng VERIFIED & ARCHIVED records (Status 4)
$query = "SELECT t.*, c.accomplishment_text, c.submitted_at as completion_date, 
          e.first_name, e.last_name, d.department_name
          FROM ta_tbl t 
          JOIN travel_completions_tbl c ON t.ta_id = c.ta_id 
          JOIN employee_tbl e ON c.employee_id = e.employee_id 
          JOIN department_tbl d ON e.department_id = d.department_id
          WHERE t.status = 4
          ORDER BY t.admin_approved_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>
    <style>
        :root {
            --marsu-maroon: #800000;
        }

        .archive-card {
            border: none;
            border-radius: 15px;
            background: #fff;
        }

        .report-preview {
            background: #f8f9fa;
            border-left: 3px solid var(--marsu-maroon);
            padding: 10px;
            font-size: 13px;
            color: #555;
            max-width: 300px;
            cursor: pointer;
            border-radius: 0 8px 8px 0;
        }

        .report-preview:hover {
            background: #f0f0f0;
        }

        .btn-action {
            border-radius: 50px;
            font-weight: 600;
            font-size: 11px;
            width: 100%;
            margin-bottom: 3px;
        }

        /* Modal Customization */
        #attachmentModal .modal-dialog {
            max-width: 650px;
        }

        .preview-card {
            background: #f9f9f9;
            border-radius: 15px;
            border: 1px solid #eee;
            overflow: hidden;
        }

        .file-header {
            background: #fff;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }

        .scrollable-modal {
            max-height: 75vh;
            overflow-y: auto;
            padding: 15px;
        }
    </style>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1 class="fw-bold text-dark">Verified Accomplishments</h1>
            <p class="text-muted small">Master records of all completed and validated faculty travels.</p>
        </div>

        <section class="section">
            <div class="card archive-card shadow-sm">
                <div class="card-body pt-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>Personnel</th>
                                    <th>Travel Details</th>
                                    <th>Narrative Summary</th>
                                    <th class="text-center" style="width: 140px;">Operations</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)):
                                    $ta_id = $row['ta_id'];
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= $row['first_name'] . ' ' . $row['last_name'] ?>
                                            </div>
                                            <div class="smallest text-muted text-uppercase" style="font-size: 10px;">
                                                <?= $row['department_name'] ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border mb-1"
                                                style="font-family: monospace;">#<?= $row['memo_no'] ?></span>
                                            <div class="small fw-bold"><?= $row['destination'] ?></div>
                                            <div class="smallest text-muted">
                                                <?= date("M d, Y", strtotime($row['travel_date'])) ?></div>
                                        </td>
                                        <td>
                                            <div class="report-preview text-truncate"
                                                onclick="viewFullReport('<?= addslashes($row['accomplishment_text']) ?>', '<?= $row['memo_no'] ?>')">
                                                <i class="bi bi-quote me-1"></i> <?= $row['accomplishment_text'] ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column">
                                                <button class="btn btn-outline-info btn-sm btn-action"
                                                    onclick='trackTravel(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <i class="bi bi-geo-alt me-1"></i> Track
                                                </button>
                                                <button class="btn btn-outline-secondary btn-sm btn-action"
                                                    onclick="viewAttachments(<?= $ta_id ?>)">
                                                    <i class="bi bi-eye me-1"></i> Proofs
                                                </button>
                                                <a href="./report/print_ta.php?id=<?= $ta_id ?>" target="_blank"
                                                    class="btn btn-outline-success btn-sm btn-action">
                                                    <i class="bi bi-printer me-1"></i> Report
                                                </a>
                                            </div>
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

    <div class="modal fade" id="textModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold text-maroon">Narrative Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="smallest text-uppercase fw-bold text-muted mb-2">Reference No: <span
                            id="modal-memo"></span></label>
                    <div id="modal-text" class="p-3 bg-light rounded shadow-sm"
                        style="white-space: pre-wrap; font-size: 14px; line-height: 1.6; border-left: 4px solid var(--marsu-maroon);">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="attachmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered shadow-lg">
            <div class="modal-content border-0" style="border-radius: 25px;">
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-check text-maroon me-2"></i>Evidence
                        Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="scrollable-modal" id="attachment-container">
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill w-100 fw-bold" data-bs-dismiss="modal">Close
                        Gallery</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'tracking_modal.php'; ?>
    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>

    <script>
        function viewFullReport(text, memo) {
            document.getElementById('modal-text').innerText = text;
            document.getElementById('modal-memo').innerText = memo;
            new bootstrap.Modal(document.getElementById('textModal')).show();
        }

        function viewAttachments(taId) {
            const container = document.getElementById('attachment-container');
            container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-danger"></div><p class="mt-2 small text-muted">Fetching certificates...</p></div>';
            new bootstrap.Modal(document.getElementById('attachmentModal')).show();

            fetch(`get_attachments.php?ta_id=${taId}`)
                .then(res => res.json())
                .then(files => {
                    container.innerHTML = '';
                    if (files.length > 0) {
                        files.forEach(f => {
                            const fileExt = f.file_path.split('.').pop().toLowerCase();
                            const filePath = `../uploads/completions/${f.file_path}`;
                            let previewHTML = '';

                            // Smart Preview Logic
                            if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                                previewHTML = `<img src="${filePath}" class="img-fluid w-100" style="max-height: 400px; object-fit: contain; background: #eee;">`;
                            } else if (fileExt === 'pdf') {
                                previewHTML = `<iframe src="${filePath}" width="100%" height="400px" class="border-0"></iframe>`;
                            } else {
                                previewHTML = `<div class="p-5 text-center bg-light"><i class="bi bi-file-earmark-text fs-1"></i><p class="mt-2 small">No preview for .${fileExt} file</p></div>`;
                            }

                            container.innerHTML += `
                                <div class="preview-card shadow-sm mb-4">
                                    <div class="file-header d-flex justify-content-between align-items-center">
                                        <span class="small fw-bold text-truncate" style="max-width: 250px;">${f.file_path}</span>
                                        <a href="${filePath}" target="_blank" class="btn btn-sm btn-link text-maroon p-0 fw-bold text-decoration-none">Full View <i class="bi bi-box-arrow-up-right"></i></a>
                                    </div>
                                    <div class="preview-body">${previewHTML}</div>
                                </div>`;
                        });
                    } else {
                        container.innerHTML = '<div class="text-center py-5 text-muted"><i class="bi bi-folder-x fs-1"></i><p>No attachments found.</p></div>';
                    }
                });
        }
    </script>
</body>

</html>