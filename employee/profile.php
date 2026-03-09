<?php
session_start();
include './../config.php';

// Check kung naka-login
if (!isset($_SESSION['employee_id']) && !isset($_SESSION['userid'])) {
    header("Location: ../index.php");
    exit();
}

$employee_id = $_SESSION['employee_id'] ?? null;
$alert_msg = '';
$alert_type = '';

// KUNIN ANG DATA NG EMPLOYEE
if ($employee_id) {
    $query = mysqli_query($conn, "SELECT e.*, d.department_name 
                                  FROM employee_tbl e 
                                  LEFT JOIN department_tbl d ON e.department_id = d.department_id 
                                  WHERE e.employee_id = '$employee_id'");
    $emp = mysqli_fetch_assoc($query);

    $full_name = $emp['first_name'] . ' ' . $emp['last_name'];
    $position = $emp['position_name'];
    $department = $emp['department_name'] ?? 'No Department Assigned';
    $avatar = (!empty($emp['photo'])) ? './../admin/uploads/' . $emp['photo'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
}

// ==========================================
// LOGIC PARA SA PAG-UPDATE NG PROFILE
// ==========================================
if (isset($_POST['update_profile'])) {
    $fname = mysqli_real_escape_string($conn, $_POST['first_name']);
    $lname = mysqli_real_escape_string($conn, $_POST['last_name']);

    $update_query = "UPDATE employee_tbl SET first_name = '$fname', last_name = '$lname' WHERE employee_id = '$employee_id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['swal_type'] = 'success';
        $_SESSION['swal_msg'] = 'Profile updated successfully!';
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['swal_type'] = 'error';
        $_SESSION['swal_msg'] = 'Failed to update profile.';
    }
}

// ==========================================
// LOGIC PARA SA PAGBABAGO NG PASSWORD
// ==========================================
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($current_password !== $emp['password']) {
        $_SESSION['swal_type'] = 'error';
        $_SESSION['swal_msg'] = 'Current password is incorrect!';
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['swal_type'] = 'warning';
        $_SESSION['swal_msg'] = 'New passwords do not match!';
    } else {
        $update_pass = mysqli_query($conn, "UPDATE employee_tbl SET password = '$new_password' WHERE employee_id = '$employee_id'");
        if ($update_pass) {
            $_SESSION['swal_type'] = 'success';
            $_SESSION['swal_msg'] = 'Password changed successfully!';
            header("Location: profile.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include '../template/header.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body style="background-color: #f4f6f9;">
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <style>
        .profile-card {
            background: #fff;
            border-radius: 24px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            height: 100%;
        }

        /* Profile Sidebar (Left) */
        .profile-header-bg {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            /* Changed to professional blue/maroon based on your theme preference */
            background: linear-gradient(135deg, #800000 0%, #b91c1c 100%);
            height: 140px;
            width: 100%;
            position: relative;
        }

        .profile-avatar-wrapper {
            margin-top: -70px;
            text-align: center;
            position: relative;
            z-index: 2;
            padding-bottom: 20px;
        }

        .profile-avatar {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            border: 6px solid #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            object-fit: cover;
            background: #fff;
        }

        .profile-name {
            font-size: 24px;
            font-weight: 800;
            color: #1e293b;
            margin-top: 15px;
            margin-bottom: 0;
            letter-spacing: -0.5px;
        }

        .profile-role {
            font-size: 13px;
            font-weight: 700;
            color: #800000;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        /* Modern Segmented Tabs */
        .nav-custom {
            background: #f1f5f9;
            border-radius: 50px;
            padding: 6px;
            display: inline-flex;
            width: 100%;
            overflow-x: auto;
            flex-wrap: nowrap;
        }

        .nav-custom::-webkit-scrollbar {
            display: none;
        }

        .nav-custom .nav-link {
            border-radius: 50px;
            color: #64748b;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 20px;
            white-space: nowrap;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
            border: none;
        }

        .nav-custom .nav-link:hover {
            color: #800000;
        }

        .nav-custom .nav-link.active {
            background: #fff;
            color: #800000;
            font-weight: 800;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        /* Info Boxes for Overview */
        .info-box {
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            padding: 20px;
            height: 100%;
            transition: 0.3s;
        }

        .info-box:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
        }

        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            color: #1e293b;
            font-weight: 700;
            word-wrap: break-word;
        }

        /* Form Inputs */
        .form-floating>.form-control {
            border-radius: 16px;
            border: 2px solid #e2e8f0;
            background: #fff;
            color: #1e293b;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: none;
            padding-right: 45px;
            /* Space for the eye icon */
        }

        .form-floating>.form-control:focus {
            border-color: #800000;
            box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.1);
        }

        .form-floating label {
            color: #94a3b8;
            font-weight: 600;
        }

        /* Password Toggle Icon */
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            font-size: 20px;
            z-index: 10;
            transition: 0.2s;
        }

        .password-toggle:hover {
            color: #800000;
        }

        .btn-modern {
            border-radius: 50px;
            font-weight: 700;
            padding: 14px 35px;
            transition: 0.2s;
            letter-spacing: 0.5px;
            border: none;
        }

        .btn-modern:active {
            transform: scale(0.95);
        }

        @media (max-width: 768px) {
            .nav-custom {
                padding: 4px;
            }

            .nav-custom .nav-link {
                padding: 10px 15px;
                font-size: 13px;
            }

            .btn-modern {
                width: 100%;
            }
        }
    </style>

    <main id="main" class="main">
        <div class="pagetitle mb-4">
            <h1 class="fw-bolder" style="color: #1e293b; font-size: 1.8rem;">Account Profile</h1>
            <nav>
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-muted">Home</a></li>
                    <li class="breadcrumb-item active fw-bold" style="color: #800000;">Settings</li>
                </ol>
            </nav>
        </div>

        <section class="section profile">
            <div class="row g-4 align-items-stretch">

                <div class="col-xl-4 col-lg-5">
                    <div class="profile-card">
                        <div class="profile-header-bg"></div>
                        <div class="profile-avatar-wrapper">
                            <img src="<?= $avatar ?>" alt="Profile" class="profile-avatar">
                            <h2 class="profile-name"><?= $full_name ?></h2>
                            <p class="profile-role"><?= $position ?></p>
                            <div class="mt-3">
                                <span class="badge bg-light text-dark border shadow-sm px-3 py-2 rounded-pill">
                                    <i class="bi bi-building me-1" style="color: #800000;"></i> <?= $department ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8 col-lg-7">
                    <div class="profile-card d-flex flex-column">

                        <div class="p-3 border-bottom">
                            <ul class="nav nav-custom" id="profileTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab"
                                        data-bs-target="#overview" type="button"><i class="bi bi-person-vcard me-1"></i>
                                        Overview</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit"
                                        type="button"><i class="bi bi-pencil-square me-1"></i> Edit Info</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="security-tab" data-bs-toggle="tab"
                                        data-bs-target="#security" type="button"><i class="bi bi-shield-lock me-1"></i>
                                        Security</button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content p-4 flex-grow-1" id="profileTabContent">

                            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                                <h5 class="fw-bold text-dark mb-4"><i
                                        class="bi bi-info-circle me-2 text-primary"></i>Personal Details</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <div class="info-label">Full Name</div>
                                            <div class="info-value"><?= $full_name ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <div class="info-label">Employee ID / Username</div>
                                            <div class="info-value"><?= $emp['username'] ?? $employee_id ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <div class="info-label">Department</div>
                                            <div class="info-value"><?= $department ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <div class="info-label">Official Designation</div>
                                            <div class="info-value"><?= $position ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="edit" role="tabpanel">
                                <form method="POST" action="">
                                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-pencil-square me-2"
                                            style="color: #800000;"></i>Update Information</h5>

                                    <div class="alert alert-light border shadow-sm small mb-4 rounded-3 text-muted">
                                        <i class="bi bi-lightbulb-fill text-warning me-2"></i> Note: To change your
                                        Position or Department, please contact the System Administrator.
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="first_name"
                                                    name="first_name" value="<?= $emp['first_name'] ?>" required>
                                                <label for="first_name">First Name</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="last_name" name="last_name"
                                                    value="<?= $emp['last_name'] ?>" required>
                                                <label for="last_name">Last Name</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end mt-4 pt-3 border-top">
                                        <button type="submit" name="update_profile"
                                            class="btn btn-modern btn-primary shadow" style="background:#1e3c72;">
                                            <i class="bi bi-save me-1"></i> Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="security" role="tabpanel">
                                <form method="POST" action="">
                                    <h5 class="fw-bold text-dark mb-4"><i
                                            class="bi bi-shield-check me-2 text-success"></i>Update Security</h5>

                                    <div class="form-floating mb-3 position-relative">
                                        <input type="password" class="form-control pr-5" id="current_password"
                                            name="current_password" placeholder="Current Password" required>
                                        <label for="current_password">Current Password</label>
                                        <i class="bi bi-eye-slash password-toggle"
                                            onclick="togglePassword('current_password', this)"></i>
                                    </div>

                                    <div class="form-floating mb-3 position-relative">
                                        <input type="password" class="form-control pr-5" id="new_password"
                                            name="new_password" placeholder="New Password" required minlength="6">
                                        <label for="new_password">New Password (Min. 6 chars)</label>
                                        <i class="bi bi-eye-slash password-toggle"
                                            onclick="togglePassword('new_password', this)"></i>
                                    </div>

                                    <div class="form-floating mb-4 position-relative">
                                        <input type="password" class="form-control pr-5" id="confirm_password"
                                            name="confirm_password" placeholder="Confirm Password" required
                                            minlength="6">
                                        <label for="confirm_password">Re-enter New Password</label>
                                        <i class="bi bi-eye-slash password-toggle"
                                            onclick="togglePassword('confirm_password', this)"></i>
                                    </div>

                                    <div class="text-end mt-4 pt-3 border-top">
                                        <button type="submit" name="change_password"
                                            class="btn btn-modern text-white shadow" style="background:#800000;">
                                            <i class="bi bi-shield-lock me-1"></i> Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>

    <script>
        // FUNCTION PARA SA SHOW/HIDE PASSWORD
        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                iconElement.classList.remove("bi-eye-slash");
                iconElement.classList.add("bi-eye");
            } else {
                input.type = "password";
                iconElement.classList.remove("bi-eye");
                iconElement.classList.add("bi-eye-slash");
            }
        }

        // TOAST NOTIFICATIONS
        <?php if (isset($_SESSION['swal_msg'])): ?>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: '<?= $_SESSION['swal_type'] ?>',
                    title: '<?= ($_SESSION['swal_type'] == "success") ? "Success!" : "Notice" ?>',
                    text: '<?= $_SESSION['swal_msg'] ?>',
                    timer: 3500,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true,
                    background: '#fff',
                    color: '#1e293b',
                    iconColor: '<?= ($_SESSION['swal_type'] == "success") ? "#10b981" : "#ef4444" ?>'
                });
            });
            <?php
            unset($_SESSION['swal_type']);
            unset($_SESSION['swal_msg']);
        endif;
        ?>
    </script>
</body>

</html>