<?php
session_start();
include './../config.php';

if (isset($_POST['submit_pass_slip'])) {
    $emp_id = $_SESSION['employee_id'];
    $ps_date = mysqli_real_escape_string($conn, $_POST['ps_date']);
    $issued_for = mysqli_real_escape_string($conn, $_POST['issued_for']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $purpose_type = mysqli_real_escape_string($conn, $_POST['purpose_type']);
    $specific_purpose = mysqli_real_escape_string($conn, $_POST['specific_purpose']);

    // Mapapansin mong inalis na natin ang time_departure at time_return sa query
    $query = "INSERT INTO pass_slip_tbl 
             (employee_id, ps_date, issued_for, destination, purpose_type, specific_purpose, status) 
             VALUES ('$emp_id', '$ps_date', '$issued_for', '$destination', '$purpose_type', '$specific_purpose', 0)";

    if (mysqli_query($conn, $query)) {
        echo "<script>
            sessionStorage.setItem('swal_type', 'success');
            sessionStorage.setItem('swal_msg', 'Pass Slip submitted to Admin!');
            window.location.href='pass_slip.php';
        </script>";
    }
}
?>