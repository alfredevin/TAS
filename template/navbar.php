<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config.php';

$fullname = '';
$account_role = '';
$user_position = '';
$is_admin_user = false;

// Kunin ang current page para sa active state ng Bottom Nav
$current_page_nav = basename($_SERVER['PHP_SELF'], '.php');

// 1. Check kung ADMINISTRATOR
if (isset($_SESSION['userid'])) {
    $userid = mysqli_real_escape_string($conn, $_SESSION['userid']);
    $navbar = "SELECT * FROM user_tbl WHERE userid = '$userid'";
    $result_navbar = mysqli_query($conn, $navbar);

    if ($result_navbar && mysqli_num_rows($result_navbar) > 0) {
        $row = mysqli_fetch_assoc($result_navbar);
        $fullname = isset($row['fullname']) ? $row['fullname'] : $row['first_name'] . ' ' . $row['last_name'];
        $account_role = 'ADMINISTRATOR';
        $is_admin_user = true;
    }
}
// 2. LAHAT NG EMPLOYEE
elseif (isset($_SESSION['employee_id'])) {
    $employee_id = mysqli_real_escape_string($conn, $_SESSION['employee_id']);
    $navbar = "SELECT * FROM employee_tbl WHERE employee_id = '$employee_id'";
    $result_navbar = mysqli_query($conn, $navbar);

    if ($result_navbar && mysqli_num_rows($result_navbar) > 0) {
        $row = mysqli_fetch_assoc($result_navbar);
        $fullname = $row['first_name'] . ' ' . $row['last_name'];
        $dept_id = $row['department_id'];
        $user_position = strtoupper($row['position_name']);
        $_SESSION['position_name'] = $user_position;

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
    header("Location: ../index.php");
    exit();
}

$current_session_id = isset($_SESSION['userid']) ? $_SESSION['userid'] : ($_SESSION['employee_id'] ?? 0);

// Smart Notification Logic
$count_q = mysqli_query($conn, "SELECT COUNT(*) as unread FROM notifications_tbl WHERE recipient_id = '$current_session_id' AND is_read = 0");
$unread_count = mysqli_fetch_assoc($count_q)['unread'] ?? 0;

$notifs_q = mysqli_query($conn, "SELECT * FROM notifications_tbl 
                                 WHERE recipient_id = '$current_session_id' 
                                 ORDER BY is_read ASC, created_at DESC 
                                 LIMIT 5");
?>

<style>
    :root {
        --marsu-maroon: #800000;
        --marsu-gradient: linear-gradient(135deg, #800000 0%, #b91c1c 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
    }

    /* =========================================
       MODERN TOP HEADER
       ========================================= */
    .header {
        background: var(--marsu-gradient);
        height: 70px;
        border-bottom: none;
        box-shadow: 0 4px 15px rgba(128, 0, 36, 0.2);
        transition: all 0.3s ease;
    }

    .logo span {
        font-size: 20px;
        font-weight: 800;
        color: white;
        font-family: "Poppins", sans-serif;
        letter-spacing: 1px;
    }

    .system-subtext {
        font-size: 10px;
        color: rgba(255, 255, 255, 0.85);
        display: block;
        text-transform: uppercase;
        margin-top: -3px;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    /* Desktop Sidebar Toggle Force White */
    .header .toggle-sidebar-btn {
        color: #ffffff !important;
        cursor: pointer;
        font-size: 28px;
        transition: all 0.3s ease;
        padding: 5px;
    }

    .header .toggle-sidebar-btn:active {
        transform: scale(0.9);
    }

    /* Notifications */
    .nav-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        transition: background 0.3s;
        position: relative;
    }

    .nav-icon i {
        color: #ffffff !important;
        font-size: 20px;
        transition: 0.3s;
    }

    .badge-number {
        position: absolute;
        top: 2px;
        right: 2px;
        font-size: 10px;
        padding: 4px 6px;
        border: 2px solid var(--marsu-maroon);
        animation: pulseBadge 2s infinite;
    }

    @keyframes pulseBadge {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
        }

        70% {
            transform: scale(1.2);
            box-shadow: 0 0 0 6px rgba(255, 255, 255, 0);
        }

        100% {
            transform: scale(1);
        }
    }

    .notifications {
        width: 340px;
        border-radius: 20px;
        border: none;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    .notification-item {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        transition: 0.3s;
        border-bottom: 1px solid #f1f5f9;
    }

    .notification-item:hover {
        background-color: #f8fafc;
        padding-left: 25px;
    }

    .notification-item.unread {
        background-color: #fff1f2;
        border-left: 4px solid var(--marsu-maroon);
    }

    .dropdown-header {
        background: #f8fafc;
        font-size: 14px;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Profile Circle */
    .profile-trigger {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #fff;
        color: var(--marsu-maroon);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s;
        border: 2px solid transparent;
    }

    .profile {
        border-radius: 20px;
        border: none;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        min-width: 260px;
    }

    .profile .dropdown-header h6 {
        font-weight: 800;
        color: #1e293b;
        font-size: 16px;
    }

    .profile .dropdown-item {
        padding: 12px 20px;
        font-weight: 600;
        color: #475569;
        transition: 0.2s;
        border-radius: 8px;
        margin: 0 8px;
        width: auto;
    }

    .profile .dropdown-item:hover {
        background-color: #f1f5f9;
        color: var(--marsu-maroon);
        transform: translateX(5px);
    }

    /* =========================================
       FLOATING PILL BOTTOM NAV (MOBILE ONLY)
       ========================================= */
    .bottom-nav-container {
        position: fixed;
        bottom: 25px;
        left: 20px;
        right: 20px;
        z-index: 1000;
        pointer-events: none;
    }

    .bottom-nav {
        pointer-events: auto;
        background: var(--glass-bg);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        box-shadow: 0 10px 40px rgba(128, 0, 36, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 50px;
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 10px 15px;
    }

    .bottom-nav-item {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 700;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        width: 20%;
        padding-top: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
    }

    .bottom-nav-item i {
        font-size: 22px;
        margin-bottom: 2px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .bottom-nav-item:active {
        transform: scale(0.9);
    }

    .bottom-nav-item.active {
        color: var(--marsu-maroon);
    }

    .bottom-nav-item.active i {
        transform: translateY(-18px);
        color: #fff !important;
        background: var(--marsu-gradient);
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        box-shadow: 0 8px 20px rgba(128, 0, 36, 0.4);
        border: 4px solid #f8fafc;
    }

    .bottom-nav-item.active span {
        transform: translateY(-10px);
        opacity: 0;
    }

    /* The Central "Menu" Button Styling */
    .nav-menu-btn i {
        color: var(--marsu-maroon) !important;
        background: #fff1f2;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    /* Mobile Adjustments */
    @media (max-width: 767px) {
        body {
            padding-bottom: 110px !important;
        }

        .header {
            height: 60px;
        }

        .logo img {
            max-height: 35px !important;
        }

        .notifications {
            width: 300px;
            right: -10px !important;
        }

        /* Force hide ang blue hamburger sa top sa mobile */
        .desktop-toggle-btn {
            display: none !important;
        }
    }
</style>

<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between px-3 w-100">

        <div class="d-flex align-items-center">
            <a href="index.php" class="logo d-flex align-items-center text-decoration-none">
                <img src="https://hrmis.marsu.edu.ph/dist/img/marsu_logo.png" alt="MarSU"
                    style="max-height: 40px; border-radius: 50%; background:white; padding:2px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                <div class="ms-2">
                    <span class="d-block">E-TAMS</span>
                    <small class="system-subtext d-none d-sm-block">Travel Management</small>
                </div>
            </a>
            <i class="bi bi-list toggle-sidebar-btn ms-4 desktop-toggle-btn"></i>
        </div>

        <nav class="header-nav">
            <ul class="d-flex align-items-center m-0 p-0" style="list-style: none;">

                <li class="nav-item dropdown pe-2 pe-md-3">
                    <a class="nav-link nav-icon position-relative" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bell-fill"></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="badge bg-danger badge-number rounded-pill pulse-bell"><?= $unread_count ?></span>
                        <?php endif; ?>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications p-0 mt-2">
                        <li class="dropdown-header p-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bolder text-dark fs-6">Alerts & Updates</span>
                            <?php if ($unread_count > 0): ?>
                                <span class="badge rounded-pill bg-danger px-2 py-1"><?= $unread_count ?> New</span>
                            <?php endif; ?>
                        </li>

                        <div style="max-height: 350px; overflow-y: auto;">
                            <?php if (mysqli_num_rows($notifs_q) > 0): ?>
                                <?php while ($n = mysqli_fetch_assoc($notifs_q)):
                                    $icon_class = (strpos($n['title'], 'Ready') !== false || strpos($n['title'], 'Approved') !== false || strpos($n['title'], 'Confirmed') !== false) ? 'bi-check-circle-fill text-success' : ((strpos($n['title'], 'Declined') !== false) ? 'bi-x-circle-fill text-danger' : 'bi-info-circle-fill text-primary');
                                    ?>
                                    <li>
                                        <a class="notification-item text-decoration-none <?= ($n['is_read'] == 0) ? 'unread' : '' ?>"
                                            href="read_notif.php?id=<?= $n['notif_id'] ?>">
                                            <div class="me-3"><i class="bi <?= $icon_class ?> fs-3"></i></div>
                                            <div>
                                                <h4 class="fs-6 fw-bold mb-1 text-dark" style="line-height: 1.2;">
                                                    <?= $n['title'] ?></h4>
                                                <p class="mb-1 small text-muted text-truncate" style="max-width: 220px;">
                                                    <?= $n['message'] ?></p>
                                                <p class="mb-0 text-secondary" style="font-size: 10px; font-weight: 600;">
                                                    <i
                                                        class="bi bi-clock me-1"></i><?= date("M d, h:i A", strtotime($n['created_at'])) ?>
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li class="p-5 text-center text-muted">
                                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                                        style="width: 60px; height: 60px;">
                                        <i class="bi bi-bell-slash fs-3 text-secondary"></i>
                                    </div>
                                    <span class="small fw-bold d-block">You're all caught up!</span>
                                </li>
                            <?php endif; ?>
                        </div>

                        <li class="text-center p-2 bg-light border-top">
                            <a href="all_notifications.php" class="small fw-bolder text-decoration-none"
                                style="color: var(--marsu-maroon);">See all notifications <i
                                    class="bi bi-arrow-right ms-1"></i></a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                        <div class="profile-trigger">
                            <?= strtoupper(substr($fullname, 0, 1)) ?>
                        </div>
                        <span
                            class="d-none d-md-block dropdown-toggle ps-2 text-white fw-bold small"><?= explode(' ', $fullname)[0] ?></span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile p-2 mt-2">
                        <li class="dropdown-header text-center p-3 mb-2 rounded" style="background: #f8fafc;">
                            <div class="profile-trigger mx-auto mb-2"
                                style="width: 50px; height: 50px; font-size: 24px;">
                                <?= strtoupper(substr($fullname, 0, 1)) ?>
                            </div>
                            <h6 class="mb-1"><?= $fullname ?></h6>
                            <span class="badge bg-secondary rounded-pill fw-normal"
                                style="font-size: 10px; letter-spacing: 0.5px;"><?= $account_role ?></span>
                        </li>
                        <li><a class="dropdown-item d-flex align-items-center mt-1" href="profile.php"><i
                                    class="bi bi-person-gear me-2 fs-5 text-primary"></i> My Profile Settings</a></li>
                        <li>
                            <hr class="dropdown-divider my-2">
                        </li>
                        <li><a class="dropdown-item d-flex align-items-center text-danger fw-bold mb-1"
                                id="signOutLinkNav" href="#"><i class="bi bi-box-arrow-right me-2 fs-5"></i> Secure Sign
                                Out</a></li>
                    </ul>
                </li>

            </ul>
        </nav>
    </div>
</header>

<?php if (!$is_admin_user): ?>
    <div class="bottom-nav-container d-md-none">
        <nav class="bottom-nav">
            <a href="index.php" class="bottom-nav-item <?= ($current_page_nav == 'index') ? 'active' : '' ?>">
                <i class="bi <?= ($current_page_nav == 'index') ? 'bi-house-fill' : 'bi-house' ?>"></i>
                <span>Home</span>
            </a>
            <a href="request_ta.php" class="bottom-nav-item <?= ($current_page_nav == 'request_ta') ? 'active' : '' ?>">
                <i class="bi <?= ($current_page_nav == 'request_ta') ? 'bi-briefcase-fill' : 'bi-briefcase' ?>"></i>
                <span>File TA</span>
            </a>

            <div class="bottom-nav-item nav-menu-btn" id="mobileMenuTrigger">
                <i class="bi bi-grid-fill"></i>
                <span>Menu</span>
            </div>

            <a href="pass_slip.php" class="bottom-nav-item <?= ($current_page_nav == 'pass_slip') ? 'active' : '' ?>">
                <i
                    class="bi <?= ($current_page_nav == 'pass_slip') ? 'bi-ticket-detailed-fill' : 'bi-ticket-detailed' ?>"></i>
                <span>Pass Slip</span>
            </a>
            <a href="my_travels.php" class="bottom-nav-item <?= ($current_page_nav == 'my_travels') ? 'active' : '' ?>">
                <i class="bi <?= ($current_page_nav == 'my_travels') ? 'bi-compass-fill' : 'bi-compass' ?>"></i>
                <span>Tracker</span>
            </a>
        </nav>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // 1. I-FORCE BUKAS ANG SIDEBAR KAPAG PININDOT YUNG GRID MENU SA BABA
        const mobileMenuTrigger = document.getElementById('mobileMenuTrigger');
        if (mobileMenuTrigger) {
            mobileMenuTrigger.addEventListener('click', function () {
                // Toggle the class on the body tag which NiceAdmin uses to show/hide sidebar
                document.body.classList.toggle('toggle-sidebar');
            });
        }

        // 2. SweetAlert Logout
        const signOutBtn = document.getElementById('signOutLinkNav');
        if (signOutBtn) {
            signOutBtn.addEventListener('click', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Sign Out?',
                    text: "You will be securely logged out of your account.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#800000',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Yes, Sign Out',
                    borderRadius: '24px'
                }).then((res) => {
                    if (res.isConfirmed) window.location.href = '../logout.php';
                });
            });
        }
    });
</script>