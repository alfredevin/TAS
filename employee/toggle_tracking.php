<?php
include './../config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ta_id = mysqli_real_escape_string($conn, $_POST['ta_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    if($status == 0) {
        mysqli_query($conn, "UPDATE ta_tbl SET is_tracking_active = 0, current_lat = NULL, current_lng = NULL WHERE ta_id = '$ta_id'");
    } else {
        mysqli_query($conn, "UPDATE ta_tbl SET is_tracking_active = 1 WHERE ta_id = '$ta_id'");
    }
}
?>