<?php
session_start();
include '../config.php';

$fullname = '';
$account_role = '';
$user_position = '';

// 1. Check kung ADMINISTRATOR (Galing sa user_tbl)
if (isset($_SESSION['userid'])) {
    $userid = mysqli_real_escape_string($conn, $_SESSION['userid']);
    $navbar = "SELECT * FROM user_tbl WHERE userid = '$userid'";
    $result_navbar = mysqli_query($conn, $navbar);

    if ($result_navbar && mysqli_num_rows($result_navbar) > 0) {
        $row = mysqli_fetch_assoc($result_navbar);
        $fullname = isset($row['fullname']) ? $row['fullname'] : $row['first_name'] . ' ' . $row['last_name'];
        $account_role = 'ADMINISTRATOR';
    }
}
// 2. LAHAT NG EMPLOYEE (Dean, Director, Head, Staff - Galing sa employee_tbl)
elseif (isset($_SESSION['employee_id'])) {
    $employee_id = mysqli_real_escape_string($conn, $_SESSION['employee_id']);

    // Kunin ang info pati ang position_name
    $navbar = "SELECT * FROM employee_tbl WHERE employee_id = '$employee_id'";
    $result_navbar = mysqli_query($conn, $navbar);

    if ($result_navbar && mysqli_num_rows($result_navbar) > 0) {
        $row = mysqli_fetch_assoc($result_navbar);
        $fullname = $row['first_name'] . ' ' . $row['last_name'];
        $dept_id = $row['department_id'];
        $user_position = strtoupper($row['position_name']); // Halimbawa: CAMPUS DIRECTOR

        // MAHALAGA: I-save sa session para magamit ng sidebar.php
        $_SESSION['position_name'] = $user_position;

        // Role Identification logic para sa UI
        if (strpos($user_position, 'CAMPUS DIRECTOR') !== false) {
            $account_role = 'CAMPUS DIRECTOR';
        } elseif (strpos($user_position, 'DEAN') !== false) {
            $account_role = 'DEAN';
        } elseif (strpos($user_position, 'HEAD') !== false) {
            $account_role = 'DEPARTMENT HEAD';
        } else {
            $account_role = 'STAFF / FACULTY';
        }
    }
} else {
    // Pag walang session, balik sa login page
    header("Location: ../index");
    exit();
}

$current_session_id = isset($_SESSION['userid']) ? $_SESSION['userid'] : ($_SESSION['employee_id'] ?? 0);
// 3. Smart Notification Logic
// Bilangin lang ang UNREAD
$count_q = mysqli_query($conn, "SELECT COUNT(*) as unread FROM notifications_tbl WHERE recipient_id = '$current_session_id' AND is_read = 0");
$unread_count = mysqli_fetch_assoc($count_q)['unread'] ?? 0;

// Kunin ang latest 5, pero UNAHIN ang UNREAD para hindi sila 'ma-ghost'
$notifs_q = mysqli_query($conn, "SELECT * FROM notifications_tbl 
                                 WHERE recipient_id = '$current_session_id' 
                                 ORDER BY is_read ASC, created_at DESC 
                                 LIMIT 5");
?>
<style>
    :root {

        --marsu-maroon: rgb(128, 0, 36);

    }



    .header {

        background: var(--marsu-maroon);

        height: 65px;

        border-bottom: 1px solid rgba(255, 255, 255, 0.2);

    }



    /* Branding */

    .logo span {

        font-size: 20px;

        font-weight: 800;

        color: white;

        font-family: "Poppins", sans-serif;

        letter-spacing: 1px;

    }



    .system-subtext {

        font-size: 10px;

        color: rgba(255, 255, 255, 0.7);

        display: block;

        text-transform: uppercase;

        margin-top: -3px;

    }



    /* Notification Bell Customization */

    .nav-icon i {

        color: #ffffff !important;

        font-size: 22px;

    }



    .badge-number {

        position: absolute;

        inset: -2px -2px auto auto;

        font-size: 10px;

        padding: 3px 6px;

        border: 2px solid var(--marsu-maroon);

    }



    /* Dropdown Improvements */

    .notifications {

        width: 320px;

        border-radius: 12px;

        border: none;

        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.15);

    }



    .notification-item {

        display: flex;

        align-items: center;

        padding: 12px 15px;

        transition: 0.3s;

        border-bottom: 1px solid #f4f4f4;

    }



    .notification-item:hover {

        background-color: #f9f9f9;

    }



    .notification-item.unread {

        background-color: #fff9f9;

        border-left: 3px solid var(--marsu-maroon);

    }



    .pulse-bell {

        animation: pulse-white 2s infinite;

    }



    @keyframes pulse-white {

        0% {

            transform: scale(1);

        }



        50% {

            transform: scale(1.1);

        }



        100% {

            transform: scale(1);

        }

    }
</style>







<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between px-3">

        <a href="index" class="logo d-flex align-items-center">

            <img src="https://hrmis.marsu.edu.ph/dist/img/marsu_logo.png" alt="MarSU"
                style="max-height: 40px; border-radius: 50%; background:white; padding:2px;">

            <div class="ms-2 d-none d-lg-block">

                <span>E-TAMS</span>

                <small class="system-subtext mt-1">E-Travel Authority Management System</small>

            </div>

        </a>

        <i class="bi bi-list toggle-sidebar-btn ms-3" style="color:white; cursor:pointer; font-size: 24px;"></i>

    </div>

    <nav class="header-nav ms-auto pe-4">
        <ul class="d-flex align-items-center">

            <li class="nav-item dropdown">
                <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    <?php if ($unread_count > 0): ?>
                        <span class="badge bg-danger badge-number rounded-pill pulse-bell"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications shadow">
                    <li class="dropdown-header py-3 text-start">
                        <span class="fw-bold">Notifications</span>
                        <?php if ($unread_count > 0): ?>
                            <span class="badge rounded-pill bg-primary ms-2"><?= $unread_count ?> New</span>
                        <?php endif; ?>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <div style="max-height: 350px; overflow-y: auto;">
                        <?php if (mysqli_num_rows($notifs_q) > 0): ?>
                            <?php while ($n = mysqli_fetch_assoc($notifs_q)):
                                $icon_class = (strpos($n['title'], 'Ready') !== false) ? 'bi-check-circle text-success' : 'bi-info-circle text-primary';
                                ?>
                                <li>
                                    <a class="notification-item <?= ($n['is_read'] == 0) ? 'unread' : '' ?>"
                                        href="read_notif.php?id=<?= $n['notif_id'] ?>">
                                        <div class="me-3"><i class="bi <?= $icon_class ?> fs-4"></i></div>
                                        <div>
                                            <h4 class="fs-6 fw-bold mb-0 text-dark"><?= $n['title'] ?></h4>
                                            <p class="mb-0 small text-muted text-truncate" style="max-width: 200px;">
                                                <?= $n['message'] ?>
                                            </p>
                                            <p class="smallest text-secondary mt-1" style="font-size: 10px;">
                                                <?= date("M d, h:i A", strtotime($n['created_at'])) ?>
                                            </p>
                                        </div>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="p-4 text-center text-muted small">No recent alerts</li>
                        <?php endif; ?>
                    </div>

                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li class="text-center py-2"><a href="all_notifications" class="small fw-bold text-maroon">View All
                            Notifications</a></li>
                </ul>
            </li>

            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <div class="bg-white rounded-circle p-1 d-flex align-items-center justify-content-center shadow-sm"
                        style="width:32px; height:32px;">
                        <i class="bi bi-person-fill text-dark"></i>
                    </div>
                    <span
                        class="d-none d-md-block dropdown-toggle ps-2 text-white fw-bold small"><?= $fullname ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile shadow">
                    <li class="dropdown-header text-start">
                        <h6 class="mb-0 text-dark"><?= $fullname ?></h6>
                        <span class="text-muted smallest text-uppercase"
                            style="font-size: 10px;"><?= $account_role ?></span>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item d-flex align-items-center" href="profile"><i
                                class="bi bi-person me-2"></i> My Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item d-flex align-items-center text-danger fw-bold" id="signOutLinkNav"
                            href="#"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
                </ul>
            </li>

        </ul>
    </nav>
</header>

<script>
    document.getElementById('signOutLinkNav').addEventListener('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Sign Out?',
            text: "Are you sure you want to end your session?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#800000',
            confirmButtonText: 'Yes, Sign Out'
        }).then((res) => { if (res.isConfirmed) window.location.href = '../logout'; });
    });
</script>