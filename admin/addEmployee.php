<?php
include './../config.php';

// --- HELPER FUNCTION FOR ALERTS ---
function showAlert($icon, $title)
{
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const Toast = Swal.mixin({
                toast: true, position: "top-end", showConfirmButton: false, timer: 3000, timerProgressBar: true
            });
            Toast.fire({ icon: "' . $icon . '", title: "' . $title . '" });
        });
    </script>';
}

// --- ADD EMPLOYEE LOGIC ---
if (isset($_POST['submit'])) {
    $employee_no = strtoupper(trim($_POST['employee_no']));
    $first_name  = strtoupper(trim($_POST['first_name']));
    $last_name   = strtoupper(trim($_POST['last_name']));
    $department_id = $_POST['department_id'];
    $position_name = $_POST['position_name'];

    // Password is auto-set to Employee ID
    $hashed_password = password_hash($employee_no, PASSWORD_DEFAULT);

    $photo = $_FILES['photo']['name'];
    $photo_tmp = $_FILES['photo']['tmp_name'];
    $file_ext = strtolower(pathinfo($photo, PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png'];

    $check_stmt = mysqli_prepare($conn, "SELECT employee_id FROM employee_tbl WHERE employee_no = ? ");
    mysqli_stmt_bind_param($check_stmt, "s", $employee_no);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        showAlert('error', 'Employee ID already exists!');
    } else {
        $new_photo_name = !empty($photo) ? uniqid() . '.' . $file_ext : '';
        if (empty($photo) || move_uploaded_file($photo_tmp, './uploads/' . $new_photo_name)) {
            $insert_stmt = $conn->prepare("INSERT INTO employee_tbl (employee_no, first_name, last_name, department_id, position_name, photo, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssssss", $employee_no, $first_name, $last_name, $department_id, $position_name, $new_photo_name, $hashed_password);
            if ($insert_stmt->execute()) showAlert('success', 'Staff Registered! Password is the ID.');
            $insert_stmt->close();
        }
    }
    $check_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../template/header.php'; ?>
<style>
    .image-preview-container {
        width: 100%;
        height: 150px;
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background-color: #f8f9fa;
        margin-bottom: 10px;
    }

    .image-preview-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
    }

    #formView {
        display: none;
    }

    /* Hidden by default */
</style>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle d-flex justify-content-between align-items-center">
            <h1>Employee Management</h1>
            <button id="toggleBtn" class="btn btn-primary shadow-sm"><i class="bi bi-person-plus-fill me-1"></i> Add Employee</button>
        </div>

        <section class="section mt-3">
            <div id="tableView" class="row">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Staff Directory</h5>

                            <ul class="nav nav-tabs nav-tabs-bordered" id="staffTabs" role="tablist">
                                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all-staff">All Staff</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#directors">Campus Directors</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#dept-heads">Department Heads</button></li>
                            </ul>

                            <div class="tab-content pt-3">
                                <?php
                                $tabs = [
                                    'all-staff' => "status = 1",
                                    'directors' => "position_name LIKE '%CAMPUS DIRECTOR%'",
                                    'dept-heads' => "position_name LIKE '%HEAD%'"
                                ];

                                foreach ($tabs as $id => $condition) {
                                    $active = ($id == 'all-staff') ? 'show active' : '';
                                    echo "<div class='tab-pane fade $active' id='$id'>";
                                    echo "<div class='table-responsive'><table class='table table-hover datatable'>";
                                    echo "<thead><tr><th>Staff</th><th>Work Info</th><th class='text-center'>Action</th></tr></thead><tbody>";

                                    $query = "SELECT e.*, d.department_name FROM employee_tbl e LEFT JOIN department_tbl d ON e.department_id = d.department_id WHERE e.$condition ORDER BY e.employee_id DESC";
                                    $res = mysqli_query($conn, $query);
                                    while ($emp = mysqli_fetch_assoc($res)) {
                                        $img = !empty($emp['photo']) && file_exists("./uploads/" . $emp['photo']) ? "./uploads/" . $emp['photo'] : "https://ui-avatars.com/api/?name=" . urlencode($emp['first_name'] . ' ' . $emp['last_name']) . "&background=random";
                                ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center"><img src="<?= $img ?>" width="40" height="40" class="rounded-circle me-3">
                                                    <div>
                                                        <div class="fw-bold"><?= $emp['first_name'] . ' ' . $emp['last_name'] ?></div><small class="text-muted"><?= $emp['employee_no'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-light text-primary border border-primary-subtle rounded-pill mb-1"><?= $emp['department_name'] ?></span>
                                                <div class="small text-secondary"><?= $emp['position_name'] ?></div>
                                            </td>
                                            <td class="text-center"><button class="btn btn-sm btn-outline-primary rounded-circle"><i class="bi bi-pencil"></i></button></td>
                                        </tr>
                                <?php
                                    }
                                    echo "</tbody></table></div></div>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="formView" class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-center">New Staff Registration</h5>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="image-preview-container"><i class="bi bi-camera text-muted fs-1" id="placeholderIcon"></i><img src="" id="imagePreview"></div>
                                <input type="file" class="form-control form-control-sm mb-3" name="photo" id="photoInput">

                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" name="employee_no" placeholder="ID" required><label>Employee ID No.</label></div>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6"><select class="form-select py-3" name="department_id" required>
                                            <option value="" disabled selected>Department</option>
                                            <?php $dq = mysqli_query($conn, "SELECT * FROM department_tbl WHERE dept_status = 1");
                                            while ($d = mysqli_fetch_assoc($dq)) echo "<option value='{$d['department_id']}'>{$d['department_name']}</option>"; ?>
                                        </select></div>
                                    <div class="col-md-6"><select class="form-select py-3" name="position_name" required>
                                            <option value="" disabled selected>Position</option>
                                            <?php $pq = mysqli_query($conn, "SELECT position_name FROM position_tbl ORDER BY position_name ASC");
                                            while ($p = mysqli_fetch_assoc($pq)) echo "<option value='" . htmlspecialchars($p['position_name']) . "'>" . htmlspecialchars($p['position_name']) . "</option>"; ?>
                                        </select></div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="form-floating"><input type="text" class="form-control" name="first_name" required><label>First Name</label></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-floating"><input type="text" class="form-control" name="last_name" required><label>Last Name</label></div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" id="backBtn" class="btn btn-secondary w-50">Back to Directory</button>
                                    <button type="submit" name="submit" class="btn btn-primary w-50 shadow">Register Staff</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>

    <script>
        const tableView = document.getElementById('tableView');
        const formView = document.getElementById('formView');
        const toggleBtn = document.getElementById('toggleBtn');
        const backBtn = document.getElementById('backBtn');

        // Toggle Views
        toggleBtn.onclick = () => {
            tableView.style.display = 'none';
            formView.style.display = 'flex';
            toggleBtn.style.display = 'none';
        };
        backBtn.onclick = () => {
            tableView.style.display = 'flex';
            formView.style.display = 'none';
            toggleBtn.style.display = 'block';
        };

        // Photo Preview
        const photoInput = document.getElementById('photoInput');
        const imagePreview = document.getElementById('imagePreview');
        const placeholderIcon = document.getElementById('placeholderIcon');
        photoInput.onchange = () => {
            const [file] = photoInput.files;
            if (file) {
                imagePreview.src = URL.createObjectURL(file);
                imagePreview.style.display = 'block';
                placeholderIcon.style.display = 'none';
            }
        }
    </script>
</body>

</html>