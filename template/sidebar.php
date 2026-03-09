<?php
// Kunin ang pangalan ng kasalukuyang page para sa active links
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Kunin ang position at role ng user mula sa session
$is_admin = isset($_SESSION['userid']);
$user_pos = strtoupper($_SESSION['position_name'] ?? '');

// Check roles based sa keywords sa position_name
$is_director = (strpos($user_pos, 'CAMPUS DIRECTOR') !== false);
$is_head = (strpos($user_pos, 'DEPARTMENT HEAD') !== false);
$is_staff = (!$is_admin && !$is_director && !$is_head);
?>

<style>
    /* =========================================
       MODERN, INTERACTIVE & AURA SIDEBAR STYLES
       ========================================= */
    .sidebar {
        background: #ffffff;
        box-shadow: 4px 0 25px rgba(0, 0, 0, 0.03);
        border-right: 1px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Main Nav Items */
    .sidebar-nav .nav-item {
        margin-bottom: 5px;
    }

    .sidebar-nav .nav-link {
        display: flex;
        align-items: center;
        font-size: 15px;
        font-weight: 600;
        color: #475569;
        transition: all 0.3s ease;
        background: transparent;
        padding: 12px 15px;
        border-radius: 12px;
        margin: 0 15px;
    }

    .sidebar-nav .nav-link i {
        font-size: 18px;
        margin-right: 12px;
        color: #94a3b8;
        transition: all 0.3s ease;
    }

    /* Hover Effects (Makes it "Alive") */
    .sidebar-nav .nav-link:hover {
        color: #800000;
        background: #f8fafc;
        transform: translateX(4px);
        /* Slight nudge to the right */
    }

    .sidebar-nav .nav-link:hover i {
        color: #800000;
        transform: scale(1.1);
    }

    /* Active State for Main Menus */
    .sidebar-nav .nav-link:not(.collapsed) {
        color: #800000;
        background: #fff1f2;
        /* Light maroon tint */
        box-shadow: inset 4px 0 0 0 #800000;
        /* Modern left accent line */
        font-weight: 700;
    }

    .sidebar-nav .nav-link:not(.collapsed) i {
        color: #800000;
    }

    /* Sub-menu (Collapse) Styles */
    .sidebar-nav .nav-content {
        padding: 5px 0 5px 35px;
        margin: 0;
        list-style: none;
    }

    .sidebar-nav .nav-content a {
        display: flex;
        align-items: center;
        font-size: 14px;
        font-weight: 500;
        color: #64748b;
        padding: 10px 15px;
        transition: all 0.3s ease;
        border-radius: 8px;
        margin-bottom: 3px;
        margin-right: 15px;
    }

    .sidebar-nav .nav-content a i {
        font-size: 8px;
        margin-right: 12px;
        transition: all 0.3s ease;
    }

    .sidebar-nav .nav-content a:hover {
        color: #800000;
        transform: translateX(3px);
    }

    .sidebar-nav .nav-content a:hover i {
        color: #800000;
        transform: scale(1.2);
    }

    /* Active State for Sub-menus */
    .sidebar-nav .nav-content a.active {
        color: #800000;
        font-weight: 700;
        background: transparent;
    }

    .sidebar-nav .nav-content a.active i {
        color: #800000;
        font-size: 10px;
        /* Creates a target/bullseye effect for the active dot */
        background-color: #800000;
        border-radius: 50%;
        box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.15);
    }

    /* Headings (Section Titles) */
    .nav-heading {
        font-size: 11px;
        text-transform: uppercase;
        color: #94a3b8;
        font-weight: 800;
        margin: 20px 0 10px 20px;
        letter-spacing: 1px;
    }

    /* Subtle Divider */
    .sidebar-divider {
        border-top: 1px dashed #e2e8f0;
        margin: 15px 20px;
    }
</style>

<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item mt-3">
            <a class="nav-link <?php echo ($current_page == 'index' || $current_page == 'dashboard') ? '' : 'collapsed'; ?>"
                href="index.php">
                <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
            </a>
        </li>

        <?php if ($is_admin): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'monitor_travel') ? '' : 'collapsed'; ?>"
                    href="monitor_travel.php">
                    <i class="bi bi-geo-alt-fill"></i><span>Live Monitoring</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'travel_history_map') ? '' : 'collapsed'; ?>"
                    href="travel_history_map.php">
                    <i class="bi bi-map-fill"></i><span>Travel Archives</span>
                </a>
            </li>
        <?php endif; ?>

        <div class="sidebar-divider"></div>
        <li class="nav-heading">Travel Management</li>

        <li class="nav-item">
            <a class="nav-link <?php echo in_array($current_page, ['request_ta', 'pass_slip', 'pass_slip_approval', 'faculty_travel_stats', 'employee_ta_details', 'my_travels', 'for_confirmation', 'pending_approval', 'travel_archive']) ? '' : 'collapsed'; ?>"
                data-bs-target="#ta-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-airplane-engines-fill"></i><span>Operations</span><i
                    class="bi bi-chevron-down ms-auto"></i>
            </a>

            <ul id="ta-nav"
                class="nav-content collapse <?php echo in_array($current_page, ['request_ta', 'pass_slip_approval', 'pass_slip', 'faculty_travel_stats', 'employee_ta_details', 'my_travels', 'for_confirmation', 'pending_approval', 'travel_archive']) ? 'show' : ''; ?>"
                data-bs-parent="#sidebar-nav">

                <?php if ($is_staff || $is_head): ?>
                    <li>
                        <a href="request_ta.php" class="<?php echo ($current_page == 'request_ta') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>New TA Request</span>
                        </a>
                    </li>
                    <li>
                        <a href="pass_slip.php" class="<?php echo ($current_page == 'pass_slip') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>Pass Slip Request</span>
                        </a>
                    </li>
                    <li>
                        <a href="my_travels.php" class="<?php echo ($current_page == 'my_travels') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>My Travel Status</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($is_head): ?>
                    <li>
                        <a href="for_confirmation.php"
                            class="<?php echo ($current_page == 'for_confirmation') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>TA For Confirmation</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($is_admin): ?>
                    <li>
                        <a href="faculty_travel_stats.php"
                            class="<?php echo ($current_page == 'faculty_travel_stats' || $current_page == 'employee_ta_details') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>Faculty Travel Stats</span>
                        </a>
                    </li>
                    <li>
                        <a href="pending_approval.php"
                            class="<?php echo ($current_page == 'pending_approval') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>TA Approval</span>
                        </a>
                    </li>
                    <li>
                        <a href="pass_slip_approval.php"
                            class="<?php echo ($current_page == 'pass_slip_approval') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>Pass Slip Approval</span>
                        </a>
                    </li>
                    <li>
                        <a href="travel_archive.php"
                            class="<?php echo ($current_page == 'travel_archive') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>Print Approved TA</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($is_director): ?>
                    <li>
                        <a href="travel_archive.php"
                            class="<?php echo ($current_page == 'travel_archive') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>Monitor Travel Logs</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </li>

        <?php if (!$is_director): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo in_array($current_page, ['travel_reports', 'certificate_appearance']) ? '' : 'collapsed'; ?>"
                    data-bs-target="#compliance-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-journal-bookmark-fill"></i><span>Reports & Docs</span><i
                        class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="compliance-nav"
                    class="nav-content collapse <?php echo in_array($current_page, ['travel_reports', 'certificate_appearance']) ? 'show' : ''; ?>"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="certificate_appearance.php"
                            class="<?php echo ($current_page == 'certificate_appearance') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>Cert. of Appearance</span>
                        </a>
                    </li>
                    <li>
                        <a href="travel_reports.php"
                            class="<?php echo ($current_page == 'travel_reports') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>Travel Reports</span>
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if ($is_admin): ?>
            <div class="sidebar-divider"></div>
            <li class="nav-heading">System Maintenance</li>

            <li class="nav-item">
                <a class="nav-link <?php echo in_array($current_page, ['addEmployee', 'department', 'position']) ? '' : 'collapsed'; ?>"
                    data-bs-target="#settings-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-gear-fill"></i><span>System Setup</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="settings-nav"
                    class="nav-content collapse <?php echo in_array($current_page, ['addEmployee', 'department', 'position']) ? 'show' : ''; ?>"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="addEmployee.php" class="<?php echo ($current_page == 'addEmployee') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>Employees Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="department.php" class="<?php echo ($current_page == 'department') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>Departments</span>
                        </a>
                    </li>
                    <li>
                        <a href="position.php" class="<?php echo ($current_page == 'position') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>Positions</span>
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>

    </ul>
</aside>