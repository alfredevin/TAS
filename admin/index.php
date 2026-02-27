<?php
include './../config.php';

// 1. INITIALIZE DATE FILTER
// Default: Start of year to today
$from_date = isset($_GET['from']) ? $_GET['from'] : date('Y-01-01');
$to_date = isset($_GET['to']) ? $_GET['to'] : date('Y-12-31');

// 2. STATS DATA (Filtered by Date)
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM ta_tbl WHERE status = 1 AND (submitted_at BETWEEN '$from_date' AND '$to_date')"))['total'];
$archived_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM ta_tbl WHERE status = 4 AND (submitted_at BETWEEN '$from_date' AND '$to_date')"))['total'];
$total_employees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM employee_tbl WHERE status = 1"))['total'];

// 3. MONTHLY ANALYTICS (Filtered)
$m_query = mysqli_query($conn, "
    SELECT MONTHNAME(submitted_at) as m_name, COUNT(*) as total 
    FROM ta_tbl 
    WHERE submitted_at BETWEEN '$from_date' AND '$to_date'
    GROUP BY MONTH(submitted_at) 
    ORDER BY MONTH(submitted_at) ASC
");
$months = [];
$counts = [];
while ($row = mysqli_fetch_assoc($m_query)) {
    $months[] = $row['m_name'];
    $counts[] = (int) $row['total'];
}

// 4. DEPT DISTRIBUTION (Filtered)
$dept_names = [];
$dept_counts = [];
$d_query = mysqli_query($conn, "
    SELECT d.department_name, COUNT(tp.ta_id) as total 
    FROM department_tbl d 
    LEFT JOIN employee_tbl e ON d.department_id = e.department_id 
    LEFT JOIN ta_participants_tbl tp ON e.employee_id = tp.employee_id 
    LEFT JOIN ta_tbl t ON tp.ta_id = t.ta_id
    WHERE t.submitted_at BETWEEN '$from_date' AND '$to_date' OR t.ta_id IS NULL
    GROUP BY d.department_id
");
while ($row = mysqli_fetch_assoc($d_query)) {
    $dept_names[] = $row['department_name'];
    $dept_counts[] = (int) $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>
    <style>
        :root {
            --marsu-maroon: #800000;
            --marsu-blue: #012970;
        }

        .dashboard-toggle .nav-pills .nav-link {
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            color: var(--marsu-blue);
            border: 1px solid #dee2e6;
            margin-right: 10px;
        }

        .dashboard-toggle .nav-pills .nav-link.active {
            background-color: var(--marsu-maroon);
            color: white;
            border-color: var(--marsu-maroon);
        }

        .filter-section {
            background: #fff;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #eee;
        }

        .info-card {
            border: none;
            border-radius: 20px;
            transition: 0.3s;
        }

        .chart-wrapper {
            height: 350px;
            width: 100%;
            position: relative;
        }

        .view-section {
            display: none;
        }

        .view-section.active {
            display: block;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold text-maroon">Admin Analytics</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">Dashboard</li>
                        <li class="breadcrumb-item active">Visual Insights</li>
                    </ol>
                </nav>
            </div>
            <div class="dashboard-toggle">
                <ul class="nav nav-pills" id="pills-tab">
                    <li class="nav-item"><button class="nav-link <?= !isset($_GET['from']) ? 'active' : '' ?>"
                            id="btn-monitor" onclick="switchView('monitor')">Monitor</button></li>
                    <li class="nav-item"><button class="nav-link <?= isset($_GET['from']) ? 'active' : '' ?>"
                            id="btn-graphs" onclick="switchView('graphs')">Analytics</button></li>
                </ul>
            </div>
        </div>

        <div class="filter-section shadow-sm">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="small fw-bold text-muted text-uppercase mb-1">From Date</label>
                    <input type="date" name="from" class="form-control border-0 bg-light" value="<?= $from_date ?>">
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold text-muted text-uppercase mb-1">To Date</label>
                    <input type="date" name="to" class="form-control border-0 bg-light" value="<?= $to_date ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill shadow-sm"
                        style="background: var(--marsu-maroon); border:none;">
                        <i class="bi bi-filter-circle me-2"></i> Apply Analytics Filter
                    </button>
                </div>
            </form>
        </div>

        <div id="monitor-view" class="view-section <?= !isset($_GET['from']) ? 'active' : '' ?>">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card info-card shadow-sm p-4">
                        <h6 class="text-muted small fw-bold">PENDING APPROVALS</h6>
                        <h2 class="fw-bold text-dark"><?= $pending_count ?></h2>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar bg-warning" style="width: 70%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card info-card shadow-sm p-4">
                        <h6 class="text-muted small fw-bold">COMPLETED TRIPS</h6>
                        <h2 class="fw-bold text-dark"><?= $archived_count ?></h2>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card info-card shadow-sm p-4">
                        <h6 class="text-muted small fw-bold">ACTIVE FACULTY</h6>
                        <h2 class="fw-bold text-dark"><?= $total_employees ?></h2>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar bg-primary" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="graphs-view" class="view-section <?= isset($_GET['from']) ? 'active' : '' ?>">
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm border-0 p-4" style="border-radius: 20px;">
                        <h5 class="fw-bold mb-4">Monthly Travel Volume</h5>
                        <div class="chart-wrapper"><canvas id="monthlyChart"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 p-4" style="border-radius: 20px;">
                        <h5 class="fw-bold mb-4">Dept. Share</h5>
                        <div class="chart-wrapper"><canvas id="deptChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        function switchView(view) {
            document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(n => n.classList.remove('active'));
            if (view === 'monitor') {
                document.getElementById('monitor-view').classList.add('active');
                document.getElementById('btn-monitor').classList.add('active');
            } else {
                document.getElementById('graphs-view').classList.add('active');
                document.getElementById('btn-graphs').classList.add('active');
                window.dispatchEvent(new Event('resize'));
            }
        }

        // CHARTS LOGIC
        document.addEventListener("DOMContentLoaded", () => {
            const ctxMonthly = document.getElementById('monthlyChart');
            new Chart(ctxMonthly, {
                type: 'bar', // Mas maganda ang line chart para sa "From-To" trends
                data: {
                    labels: <?= json_encode($months) ?>,
                    datasets: [{
                        label: 'Requests',
                        data: <?= json_encode($counts) ?>,
                        borderColor: '#800000',
                        backgroundColor: 'rgba(128, 0, 0, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            const ctxDept = document.getElementById('deptChart');
            new Chart(ctxDept, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($dept_names) ?>,
                    datasets: [{
                        data: <?= json_encode($dept_counts) ?>,
                        backgroundColor: ['#800000', '#012970', '#ffc107', '#198754']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%' }
            });
        });
    </script>
</body>

</html>