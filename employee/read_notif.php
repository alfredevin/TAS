<?php
include './../config.php';
session_start();

if (isset($_GET['id'])) {
    $notif_id = mysqli_real_escape_string($conn, $_GET['id']);

    // 1. Kunin muna ang link kung saan dapat mag-redirect
    $get_notif = mysqli_query($conn, "SELECT link FROM notifications_tbl WHERE notif_id = '$notif_id'");
    $data = mysqli_fetch_assoc($get_notif);
    $target_url = $data['link'] ?? 'index.php';

    // 2. I-update ang status para maging "Read"
    mysqli_query($conn, "UPDATE notifications_tbl SET is_read = 1 WHERE notif_id = '$notif_id'");

    // 3. I-redirect ang user sa tamang page
    header("Location: " . $target_url);
    exit();
} else {
    header("Location: index.php");
    exit();
}
