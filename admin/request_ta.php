<?php
include './../config.php';

// --- AUTO-GENERATE MEMO NUMBER ---
$currentYear = date('Y');
$countQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM ta_tbl WHERE YEAR(date_issued) = '$currentYear'");
$row = mysqli_fetch_assoc($countQuery);
$nextNumber = $row['total'] + 1;
$autoMemoNo = "TA" . $currentYear . "-" . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);


if (isset($_POST['submit_ta'])) {
    $memo_no = $_POST['memo_no'];
    $date_issued = $_POST['date_issued'];
    $destination = strtoupper(trim($_POST['destination']));
    $travel_date = $_POST['travel_date'];
    $task = trim($_POST['task']);
    $return_date = $_POST['return_date'];
    $approver_id = $_POST['approver_id'];
    $traveler_ids = $_POST['traveler_ids'];

    $insert_stmt = $conn->prepare("INSERT INTO ta_tbl (memo_no, date_issued, destination, travel_date, task, return_date, approver_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("sssssss", $memo_no, $date_issued, $destination, $travel_date, $task, $return_date, $approver_id);

    if ($insert_stmt->execute()) {
        $ta_id = $insert_stmt->insert_id;
        foreach ($traveler_ids as $emp_id) {
            $stmt_person = $conn->prepare("INSERT INTO ta_participants_tbl (ta_id, employee_id) VALUES (?, ?)");
            $stmt_person->bind_param("ii", $ta_id, $emp_id);
            $stmt_person->execute();
        }

        // DITO ANG MAGIC: Bubukas ang print page sa bagong tab
        echo "<script>
                window.open('./report/print_ta?id=$ta_id', '_blank');
                window.location.href = 'request_ta.php?success=1';
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <style>
        /* Style para sa Form Card */
        .form-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .form-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #012970;
            text-transform: uppercase;
            border-bottom: 2px solid #f2f2f2;
            margin-bottom: 20px;
            padding-bottom: 5px;
            display: block;
        }

        /* PREVIEW STYLE (Eksaktong gaya ng MSU Memo) */
        #previewSection {
            display: none;
            background: #fff;
            padding: 0.8in;
            border: 1px solid #ddd;
            font-family: "Times New Roman", Times, serif;
            color: #000;
            line-height: 1.3;
            font-size: 13pt;
            width: 8.5in;
            margin: auto;
        }

        .memo-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .msu-title {
            font-weight: bold;
            font-size: 18pt;
            text-transform: uppercase;
            margin: 0;
        }

        .campus-title {
            font-size: 12pt;
            margin: 0;
        }

        .office-title {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 25px;
            font-size: 14pt;
        }

        .memo-no {
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .memo-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .memo-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .underline-box {
            border-bottom: 1px solid #000;
            font-weight: bold;
            min-width: 250px;
            display: inline-block;
            padding-left: 10px;
        }

        .body-text {
            text-align: justify;
            margin-top: 30px;
            text-indent: 50px;
        }

        .signature-part {
            margin-top: 40px;
            margin-left: 50%;
        }

        /* Certificate of Appearance */
        .cert-appearance {
            margin-top: 50px;
            border-top: 2px solid #000;
            padding-top: 20px;
            text-align: center;
        }

        .cert-title {
            font-weight: bold;
            text-decoration: underline;
            font-size: 14pt;
            margin-bottom: 20px;
            display: block;
        }

        .cert-body {
            text-align: justify;
            text-indent: 50px;
        }

        .cert-lines {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .line-box {
            border-bottom: 1px solid #000;
            width: 45%;
            height: 20px;
            margin-bottom: 5px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            #previewSection {
                border: none;
                padding: 0;
                margin: 0;
                width: 100%;
                display: block !important;
            }

            #formContainer {
                display: none !important;
            }
        }
    </style>
    <main id="main" class="main">
        <div class="pagetitle no-print">
            <h1>Travel Authority Request</h1>
        </div>

        <section class="section">
            <div class="row justify-content-center">
                <div class="col-lg-11">

                    <div id="formContainer" class="form-card no-print">
                        <form method="POST" id="taForm">
                            <span class="form-section-title"><i class="bi bi-info-circle me-2"></i>General Info</span>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-floating"><input type="text" class="form-control" name="memo_no" value="<?= $autoMemoNo ?>" readonly><label>Memo No.</label></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating"><input type="date" class="form-control" id="f_date_issued" name="date_issued" value="<?= date('Y-m-d') ?>"><label>Date of Filing</label></div>
                                </div>
                            </div>

                            <span class="form-section-title"><i class="bi bi-people me-2"></i>Personnel Involved</span>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Select Employee(s) to be Authorized:</label>
                                <select class="form-select select2-multiple" name="traveler_ids[]" id="traveler_select" multiple="multiple" style="width: 100%" required>
                                    <?php
                                    $emp = mysqli_query($conn, "SELECT employee_id, first_name, last_name FROM employee_tbl WHERE status = 1");
                                    while ($e = mysqli_fetch_assoc($emp)) {
                                        echo "<option value='{$e['employee_id']}'>{$e['first_name']} {$e['last_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <span class="form-section-title"><i class="bi bi-geo-alt me-2"></i>Travel Details</span>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <div class="form-floating"><input type="text" class="form-control" id="f_destination" name="destination" placeholder="Destination" required><label>Authorized to go to (Destination)</label></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating"><input type="date" class="form-control" id="f_travel_date" name="travel_date" required><label>Date of Travel</label></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating"><input type="date" class="form-control" id="f_return_date" name="return_date" required><label>Expected Return Date</label></div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating"><textarea class="form-control" id="f_task" name="task" style="height: 100px" required></textarea><label>Purpose / Action to be done</label></div>
                                </div>
                            </div>

                            <span class="form-section-title"><i class="bi bi-pen me-2"></i>Approvals</span>
                            <div class="mb-4">
                                <select class="form-select" id="f_approver" name="approver_id" required>
                                    <option value="" disabled selected>Select Campus Director</option>
                                    <?php
                                    $off = mysqli_query($conn, "SELECT employee_id, first_name, last_name, position_name FROM employee_tbl");
                                    while ($o = mysqli_fetch_assoc($off)) {
                                        echo "<option value='{$o['employee_id']}' data-pos='{$o['position_name']}'>{$o['first_name']} {$o['last_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="text-end">
                                <button type="button" id="btnPreview" class="btn btn-info px-4 me-2"><i class="bi bi-eye"></i> Preview MSU Memo</button>
                                <button type="submit" name="submit_ta" class="btn btn-primary px-4 shadow"><i class="bi bi-save"></i> Submit TA</button>
                            </div>
                        </form>
                    </div>

                    <div id="previewSection">
                        <div class="text-end no-print mb-4">
                            <button type="button" id="btnBack" class="btn btn-secondary me-2">Back to Edit</button>
                        </div>

                        <div class="memo-header">
                            <h5 class="msu-title">MARINDUQUE STATE UNIVERSITY</h5>
                            <p class="campus-title">SANTA CRUZ CAMPUS</p>
                            <hr style="border-top: 2px solid #000; margin: 10px 0;">
                        </div>

                        <p class="office-title">OFFICE OF THE CAMPUS DIRECTOR</p>
                        <p class="memo-no">MEMORANDUM NO. <?= $autoMemoNo ?></p>

                        <table class="memo-table">
                            <tr>
                                <td style="width: 15%;">DATE</td>
                                <td style="width: 5%;">:</td>
                                <td class="underline-box" id="p_date"></td>
                            </tr>
                            <tr>
                                <td>FROM</td>
                                <td>:</td>
                                <td class="underline-box">Campus Director</td>
                            </tr>
                            <tr>
                                <td>TO</td>
                                <td>:</td>
                                <td class="underline-box" id="p_to"></td>
                            </tr>
                            <tr>
                                <td>SUBJECT</td>
                                <td>:</td>
                                <td><b>LOCAL TRAVEL AUTHORITY</b></td>
                            </tr>
                        </table>

                        <div class="body-text">
                            In the exigency of service, please be informed that you are hereby authorized to go to
                            <b><u><span id="p_dest"></span></u></b> on
                            <b><u><span id="p_travel_date"></span></u></b>, to
                            <b><u><span id="p_task"></span></u></b>.
                        </div>

                        <div class="body-text" style="margin-top: 15px;">
                            You are expected to return to your official workstation on
                            <b><u><span id="p_return"></span></u></b> unless this office informs you of an extension due to other official business.
                        </div>

                        <div class="body-text" style="margin-top: 15px;">For information and compliance.</div>

                        <div class="signature-part">
                            <p style="font-style: italic; font-size: 11pt; margin-bottom: 30px;">By the Authority of the University President:</p>
                            <p style="font-weight: bold; text-transform: uppercase; margin: 0; font-size: 14pt;" id="p_approver_name"></p>
                            <p style="margin: 0;" id="p_approver_pos"></p>
                        </div>

                        <div class="cert-appearance">
                            <span class="cert-title">CERTIFICATE OF APPEARANCE</span>
                            <div class="cert-body">
                                This is to certify that <b><u><span id="p_to_cert"></span></u></b> personally appeared in this office to transact official business on <b><u><span id="p_travel_date_cert"></span></u></b>.
                            </div>
                            <p style="text-align: left; margin-top: 15px;">This certificate is issued with travel itinerary.</p>

                            <div class="cert-lines">
                                <div style="width: 45%;">
                                    <div class="line-box"></div>
                                    <div class="line-box"></div>
                                    <div class="line-box"></div>
                                </div>
                                <div style="width: 45%; text-align: center;">
                                    <div style="height: 60px;"></div>
                                    <div style="border-top: 1px solid #000; font-size: 10pt;">(Signature Over Printed Name of Head / Representative of Office/Agency Visited)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <?php include '../template/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // INITIALIZE SELECT2 (Siguraduhin na id="traveler_select")
            $('#traveler_select').select2({
                placeholder: "Search Employee Names...",
                allowClear: true,
                width: '100%'
            });

            // PREVIEW BUTTON CLICK
            $('#btnPreview').click(function() {
                // Formatting Date
                const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric'
                }) : "";

                // Get Values
                $('#p_date').text(formatDate($('#f_date_issued').val()));
                $('#p_dest').text($('#f_destination').val());
                $('#p_travel_date').text(formatDate($('#f_travel_date').val()));
                $('#p_travel_date_cert').text(formatDate($('#f_travel_date').val()));
                $('#p_task').text($('#f_task').val());
                $('#p_return').text(formatDate($('#f_return_date').val()));

                // Get Names from Multi-select
                let selectedNames = [];
                $('#traveler_select option:selected').each(function() {
                    selectedNames.push($(this).text());
                });
                $('#p_to').text(selectedNames.join(', '));
                $('#p_to_cert').text(selectedNames.join(', '));

                // Approver Info
                let approver = $('#f_approver option:selected');
                $('#p_approver_name').text(approver.text());
                $('#p_approver_pos').text(approver.data('pos'));

                // Toggle UI
                $('#formContainer').hide();
                $('#previewSection').show();
                window.scrollTo(0, 0);
            });

            // BACK BUTTON
            $('#btnBack').click(function() {
                $('#previewSection').hide();
                $('#formContainer').show();
            });
        });
    </script>
</body>

</html>