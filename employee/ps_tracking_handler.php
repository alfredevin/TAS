<?php
// I-set ang timezone para sakto ang oras
date_default_timezone_set('Asia/Manila');
include './../config.php';
header('Content-Type: application/json');

// Siguraduhing kumpleto ang data na ipinasa
if (isset($_POST['ps_id']) && isset($_POST['action']) && isset($_POST['lat']) && isset($_POST['lng'])) {

    $ps_id = mysqli_real_escape_string($conn, $_POST['ps_id']);
    $action = mysqli_real_escape_string($conn, $_POST['action']);
    $lat = mysqli_real_escape_string($conn, $_POST['lat']);
    $lng = mysqli_real_escape_string($conn, $_POST['lng']);

    $step = 0;
    $action_name = "";
    $time_column_update = "";
    $is_tracking_active = 1; // Default ay naka-ON ang live location

    // Tukuyin kung anong step at anong column ang ia-update base sa action
    if ($action === 'depart') {
        $step = 1;
        $action_name = "Departed Campus";
        $time_column_update = "time_departure = CURRENT_TIME(),";
    } elseif ($action === 'arrive') {
        $step = 2;
        $action_name = "Arrived at Destination";
        $time_column_update = "time_arrived = CURRENT_TIME(),";
    } elseif ($action === 'leave_dest') {
        $step = 3;
        $action_name = "Left Destination";
        $time_column_update = "time_leaving = CURRENT_TIME(),";
    } elseif ($action === 'return') {
        $step = 4;
        $action_name = "Returned to Campus";
        $time_column_update = "time_return = CURRENT_TIME(),";
        $is_tracking_active = 0; // Kapag nakabalik na, patayin na ang live tracking map
    }

    // KUNG ANG REQUEST AY ISANG STEP BUTTON (1, 2, 3, o 4)
    if ($step > 0) {
        
        // 1. I-RECORD SA MILESTONE / LOGS TABLE (Para sa History)
        $insert_log = "INSERT INTO ps_tracking_logs_tbl (ps_id, step_number, action_name, latitude, longitude) 
                       VALUES ('$ps_id', $step, '$action_name', '$lat', '$lng')";
        mysqli_query($conn, $insert_log);

        // 2. I-UPDATE ANG MAIN TABLE (Para sa UI at Admin Map)
        $update_main = "UPDATE pass_slip_tbl 
                        SET $time_column_update
                            tracking_step = $step,
                            is_tracking_active = $is_tracking_active,
                            current_lat = '$lat',
                            current_lng = '$lng',
                            location_updated_at = NOW()
                        WHERE ps_id = '$ps_id'";

        if (mysqli_query($conn, $update_main)) {
            echo json_encode(['status' => 'success', 'message' => $action_name . ' recorded successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Main update error: ' . mysqli_error($conn)]);
        }
    } 
    // KUNG ANG REQUEST AY BACKGROUND LOCATION UPDATE LANG (Walang pinindot)
    elseif ($action === 'location') {
        $update_loc = "UPDATE pass_slip_tbl 
                       SET current_lat = '$lat', current_lng = '$lng', location_updated_at = NOW() 
                       WHERE ps_id = '$ps_id'";
        if (mysqli_query($conn, $update_loc)) {
            echo json_encode(['status' => 'success', 'message' => 'Live location updated.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Location update failed.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action type.']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Incomplete data provided.']);
}
?>