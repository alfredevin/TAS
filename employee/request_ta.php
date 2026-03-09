<?php
include './../config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include '../template/header.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <style>
     
        body {
            background-color: #f8fafc;
        }

        .form-card {
            background: #fff;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.04);
            border: 1px solid #f1f5f9;
            position: relative;
            overflow: hidden;
        }

        /* Wizard Animations */
        .wizard-step {
            display: none;
            opacity: 0;
            transform: translateY(15px);
            transition: all 0.4s ease-in-out;
        }

        .wizard-step.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
            animation: slideUpFade 0.5s forwards;
        }

        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Modern Progress Tracker */
        .wizard-progress-container {
            position: relative;
            max-width: 500px;
            margin: 0 auto 50px auto;
            padding-bottom: 20px;
        }

        .step-line-bg {
            position: absolute;
            top: 20px;
            left: 15%;
            right: 15%;
            height: 4px;
            background: #e2e8f0;
            border-radius: 4px;
            z-index: 1;
        }

        .step-line-fill {
            position: absolute;
            top: 20px;
            left: 15%;
            height: 4px;
            background: linear-gradient(90deg, #800000, #b91c1c);
            border-radius: 4px;
            z-index: 2;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            width: 0%;
        }

        .steps-wrapper {
            display: flex;
            justify-content: space-between;
            position: relative;
            z-index: 3;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 80px;
        }

        .step-circle {
            width: 44px;
            height: 44px;
            background: #fff;
            border: 3px solid #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #94a3b8;
            font-size: 16px;
            transition: all 0.4s ease;
        }

        .step-label {
            margin-top: 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.5px;
            transition: all 0.4s ease;
        }

        /* Active & Completed States */
        .step-item.active .step-circle {
            border-color: #800000;
            background: #800000;
            color: #fff;
            box-shadow: 0 0 0 6px rgba(128, 0, 0, 0.1);
            transform: scale(1.1);
        }

        .step-item.active .step-label {
            color: #800000;
        }

        .step-item.completed .step-circle {
            border-color: #10b981;
            background: #10b981;
            color: #fff;
        }

        /* Input Customization */
        .form-floating>.form-control,
        .form-floating>.form-select {
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background-color: #f8fafc;
            transition: all 0.3s ease;
            box-shadow: none;
        }

        .form-floating>.form-control:focus,
        .form-floating>.form-select:focus {
            border-color: #800000;
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.1);
        }

        .form-floating label {
            color: #64748b;
            font-weight: 500;
        }

        /* User Identity Box */
        .identity-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .identity-icon {
            width: 50px;
            height: 50px;
            background: #800000;
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        /* Responsive Memo Preview */
        .memo-container {
            background: #e2e8f0;
            padding: 20px;
            border-radius: 16px;
            overflow-x: auto;
        }

        #memo-paper {
            background: #fff;
            padding: 40px;
            font-family: "Times New Roman", Times, serif;
            color: #000;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            min-width: 600px;
            /* Forces scrolling on very small screens to preserve format */
            margin: auto;
            border-radius: 4px;
        }

        .preview-header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #800000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .preview-header img {
            width: 70px;
            height: auto;
        }

        .header-text {
            flex-grow: 1;
            text-align: center;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11pt;
        }

        .meta-table td {
            padding: 6px 0;
            vertical-align: top;
        }

        .underline-val {
            border-bottom: 1px solid #000;
            font-weight: bold;
            padding-left: 10px;
        }

        .cert-box {
            margin-top: 40px;
            border-top: 1px dashed #94a3b8;
            padding-top: 25px;
        }

        .cert-lines-row {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .blank-line {
            border-bottom: 1px solid #000;
            width: 200px;
            height: 20px;
            margin-bottom: 8px;
        }

        /* Buttons */
        .btn-modern {
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary-aura {
            background: linear-gradient(135deg, #800000 0%, #b91c1c 100%);
            color: white;
            border: none;
        }

        .btn-primary-aura:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(128, 0, 0, 0.25);
            color: white;
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .form-card {
                padding: 20px;
            }

            .identity-box {
                flex-direction: column;
                text-align: center;
            }

            .memo-container {
                padding: 10px;
                border-radius: 12px;
            }

            #memo-paper {
                padding: 20px;
            }

            .preview-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .header-text h4 {
                font-size: 16px;
            }

            .cert-lines-row {
                flex-direction: column;
                gap: 30px;
                align-items: center;
            }

            .btn-modern {
                width: 100%;
                margin-bottom: 10px;
            }

            /* .d-flex.justify-content-between {
                flex-direction: column-reverse;
            } */
        }
    </style>

    <?php
    // 1. Logged-in User Info
    $get_me = mysqli_query($conn, "SELECT first_name, last_name, position_name FROM employee_tbl WHERE employee_id = '$employee_id'");
    $me = mysqli_fetch_assoc($get_me);
    $my_fullname = strtoupper($me['first_name'] . ' ' . $me['last_name']);
    $my_pos = strtoupper($me['position_name']);

    // 2. Default Campus Director Info
    $get_director = mysqli_query($conn, "SELECT employee_id, first_name, last_name, position_name FROM employee_tbl WHERE position_name LIKE '%CAMPUS DIRECTOR%' LIMIT 1");
    $dir_data = mysqli_fetch_assoc($get_director);
    $dir_id = $dir_data['employee_id'] ?? '';
    $dir_fullname = strtoupper(($dir_data['first_name'] ?? '') . ' ' . ($dir_data['last_name'] ?? ''));
    $dir_pos = strtoupper($dir_data['position_name'] ?? 'CAMPUS DIRECTOR');

    if (isset($_POST['submit_ta'])) {
        $date_issued = $_POST['date_issued'];
        $destination = strtoupper(trim($_POST['destination']));
        $travel_date = $_POST['travel_date'];
        $task = trim($_POST['task']);
        $return_date = $_POST['return_date'];
        $approver_id = $_POST['approver_id'];
        $traveler_ids = $_POST['traveler_ids'];

        $memo_placeholder = "PENDING";
        $status = 0;

        // 1. INSERT TA MAIN RECORD
        $insert_stmt = $conn->prepare("INSERT INTO ta_tbl (memo_no, date_issued, destination, travel_date, task, return_date, approver_id, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $insert_stmt->bind_param("ssssssis", $memo_placeholder, $date_issued, $destination, $travel_date, $task, $return_date, $approver_id, $status);

        if ($insert_stmt->execute()) {
            $ta_id = $insert_stmt->insert_id;

            // 2. INSERT PARTICIPANTS
            foreach ($traveler_ids as $emp_id) {
                $stmt_person = $conn->prepare("INSERT INTO ta_participants_tbl (ta_id, employee_id) VALUES (?, ?)");
                $stmt_person->bind_param("ii", $ta_id, $emp_id);
                $stmt_person->execute();
            }

            // 3. NOTIFICATION LOGIC
            $get_my_dept = mysqli_query($conn, "SELECT department_id FROM employee_tbl WHERE employee_id = '$employee_id'");
            $dept_row = mysqli_fetch_assoc($get_my_dept);

            if ($dept_row && !empty($dept_row['department_id'])) {
                $my_dept_id = $dept_row['department_id'];
                $get_head = mysqli_query($conn, "SELECT employee_id FROM employee_tbl WHERE department_id = '$my_dept_id' AND position_name LIKE '%HEAD%' AND employee_id != '$employee_id' LIMIT 1");
                $head_row = mysqli_fetch_assoc($get_head);
                $head_id = $head_row['employee_id'] ?? null;

                if ($head_id) {
                    $notif_title = "New Travel Request";
                    $notif_msg = "$my_fullname submitted a TA request for destination: $destination.";
                    $notif_stmt = $conn->prepare("INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, is_read) VALUES (?, ?, ?, ?, 0)");
                    $notif_stmt->bind_param("iiss", $head_id, $employee_id, $notif_title, $notif_msg);
                    if (!$notif_stmt->execute()) {
                        error_log("Notification Insert Failed: " . $notif_stmt->error);
                    }
                }
            }

            // SweetAlert Success & Redirect
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Request Submitted!',
                        text: 'Your Travel Authority request has been forwarded for approval.',
                        icon: 'success',
                        confirmButtonColor: '#800000',
                        confirmButtonText: 'View My Travels'
                    }).then(() => {
                        window.location.href='my_travels.php';
                    });
                });
            </script>";
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire('Error', 'Failed to submit TA request. Please try again.', 'error');
                });
            </script>";
        }
    }
    ?>

    <main id="main" class="main">
        <div class="pagetitle mb-4">
            <h1 class="fw-bolder" style="color: #1e293b;">Create Travel Authority</h1>
            <nav>
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-muted">Employee</a></li>
                    <li class="breadcrumb-item active fw-bold" style="color: #800000;">New TA Request</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="form-card">

                        <div class="wizard-progress-container">
                            <div class="step-line-bg"></div>
                            <div class="step-line-fill" id="line-fill"></div>

                            <div class="steps-wrapper">
                                <div class="step-item active" id="dot-1">
                                    <div class="step-circle"><i class="bi bi-file-earmark-person"></i></div>
                                    <div class="step-label">General</div>
                                </div>
                                <div class="step-item" id="dot-2">
                                    <div class="step-circle"><i class="bi bi-geo-alt"></i></div>
                                    <div class="step-label">Travel Details</div>
                                </div>
                                <div class="step-item" id="dot-3">
                                    <div class="step-circle"><i class="bi bi-eye"></i></div>
                                    <div class="step-label">Review</div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" id="wizardForm">

                            <div class="wizard-step active" id="step-1">
                                <h5 class="fw-bold mb-4 text-dark"><i
                                        class="bi bi-info-circle me-2 text-primary"></i>General Information</h5>
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="date" class="form-control fw-bold" name="date_issued"
                                                value="<?= date('Y-m-d') ?>" readonly>
                                            <label>Date of Application</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control fw-bold text-danger bg-white"
                                                value="System Generated" readonly>
                                            <label>Memorandum Reference No.</label>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <label
                                            class="form-label small fw-bold text-muted text-uppercase mb-2">Requesting
                                            Personnel</label>
                                        <div class="identity-box shadow-sm">
                                            <div class="identity-icon"><i class="bi bi-person-badge"></i></div>
                                            <div>
                                                <h5 class="m-0 fw-bolder text-dark"><?= $my_fullname ?></h5>
                                                <p class="m-0 text-muted" style="font-size: 14px;"><?= $my_pos ?></p>
                                                <input type="hidden" name="traveler_ids[]" value="<?= $employee_id ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end border-top pt-4">
                                    <button type="button" class="btn btn-modern btn-primary-aura w-sm-100"
                                        onclick="goToStep(2)">
                                        Continue to Travel Details <i class="bi bi-arrow-right-circle ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="wizard-step" id="step-2">
                                <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-geo-alt me-2 text-danger"></i>Travel
                                    Specifics</h5>
                                <div class="row g-4 mb-4">
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="f_dest" name="destination"
                                                placeholder="Destination" required>
                                            <label><i class="bi bi-building me-1"></i> Destination (Where are you
                                                going?)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="date" class="form-control" id="f_travel" name="travel_date"
                                                min="<?= date('Y-m-d') ?>" required>
                                            <label><i class="bi bi-calendar-event me-1"></i> Date of Travel</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="date" class="form-control" id="f_return" name="return_date"
                                                required>
                                            <label><i class="bi bi-calendar-check me-1"></i> Expected Return
                                                Date</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="f_task" name="task" style="height: 120px"
                                                placeholder="Purpose" required></textarea>
                                            <label><i class="bi bi-journal-text me-1"></i> Purpose of the travel / Tasks
                                                to be done</label>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Final
                                            Approving Authority</label>
                                        <div
                                            class="p-3 border border-warning rounded bg-light fw-bold d-flex align-items-center">
                                            <i class="bi bi-shield-lock-fill text-warning fs-4 me-3"></i>
                                            <div>
                                                <div class="text-dark"><?= $dir_fullname ?></div>
                                                <div class="text-muted small fw-normal"><?= $dir_pos ?></div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="approver_id" value="<?= $dir_id ?>">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-4">
                                    <button type="button" class="btn btn-modern btn-light border" onclick="goToStep(1)">
                                        <i class="bi bi-arrow-left me-1"></i> Back
                                    </button>
                                    <button type="button" class="btn btn-modern btn-primary-aura"
                                        onclick="loadPreview()">
                                        Generate Preview <i class="bi bi-file-earmark-text ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="wizard-step" id="step-3">
                                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center" role="alert">
                                    <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                                    <div>
                                        <h6 class="alert-heading fw-bold mb-1">Final Review</h6>
                                        <p class="mb-0 small">Please double-check the generated document below before
                                            submitting.</p>
                                    </div>
                                </div>

                                <div class="memo-container mb-4">
                                    <div id="memo-paper">
                                        <div class="preview-header">
                                            <img src="https://hrmis.marsu.edu.ph/dist/img/marsu_logo.png"
                                                alt="MarSU Logo">
                                            <div class="header-text">
                                                <h4 class="m-0 fw-bold">MARINDUQUE STATE UNIVERSITY</h4>
                                                <p class="m-0" style="font-size: 11pt;">SANTA CRUZ CAMPUS</p>
                                            </div>
                                            <div style="width: 70px;" class="d-none d-md-block"></div>
                                        </div>

                                        <h5 class="text-center fw-bold text-decoration-underline mb-4"
                                            style="font-size: 12pt;">OFFICE OF THE CAMPUS DIRECTOR</h5>
                                        <p class="fw-bold mb-4" style="font-size: 11pt;">MEMORANDUM NO. <span
                                                class="text-danger">PENDING</span></p>

                                        <table class="meta-table">
                                            <tr>
                                                <td width="15%">DATE</td>
                                                <td width="5%">:</td>
                                                <td class="underline-val" id="pv-date"></td>
                                            </tr>
                                            <tr>
                                                <td>TO</td>
                                                <td>:</td>
                                                <td class="underline-val"><?= $my_fullname ?></td>
                                            </tr>
                                            <tr>
                                                <td>SUBJECT</td>
                                                <td>:</td>
                                                <td class="fw-bold" style="padding-left: 10px;">LOCAL TRAVEL AUTHORITY
                                                </td>
                                            </tr>
                                        </table>

                                        <p
                                            style="text-indent: 50px; text-align: justify; line-height: 1.6; font-size: 12pt;">
                                            In the exigency of service, please be informed that you are hereby
                                            authorized to go to
                                            <b id="pv-dest" style="text-decoration: underline;"></b> on <b
                                                id="pv-travel" style="text-decoration: underline;"></b>,
                                            to <span id="pv-task"
                                                style="text-decoration: underline; font-weight: bold;"></span>.
                                        </p>
                                        <p
                                            style="text-indent: 50px; text-align: justify; line-height: 1.6; font-size: 12pt;">
                                            You are expected to return to your workstation upon completion of the said
                                            purposes.
                                        </p>

                                        <div class="mt-5" style="margin-left: 40%;">
                                            <p style="font-style: italic; font-size: 10pt; margin-bottom: 30px;">By the
                                                Authority of the University President:</p>
                                            <p class="fw-bold m-0" style="font-size: 12pt; text-transform: uppercase;">
                                                <?= $dir_fullname ?></p>
                                            <p class="m-0" style="font-size: 11pt;"><?= $dir_pos ?></p>
                                        </div>

                                        <div class="cert-box">
                                            <h5 class="text-center fw-bold text-decoration-underline mb-4"
                                                style="font-size: 11pt;">CERTIFICATE OF APPEARANCE</h5>
                                            <p style="text-indent: 50px; text-align: justify; font-size: 11pt;">This is
                                                to certify that <b><?= $my_fullname ?></b> personally appeared in this
                                                office to transact official business on <span id="pv-date-cert"
                                                    style="text-decoration: underline; font-weight: bold;"></span>.</p>

                                            <div class="cert-lines-row">
                                                <div>
                                                    <div class="blank-line"></div>
                                                    <div class="blank-line"></div>
                                                    <div class="blank-line"></div>
                                                </div>
                                                <div style="text-align: center;">
                                                    <div style="height: 60px;"></div>
                                                    <div
                                                        style="border-top: 1px solid #000; width: 250px; font-size: 9pt; padding-top: 5px;">
                                                        (Signature Over Printed Name of Head /<br>Representative of
                                                        Visited Office)
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between border-top pt-4">
                                    <button type="button" class="btn btn-modern btn-light border" onclick="goToStep(2)">
                                        <i class="bi bi-pencil me-1"></i> Edit Details
                                    </button>
                                    <button type="submit" name="submit_ta"
                                        class="btn btn-modern btn-success shadow fw-bold">
                                        Confirm & Submit Request <i class="bi bi-send-check ms-1"></i>
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../template/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function goToStep(step) {
            // Validation before moving to step 3
            if (step === 3) {
                if (!$('#f_dest').val() || !$('#f_travel').val() || !$('#f_task').val() || !$('#f_return').val()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Fields',
                        text: 'Please fill in all required travel details.',
                        confirmButtonColor: '#800000'
                    });
                    return;
                }
            }

            $('.wizard-step').removeClass('active');
            $('#step-' + step).addClass('active');

            // Progress Bar Fill Logic
            let fillWidth = (step === 1) ? '0%' : (step === 2) ? '50%' : '100%';
            $('#line-fill').css('width', fillWidth);

            // Step Dots Logic
            $('.step-item').removeClass('active completed');
            for (let i = 1; i <= 3; i++) {
                if (i < step) {
                    $('#dot-' + i).addClass('completed');
                    $('#dot-' + i + ' .step-circle').html('<i class="bi bi-check-lg"></i>');
                } else if (i === step) {
                    $('#dot-' + i).addClass('active');
                    // Reset icons for active/future steps
                    if (i === 1) $('#dot-1 .step-circle').html('<i class="bi bi-file-earmark-person"></i>');
                    if (i === 2) $('#dot-2 .step-circle').html('<i class="bi bi-geo-alt"></i>');
                    if (i === 3) $('#dot-3 .step-circle').html('<i class="bi bi-eye"></i>');
                } else {
                    if (i === 2) $('#dot-2 .step-circle').html('<i class="bi bi-geo-alt"></i>');
                    if (i === 3) $('#dot-3 .step-circle').html('<i class="bi bi-eye"></i>');
                }
            }

            // Scroll up smoothly
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function loadPreview() {
            const fmt = (d) => {
                if (!d) return "";
                let dateObj = new Date(d);
                return dateObj.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            };

            $('#pv-date').text(fmt('<?= date('Y-m-d') ?>'));
            $('#pv-date-cert').text(fmt($('#f_travel').val()));
            $('#pv-dest').text($('#f_dest').val().toUpperCase());
            $('#pv-travel').text(fmt($('#f_travel').val()));
            $('#pv-task').text($('#f_task').val());

            goToStep(3);
        }

        $(document).ready(function () {
            // Auto-adjust return date minimum based on travel date
            $('#f_travel').change(function () {
                $('#f_return').attr('min', $(this).val());
                if ($('#f_return').val() < $(this).val()) {
                    $('#f_return').val($(this).val());
                }
            });
        });
    </script>
</body>

</html>