<?php
include './../config.php';
header('Content-Type: application/json');

// UNION query to get active tracking from BOTH Travel Authorities and Pass Slips
$query = "
    SELECT 
        t.ta_id as id, 
        'Travel Authority' as travel_type, 
        CONCAT(e.first_name, ' ', e.last_name) as name, 
        t.destination, 
        t.current_lat, 
        t.current_lng, 
        t.location_updated_at 
    FROM ta_tbl t
    JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id
    JOIN employee_tbl e ON tp.employee_id = e.employee_id
    WHERE t.is_tracking_active = 1 AND t.current_lat IS NOT NULL

    UNION ALL

    SELECT 
        p.ps_id as id, 
        'Pass Slip' as travel_type, 
        CONCAT(e.first_name, ' ', e.last_name) as name, 
        p.destination, 
        p.current_lat, 
        p.current_lng, 
        p.location_updated_at 
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