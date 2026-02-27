<?php
include './../config.php';

// 1. Kunin ang Total Employees
$emp_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM employee_tbl WHERE status = 1");
$total_employees = mysqli_fetch_assoc($emp_q)['total'];



// 3. Kunin ang Total Departments
$dept_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM department_tbl");
$total_depts = mysqli_fetch_assoc($dept_q)['total'];
 
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../template/header.php'; ?>
<style>
    .info-card h6 {
        font-size: 28px;
        color: #012970;
        font-weight: 700;
        margin: 0;
        padding: 0;
    }

    .card-icon {
        font-size: 32px;
        line-height: 0;
        width: 64px;
        height: 64px;
        flex-shrink: 0;
        flex-grow: 0;
    }

    .recent-activity img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
</style>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Admin Dashboard</h1>
            <p class="text-muted">Travel Authority System: Real-time  Management Overview</p>
        </div>

        <section class="section dashboard">
            <div class="row">

                <div class="col-xxl-4 col-md-6">
                    <div class="card info-card sales-card border-bottom border-primary border-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase small fw-bold">Total Employee</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?= $total_employees ?></h6>
                                    <span class="text-muted small">Active Faculty</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

               

                <div class="col-xxl-4 col-md-6">
                    <div class="card info-card customers-card border-bottom border-warning border-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase small fw-bold">Total Department</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?= $total_depts ?></h6>
                                    <span class="text-muted small">College Units</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


              
            </div>
        </section>
    </main>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
</body>

</html>