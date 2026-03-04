<?php
include './../config.php';

if (isset($_POST['ta_id']) && isset($_POST['lat']) && isset($_POST['lng'])) {
    $ta_id = mysqli_real_escape_string($conn, $_POST['ta_id']);
    $lat = mysqli_real_escape_string($conn, $_POST['lat']);
    $lng = mysqli_real_escape_string($conn, $_POST['lng']);

    // Update the TA table with new coordinates and the current timestamp
    $update_query = "UPDATE ta_tbl 
                     SET current_lat = '$lat', 
                         current_lng = '$lng', 
                         location_updated_at = NOW() 
                     WHERE ta_id = '$ta_id'";
                     
    if(mysqli_query($conn, $update_query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
}
?>