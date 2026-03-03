<?php
// FILE: admin/get_active_locations.php
include './../config.php';

// Kukunin natin ang mga travelers na nag-click ng "Start Travel" at may lat/lng values
$query = "SELECT 
            t.ta_id, 
            t.memo_no, 
            t.destination, 
            t.current_lat, 
            t.current_lng, 
            CONCAT(e.first_name, ' ', e.last_name) as name
          FROM ta_tbl t 
          JOIN ta_participants_tbl tp ON t.ta_id = tp.ta_id
          JOIN employee_tbl e ON tp.employee_id = e.employee_id
          WHERE t.is_tracking_active = 1 
          AND t.current_lat IS NOT NULL 
          AND t.current_lng IS NOT NULL";

$result = mysqli_query($conn, $query);
$data = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

// Ibabalik natin ito bilang JSON format para madaling basahin ng JavaScript/Leaflet
header('Content-Type: application/json');
echo json_encode($data);
?>