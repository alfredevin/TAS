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

    // 1. UPDATE QUERY: Ibalik sa Status 0, bura ang decline_reason at timestamps
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
        // 2. Notify Department Head ulit
        // Hanapin ang head ng department ng user na ito
        $get_head = mysqli_query($conn, "SELECT e2.employee_id 
                                       FROM employee_tbl e1 
                                       JOIN employee_tbl e2 ON e1.department_id = e2.department_id 
                                       WHERE e1.employee_id = '$my_id' AND e2.position_name LIKE '%Head%' LIMIT 1");
        $head = mysqli_fetch_assoc($get_head);
        $head_id = $head['employee_id'] ?? 0;

        $msg = "An employee has resubmitted a declined travel request for your review.";
        mysqli_query($conn, "INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, link) 
                            VALUES ('$head_id', '$my_id', 'TA Resubmitted', '$msg', 'for_confirmation.php')");

        $_SESSION['swal_type'] = 'success';
        $_SESSION['swal_msg'] = 'Request resubmitted successfully!';
    } else {
        $_SESSION['swal_type'] = 'error';
        $_SESSION['swal_msg'] = 'Something went wrong. Please try again.';
    }

    header("Location: my_travels.php");
    exit();
}