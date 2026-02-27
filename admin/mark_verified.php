<?php
session_start();
include '../config.php';

if (isset($_POST['ta_id'])) {
    $ta_id = mysqli_real_escape_string($conn, $_POST['ta_id']);

    // Status 4: Ibig sabihin ay Verified na ni Admin at tapos na ang usapan
    $query = "UPDATE ta_tbl SET status = 4 WHERE ta_id = '$ta_id'";

    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>