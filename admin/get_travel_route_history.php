<?php
include './../config.php';
header('Content-Type: application/json');

$query = "
    -- 1. COMPLETED TRAVEL AUTHORITY (TA)
    SELECT 
        t.ta_id as id, 
        'TA' as travel_type, 
        CONCAT(e.first_name, ' ', e.last_name) as name, 
        e.photo, 
        d.department_name,
        t.destination, 
        t.travel_date as date_of_travel,
        (SELECT CONCAT(lat, ',', lng) FROM travel_milestones_tbl WHERE ta_id = t.ta_id AND step_number = 1 LIMIT 1) as loc1,
        (SELECT CONCAT(lat, ',', lng) FROM travel_milestones_tbl WHERE ta_id = t.ta_id AND step_number = 2 LIMIT 1) as loc2,
        (SELECT CONCAT(lat, ',', lng) FROM travel_milestones_tbl WHERE ta_id = t.ta_id AND step_number = 3 LIMIT 1) as loc3,
        (SELECT CONCAT(lat, ',', lng) FROM travel_milestones_tbl WHERE ta_id = t.ta_id AND step_number = 4 LIMIT 1) as loc4
    FROM ta_tbl t
    JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id
    JOIN employee_tbl e ON tp.employee_id = e.employee_id
    LEFT JOIN department_tbl d ON e.department_id = d.department_id
    WHERE t.tracking_step = 4

    UNION ALL

    -- 2. COMPLETED PASS SLIP (PS)
    SELECT 
        p.ps_id as id, 
        'PS' as travel_type, 
        CONCAT(e.first_name, ' ', e.last_name) as name, 
        e.photo, 
        d.department_name,
        p.destination, 
        p.ps_date as date_of_travel,
        (SELECT CONCAT(latitude, ',', longitude) FROM ps_tracking_logs_tbl WHERE ps_id = p.ps_id AND step_number = 1 LIMIT 1) as loc1,
        (SELECT CONCAT(latitude, ',', longitude) FROM ps_tracking_logs_tbl WHERE ps_id = p.ps_id AND step_number = 2 LIMIT 1) as loc2,
        (SELECT CONCAT(latitude, ',', longitude) FROM ps_tracking_logs_tbl WHERE ps_id = p.ps_id AND step_number = 3 LIMIT 1) as loc3,
        (SELECT CONCAT(latitude, ',', longitude) FROM ps_tracking_logs_tbl WHERE ps_id = p.ps_id AND step_number = 4 LIMIT 1) as loc4
    FROM pass_slip_tbl p
    JOIN employee_tbl e ON p.employee_id = e.employee_id
    LEFT JOIN department_tbl d ON e.department_id = d.department_id
    WHERE p.tracking_step = 4

    ORDER BY date_of_travel DESC
";

$result = mysqli_query($conn, $query);
$history = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // If department is null, set a fallback
        $row['department_name'] = $row['department_name'] ?: 'Unassigned';
        $history[] = $row;
    }
}

echo json_encode($history);
?>