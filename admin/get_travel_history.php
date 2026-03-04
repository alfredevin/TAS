<?php
include './../config.php';
header('Content-Type: application/json');

if (isset($_GET['ta_id'])) {
    $ta_id = mysqli_real_escape_string($conn, $_GET['ta_id']);

    // Kunin lahat ng milestones para sa specific na Travel Authority na ito
    $query = "SELECT * FROM travel_milestones_tbl WHERE ta_id = '$ta_id' ORDER BY step_number ASC";
    $result = mysqli_query($conn, $query);

    $history = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $history[] = $row;
        }
    }
    echo json_encode(['status' => 'success', 'data' => $history]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
}
?>