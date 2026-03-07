<?php
include './../config.php';

// 1. INITIALIZE DATE FILTER (Default: Start of year to today)
$from_date = isset($_GET['from']) ? $_GET['from'] : date('Y-01-01');
$to_date = isset($_GET['to']) ? $_GET['to'] : date('Y-12-31');

// 2. OPTIMIZED STATS QUERIES (One trip to DB per table)
// --- TRAVEL AUTHORITY STATS ---
$ta_query = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_ta,
        SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as pending_ta,
        SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) as completed_ta,
        SUM(CASE WHEN status = 11 THEN 1 ELSE 0 END) as returned_ta,
        SUM(CASE WHEN status = 99 THEN 1 ELSE 0 END) as declined_ta
    FROM ta_tbl 
    WHERE submitted_at BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59'
");
$ta_stats = mysqli_fetch_assoc($ta_query);

// --- PASS SLIP STATS ---
$ps_query = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_ps,
        SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending_ps,
        SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as approved_ps,
        SUM(CASE WHEN status = 99 THEN 1 ELSE 0 END) as declined_ps
    FROM pass_slip_tbl 
    WHERE ps_date BETWEEN '$from_date' AND '$to_date'
");
$ps_stats = mysqli_fetch_assoc($ps_query);

// --- ACTIVE FACULTY ---
$total_employees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM employee_tbl WHERE status = 1"))['total'];

// 3. MONTHLY ANALYTICS (TA vs Pass Slip)
$months_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$ta_monthly_data = array_fill(0, 12, 0);
$ps_monthly_data = array_fill(0, 12, 0);

// Get TA Monthly
$ta_m_query = mysqli_query($conn, "SELECT MONTH(submitted_at) as m_num, COUNT(*) as total FROM ta_tbl WHERE submitted_at BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59' GROUP BY MONTH(submitted_at)");
while ($row = mysqli_fetch_assoc($ta_m_query)) {
    $ta_monthly_data[$row['m_num'] - 1] = (int) $row['total'];
}

// Get Pass Slip Monthly
$ps_m_query = mysqli_query($conn, "SELECT MONTH(ps_date) as m_num, COUNT(*) as total FROM pass_slip_tbl WHERE ps_date BETWEEN '$from_date' AND '$to_date' GROUP BY MONTH(ps_date)");
while ($row = mysqli_fetch_assoc($ps_m_query)) {
    $ps_monthly_data[$row['m_num'] - 1] = (int) $row['total'];
}

// 4. DEPT DISTRIBUTION (TA Only for now)
$dept_names = [];
$dept_counts = [];
$d_query = mysqli_query($conn, "
    SELECT d.department_name, COUNT(tp.ta_id) as total 
    FROM department_tbl d 
    LEFT JOIN employee_tbl e ON d.department_id = e.department_id 
    LEFT JOIN ta_participants_tbl tp ON e.employee_id = tp.employee_id 
    LEFT JOIN ta_tbl t ON tp.ta_id = t.ta_id AND t.submitted_at BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59'
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
   
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
 <style>
        :root {
            --marsu-maroon: #800000;
            --marsu-blue: #012970;
            --status-pending: #ffc107;
            --status-completed: #198754;
            --status-returned: #fd7e14;
            --status-declined: #dc3545;
        }

        /* Toggles & Filters */
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
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #eee;
        }

        /* Custom Stat Cards */
        .stat-card {
            border-radius: 15px;
            border: none;
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .border-left-maroon {
            border-left: 5px solid var(--marsu-maroon);
        }

        .border-left-blue {
            border-left: 5px solid var(--marsu-blue);
        }

        .view-section {
            display: none;
        }

        .view-section.active {
            display: block;
            animation: fadeIn 0.4s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sub-stat {
            font-size: 12px;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
        }
    </style>
    <main id="main" class="main">
        <div class="pagetitle d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold text-maroon">Admin Analytics</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">Dashboard</li>
                        <li class="breadcrumb-item active">Overview</li>
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
                    <button type="submit" class="btn w-100 fw-bold rounded-pill text-white shadow-sm"
                        style="background: var(--marsu-maroon);">
                        <i class="bi bi-filter-circle me-2"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>

        <div id="monitor-view" class="view-section <?= !isset($_GET['from']) ? 'active' : '' ?>">

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm border-left-maroon h-100 p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted fw-bold mb-1">TOTAL TA REQUESTS</h6>
                                <h2 class="fw-bold mb-0 text-dark"><?= $ta_stats['total_ta'] ?: 0 ?></h2>
                            </div>
                            <div class="stat-icon bg-light text-maroon"><i class="bi bi-file-earmark-text"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm border-left-blue h-100 p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted fw-bold mb-1">TOTAL PASS SLIPS</h6>
                                <h2 class="fw-bold mb-0 text-dark"><?= $ps_stats['total_ps'] ?: 0 ?></h2>
                            </div>
                            <div class="stat-icon bg-light text-primary"><i class="bi bi-ticket-detailed"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm h-100 p-3" style="border-left: 5px solid #6c757d;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted fw-bold mb-1">ACTIVE FACULTY</h6>
                                <h2 class="fw-bold mb-0 text-dark"><?= $total_employees ?></h2>
                            </div>
                            <div class="stat-icon bg-light text-secondary"><i class="bi bi-people"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold text-muted text-uppercase mb-3 mt-2"><i class="bi bi-bar-chart-steps me-2"></i>Status
                Breakdown</h6>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stat-card shadow-sm p-3 border-0" style="background: #fff8e1;">
                        <h6 class="fw-bold text-warning mb-3"><i class="bi bi-hourglass-split me-2"></i>Pending Review
                        </h6>
                        <div class="d-flex justify-content-between">
                            <span class="sub-stat bg-white text-dark shadow-sm">TA:
                                <?= $ta_stats['pending_ta'] ?: 0 ?></span>
                            <span class="sub-stat bg-white text-dark shadow-sm">PS:
                                <?= $ps_stats['pending_ps'] ?: 0 ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stat-card shadow-sm p-3 border-0" style="background: #d1e7dd;">
                        <h6 class="fw-bold text-success mb-3"><i class="bi bi-check-circle me-2"></i>Completed/Approved
                        </h6>
                        <div class="d-flex justify-content-between">
                            <span class="sub-stat bg-white text-dark shadow-sm">TA:
                                <?= $ta_stats['completed_ta'] ?: 0 ?></span>
                            <span class="sub-stat bg-white text-dark shadow-sm">PS:
                                <?= $ps_stats['approved_ps'] ?: 0 ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stat-card shadow-sm p-3 border-0" style="background: #ffe5d0;">
                        <h6 class="fw-bold mb-3" style="color: #fd7e14;"><i
                                class="bi bi-arrow-return-left me-2"></i>Returned (Fix)</h6>
                        <div class="d-flex justify-content-between">
                            <span class="sub-stat bg-white text-dark shadow-sm">TA:
                                <?= $ta_stats['returned_ta'] ?: 0 ?></span>
                            <span class="sub-stat bg-white text-muted shadow-sm border" style="opacity:0.5;">PS:
                                N/A</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stat-card shadow-sm p-3 border-0" style="background: #f8d7da;">
                        <h6 class="fw-bold text-danger mb-3"><i class="bi bi-x-octagon me-2"></i>Declined</h6>
                        <div class="d-flex justify-content-between">
                            <span class="sub-stat bg-white text-dark shadow-sm">TA:
                                <?= $ta_stats['declined_ta'] ?: 0 ?></span>
                            <span class="sub-stat bg-white text-dark shadow-sm">PS:
                                <?= $ps_stats['declined_ps'] ?: 0 ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="graphs-view" class="view-section <?= isset($_GET['from']) ? 'active' : '' ?>">
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm border-0 p-4" style="border-radius: 20px;">
                        <h5 class="fw-bold mb-4"><i class="bi bi-bar-chart me-2 text-maroon"></i>Monthly Volume (TA vs
                            Pass Slip)</h5>
                        <div style="height: 350px;"><canvas id="monthlyChart"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 p-4" style="border-radius: 20px;">
                        <h5 class="fw-bold mb-4"><i class="bi bi-pie-chart me-2 text-primary"></i>TA by Department</h5>
                        <div style="height: 350px;"><canvas id="deptChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // View Switcher
        function switchView(view) {
            document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(n => n.classList.remove('active'));
            if (view === 'monitor') {
                document.getElementById('monitor-view').classList.add('active');
                document.getElementById('btn-monitor').classList.add('active');
            } else {
                document.getElementById('graphs-view').classList.add('active');
                document.getElementById('btn-graphs').classList.add('active');
                window.dispatchEvent(new Event('resize')); // Fix for Chart.js rendering bug on hidden tabs
            }
        }

        // CHARTS LOGIC
        document.addEventListener("DOMContentLoaded", () => {

            // 1. Dual Bar Chart (TA and Pass Slip)
            const ctxMonthly = document.getElementById('monthlyChart');
            new Chart(ctxMonthly, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($months_labels) ?>,
                    datasets: [
                        {
                            label: 'Travel Authority (TA)',
                            data: <?= json_encode($ta_monthly_data) ?>,
                            backgroundColor: '#800000',
                            borderRadius: 4
                        },
                        {
                            label: 'Pass Slips (PS)',
                            data: <?= json_encode($ps_monthly_data) ?>,
                            backgroundColor: '#012970',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. Doughnut Chart (Department Share)
            const ctxDept = document.getElementById('deptChart');
            new Chart(ctxDept, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($dept_names) ?>,
                    datasets: [{
                        data: <?= json_encode($dept_counts) ?>,
                        backgroundColor: ['#800000', '#012970', '#ffc107', '#198754', '#fd7e14', '#0dcaf0'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12 } }
                    }
                }
            });
        });
    </script>
</body>

</html>