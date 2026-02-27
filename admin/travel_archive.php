<?php
include './../config.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>
    <style>
        .archive-card {
            border-radius: 15px;
            border: none;
        }

        .text-maroon {
            color: rgb(128, 0, 36);
        }

        .memo-badge {
            background: #e8f5e9;
            color: #2e7d32;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            border: 1px solid #c8e6c9;
        }
    </style>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <?php

    // 2. Query: Kunin lahat ng Approved TA (Status 2)
// Isinama natin ang Department para mas madaling mag-filter sa archive
    $query = "SELECT t.*, e.first_name, e.last_name, d.department_name, e.position_name
          FROM ta_tbl t 
          JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id 
          JOIN employee_tbl e ON tp.employee_id = e.employee_id 
          JOIN department_tbl d ON e.department_id = d.department_id
          WHERE t.status = 2 
          GROUP BY t.ta_id 
          ORDER BY t.admin_approved_at DESC";
    $result = mysqli_query($conn, $query);
    ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Travel Authority Archive</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Records</li>
                    <li class="breadcrumb-item active">Approved History</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="card archive-card shadow-sm">
                <div class="card-body pt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                        <h5 class="card-title m-0 p-0">Master List of Approved TAs</h5>
                        <button class="btn btn-outline-secondary btn-sm rounded-pill"
                            onclick="window.location.reload();">
                            <i class="bi bi-arrow-clockwise"></i> Refresh Archive
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>Memo Number</th>
                                    <th>Personnel Details</th>
                                    <th>Travel Information</th>
                                    <th>Date Approved</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <span class="badge memo-badge px-3 py-2"><?= $row['memo_no'] ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <?= $row['first_name'] . ' ' . $row['last_name'] ?>
                                            </div>
                                            <div class="smallest text-muted text-uppercase" style="font-size: 10px;">
                                                <?= $row['department_name'] ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-maroon fw-bold mb-0"><?= $row['destination'] ?></div>
                                            <small class="text-muted d-block" style="font-size: 11px;">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?= date("M d, Y", strtotime($row['travel_date'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="small fw-bold">
                                                <?= date("M d, Y", strtotime($row['admin_approved_at'])) ?>
                                            </div>
                                            <div class="smallest text-muted" style="font-size: 10px;">
                                                <?= date("h:i A", strtotime($row['admin_approved_at'])) ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group shadow-sm" style="border-radius: 50px; overflow: hidden;">
                                                <button class="btn btn-light btn-sm px-3 border-end" title="Track Journey"
                                                    onclick='trackTravel(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <i class="bi bi-geo text-info"></i>
                                                </button>
                                                <a href="./report/print_ta.php?id=<?= $row['ta_id'] ?>" target="_blank"
                                                    class="btn btn-light btn-sm px-3" title="Print TA">
                                                    <i class="bi bi-printer-fill text-success"></i>
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

    <?php include 'tracking_modal.php'; ?>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>
</body>

</html>