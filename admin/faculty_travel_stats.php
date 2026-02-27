<?php
include '../config.php';
 
// 1. KUNIN ANG FILTER VALUE
$selected_dept = isset($_GET['dept_id']) ? mysqli_real_escape_string($conn, $_GET['dept_id']) : '';

// 2. QUERY PARA SA DROPDOWN LIST
$depts_q = mysqli_query($conn, "SELECT * FROM department_tbl ORDER BY department_name ASC");

// 3. DYNAMIC WHERE CLAUSE PARA SA ANALYTICS
$where_clause = "";
if ($selected_dept !== "") {
    $where_clause = " WHERE d.department_id = '$selected_dept' ";
}

// 4. DATA para sa Summary Cards (Filtered)
$total_faculty_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM employee_tbl e JOIN department_tbl d ON e.department_id = d.department_id $where_clause AND e.status = 1");
$total_faculty = mysqli_fetch_assoc($total_faculty_q)['total'];

$grand_total_ta_q = mysqli_query($conn, "
    SELECT COUNT(tp.ta_id) as total 
    FROM ta_participants_tbl tp 
    JOIN employee_tbl e ON tp.employee_id = e.employee_id 
    JOIN department_tbl d ON e.department_id = d.department_id
    $where_clause
");
$grand_total_ta = mysqli_fetch_assoc($grand_total_ta_q)['total'];

// 5. MAIN TABLE QUERY
$query = "SELECT e.employee_id, e.first_name, e.last_name, d.department_name, 
          COUNT(tp.ta_id) as total_requests 
          FROM employee_tbl e
          JOIN department_tbl d ON e.department_id = d.department_id
          LEFT JOIN ta_participants_tbl tp ON e.employee_id = tp.employee_id
          $where_clause
          GROUP BY e.employee_id
          ORDER BY total_requests DESC";
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

        .stat-card {
            border: none;
            border-radius: 15px;
            border-left: 5px solid var(--marsu-maroon);
            background: #fff;
        }

        .filter-box {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            border: 1px solid #eee;
        }

        .avatar-circle {
            width: 38px;
            height: 38px;
            background: #f8f9fa;
            color: var(--marsu-maroon);
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1px solid #dee2e6;
            font-size: 14px;
        }

        .table-container {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
        }
    </style>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle d-flex justify-content-between align-items-center">
            <div>
                <h1 class="fw-bold">Faculty Travel Analytics</h1>
                <p class="text-muted small">Monitor participation density per department.</p>
            </div>
            <div class="filter-box shadow-sm" style="min-width: 300px;">
                <form id="filterForm" method="GET">
                    <label class="smallest fw-bold text-uppercase text-muted mb-1">Filter by Department</label>
                    <select name="dept_id" class="form-select border-0 bg-light rounded-pill"
                        onchange="this.form.submit()">
                        <option value="">All Departments (University Wide)</option>
                        <?php while ($d = mysqli_fetch_assoc($depts_q)): ?>
                            <option value="<?= $d['department_id'] ?>" <?= ($selected_dept == $d['department_id']) ? 'selected' : '' ?>>
                                <?= $d['department_name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </form>
            </div>
        </div>

        <section class="section mt-4">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm mb-0">
                        <div class="card-body p-3">
                            <small class="text-muted fw-bold">FACULTY COUNT</small>
                            <h3 class="fw-bold mb-0 text-dark"><?= $total_faculty ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm mb-0" style="border-left-color: #0d6efd;">
                        <div class="card-body p-3">
                            <small class="text-muted fw-bold">TOTAL TA ISSUED</small>
                            <h3 class="fw-bold mb-0 text-dark"><?= $grand_total_ta ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm mb-0" style="border-left-color: #198754;">
                        <div class="card-body p-3">
                            <small class="text-muted fw-bold">RATIO</small>
                            <h3 class="fw-bold mb-0 text-dark">
                                <?= ($total_faculty > 0) ? round($grand_total_ta / $total_faculty, 1) : 0 ?> <span
                                    class="fs-6 fw-normal text-muted">TA/Pax</span></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-container shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0 text-maroon"><i class="bi bi-award me-2"></i>Participation Rankings</h5>
                
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead>
                            <tr class="table-light">
                                <th style="width: 60px;">Rank</th>
                                <th>Personnel</th>
                                <th>College/Dept</th>
                                <th class="text-center">Activity Level</th>
                                <th class="text-center">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $rank = 1;
                            while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td class="text-center"><span class="badge bg-light text-dark border rounded-circle p-2"
                                            style="width:30px; height:30px;"><?= $rank++ ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-3">
                                                <?= substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?= $row['first_name'] . ' ' . $row['last_name'] ?>
                                                </div>
                                                <div class="smallest text-muted">ID: #<?= $row['employee_id'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span
                                            class="small text-uppercase fw-bold text-muted"><?= $row['department_name'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $count = $row['total_requests'];
                                        $color = ($count >= 10) ? 'danger' : (($count >= 5) ? 'warning' : 'primary');
                                        ?>
                                        <div class="d-inline-block text-center">
                                            <div class="fw-bold text-<?= $color ?>"><?= $count ?></div>
                                            <div class="progress" style="height: 4px; width: 60px;">
                                                <div class="progress-bar bg-<?= $color ?>"
                                                    style="width: <?= min(($count / 15) * 100, 100) ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="employee_ta_details?id=<?= $row['employee_id'] ?>"
                                            class="btn btn-sm btn-outline-maroon rounded-pill px-3 shadow-sm"
                                            style="font-size: 11px; border-width: 2px;">
                                            LOGS <i class="bi bi-arrow-right-short"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>
</body>

</html>