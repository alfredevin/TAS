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

// ==========================================
// 1. ADD EMPLOYEE LOGIC
// ==========================================
if (isset($_POST['submit'])) {
    $employee_no = strtoupper(trim($_POST['employee_no']));
    $first_name = strtoupper(trim($_POST['first_name']));
    $last_name = strtoupper(trim($_POST['last_name']));
    $department_id = $_POST['department_id'];
    $position_name = $_POST['position_name'];

    // Password is auto-set to Employee ID
    $hashed_password = password_hash($employee_no, PASSWORD_DEFAULT);

    $photo = $_FILES['photo']['name'];
    $photo_tmp = $_FILES['photo']['tmp_name'];
    $file_ext = strtolower(pathinfo($photo, PATHINFO_EXTENSION));

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
            if ($insert_stmt->execute())
                showAlert('success', 'Staff Registered! Password is the ID.');
            $insert_stmt->close();
        }
    }
    $check_stmt->close();
}

// ==========================================
// 2. UPDATE EMPLOYEE LOGIC (NEW)
// ==========================================
if (isset($_POST['update_employee'])) {
    $emp_id = $_POST['edit_employee_id'];
    $first_name = strtoupper(trim($_POST['edit_first_name']));
    $last_name = strtoupper(trim($_POST['edit_last_name']));
    $department_id = $_POST['edit_department_id'];
    $position_name = $_POST['edit_position_name'];

    $photo = $_FILES['edit_photo']['name'];
    $photo_tmp = $_FILES['edit_photo']['tmp_name'];
    $file_ext = strtolower(pathinfo($photo, PATHINFO_EXTENSION));

    // Kung may bagong picture na in-upload
    if (!empty($photo)) {
        $new_photo_name = uniqid() . '.' . $file_ext;
        if (move_uploaded_file($photo_tmp, './uploads/' . $new_photo_name)) {
            $update_stmt = $conn->prepare("UPDATE employee_tbl SET first_name=?, last_name=?, department_id=?, position_name=?, photo=? WHERE employee_id=?");
            $update_stmt->bind_param("sssssi", $first_name, $last_name, $department_id, $position_name, $new_photo_name, $emp_id);
            if ($update_stmt->execute())
                showAlert('success', 'Employee updated successfully!');
            $update_stmt->close();
        }
    } else {
        // Update details lang, walang picture change
        $update_stmt = $conn->prepare("UPDATE employee_tbl SET first_name=?, last_name=?, department_id=?, position_name=? WHERE employee_id=?");
        $update_stmt->bind_param("ssssi", $first_name, $last_name, $department_id, $position_name, $emp_id);
        if ($update_stmt->execute())
            showAlert('success', 'Employee details updated!');
        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../template/header.php'; ?>
<style>
    /* Add Form Preview */
    .image-preview-container {
        width: 100%; height: 150px; border: 2px dashed #dee2e6;
        border-radius: 10px; display: flex; align-items: center;
        justify-content: center; overflow: hidden; background-color: #f8f9fa; margin-bottom: 10px;
    }
    .image-preview-container img { width: 100%; height: 100%; object-fit: cover; display: none; }

    /* Edit Modal Preview */
    .edit-preview-container {
        width: 120px; height: 120px; border-radius: 50%;
        border: 4px solid #e2e8f0; overflow: hidden; margin: 0 auto 15px auto;
        position: relative; background: #f1f5f9; display: flex; align-items: center; justify-content: center;
    }
    .edit-preview-container img { width: 100%; height: 100%; object-fit: cover; }
    
    #formView { display: none; }

    /* Premium Table Styling */
    .table-hover tbody tr { transition: all 0.2s ease; }
    .table-hover tbody tr:hover { background-color: #f8fafc; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.03); }
</style>

<body style="background-color: #f4f6f9;">
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bolder" style="color: #1e3c72;">Employee Management</h1>
                <nav>
                    <ol class="breadcrumb bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-muted">Admin</a></li>
                        <li class="breadcrumb-item active fw-bold" style="color: #800000;">Staff Directory</li>
                    </ol>
                </nav>
            </div>
            <button id="toggleBtn" class="btn btn-primary rounded-pill shadow-sm px-4 fw-bold"><i class="bi bi-person-plus-fill me-1"></i> Add Employee</button>
        </div>

        <section class="section">
            
            <div id="tableView" class="row">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                        <div class="card-body p-4">

                            <ul class="nav nav-pills mb-4" id="staffTabs" role="tablist">
                                <li class="nav-item me-2"><button class="nav-link active rounded-pill fw-bold border" data-bs-toggle="pill" data-bs-target="#all-staff">All Staff</button></li>
                                <li class="nav-item me-2"><button class="nav-link rounded-pill fw-bold border" data-bs-toggle="pill" data-bs-target="#directors">Campus Directors</button></li>
                                <li class="nav-item"><button class="nav-link rounded-pill fw-bold border" data-bs-toggle="pill" data-bs-target="#dept-heads">Department Heads</button></li>
                            </ul>

                            <div class="tab-content">
                                <?php
                                $tabs = [
                                    'all-staff' => "status = 1",
                                    'directors' => "position_name LIKE '%CAMPUS DIRECTOR%'",
                                    'dept-heads' => "position_name LIKE '%HEAD%'"
                                ];

                                foreach ($tabs as $id => $condition) {
                                    $active = ($id == 'all-staff') ? 'show active' : '';
                                    echo "<div class='tab-pane fade $active' id='$id'>";
                                    echo "<div class='table-responsive'><table class='table table-hover align-middle datatable w-100'>";
                                    echo "<thead class='table-light'><tr><th>Staff Info</th><th>Department & Role</th><th class='text-center'>Action</th></tr></thead><tbody>";

                                    $query = "SELECT e.*, d.department_name FROM employee_tbl e LEFT JOIN department_tbl d ON e.department_id = d.department_id WHERE e.$condition ORDER BY e.first_name ASC";
                                    $res = mysqli_query($conn, $query);
                                    while ($emp = mysqli_fetch_assoc($res)) {
                                        $img = !empty($emp['photo']) && file_exists("./uploads/" . $emp['photo']) ? "./uploads/" . $emp['photo'] : "https://ui-avatars.com/api/?name=" . urlencode($emp['first_name'] . ' ' . $emp['last_name']) . "&background=random";

                                        // Prepare safe strings for JS modal passing
                                        $safe_fname = htmlspecialchars($emp['first_name'], ENT_QUOTES);
                                        $safe_lname = htmlspecialchars($emp['last_name'], ENT_QUOTES);
                                        $safe_pos = htmlspecialchars($emp['position_name'], ENT_QUOTES);
                                        ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?= $img ?>" width="45" height="45" class="rounded-circle me-3 shadow-sm" style="object-fit:cover;">
                                                            <div>
                                                                <div class="fw-bold text-dark"><?= $emp['first_name'] . ' ' . $emp['last_name'] ?></div>
                                                                <small class="text-muted"><i class="bi bi-badge-ad me-1"></i><?= $emp['employee_no'] ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-primary border border-primary-subtle rounded-pill mb-1 px-3 py-2"><?= $emp['department_name'] ?? 'Unassigned' ?></span>
                                                        <div class="small text-secondary fw-bold ms-1 mt-1"><?= $emp['position_name'] ?></div>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm edit-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editEmployeeModal"
                                                            data-id="<?= $emp['employee_id'] ?>"
                                                            data-eno="<?= $emp['employee_no'] ?>"
                                                            data-fname="<?= $safe_fname ?>"
                                                            data-lname="<?= $safe_lname ?>"
                                                            data-dept="<?= $emp['department_id'] ?>"
                                                            data-pos="<?= $safe_pos ?>"
                                                            data-photo="<?= $img ?>">
                                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                                        </button>
                                                    </td>
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
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <h4 class="fw-bolder" style="color: #1e3c72;">New Staff Registration</h4>
                                <p class="text-muted small">Fill in the details to add a new employee to the system.</p>
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="image-preview-container"><i class="bi bi-camera text-muted fs-1" id="placeholderIcon"></i><img src="" id="imagePreview"></div>
                                <input type="file" class="form-control form-control-sm mb-4" name="photo" id="photoInput">

                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" name="employee_no" placeholder="ID" required>
                                    <label><i class="bi bi-upc-scan me-1"></i> Employee ID No.</label>
                                </div>
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" name="department_id" required>
                                                <option value="" disabled selected>Select Dept</option>
                                                <?php
                                                $dq = mysqli_query($conn, "SELECT * FROM department_tbl WHERE dept_status = 1");
                                                while ($d = mysqli_fetch_assoc($dq))
                                                    echo "<option value='{$d['department_id']}'>{$d['department_name']}</option>";
                                                ?>
                                            </select>
                                            <label>Department</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" name="position_name" required>
                                                <option value="" disabled selected>Select Pos</option>
                                                <?php
                                                $pq = mysqli_query($conn, "SELECT position_name FROM position_tbl ORDER BY position_name ASC");
                                                while ($p = mysqli_fetch_assoc($pq))
                                                    echo "<option value='" . htmlspecialchars($p['position_name']) . "'>" . htmlspecialchars($p['position_name']) . "</option>";
                                                ?>
                                            </select>
                                            <label>Position</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row g-3 mb-4">
                                    <div class="col-6">
                                        <div class="form-floating"><input type="text" class="form-control" name="first_name" placeholder="First" required><label>First Name</label></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-floating"><input type="text" class="form-control" name="last_name" placeholder="Last" required><label>Last Name</label></div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2 border-top pt-4">
                                    <button type="button" id="backBtn" class="btn btn-light border fw-bold w-50 rounded-pill">Cancel & Back</button>
                                    <button type="submit" name="submit" class="btn btn-primary fw-bold w-50 rounded-pill shadow-sm">Register Staff</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 bg-light p-4 pb-2" style="border-radius: 20px 20px 0 0;">
                    <h5 class="modal-title fw-bolder text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Employee Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <input type="hidden" name="edit_employee_id" id="edit_employee_id">

                        <div class="text-center mb-4">
                            <div class="edit-preview-container shadow-sm">
                                <img src="" id="editImagePreview" alt="Current Photo">
                            </div>
                            <label for="editPhotoInput" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                <i class="bi bi-camera me-1"></i> Change Photo
                            </label>
                            <input type="file" name="edit_photo" id="editPhotoInput" class="d-none" accept=".jpg,.jpeg,.png">
                            <div class="small text-muted mt-1" style="font-size:11px;">Leave empty to keep current photo.</div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control bg-light" id="edit_employee_no" readonly>
                            <label>Employee ID No. (Cannot be changed)</label>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="edit_first_name" id="edit_first_name" required>
                                    <label>First Name</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="edit_last_name" id="edit_last_name" required>
                                    <label>Last Name</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <div class="form-floating">
                                    <select class="form-select" name="edit_department_id" id="edit_department_id" required>
                                        <?php
                                        $dq_edit = mysqli_query($conn, "SELECT * FROM department_tbl WHERE dept_status = 1");
                                        while ($d_edit = mysqli_fetch_assoc($dq_edit)) {
                                            echo "<option value='{$d_edit['department_id']}'>{$d_edit['department_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                    <label>Department</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating">
                                    <select class="form-select" name="edit_position_name" id="edit_position_name" required>
                                        <?php
                                        $pq_edit = mysqli_query($conn, "SELECT position_name FROM position_tbl ORDER BY position_name ASC");
                                        while ($p_edit = mysqli_fetch_assoc($pq_edit)) {
                                            echo "<option value='" . htmlspecialchars($p_edit['position_name']) . "'>" . htmlspecialchars($p_edit['position_name']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <label>Position</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill fw-bold border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_employee" class="btn btn-primary rounded-pill fw-bold px-4 shadow-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>

    <script>
        // Toggle Views (Add form vs Table)
        const tableView = document.getElementById('tableView');
        const formView = document.getElementById('formView');
        const toggleBtn = document.getElementById('toggleBtn');
        const backBtn = document.getElementById('backBtn');

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

        // Add Form Photo Preview
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

        // =====================================
        // MODAL DATA POPULATION SCRIPT
        // =====================================
        document.addEventListener('click', function (e) {
            // Check if clicked element is an edit button (or inside it)
            const btn = e.target.closest('.edit-btn');
            if (btn) {
                // Kunin ang data attributes mula sa button
                const id = btn.getAttribute('data-id');
                const eno = btn.getAttribute('data-eno');
                const fname = btn.getAttribute('data-fname');
                const lname = btn.getAttribute('data-lname');
                const dept = btn.getAttribute('data-dept');
                const pos = btn.getAttribute('data-pos');
                const photoSrc = btn.getAttribute('data-photo');

                // I-pasok ang data sa Modal Inputs
                document.getElementById('edit_employee_id').value = id;
                document.getElementById('edit_employee_no').value = eno;
                document.getElementById('edit_first_name').value = fname;
                document.getElementById('edit_last_name').value = lname;
                document.getElementById('edit_department_id').value = dept;
                document.getElementById('edit_position_name').value = pos;
                
                // Update Image Preview
                document.getElementById('editImagePreview').src = photoSrc;
                
                // Clear any previous file input in edit
                document.getElementById('editPhotoInput').value = '';
            }
        });

        // Edit Modal Photo Preview Change
        document.getElementById('editPhotoInput').addEventListener('change', function() {
            const [file] = this.files;
            if (file) {
                document.getElementById('editImagePreview').src = URL.createObjectURL(file);
            }
        });
    </script>
</body>
</html>