<?php
// I-set ang timezone sa Pilipinas para tama ang oras ng Departure at Return
date_default_timezone_set('Asia/Manila');
include './../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kunin ang Pass Slip ID at ang Action na gustong gawin
    $ps_id = mysqli_real_escape_string($conn, $_POST['ps_id']);
    $action = $_POST['action'];

    if ($action === 'start') {
        // ACTION 1: START TRAVEL & INITIAL LOCATION
        // Kukunin ang lat at lng na ipinasa mula sa JavaScript
        $lat = isset($_POST['lat']) ? mysqli_real_escape_string($conn, $_POST['lat']) : NULL;
        $lng = isset($_POST['lng']) ? mysqli_real_escape_string($conn, $_POST['lng']) : NULL;

        if ($lat && $lng) {
            // Kung may location data, isave pati location
            $query = "UPDATE pass_slip_tbl 
                      SET time_departure = CURRENT_TIME(), 
                          is_tracking_active = 1,
                          current_lat = '$lat',
                          current_lng = '$lng',
                          location_updated_at = NOW()
                      WHERE ps_id = '$ps_id'";
        } else {
            // Fallback kung oras lang
            $query = "UPDATE pass_slip_tbl 
                      SET time_departure = CURRENT_TIME(), 
                          is_tracking_active = 1 
                      WHERE ps_id = '$ps_id'";
        }
        
        if(mysqli_query($conn, $query)) {
            echo "success_start";
        } else {
            http_response_code(500); 
            echo "Error: " . mysqli_error($conn);
        }
    } 
    elseif ($action === 'stop') {
        // ACTION 2: END TRAVEL
        $query = "UPDATE pass_slip_tbl 
                  SET time_return = CURRENT_TIME(), 
                      is_tracking_active = 0, 
                      current_lat = NULL, 
                      current_lng = NULL 
                  WHERE ps_id = '$ps_id'";
        
        if(mysqli_query($conn, $query)) {
            echo "success_stop";
        } else {
            http_response_code(500);
        }
    } 
    elseif ($action === 'location') {
        // ACTION 3: UPDATE REAL-TIME LOCATION (Background updating)
        if(isset($_POST['lat']) && isset($_POST['lng'])) {
            $lat = mysqli_real_escape_string($conn, $_POST['lat']);
            $lng = mysqli_real_escape_string($conn, $_POST['lng']);
            
            $query = "UPDATE pass_slip_tbl 
                      SET current_lat = '$lat', 
                          current_lng = '$lng',
                          location_updated_at = NOW() 
                      WHERE ps_id = '$ps_id'";
            mysqli_query($conn, $query);
            echo "location_updated";
        }
    }
} else {
    http_response_code(403);
    echo "Forbidden";
}
?>