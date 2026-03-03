<?php
session_start();
include './../config.php';

if (isset($_POST['btn_resubmit'])) {
    $ta_id = mysqli_real_escape_string($conn, $_POST['ta_id']);
    $dest = mysqli_real_escape_string($conn, $_POST['destination']);
    $task = mysqli_real_escape_string($conn, $_POST['task']);
    $t_date = mysqli_real_escape_string($conn, $_POST['travel_date']);
    $r_date = mysqli_real_escape_string($conn, $_POST['return_date']);
    $my_id = $_SESSION['employee_id'];

    // SQL: I-reset ang status sa 0, NULL lahat ng timestamps at decline_reason
    $sql = "UPDATE ta_tbl SET 
                destination = '$dest', 
                task = '$task', 
                travel_date = '$t_date', 
                return_date = '$r_date', 
                status = 0, 
                decline_reason = NULL, 
                head_confirmed_at = NULL, 
                admin_approved_at = NULL,
                submitted_at = NOW() 
            WHERE ta_id = '$ta_id'";

    if (mysqli_query($conn, $sql)) {
        // Notification Logic: I-notify ang Department Head ulit
        $get_head = mysqli_query($conn, "SELECT e2.employee_id 
                                       FROM employee_tbl e1 
                                       JOIN employee_tbl e2 ON e1.department_id = e2.department_id 
                                       WHERE e1.employee_id = '$my_id' AND e2.position_name LIKE '%Head%' LIMIT 1");
        $head = mysqli_fetch_assoc($get_head);
        $head_id = $head['employee_id'] ?? 0;

        $msg = "A previously declined travel request has been updated and resubmitted for your review.";
        mysqli_query($conn, "INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, link) 
                            VALUES ('$head_id', '$my_id', 'TA Resubmitted', '$msg', 'for_confirmation.php')");

        echo "<script>sessionStorage.setItem('swal_type', 'success'); sessionStorage.setItem('swal_msg', 'Resubmitted successfully!'); window.location.href='my_travels.php';</script>";
    } else {
        echo "<script>alert('Error updating record.'); window.location.href='my_travels.php';</script>";
    }
}