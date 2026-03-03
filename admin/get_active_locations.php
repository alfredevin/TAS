<?php
include './../config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Query 1: Kunin ang mga Active Travelers mula sa Travel Authority (TA)
$query_ta = "SELECT 
                t.ta_id as id, 
                t.memo_no as doc_no, 
                t.destination, 
                t.current_lat, 
                t.current_lng, 
                t.location_updated_at, 
                CONCAT(e.first_name, ' ', e.last_name) as name,
                'Travel Authority' as travel_type
            FROM ta_tbl t 
            JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id
            JOIN employee_tbl e ON tp.employee_id = e.employee_id
            WHERE t.is_tracking_active = 1 
            AND t.current_lat IS NOT NULL 
            AND t.current_lng IS NOT NULL";

// Query 2: Kunin ang mga Active Travelers mula sa Pass Slip (PS)
$query_ps = "SELECT 
                ps.ps_id as id, 
                CONCAT('PS-', ps.ps_id) as doc_no, 
                ps.destination, 
                ps.current_lat, 
                ps.current_lng, 
                ps.location_updated_at,
                CONCAT(e.first_name, ' ', e.last_name) as name,
                'Pass Slip' as travel_type
            FROM pass_slip_tbl ps 
            JOIN employee_tbl e ON ps.employee_id = e.employee_id
            WHERE ps.is_tracking_active = 1 
            AND ps.current_lat IS NOT NULL 
            AND ps.current_lng IS NOT NULL";

// Pagsamahin ang dalawang query gamit ang UNION ALL
$final_query = "$query_ta UNION ALL $query_ps";
$result = mysqli_query($conn, $final_query);

$data = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data);
?>