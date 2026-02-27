<?php
session_start();
date_default_timezone_set("Asia/Manila");
include 'config.php';

// PHPMailer Classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require './mailer/src/Exception.php';
require './mailer/src/PHPMailer.php';
require './mailer/src/SMTP.php';
if (isset($_POST['submit'])) {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);

  $lockoutTime = 10 * 60;
  $maxLoginAttempts = 3;
  $currentTime = time();
  $ipAddress = $_SERVER['REMOTE_ADDR'];

  // Check Lockout Status
  $sql = "SELECT * FROM login_attempts WHERE ip_address = '$ipAddress'";
  $result = mysqli_query($conn, $sql);
  $failedAttemptsCount = mysqli_num_rows($result);

  if ($failedAttemptsCount >= $maxLoginAttempts) {
    $lastAttemptTime = mysqli_fetch_assoc($result)['last_attempt'];
    $remainingLockoutTime = $lastAttemptTime + $lockoutTime - $currentTime;

    if ($remainingLockoutTime > 0) {
      $minutesRemaining = ceil($remainingLockoutTime / 60);
      echo '<script>
                document.addEventListener("DOMContentLoaded", function () {
                    Swal.fire({
                        title: "Unable to Login!",
                        text: "Try Again After ' . $minutesRemaining . ' minutes!",
                        icon: "error",
                        confirmButtonText: "Okay"
                    });
                });
            </script>';
    } else {
      mysqli_query($conn, "DELETE FROM login_attempts WHERE ip_address = '$ipAddress'");
    }
  } else {
    // 1. UNA: I-check sa user_tbl (Admin)
    $admin_query = "SELECT * FROM user_tbl WHERE username = '$username' AND useractive = '1'";
    $admin_result = mysqli_query($conn, $admin_query);

    // 2. IKALAWA: I-check sa employee_tbl (Staff)
    $employee_query = "SELECT * FROM employee_tbl WHERE employee_no = '$username' AND status = '1'";
    $employee_result = mysqli_query($conn, $employee_query);

    if (mysqli_num_rows($admin_result) === 1) {
      // LOGIN AS ADMIN
      $row = mysqli_fetch_assoc($admin_result);
      if (password_verify($password, $row['password'])) {
        mysqli_query($conn, "DELETE FROM login_attempts WHERE ip_address = '$ipAddress'");

        $_SESSION['usertype'] = 1;
        $_SESSION['userid'] = $row['userid'];

        $loginTime = date("Y-m-d H:i:s");
        mysqli_query($conn, "INSERT INTO userlogs_tbl (userid, username, login_time, ip_address) VALUES ('1', '$username', '$loginTime', '$ipAddress')");

        $redirectUrl = base64_encode("./admin/");
        showSuccessToast("Admin Access Granted!", $redirectUrl);
      } else {
        handleFailedAttempt($conn, $ipAddress, $currentTime);
      }
    } else if (mysqli_num_rows($employee_result) === 1) {
      // LOGIN AS EMPLOYEE/STAFF
      $row = mysqli_fetch_assoc($employee_result);
      if (password_verify($password, $row['password'])) {
        mysqli_query($conn, "DELETE FROM login_attempts WHERE ip_address = '$ipAddress'");

        $_SESSION['employee_id'] = $row['employee_id'];
        $_SESSION['employee_no'] = $row['employee_no'];

        $loginTime = date("Y-m-d H:i:s");
        mysqli_query($conn, "INSERT INTO userlogs_tbl (userid, username, login_time, ip_address) VALUES ('{$row['employee_id']}', '$username', '$loginTime', '$ipAddress')");

        $redirectUrl = base64_encode("./employee/");
        showSuccessToast("Employee Access Granted!", $redirectUrl);
      } else {
        handleFailedAttempt($conn, $ipAddress, $currentTime);
      }
    } else {
      // Walang nahanap na record
      handleFailedAttempt($conn, $ipAddress, $currentTime);
    }
  }
}

// Helper Functions para mas malinis ang code
function showSuccessToast($msg, $url)
{
  echo '<script>
        document.addEventListener("DOMContentLoaded", function () {
            const Toast = Swal.mixin({
                toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true
            });
            Toast.fire({ icon: "success", title: "' . $msg . '" });
            setTimeout(function () { window.location.href = atob("' . $url . '"); }, 2000);
        });
    </script>';
}

function handleFailedAttempt($conn, $ip, $time)
{
  mysqli_query($conn, "INSERT INTO login_attempts (ip_address, last_attempt) VALUES ('$ip', $time)");
  echo '<script>
        document.addEventListener("DOMContentLoaded", function () {
            Swal.fire({ title: "Incorrect Credentials!", text: "Please check your ID and password.", icon: "warning", confirmButtonColor: "#800000" });
        });
    </script>';
}

// --- FORGOT PASSWORD LOGIC ---
if (isset($_POST['send_reset_link'])) {
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $code = mysqli_real_escape_string($conn, md5(rand()));

  // Check if the email exists in user_tbl
  if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM user_tbl WHERE email='{$email}'")) > 0) {
    $query = mysqli_query($conn, "UPDATE user_tbl SET code='{$code}' WHERE email='{$email}'");

    if ($query) {
      echo "<div style='display: none;'>";
      $mail = new PHPMailer(true);
      try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'onlineorderingandinventory@gmail.com';
        $mail->Password = 'kdikvnkzmzrcvylc'; // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->setFrom('onlineorderingandinventory@gmail.com', 'MarSU FILE MANAGEMENT SYSTEM');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - MarSU FILE MANAGEMENT SYSTEM';

        // IMPORTANT: Updated the link to point to change-password.php
        $resetLink = "http://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . "/changepassword.php?reset=" . $code;

        $mail->Body = '
                    <p>Dear User,</p>
                    <p>We received a request to reset your password. 
                    If you made this request, please click the button below to create a new password.</p>

                    <p style="text-align: center;">
                        <a href="' . $resetLink . '" style="
                            background-color: #800000; 
                            color: white; 
                            padding: 10px 20px; 
                            text-decoration: none; 
                            border-radius: 5px;
                            font-size: 16px;
                            display: inline-block;
                        ">
                            Reset Your Password
                        </a>
                    </p>
                    <br>
                    <p>Best regards,<br>Marinduque State University</p>
                ';
        $mail->send();
        ?>
                <script>
                  document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                      title: "Success!",
                      text: "We have sent a password reset link to your email address.",
                      icon: "success",
                      confirmButtonText: "Okay",
                      confirmButtonColor: "#800000",
                      allowOutsideClick: false,
                      allowEscapeKey: false,
                    }).then((result) => {
                      if (result.isConfirmed) {
                        window.location.href = 'index';
                      }
                    });
                  });
                </script>
                <?php
      } catch (Exception $e) {
        ?>
                <script>
                  document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                      title: "Failed!",
                      text: "Message could not be sent. Please try again later.",
                      icon: "error",
                      confirmButtonText: "Okay",
                      confirmButtonColor: "#800000"
                    });
                  });
                </script>
                <?php
      }
    } else {
      ?>
            <script>
              document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                  title: "Failed!",
                  text: "Something went wrong while processing your request.",
                  icon: "error",
                  confirmButtonText: "Okay",
                  confirmButtonColor: "#800000"
                });
              });
            </script>
            <?php
    }
  } else {
    ?>
        <script>
          document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
              title: "Email Not Found!",
              text: "No account associated with this email address.",
              icon: "warning",
              confirmButtonText: "Okay",
              confirmButtonColor: "#800000"
            });
          });
        </script>
        <?php
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>E-TAMS | MarSU Travel Portal</title>
  <link href="https://hrmis.marsu.edu.ph/dist/img/marsu_logo.png" rel="icon">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      --maroon: #800000;
      --gold: #FFD700;
      --glass: rgba(255, 255, 255, 0.12);
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(128, 0, 0, 0.4)),
        url('https://scontent-mnl1-2.xx.fbcdn.net/v/t39.30808-6/524896578_122177118542545430_58775251789529293_n.jpg?_nc_cat=100&ccb=1-7&_nc_sid=7b2446&_nc_eui2=AeE37Uv0tRHsVfkLfoYr8T4ygGmr1_SwJByAaavX9LAkHFgdYE0WZreDZ-MxTbQwsVPnfxeYnjEnc2dgAeYFmvpo&_nc_ohc=lbyVqurAOb4Q7kNvwFIVN1W&_nc_oc=Adl3dikyUm3jr7EQKPeZheP6qWuZ7GPTodZ03LbThggMc5a29bJxm-zKyflFJBLE6LeUmZqGi_iGxw4WoOhRWUPe&_nc_zt=23&_nc_ht=scontent-mnl1-2.xx&_nc_gid=4yeOzKVQxoe9I0p2_ZBEKw&oh=00_AfshMnal_pUbSqE25QmAyWC1P2H_P4R8n01Wj4mIfnJXtw&oe=69A1C1E9');
      /* Modern Airplane/Travel BG */
      background-size: cover;
      background-position: center;
      height: 100vh;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Travel Path Animation */
    .flight-path {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      pointer-events: none;
      background-image: radial-gradient(circle, white 1px, transparent 1px);
      background-size: 50px 50px;
      opacity: 0.1;
    }

    .glass-card {  
      background: rgba(0,0,0,0.7);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 40px;
      padding: 50px 40px; 
      width: 100%;
      max-width: 440px;
      position: relative;
      z-index: 10;
    }

    /* Airplane Animation */
    .airplane-anim {
      position: absolute;
      top: 10%;
      right: -100px;
      font-size: 3rem;
      color: rgba(255, 255, 255, 0.2);
      animation: fly 15s linear infinite;
    }

    @keyframes fly {
      from {
        right: -100px;
        top: 10%;
      }

      to {
        left: -100px;
        top: 50%;
      }
    }

    .logo-img {
      width: 110px;
      height: 110px;
      background: white;
      padding: 6px;
      border-radius: 50%;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      position: absolute;
      top: -55px;
      left: 50%;
      transform: translateX(-50%);
    }

    .boarding-text {
      background: #1a1a1a;
      color: var(--gold);
      padding: 5px 15px;
      font-family: monospace;
      border-radius: 5px;
      display: inline-block;
      letter-spacing: 2px;
      font-weight: bold;
      border-left: 4px solid var(--gold);
    }

    .form-control {
      background: rgba(255, 255, 255, 0.95);
      border: none;
      border-radius: 15px;
      padding: 15px 20px;
      font-weight: 600;
      font-size: 14px;
    }

    .btn-login {
      background: var(--maroon);
      border: none;
      border-radius: 15px;
      font-weight: 800;
      letter-spacing: 2px;
      padding: 15px;
      transition: 0.4s;
      color: white;
      text-transform: uppercase;
    }

    .btn-login:hover {
      background: var(--gold);
      color: var(--maroon);
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(255, 215, 0, 0.3);
    }

    .input-group-text {
      background: white;
      border: none;
      border-radius: 0 15px 15px 0;
      color: var(--maroon);
    }

    .form-floating>label {
      padding-left: 20px;
      color: #555;
    }

    .travel-footer {
      margin-top: 30px;
      font-size: 11px;
      color: white;
      opacity: 0.7;
      border-top: 1px dashed rgba(255, 255, 255, 0.3);
      padding-top: 20px;
    }
  </style>
</head>

<body>

  <div class="flight-path"></div>
  <i class="bi bi-airplane airplane-anim"></i>

  <div class="glass-card text-center shadow-lg" >
    <img src="https://hrmis.marsu.edu.ph/dist/img/marsu_logo.png" class="logo-img" alt="MarSU Logo">

    <div id="loginSection" class="mt-4" >
      <div class="mb-3">
        <h2 class="fw-800 text-white mt-2 mb-0" style="letter-spacing: 1px;">E-TAMS PORTAL</h2>
        <small class="text-uppercase text-white-50 fw-bold" style="font-size: 10px;">University Travel Authority
          System</small>
      </div>

      <form class="row g-3 mt-2" method="POST" autocomplete="off">
        <div class="col-12">
          <div class="form-floating text-dark">
            <input type="text" name="username" class="form-control shadow-sm" id="u" placeholder="ID" required>
            <label for="u"><i class="bi bi-passport me-2"></i>Employee ID / Username</label>
          </div>
        </div>

        <div class="col-12">
          <div class="input-group shadow-sm">
            <div class="form-floating flex-grow-1 text-dark">
              <input type="password" name="password" class="form-control" id="p" placeholder="Key"
                style="border-radius: 15px 0 0 15px;" required>
              <label for="p"><i class="bi bi-key-fill me-2"></i>Security Key</label>
            </div>
            <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
              <i class="bi bi-eye-slash"></i>
            </span>
          </div>
        </div>

        <div class="col-12 mt-4">
          <button class="btn btn-primary w-100 btn-login" type="submit" name="submit">
            CONFIRM ARRIVAL <i class="bi bi-send-check-fill ms-2"></i>
          </button>
        </div>

        <div class="col-12 mt-3">
          <a href="javascript:void(0)" onclick="toggleForms('forgot')"
            class="text-white opacity-75 small text-decoration-none">
            <i class="bi bi-question-circle me-1"></i> Lost your credentials? <span class="fw-bold"
              style="color: var(--gold);">Reset Access</span>
          </a>
        </div>
      </form>
    </div>

    <div id="forgotSection" style="display: none;" class="mt-4">
      <h3 class="fw-bold text-white mb-1"><i class="bi bi-shield-lock me-2"></i>RECOVERY</h3>
      <p class="small text-white-50 mb-4">Enter your registered email to receive a secure link.</p>
      <form class="row g-3" method="POST">
        <div class="col-12 text-dark">
          <div class="form-floating">
            <input type="email" name="email" class="form-control shadow-sm" id="e" placeholder="Email" required>
            <label for="e"><i class="bi bi-envelope-paper-fill me-2"></i>Official Email</label>
          </div>
        </div>
        <div class="col-12 mt-4">
          <button class="btn btn-primary w-100 btn-login" type="submit" name="send_reset_link">SEND ACCESS LINK</button>
        </div>
        <div class="col-12 mt-3">
          <a href="javascript:void(0)" onclick="toggleForms('login')" class="text-white small opacity-75">&larr; Return
            to Check-in</a>
        </div>
      </form>
    </div>

    <div class="travel-footer">
      <div class="d-flex justify-content-between align-items-center mb-2 px-3">
        <span class="fw-bold"><i class="bi bi-geo-alt-fill me-1"></i> BOAC, MQE</span>
        <span class="fw-bold">TERMINAL <?= date('Y') ?> <i class="bi bi-check-circle-fill ms-1 text-success"></i></span>
      </div>
      <p class="mb-0">Marinduque State University - CICS System</p>
    </div>
  </div>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    // Password Toggle
    document.querySelector('#togglePassword').addEventListener('click', function () {
      const p = document.querySelector('#p');
      const icon = this.querySelector('i');
      const type = p.getAttribute('type') === 'password' ? 'text' : 'password';
      p.setAttribute('type', type);
      icon.classList.toggle('bi-eye');
      icon.classList.toggle('bi-eye-slash');
    });

    // Form Switcher
    function toggleForms(type) {
      const login = document.getElementById('loginSection');
      const forgot = document.getElementById('forgotSection');
      login.style.display = (type === 'forgot') ? 'none' : 'block';
      forgot.style.display = (type === 'forgot') ? 'block' : 'none';
    }
  </script>
</body>

</html>