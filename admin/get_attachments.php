<?php
include '../config.php';
$ta_id = $_GET['ta_id'];
$query = mysqli_query($conn, "SELECT file_path FROM travel_attachments_tbl WHERE ta_id = '$ta_id'");
$files = [];
while ($row = mysqli_fetch_assoc($query)) {
    $files[] = $row;
}
echo json_encode($files);
?>