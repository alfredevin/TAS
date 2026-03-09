<?php
include './../config.php';
header('Content-Type: application/json');

$query = "
    -- ==========================================
    -- 1. KUNIN ANG DATA NG TRAVEL AUTHORITY (TA)
    -- ==========================================
    SELECT 
        t.ta_id as id, 
        'TA' as travel_type, 
        CONCAT(e.first_name, ' ', e.last_name) as name, 
        e.photo, 
        t.destination, 
        t.current_lat, 
        t.current_lng, 
        t.tracking_step,
        (SELECT CONCAT(lat, ',', lng, '|', DATE_FORMAT(logged_at, '%h:%i %p')) FROM travel_milestones_tbl WHERE ta_id = t.ta_id AND step_number = 1 ORDER BY logged_at DESC LIMIT 1) as step1_data,
        (SELECT CONCAT(lat, ',', lng, '|', DATE_FORMAT(logged_at, '%h:%i %p')) FROM travel_milestones_tbl WHERE ta_id = t.ta_id AND step_number = 2 ORDER BY logged_at DESC LIMIT 1) as step2_data,
        (SELECT CONCAT(lat, ',', lng, '|', DATE_FORMAT(logged_at, '%h:%i %p')) FROM travel_milestones_tbl WHERE ta_id = t.ta_id AND step_number = 3 ORDER BY logged_at DESC LIMIT 1) as step3_data,
        (SELECT CONCAT(lat, ',', lng, '|', DATE_FORMAT(logged_at, '%h:%i %p')) FROM travel_milestones_tbl WHERE ta_id = t.ta_id AND step_number = 4 ORDER BY logged_at DESC LIMIT 1) as step4_data
    FROM ta_tbl t
    JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id
    JOIN employee_tbl e ON tp.employee_id = e.employee_id
    WHERE t.is_tracking_active = 1 AND t.current_lat IS NOT NULL

    UNION ALL

    -- ==========================================
    -- 2. KUNIN ANG DATA NG PASS SLIP (PS)
    -- ==========================================
    SELECT 
        p.ps_id as id, 
        'PS' as travel_type, 
        CONCAT(e.first_name, ' ', e.last_name) as name, 
        e.photo, 
        p.destination, 
        p.current_lat, 
        p.current_lng, 
        p.tracking_step,
        (SELECT CONCAT(latitude, ',', longitude, '|', DATE_FORMAT(logged_at, '%h:%i %p')) FROM ps_tracking_logs_tbl WHERE ps_id = p.ps_id AND step_number = 1 ORDER BY logged_at DESC LIMIT 1) as step1_data,
        (SELECT CONCAT(latitude, ',', longitude, '|', DATE_FORMAT(logged_at, '%h:%i %p')) FROM ps_tracking_logs_tbl WHERE ps_id = p.ps_id AND step_number = 2 ORDER BY logged_at DESC LIMIT 1) as step2_data,
        (SELECT CONCAT(latitude, ',', longitude, '|', DATE_FORMAT(logged_at, '%h:%i %p')) FROM ps_tracking_logs_tbl WHERE ps_id = p.ps_id AND step_number = 3 ORDER BY logged_at DESC LIMIT 1) as step3_data,
        (SELECT CONCAT(latitude, ',', longitude, '|', DATE_FORMAT(logged_at, '%h:%i %p')) FROM ps_tracking_logs_tbl WHERE ps_id = p.ps_id AND step_number = 4 ORDER BY logged_at DESC LIMIT 1) as step4_data
    FROM pass_slip_tbl p
    JOIN employee_tbl e ON p.employee_id = e.employee_id
    WHERE p.is_tracking_active = 1 AND p.current_lat IS NOT NULL
";

$result = mysqli_query($conn, $query);
$locations = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $locations[] = $row;
    }
}

echo json_encode($locations);
?>