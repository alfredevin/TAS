<?php
include './../config.php';
 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>
    <style>
        .history-card {
            border-radius: 15px;
            border: none;
            transition: 0.3s;
        }

        .text-maroon {
            color: rgb(128, 0, 36);
        }

        .badge-step {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 5px 12px;
            border-radius: 50px;
        }
    </style>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <?php
    $query = "SELECT t.*, e.first_name, e.last_name, e.position_name 
          FROM ta_tbl t 
          JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id 
          JOIN employee_tbl e ON tp.employee_id = e.employee_id 
          WHERE t.status IN (1, 2) AND e.department_id = '$dept_id'
          GROUP BY t.ta_id ORDER BY t.head_confirmed_at DESC";
    $result = mysqli_query($conn, $query);

    // Counts para sa Summary Cards
    $total_confirmed = mysqli_num_rows($result);
    ?>

    <main id="main" class="main">
        <div class="pagetitle" style="margin-top: -30px;">
            <h1>Confirmed History</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">E-TAMS</li>
                    <li class="breadcrumb-item active">History</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row ">
                <div class="col-md-4">
                    <div class="card shadow-sm history-card" style="border-left: 5px solid #28a745;">
                        <div class="card-body p-3">
                            <h6 class="text-muted small">Total Confirmed Requests</h6>
                            <h3 class="mb-0 fw-bold"><?= $total_confirmed ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body pt-4">
                    <h5 class="card-title px-2">Recently Confirmed Travel Authorities</h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>Faculty Member</th>
                                    <th>Destination & Purpose</th>
                                    <th>Date Confirmed</th>
                                    <th>Current Progress</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)):
                                    // Status Badge Logic
                                    $curr_status = $row['status'];
                                    if ($curr_status == 1) {
                                        $status_label = "Pending at Admin";
                                        $status_class = "bg-info text-white";
                                    } else {
                                        $status_label = "Fully Approved";
                                        $status_class = "bg-success text-white";
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <?= $row['first_name'] . ' ' . $row['last_name'] ?>
                                            </div>
                                            <small class="text-muted"><?= $row['position_name'] ?></small>
                                        </td>
                                        <td>
                                            <div class="text-maroon fw-bold"><?= $row['destination'] ?></div>
                                            <div class="text-muted small text-truncate" style="max-width: 200px;">
                                                <?= $row['task'] ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small fw-bold">
                                                <?= date("M d, Y", strtotime($row['head_confirmed_at'])) ?>
                                            </div>
                                            <div class="smallest text-muted">
                                                <?= date("h:i A", strtotime($row['head_confirmed_at'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-step <?= $status_class ?> shadow-sm">
                                                <i
                                                    class="bi <?= ($curr_status == 2) ? 'bi-check-all' : 'bi-clock' ?> me-1"></i>
                                                <?= $status_label ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                onclick='trackTravel(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                <i class="bi bi-geo"></i> Track
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

    <?php include 'tracking_modal.php'; ?>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>
</body>

</html>