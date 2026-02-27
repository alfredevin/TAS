<?php
session_start();
include '../config.php';

if (isset($_POST['submit_report'])) {
    $ta_id = mysqli_real_escape_string($conn, $_POST['ta_id']);
    $employee_id = $_SESSION['employee_id'];
    $report_text = mysqli_real_escape_string($conn, $_POST['accomplishment_report']);

    // 1. I-save ang main report sa database
    $query_report = "INSERT INTO travel_completions_tbl (ta_id, employee_id, accomplishment_text) 
                     VALUES ('$ta_id', '$employee_id', '$report_text')";

    if (mysqli_query($conn, $query_report)) {

        // 2. Ihanda ang Folder para sa attachments
        $upload_dir = "../uploads/completions/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // 3. Loop para sa MULTIPLE FILES (Certificates)
        if (!empty($_FILES['cert_files']['name'][0])) {
            foreach ($_FILES['cert_files']['name'] as $key => $val) {
                $file_name = $_FILES['cert_files']['name'][$key];
                $tmp_name = $_FILES['cert_files']['tmp_name'][$key];
                $error = $_FILES['cert_files']['error'][$key];

                if ($error === 0) {
                    // Unique naming para hindi mag-overwrite
                    $new_file_name = "TA" . $ta_id . "_" . time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
                    $target_path = $upload_dir . $new_file_name;

                    if (move_uploaded_file($tmp_name, $target_path)) {
                        // I-save ang bawat file path sa travel_attachments_tbl
                        mysqli_query($conn, "INSERT INTO travel_attachments_tbl (ta_id, file_path) 
                                            VALUES ('$ta_id', '$new_file_name')");
                    }
                }
            }
        }

        // 4. Update status ng TA sa 3 (Completed)
        mysqli_query($conn, "UPDATE ta_tbl SET status = 3 WHERE ta_id = '$ta_id'");

        // Success Response gamit ang SweetAlert logic mo
        echo "<script>
            alert('Travel Report and Certificates submitted successfully!');
            window.location.href = 'certificate_appearance';
        </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>