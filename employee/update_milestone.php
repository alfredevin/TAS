<?php
include './../config.php';
header('Content-Type: application/json');

if (isset($_POST['ta_id']) && isset($_POST['step']) && isset($_POST['action_name']) && isset($_POST['lat']) && isset($_POST['lng'])) {

    $ta_id = mysqli_real_escape_string($conn, $_POST['ta_id']);
    $step = (int) $_POST['step'];
    $action_name = mysqli_real_escape_string($conn, $_POST['action_name']);
    $lat = mysqli_real_escape_string($conn, $_POST['lat']);
    $lng = mysqli_real_escape_string($conn, $_POST['lng']);

    // Status 4 (Nakabalik Na) turns off the map tracking flag. 1, 2, 3 turns it on.
    $is_tracking_active = ($step == 4) ? 0 : 1;

    // 1. I-save ang exact time and location sa History Table
    $insert_log = "INSERT INTO travel_milestones_tbl (ta_id, step_number, action_name, lat, lng) 
                   VALUES ('$ta_id', $step, '$action_name', '$lat', '$lng')";
    mysqli_query($conn, $insert_log);

    // 2. I-update ang Main Table para makita sa Admin Map
    $update_main = "UPDATE ta_tbl 
                    SET tracking_step = $step, 
                        is_tracking_active = $is_tracking_active, 
                        current_lat = '$lat', 
                        current_lng = '$lng', 
                        location_updated_at = NOW() 
                    WHERE ta_id = '$ta_id'";

    if (mysqli_query($conn, $update_main)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Incomplete data.']);
}
?>