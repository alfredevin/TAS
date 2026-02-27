<?php
include './../config.php';

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
            overflow: hidden;
        }

        .profile-card .card-body {
            padding-top: 30px;
        }

        .nav-tabs-bordered .nav-link.active {
            color: var(--marsu-maroon);
            border-bottom: 3px solid var(--marsu-maroon);
            font-weight: 700;
        }

        .label-profile {
            font-weight: 700;
            color: #444;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: #666;
            font-size: 15px;
        }

        .form-control:focus {
            border-color: var(--marsu-maroon);
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.1);
        }

        .input-group-text {
            background-color: #f8f9fa;
            color: var(--marsu-maroon);
            border-right: none;
        }

        .input-group .form-control {
            border-left: none;
        }

        .btn-maroon {
            background: var(--marsu-maroon);
            color: white;
            border: none;
            border-radius: 50px;
            transition: 0.3s;
        }

        .btn-maroon:hover {
            background: #600000;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(128, 0, 0, 0.3);
        }
    </style>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <?php

    $id = $_SESSION['userid'];

    // 1. KUNIN ANG KASALUKUYANG DETALYE
    $user_q = mysqli_query($conn, "SELECT * FROM user_tbl WHERE userid = '$id'");
    $user = mysqli_fetch_assoc($user_q);

    $status = "";

    // 2. LOGIC: UPDATE PROFILE INFORMATION
    if (isset($_POST['update_profile'])) {
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $contact = mysqli_real_escape_string($conn, $_POST['contact_number']);

        $update_info = "UPDATE user_tbl SET fullname = '$fullname', email = '$email', username = '$username', contact_number = '$contact' WHERE userid = '$id'";

        if (mysqli_query($conn, $update_info)) {
            $status = "profile_success";
            $user_q = mysqli_query($conn, "SELECT * FROM user_tbl WHERE userid = '$id'");
            $user = mysqli_fetch_assoc($user_q);
        } else {
            $status = "error_db";
        }
    }

    // 3. LOGIC: CHANGE PASSWORD
    if (isset($_POST['change_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        if (password_verify($old_password, $user['password'])) {
            if ($new_password === $confirm_new_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                if (mysqli_query($conn, "UPDATE user_tbl SET password = '$hashed_password' WHERE userid = '$id'")) {
                    $status = "pw_success";
                }
            } else {
                $status = "mismatch";
            }
        } else {
            $status = "incorrect_old";
        }
    }
    ?>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Account Center</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">Profile Management</li>
                </ol>
            </nav>
        </div>

        <section class="section profile">
            <div class="row">
                <div class="col-xl-4">
                    <div class="card profile-card shadow-sm">
                        <div class="card-body d-flex flex-column align-items-center">
                            <div class="position-relative mb-3">
                                <div class="bg-maroon bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 120px; height: 120px; background: #fdf2f2;">
                                    <i class="bi bi-person-circle fs-1" style="color: var(--marsu-maroon);"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold mb-1 text-center"><?= $user['fullname'] ?></h4>
                            <p class="text-muted small mb-3 text-center text-uppercase fw-bold"><?= $user['position'] ?>
                            </p>
                            <div class="d-flex gap-2">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Verified
                                    Account</span>
                            </div>

                            <hr class="w-100 my-4">

                            <div class="w-100">
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span class="text-muted">Username:</span>
                                    <span class="fw-bold"><?= $user['username'] ?></span>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted">Account ID:</span>
                                    <span class="fw-bold text-maroon">#TAS-<?= $user['userid'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card shadow-sm border-0" style="border-radius: 20px;">
                        <div class="card-body pt-3">
                            <ul class="nav nav-tabs nav-tabs-bordered w-100">
                                <li class="nav-item flex-fill"><button class="nav-link w-100 active"
                                        data-bs-toggle="tab" data-bs-target="#overview"><i
                                            class="bi bi-info-circle me-2"></i>Overview</button></li>
                                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab"
                                        data-bs-target="#edit"><i class="bi bi-pencil-square me-2"></i>Edit
                                        Profile</button></li>
                                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab"
                                        data-bs-target="#password"><i
                                            class="bi bi-shield-lock me-2"></i>Security</button></li>
                            </ul>

                            <div class="tab-content pt-4">
                                <div class="tab-pane fade show active px-3" id="overview">
                                    <div class="row mb-4">
                                        <div class="col-lg-4 label-profile">Full Name</div>
                                        <div class="col-lg-8 info-value fw-bold text-dark"><?= $user['fullname'] ?>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-lg-4 label-profile">Email Address</div>
                                        <div class="col-lg-8 info-value"><?= $user['email'] ?></div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-lg-4 label-profile">Username</div>
                                        <div class="col-lg-8 info-value"><span
                                                class="badge bg-light text-dark border"><?= $user['username'] ?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-lg-4 label-profile">Contact Number</div>
                                        <div class="col-lg-8 info-value"><?= $user['contact_number'] ?></div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-lg-4 label-profile">Designated Position</div>
                                        <div class="col-lg-8 info-value text-maroon fw-bold"><?= $user['position'] ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade px-3" id="edit">
                                    <form method="POST" class="row g-3">
                                        <div class="col-md-12">
                                            <label class="label-profile mb-2">Display Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                <input name="fullname" type="text" class="form-control rounded-end-pill"
                                                    value="<?= $user['fullname'] ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="label-profile mb-2">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                                <input name="email" type="email" class="form-control rounded-end-pill"
                                                    value="<?= $user['email'] ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="label-profile mb-2">Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-at"></i></span>
                                                <input name="username" type="text" class="form-control rounded-end-pill"
                                                    value="<?= $user['username'] ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="label-profile mb-2">Contact Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                                <input name="contact_number" type="text"
                                                    class="form-control rounded-end-pill"
                                                    value="<?= $user['contact_number'] ?>" required>
                                            </div>
                                        </div>
                                        <div class="text-end mt-4"><button type="submit" name="update_profile"
                                                class="btn btn-maroon px-5 fw-bold shadow-sm">Save Changes</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane fade px-3" id="password">
                                    <div class="alert alert-info border-0 shadow-sm small mb-4"
                                        style="border-radius: 15px;">
                                        <i class="bi bi-info-circle-fill me-2"></i> Password should be unique and at
                                        least 8 characters long for better security.
                                    </div>
                                    <form method="POST" class="row g-3">
                                        <div class="col-md-12"><label class="label-profile mb-2">Current
                                                Password</label><input name="old_password" type="password"
                                                class="form-control rounded-pill" placeholder="••••••••" required></div>
                                        <div class="col-md-6"><label class="label-profile mb-2">New
                                                Password</label><input name="new_password" type="password"
                                                class="form-control rounded-pill" placeholder="New Password" required>
                                        </div>
                                        <div class="col-md-6"><label class="label-profile mb-2">Confirm New
                                                Password</label><input name="confirm_new_password" type="password"
                                                class="form-control rounded-pill" placeholder="Confirm Password"
                                                required></div>
                                        <div class="text-end mt-4"><button type="submit" name="change_password"
                                                class="btn btn-dark px-5 fw-bold rounded-pill shadow-sm">Update
                                                Security</button></div>
                                    </form>
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

    <?php if ($status): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
                <?php if ($status == "profile_success"): ?>
                    Toast.fire({ icon: 'success', title: 'Profile synchronized successfully!' });
                <?php elseif ($status == "pw_success"): ?>
                    Swal.fire({ icon: 'success', title: 'Security Protocol Updated', text: 'Password changed successfully. You need to re-authenticate.' }).then(() => { window.location.href = "../../logout"; });
                <?php elseif ($status == "incorrect_old"): ?>
                    Swal.fire({ icon: 'error', title: 'Access Denied', text: 'The current password you entered is invalid.' });
                <?php elseif ($status == "mismatch"): ?>
                    Swal.fire({ icon: 'warning', title: 'Logic Error', text: 'New passwords do not match. Please verify.' });
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>
</body>

</html>