<?php
include './../config.php';

// Set header to return JSON since your frontend fetch() expects a clean response
header('Content-Type: application/json');

if (isset($_POST['ta_id']) && isset($_POST['status'])) {
    // Sanitize inputs to prevent SQL injection
    $ta_id = mysqli_real_escape_string($conn, $_POST['ta_id']);
    $status = (int)$_POST['status']; // Cast to integer (1 or 0)

    // Update the tracking flag in the Travel Authority table
    $query = "UPDATE ta_tbl SET is_tracking_active = $status WHERE ta_id = '$ta_id'";

    if (mysqli_query($conn, $query)) {
        // If they are stopping the travel, you might optionally want to clear their last location 
        // so it doesn't accidentally show up later. Uncomment the lines below if desired:
        /*
        if ($status === 0) {
            mysqli_query($conn, "UPDATE ta_tbl SET current_lat = NULL, current_lng = NULL, location_updated_at = NULL WHERE ta_id = '$ta_id'");
        }
        */

        // Return HTTP 200 OK so the frontend knows it succeeded
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Tracking status updated successfully.']);
    } else {
        // Return HTTP 500 Internal Server Error if the query fails
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
} else {
    // Return HTTP 400 Bad Request if parameters are missing
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing ta_id or status parameters.']);
}
?>