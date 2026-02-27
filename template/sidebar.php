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
    /* Custom Styles para sa Sidebar */
    .sidebar-nav .nav-link.collapsed {
        color: #333333 !important;
        background: #F5F5F5 !important;
    }

    .sidebar-nav .nav-link:not(.collapsed) {
        color: #800000 !important;
        background: #F5F5F5 !important;
        font-weight: bold;
    }

    .sidebar-nav .nav-content a {
        color: #555555 !important;
    }

    .sidebar-nav .nav-content a.active {
        color: #800000 !important;
        font-weight: 600;
    }

    .nav-heading {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        margin: 10px 0 5px 15px;
        color: #800000 !important;
    }
</style>

<aside id="sidebar" class="sidebar" style="background:#F5F5F5;">
    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'index' || $current_page == 'dashboard') ? '' : 'collapsed'; ?>"
                href="index">
                <i class="bi bi-grid"></i><span>Dashboard</span>
            </a>
        </li>

        <hr style="border-top: 1px solid #ccc; margin: 10px 0;">
        <li class="nav-heading">Travel Authority</li>

        <li class="nav-item">
            <a class="nav-link <?php echo in_array($current_page, ['request_ta', 'faculty_travel_stats','employee_ta_details', 'my_travels', 'for_confirmation', 'pending_approval', 'travel_archive', 'monitor_travel']) ? '' : 'collapsed'; ?>"
                data-bs-target="#ta-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-airplane-engines"></i><span>Travel Management</span><i
                    class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="ta-nav"
                class="nav-content collapse <?php echo in_array($current_page, ['request_ta', 'faculty_travel_stats','employee_ta_details', 'my_travels', 'for_confirmation', 'pending_approval', 'travel_archive', 'monitor_travel']) ? 'show' : ''; ?>"
                data-bs-parent="#sidebar-nav">

                <?php if ($is_staff || $is_head): ?>
                    <li><a href="request_ta" class="<?php echo ($current_page == 'request_ta') ? 'active' : ''; ?>"><i
                                class="bi bi-circle"></i><span>New TA Request</span></a></li>
                    <li><a href="my_travels" class="<?php echo ($current_page == 'my_travels') ? 'active' : ''; ?>"><i
                                class="bi bi-circle"></i><span>My Travel Status</span></a></li>
                <?php endif; ?>

                <?php if ($is_head): ?>
                    <li><a href="for_confirmation"
                            class="<?php echo ($current_page == 'for_confirmation') ? 'active' : ''; ?>"><i
                                class="bi bi-circle"></i><span>TA For Confirmation</span></a></li>
                    <!-- <li><a href="confirmed_history"
                            class="<?php echo ($current_page == 'confirmed_history') ? 'active' : ''; ?>"><i
                                class="bi bi-circle"></i><span>Confirmed History</span></a></li> -->
                <?php endif; ?>

                <?php if ($is_admin): ?>
                    <li>
                        <a href="faculty_travel_stats"
                            class="<?php echo ($current_page == 'faculty_travel_stats' || $current_page == 'employee_ta_details') ? 'active' : ''; ?>">
                            <i class="bi bi-circle"></i><span>Faculty Travel Stats</span>
                        </a>
                    </li>

                    <li><a href="pending_approval"
                            class="<?php echo ($current_page == 'pending_approval') ? 'active' : ''; ?>"><i
                                class="bi bi-circle"></i><span>For Final Approval</span></a></li>
                    <li><a href="travel_archive"
                            class="<?php echo ($current_page == 'travel_archive') ? 'active' : ''; ?>"><i
                                class="bi bi-circle"></i><span>Print Approved TA</span></a></li>

                    <li><a href="monitor_travel"
                            class="<?php echo ($current_page == 'monitor_travel') ? 'active' : ''; ?>"><i
                                class="bi bi-circle"></i><span>Live Monitoring</span></a></li>
                <?php endif; ?>

                <?php if ($is_director): ?>
                    <li><a href="travel_archive"
                            class="<?php echo ($current_page == 'travel_archive') ? 'active' : ''; ?>"><i
                                class="bi bi-circle"></i><span>Monitor Travel Logs</span></a></li>
                <?php endif; ?>

            </ul>
        </li>

        <?php if (!$is_director): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo in_array($current_page, ['travel_reports', 'certificate_appearance']) ? '' : 'collapsed'; ?>"
                    data-bs-target="#compliance-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-journal-check"></i><span>Report</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="compliance-nav"
                    class="nav-content collapse <?php echo in_array($current_page, ['travel_reports', 'certificate_appearance']) ? 'show' : ''; ?>"
                    data-bs-parent="#sidebar-nav">
                     <li><a href="certificate_appearance"
                            class="<?php echo ($current_page == 'certificate_appearance') ? 'active' : ''; ?>"><i
                                class="bi bi-circle"></i><span>Cert. of Appearance</span></a></li>
                    <li><a href="travel_reports"
                            class="<?php echo ($current_page == 'travel_reports') ? 'active' : ''; ?>"><i
                                class="bi bi-circle"></i><span>Travel Reports</span></a></li>
                   
                </ul>
            </li>
        <?php endif; ?>

        <?php if ($is_admin): ?>
            <hr style="border-top: 1px solid #ccc; margin: 10px 0;">
            <li class="nav-heading">System Maintenance</li>
            <li class="nav-item">
                <a class="nav-link <?php echo in_array($current_page, ['addEmployee', 'department', 'position']) ? '' : 'collapsed'; ?>"
                    data-bs-target="#settings-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-gear"></i><span>System Setup</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="settings-nav"
                    class="nav-content collapse <?php echo in_array($current_page, ['addEmployee', 'department', 'position']) ? 'show' : ''; ?>"
                    data-bs-parent="#sidebar-nav">
                    <li><a href="addEmployee" class="<?php echo ($current_page == 'addEmployee') ? 'active' : ''; ?>"><i
                                class="bi bi-circle"></i><span>Employees</span></a></li>
                    <li><a href="department" class="<?php echo ($current_page == 'department') ? 'active' : ''; ?>"><i
                                class="bi bi-circle"></i><span>Departments</span></a></li>
                    <li><a href="position" class="<?php echo ($current_page == 'position') ? 'active' : ''; ?>"><i
                                class="bi bi-circle"></i><span>Positions</span></a></li>
                </ul>
            </li>
        <?php endif; ?>

    </ul>
</aside>