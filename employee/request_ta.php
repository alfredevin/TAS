<?php
include './../config.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>

</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <style>
        .form-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
        }

        .wizard-step {
            display: none;
        }

        .wizard-step.active {
            display: block;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Progress Tracker with Lines */
        .wizard-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 50px;
            position: relative;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .wizard-progress::before {
            content: "";
            position: absolute;
            top: 18px;
            left: 10%;
            width: 80%;
            height: 2px;
            background: #ddd;
            z-index: 0;
        }

        .progress-line-fill {
            position: absolute;
            top: 18px;
            left: 10%;
            width: 0%;
            height: 2px;
            background: #800000;
            z-index: 0;
            transition: 0.4s;
        }

        .progress-item {
            position: relative;
            z-index: 1;
            text-align: center;
            width: 100px;
        }

        .progress-dot {
            width: 38px;
            height: 38px;
            background: #fff;
            border: 2px solid #ddd;
            border-radius: 50%;
            display: inline-block;
            line-height: 34px;
            font-weight: bold;
            color: #999;
            transition: 0.3s;
        }

        .progress-item.active .progress-dot {
            border-color: #800000;
            background: #800000;
            color: #fff;
            box-shadow: 0 0 10px rgba(128, 0, 0, 0.3);
        }

        .progress-item.completed .progress-dot {
            border-color: #28a745;
            background: #28a745;
            color: #fff;
        }

        .progress-label {
            display: block;
            font-size: 11px;
            margin-top: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #999;
        }

        .progress-item.active .progress-label {
            color: #800000;
        }

        /* Memo Preview - Paper Style */
        #memo-paper {
            background: #fff;
            border: 1px solid #ccc;
            padding: 0.7in;
            font-family: "Times New Roman", Times, serif;
            color: #000;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 8.5in;
            margin: auto;
        }

        .preview-header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #800000;
            padding-bottom: 10px;
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
        }

        .meta-table td {
            padding: 5px 0;
            vertical-align: top;
        }

        .underline-val {
            border-bottom: 1px solid #000;
            font-weight: bold;
            padding-left: 10px;
        }

        .cert-box {
            margin-top: 40px;
            border-top: 1px solid #000;
            padding-top: 20px;
        }

        .cert-lines-row {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .blank-line {
            border-bottom: 1px solid #000;
            width: 250px;
            height: 25px;
            margin-bottom: 8px;
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

            // 3. NOTIFICATION LOGIC - DITO TAYO MAG-FOKUS
            // Hanapin ang department_id mo
            $get_my_dept = mysqli_query($conn, "SELECT department_id FROM employee_tbl WHERE employee_id = '$employee_id'");
            $dept_row = mysqli_fetch_assoc($get_my_dept);

            if ($dept_row && !empty($dept_row['department_id'])) {
                $my_dept_id = $dept_row['department_id'];

                // Hanapin ang Head ng department na iyon
                // Sinisiguro nito na hindi ikaw ang sarili mong Head (kung ikaw ang nag-submit)
                $get_head = mysqli_query($conn, "SELECT employee_id FROM employee_tbl WHERE department_id = '$my_dept_id' AND position_name LIKE '%HEAD%' AND employee_id != '$employee_id' LIMIT 1");
                $head_row = mysqli_fetch_assoc($get_head);
                $head_id = $head_row['employee_id'] ?? null;

                if ($head_id) {
                    $notif_title = "New Travel Request";
                    $notif_msg = "$my_fullname submitted a TA request for destination: $destination.";

                    $notif_stmt = $conn->prepare("INSERT INTO notifications_tbl (recipient_id, sender_id, title, message, is_read) VALUES (?, ?, ?, ?, 0)");
                    $notif_stmt->bind_param("iiss", $head_id, $employee_id, $notif_title, $notif_msg);

                    if (!$notif_stmt->execute()) {
                        // Ito ay para malaman mo kung may SQL error sa notifications table
                        error_log("Notification Insert Failed: " . $notif_stmt->error);
                    }
                }
            }

            echo "<script>alert('Travel Authority Submitted Successfully!'); window.location.href='my_travels.php';</script>";
        } else {
            echo "<script>alert('Failed to submit TA request.');</script>";
        }
    }
    ?>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Create Travel Authority</h1>
        </div>

        <section class="section">
            <div class="row justify-content-center">
                <div class="col-lg-11">
                    <div class="form-card">

                        <div class="wizard-progress">
                            <div class="progress-line-fill" id="line-fill"></div>
                            <div class="progress-item active" id="dot-1"><span class="progress-dot">1</span><span class="progress-label">General</span></div>
                            <div class="progress-item" id="dot-2"><span class="progress-dot">2</span><span class="progress-label">Travel</span></div>
                            <div class="progress-item" id="dot-3"><span class="progress-dot">3</span><span class="progress-label">Preview</span></div>
                        </div>

                        <form method="POST" id="wizardForm">

                            <div class="wizard-step active" id="step-1">
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Date of Request</label>
                                        <input type="date" class="form-control form-control-lg bg-light" name="date_issued" value="<?= date('Y-m-d') ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Memorandum Reference</label>
                                        <div class="form-control form-control-lg bg-light text-muted">Awaiting Approval</div>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <div class="p-3 border rounded bg-light">
                                            <label class="form-label small text-uppercase fw-bold text-secondary">Authorized Personnel</label>
                                            <h5 class="m-0 fw-bold"><?= $my_fullname ?></h5>
                                            <p class="m-0 text-muted"><?= $my_pos ?></p>
                                            <input type="hidden" name="traveler_ids[]" value="<?= $employee_id ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end border-top pt-4">
                                    <button type="button" class="btn btn-primary btn-lg px-5 shadow" onclick="goToStep(2)">Next: Travel Details <i class="bi bi-arrow-right ms-2"></i></button>
                                </div>
                            </div>

                            <div class="wizard-step" id="step-2">
                                <div class="row g-3 mb-4">
                                    <div class="col-12">
                                        <div class="form-floating"><input type="text" class="form-control" id="f_dest" name="destination" placeholder="Dest" required><label>Where are you going? (Destination)</label></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating"><input type="date" class="form-control" id="f_travel" name="travel_date" min="<?= date('Y-m-d') ?>" required><label>Travel Date</label></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating"><input type="date" class="form-control" id="f_return" name="return_date" required><label>Expected Return</label></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating"><textarea class="form-control" id="f_task" name="task" style="height: 120px" required></textarea><label>Purpose of the travel / Tasks to be done</label></div>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <label class="form-label small fw-bold">Approved By (Campus Director)</label>
                                        <div class="p-3 border rounded bg-light fw-bold"><?= $dir_fullname ?> (<?= $dir_pos ?>)</div>
                                        <input type="hidden" name="approver_id" value="<?= $dir_id ?>">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="goToStep(1)"><i class="bi bi-arrow-left"></i> Back</button>
                                    <button type="button" class="btn btn-primary btn-lg px-5 shadow" onclick="loadPreview()">Final Review <i class="bi bi-eye ms-2"></i></button>
                                </div>
                            </div>

                            <div class="wizard-step" id="step-3">
                                <div id="memo-paper">
                                    <div class="preview-header">
                                        <img src="https://hrmis.marsu.edu.ph/dist/img/marsu_logo.png" alt="MarSU Logo">
                                        <div class="header-text">
                                            <h4 class="m-0 fw-bold">MARINDUQUE STATE UNIVERSITY</h4>
                                            <p class="m-0">SANTA CRUZ CAMPUS</p>
                                        </div>
                                        <div style="width: 70px;"></div>
                                    </div>

                                    <h5 class="text-center fw-bold text-decoration-underline mb-4">OFFICE OF THE CAMPUS DIRECTOR</h5>
                                    <p class="fw-bold mb-4">MEMORANDUM NO. <span class="text-danger">PENDING</span></p>

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
                                            <td class="fw-bold">LOCAL TRAVEL AUTHORITY</td>
                                        </tr>
                                    </table>

                                    <p style="text-indent: 50px; text-align: justify; line-height: 1.6; font-size: 13pt;">
                                        In the exigency of service, please be informed that you are hereby authorized to go to
                                        <b id="pv-dest" style="text-decoration: underline;"></b> on <b id="pv-travel" style="text-decoration: underline;"></b>,
                                        to <b id="pv-task" style="text-decoration: underline;"></b>.
                                    </p>
                                    <p style="text-indent: 50px; text-align: justify; line-height: 1.6; font-size: 13pt;">
                                        You are expected to return to your workstation upon completion of the said purposes.
                                    </p>

                                    <div class="mt-5" style="margin-left: 50%;">
                                        <p style="font-style: italic; font-size: 11pt; margin-bottom: 30px;">By the Authority of the University President:</p>
                                        <p class="fw-bold m-0" style="font-size: 14pt;"><?= $dir_fullname ?></p>
                                        <p class="m-0"><?= $dir_pos ?></p>
                                    </div>

                                    <div class="cert-box">
                                        <h5 class="text-center fw-bold text-decoration-underline mb-4">CERTIFICATE OF APPEARANCE</h5>
                                        <p style="text-indent: 50px; text-align: justify;">This is to certify that <b><?= $my_fullname ?></b> personally appeared in this office to transact official business on <span id="pv-date-cert" style="text-decoration: underline; font-weight: bold;"></span>.</p>

                                        <div class="cert-lines-row">
                                            <div>
                                                <div class="blank-line"></div>
                                                <div class="blank-line"></div>
                                                <div class="blank-line"></div>
                                            </div>
                                            <div style="text-align: center;">
                                                <div style="height: 60px;"></div>
                                                <div style="border-top: 1px solid #000; width: 300px; font-size: 9pt;">
                                                    (Signature Over Printed Name of Head /<br>Representative of Visited Office)
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between border-top mt-5 pt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="goToStep(2)"><i class="bi bi-pencil"></i> Edit Details</button>
                                    <button type="submit" name="submit_ta" class="btn btn-success btn-lg px-5 shadow-lg fw-bold">Submit TA Request <i class="bi bi-send-check ms-2"></i></button>
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
            $('.wizard-step').removeClass('active');
            $('#step-' + step).addClass('active');

            // Progress line & dots logic
            let fillWidth = (step === 1) ? '0%' : (step === 2) ? '40%' : '80%';
            $('#line-fill').css('width', fillWidth);

            $('.progress-item').removeClass('active completed');
            for (let i = 1; i <= step; i++) {
                if (i < step) $('#dot-' + i).addClass('completed');
                if (i === step) $('#dot-' + i).addClass('active');
            }
            window.scrollTo(0, 0);
        }

        function loadPreview() {
            if (!$('#f_dest').val() || !$('#f_travel').val() || !$('#f_task').val()) {
                alert("Please fill in all required fields.");
                return;
            }

            const fmt = (d) => d ? new Date(d).toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            }) : "";

            $('#pv-date').text(fmt('<?= date('Y-m-d') ?>'));
            $('#pv-date-cert').text(fmt($('#f_travel').val()));
            $('#pv-dest').text($('#f_dest').val().toUpperCase());
            $('#pv-travel').text(fmt($('#f_travel').val()));
            $('#pv-task').text($('#f_task').val());

            goToStep(3);
        }

        $(document).ready(function() {
            $('#f_travel').change(function() {
                $('#f_return').attr('min', $(this).val());
            });
        });
    </script>
</body>

</html>