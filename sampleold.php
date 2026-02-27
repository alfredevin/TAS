<?php
session_start();
date_default_timezone_set("Asia/Manila");
include 'config.php';

// ... [PHPMailer and Login Logic remains exactly the same as your provided code] ...
// I am keeping your PHP logic untouched to ensure functionality.
if (isset($_POST['submit'])) {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);
  $lockoutTime = 10 * 60;
  $maxLoginAttempts = 3;
  $currentTime = time();
  $ipAddress = $_SERVER['REMOTE_ADDR'];

  $sql = "SELECT * FROM login_attempts WHERE ip_address = '$ipAddress'";
  $result = mysqli_query($conn, $sql);
  $failedAttemptsCount = mysqli_num_rows($result);

  if ($failedAttemptsCount >= $maxLoginAttempts) {
    $lastAttemptTime = mysqli_fetch_assoc($result)['last_attempt'];
    $remainingLockoutTime = $lastAttemptTime + $lockoutTime - $currentTime;
    if ($remainingLockoutTime > 0) {
      $minutesRemaining = ceil($remainingLockoutTime / 60);
      echo '<script>document.addEventListener("DOMContentLoaded", function () { fuiAlert("TERMINAL LOCKED", "Security breach detected. Try again in ' . $minutesRemaining . ' minutes", "error"); });</script>';
    } else {
      mysqli_query($conn, "DELETE FROM login_attempts WHERE ip_address = '$ipAddress'");
    }
  } else {
    $admin_query = "SELECT * FROM user_tbl WHERE username = '$username' AND useractive = '1'";
    $admin_result = mysqli_query($conn, $admin_query);
    $employee_query = "SELECT * FROM employee_tbl WHERE employee_no = '$username' AND status = '1'";
    $employee_result = mysqli_query($conn, $employee_query);

    if (mysqli_num_rows($admin_result) === 1) {
      $row = mysqli_fetch_assoc($admin_result);
      if (password_verify($password, $row['password'])) {
        mysqli_query($conn, "DELETE FROM login_attempts WHERE ip_address = '$ipAddress'");
        $_SESSION['userid'] = $row['userid'];
        $loginTime = date("Y-m-d H:i:s");
        mysqli_query($conn, "INSERT INTO userlogs_tbl (userid, username, login_time, ip_address) VALUES ('1', '$username', '$loginTime', '$ipAddress')");
        echo '<script>document.addEventListener("DOMContentLoaded", function() { runAccessSequence("ADMINISTRATOR", "' . base64_encode("./admin/") . '"); });</script>';
      } else {
        handleFailedAttempt($conn, $ipAddress, $currentTime);
      }
    } else if (mysqli_num_rows($employee_result) === 1) {
      $row = mysqli_fetch_assoc($employee_result);
      if (password_verify($password, $row['password'])) {
        mysqli_query($conn, "DELETE FROM login_attempts WHERE ip_address = '$ipAddress'");
        $_SESSION['employee_id'] = $row['employee_id'];
        $loginTime = date("Y-m-d H:i:s");
        mysqli_query($conn, "INSERT INTO userlogs_tbl (userid, username, login_time, ip_address) VALUES ('{$row['employee_id']}', '$username', '$loginTime', '$ipAddress')");
        echo '<script>document.addEventListener("DOMContentLoaded", function() { runAccessSequence("PERSONNEL", "' . base64_encode("./employee/") . '"); });</script>';
      } else {
        handleFailedAttempt($conn, $ipAddress, $currentTime);
      }
    } else {
      handleFailedAttempt($conn, $ipAddress, $currentTime);
    }
  }
}
function handleFailedAttempt($conn, $ip, $time)
{
  mysqli_query($conn, "INSERT INTO login_attempts (ip_address, last_attempt) VALUES ('$ip', $time)");
  echo '<script>document.addEventListener("DOMContentLoaded", function() { fuiAlert("ACCESS DENIED", "Authentication signature mismatch.", "warning"); });</script>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>E-TAMS | Cyber Core</title>
  <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Rajdhani:wght@400;600;700&display=swap"
    rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      --fui-maroon: #800000;
      --fui-neon: #ff2d2d;
      --fui-blue: #00d2ff;
      --fui-green: #0f0;
    }

    body {
      background: radial-gradient(circle at center, #1a0000 0%, #020202 100%);
      color: #fff;
      font-family: 'Rajdhani', sans-serif;
      height: 100vh;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* TECH GRID BACKGROUND */
    .grid-bg {
      position: absolute;
      width: 100%;
      height: 100%;
      background-image: linear-gradient(rgba(128, 0, 0, 0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(128, 0, 0, 0.05) 1px, transparent 1px);
      background-size: 40px 40px;
      z-index: -1;
    }

    /* FUI TERMINAL CARD */
    .fui-terminal {
      background: rgba(10, 0, 0, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(128, 0, 0, 0.5);
      border-radius: 4px;
      padding: 50px;
      width: 440px;
      position: relative;
      box-shadow: 0 0 50px rgba(0, 0, 0, 1), inset 0 0 20px rgba(128, 0, 0, 0.2);
      clip-path: polygon(0 15px, 15px 0, 100% 0, 100% calc(100% - 15px), calc(100% - 15px) 100%, 0 100%);
    }

    /* ANIMATED BRACKETS */
    .bracket {
      position: absolute;
      width: 40px;
      height: 40px;
      border: 2px solid var(--fui-neon);
      opacity: 0.8;
      animation: pulse 2s infinite;
    }

    .b-tl {
      top: 10px;
      left: 10px;
      border-right: 0;
      border-bottom: 0;
    }

    .b-tr {
      top: 10px;
      right: 10px;
      border-left: 0;
      border-bottom: 0;
    }

    .b-bl {
      bottom: 10px;
      left: 10px;
      border-right: 0;
      border-top: 0;
    }

    .b-br {
      bottom: 10px;
      right: 10px;
      border-left: 0;
      border-top: 0;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 0.5;
      }

      50% {
        opacity: 1;
        filter: drop-shadow(0 0 5px var(--fui-neon));
      }
    }

    .scanning-line {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 2px;
      background: linear-gradient(to right, transparent, var(--fui-neon), transparent);
      animation: scan 4s linear infinite;
      z-index: 5;
    }

    @keyframes scan {
      from {
        top: 0;
      }

      to {
        top: 100%;
      }
    }

    .fui-input {
      background: rgba(0, 0, 0, 0.7) !important;
      border: 1px solid #400 !important;
      color: var(--fui-blue) !important;
      font-family: 'Share Tech Mono', monospace;
      border-radius: 0 !important;
      font-size: 1.1rem;
    }

    .fui-input:focus {
      border-color: var(--fui-blue) !important;
      box-shadow: 0 0 15px rgba(0, 210, 255, 0.2) !important;
    }

    .btn-fui {
      background: var(--fui-maroon);
      border: 1px solid var(--fui-neon);
      color: #fff;
      font-weight: 800;
      border-radius: 0;
      letter-spacing: 4px;
      transition: 0.3s;
      position: relative;
      overflow: hidden;
    }

    .btn-fui:hover {
      background: var(--fui-neon);
      color: #000;
      box-shadow: 0 0 30px var(--fui-neon);
      transform: scale(1.02);
    }

    /* MATRIX OVERLAY STYLES */
    #fui-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: #000;
      z-index: 9999;
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    #matrix-canvas {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      opacity: 0.3;
    }

    .status-panel {
      position: relative;
      z-index: 10;
      padding: 40px;
      border: 1px solid var(--fui-green);
      background: rgba(0, 0, 0, 0.8);
      text-align: center;
      min-width: 400px;
    }

    .access-text {
      color: var(--fui-green);
      font-size: 3.5rem;
      font-weight: 900;
      letter-spacing: 12px;
      text-shadow: 0 0 20px var(--fui-green);
    }
  </style>
</head>

<body>

  <div class="grid-bg"></div>

  <div id="fui-overlay">
    <canvas id="matrix-canvas"></canvas>
    <div class="status-panel">
      <div class="access-text animate__animated animate__pulse animate__infinite">ACCESS GRANTED</div>
      <div id="status-log" class="mt-4 text-start mx-auto"
        style="font-family: 'Share Tech Mono'; color: var(--fui-blue); font-size: 12px; max-width: 300px;">
        > INITIALIZING BIOMETRIC UPLINK...<br>
        > DECRYPTING TRAVEL PROTOCOLS...<br>
        > ESTABLISHING SECURE GATEWAY...
      </div>
      <div class="mt-4 fs-6 fw-bold" id="role-display" style="color: #fff; letter-spacing: 2px;"></div>
    </div>
  </div>

  <div class="fui-terminal">
    <div class="bracket b-tl"></div>
    <div class="bracket b-tr"></div>
    <div class="bracket b-bl"></div>
    <div class="bracket b-br"></div>
    <div class="scanning-line"></div>

    <div class="text-center mb-5">
      <img src="https://hrmis.marsu.edu.ph/dist/img/marsu_logo.png"
        style="width: 80px; filter: drop-shadow(0 0 10px var(--fui-maroon));" alt="Logo">
      <h2 class="mt-3 fw-bold mb-0" style="letter-spacing: 5px; color: #fff;">CORE_LOGIN</h2>
      <div class="small fw-bold text-uppercase" style="color: var(--fui-neon); letter-spacing: 2px;">E-TAMS SECURE
        ACCESS</div>
    </div>

    <form method="POST" autocomplete="off">
      <div class="mb-4">
        <label class="small fw-bold text-uppercase mb-1" style="color: var(--fui-blue);">> AUTHENTICATION_ID</label>
        <input type="text" name="username" class="form-control fui-input" placeholder="_ENTER_ID" required>
      </div>
      <div class="mb-5">
        <label class="small fw-bold text-uppercase mb-1" style="color: var(--fui-blue);">> SECURITY_KEY</label>
        <input type="password" name="password" class="form-control fui-input" placeholder="_ENTER_PASS" required>
      </div>

      <button type="submit" name="submit" class="btn btn-fui w-100 py-3">
        EXECUTE_AUTH
      </button>
    </form>

    <div class="mt-4 text-center">
      <div class="small opacity-50" style="font-family: 'Share Tech Mono';">
        LOCATION: BOAC_MAIN_SERVER<br>
        ENCRYPTION: AES-256_ACTIVE
      </div>
    </div>
  </div>

  <script>
    // Matrix Rain Logic
    function startMatrix() {
      const canvas = document.getElementById('matrix-canvas');
      const ctx = canvas.getContext('2d');
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
      const chars = "01010101ABCDEFHIJKLMNOPQRSTUVWXYZアァカサタナハマヤャラワ";
      const fontSize = 16;
      const columns = canvas.width / fontSize;
      const drops = Array(Math.floor(columns)).fill(1);

      function draw() {
        ctx.fillStyle = "rgba(0, 0, 0, 0.05)";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = "#0f0";
        ctx.font = fontSize + "px monospace";
        for (let i = 0; i < drops.length; i++) {
          const text = chars.charAt(Math.floor(Math.random() * chars.length));
          ctx.fillText(text, i * fontSize, drops[i] * fontSize);
          if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
          drops[i]++;
        }
      }
      setInterval(draw, 35);
    }

    // Access Sequence Animation
    function runAccessSequence(role, url) {
      document.getElementById('fui-overlay').style.display = 'flex';
      document.getElementById('role-display').innerText = `WELCOME, ${role}`;
      startMatrix();

      // Streaming logs effect
      const logs = ["> SYNCING DATABASE...", "> LOADING UI MODULES...", "> ACCESS GRANTED."];
      let i = 0;
      const interval = setInterval(() => {
        if (i < logs.length) {
          document.getElementById('status-log').innerHTML += `<br>${logs[i]}`;
          i++;
        } else { clearInterval(interval); }
      }, 600);

      setTimeout(() => { window.location.href = atob(url); }, 3000);
    }

    function fuiAlert(title, text, icon) {
      Swal.fire({
        background: '#0a0a0a', color: '#fff',
        title: `<span style="font-family: Rajdhani; color: var(--fui-neon); letter-spacing: 2px;">[ ${title} ]</span>`,
        html: `<span style="font-family: 'Share Tech Mono'; font-size: 14px;">${text}</span>`,
        icon: icon, confirmButtonColor: '#800000',
        customClass: { popup: 'border border-danger rounded-0' }
      });
    }
  </script>
</body>

</html>