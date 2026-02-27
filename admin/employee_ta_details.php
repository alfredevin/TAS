<?php
include '../config.php';

$emp_id = $_GET['id'] ?? 0;

// 1. KUNIN ANG DETALYE NG EMPLOYEE
$emp_q = mysqli_query($conn, "
    SELECT e.*, d.department_name
    FROM employee_tbl e 
    JOIN department_tbl d ON e.department_id = d.department_id
    WHERE e.employee_id = '$emp_id'
");
$emp_info = mysqli_fetch_assoc($emp_q);

if (!$emp_info) {
    header("Location: faculty_travel_stats.php");
    exit();
}

// 2. KUNIN ANG STATS NG EMPLOYEE
$total_trips = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM ta_participants_tbl WHERE employee_id = '$emp_id'"))['total'];
$completed_trips = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(t.ta_id) as total FROM ta_tbl t JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id WHERE tp.employee_id = '$emp_id' AND t.status = 4"))['total'];

// 3. QUERY PARA SA TRAVEL LOGS
$query = "SELECT t.* FROM ta_tbl t 
          JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id 
          WHERE tp.employee_id = '$emp_id' 
          ORDER BY t.submitted_at DESC";
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

        .profile-card {
            border: none;
            border-radius: 20px;
            background: linear-gradient(45deg, var(--marsu-maroon), #a00000);
            color: white;
        }

        .mini-stat-card {
            border: none;
            border-radius: 15px;
            background: #fff;
            border-bottom: 3px solid #dee2e6;
            transition: 0.3s;
        }

        .mini-stat-card:hover {
            border-bottom-color: var(--marsu-maroon);
            transform: translateY(-3px);
        }

        .table-container {
            background: #fff;
            border-radius: 20px;
            padding: 25px;
            border: none;
        }

        .avatar-lg {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: bold;
            border: 2px solid rgba(255, 255, 255, 0.4);
        }
    </style>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Personnel Travel Profile</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="faculty_travel_stats">Analytics</a></li>
                    <li class="breadcrumb-item active">Personnel Logs</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row mb-4">
                <div class="col-lg-7">
                    <div class="card profile-card shadow-sm h-100">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="avatar-lg me-4">
                                <?= substr($emp_info['first_name'], 0, 1) . substr($emp_info['last_name'], 0, 1) ?>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-1"><?= $emp_info['first_name'] . ' ' . $emp_info['last_name'] ?>
                                </h3>
                                <p class="mb-0 opacity-75"><i
                                        class="bi bi-briefcase me-2"></i><?= $emp_info['position_name'] ?></p>
                                <p class="mb-0 opacity-75"><i
                                        class="bi bi-building me-2"></i><?= $emp_info['department_name'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="row h-100">
                        <div class="col-6">
                            <div class="card mini-stat-card shadow-sm h-100">
                                <div class="card-body p-3 text-center">
                                    <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;">Total
                                        Travels</small>
                                    <h2 class="fw-bold text-dark mb-0"><?= $total_trips ?></h2>
                                    <i class="bi bi-airplane text-maroon opacity-25"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card mini-stat-card shadow-sm h-100">
                                <div class="card-body p-3 text-center">
                                    <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;">Verified
                                        Reports</small>
                                    <h2 class="fw-bold text-success mb-0"><?= $completed_trips ?></h2>
                                    <i class="bi bi-check-circle text-success opacity-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card table-container shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0"><i class="bi bi-clock-history me-2 text-maroon"></i>Complete Travel History
                    </h5>
                    <a href="faculty_travel_stats" class="btn btn-sm btn-light border rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Back to Analytics
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th>Memo / Date</th>
                                <th>Destination & Purpose</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)):
                                $st = $row['status'];
                                $config = [
                                    0 => ['b' => 'bg-warning text-dark', 'l' => 'Pending'],
                                    1 => ['b' => 'bg-info', 'l' => 'Confirmed'],
                                    2 => ['b' => 'bg-success', 'l' => 'Approved'],
                                    3 => ['b' => 'bg-primary', 'l' => 'Completed'],
                                    4 => ['b' => 'bg-dark', 'l' => 'Verified']
                                ];
                                $c = $config[$st] ?? ['b' => 'bg-danger', 'l' => 'Rejected'];
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-maroon mb-0">#<?= $row['memo_no'] ?></div>
                                        <small
                                            class="text-muted"><?= date("M d, Y", strtotime($row['travel_date'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= $row['destination'] ?></div>
                                        <div class="smallest text-muted text-truncate" style="max-width: 300px;">
                                            <?= $row['task'] ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $c['b'] ?> rounded-pill px-3 shadow-sm"
                                            style="font-size: 10px;"><?= $c['l'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold"
                                            onclick='trackTravel(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                            <i class="bi bi-geo-alt me-1"></i> TRACK
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <?php include 'tracking_modal.php'; ?>
    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>
</body>

</html>