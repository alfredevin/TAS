<?php
include './../config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ta_id = mysqli_real_escape_string($conn, $_POST['ta_id']);
    $lat = mysqli_real_escape_string($conn, $_POST['lat']);
    $lng = mysqli_real_escape_string($conn, $_POST['lng']);

    mysqli_query($conn, "UPDATE ta_tbl SET current_lat = '$lat', current_lng = '$lng' WHERE ta_id = '$ta_id'");
}
?>