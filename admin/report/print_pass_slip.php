<?php
include './../../config.php';

if (!isset($_GET['id'])) {
    die("Invalid Request");
}
$ps_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch Pass Slip Details joined with Employee Details
$query = "SELECT ps.*, e.first_name, e.last_name, e.position_name 
          FROM pass_slip_tbl ps 
          JOIN employee_tbl e ON ps.employee_id = e.employee_id 
          WHERE ps.ps_id = '$ps_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    die("Pass Slip not found.");
}

$ps = mysqli_fetch_assoc($result);

// --- LOGIC PARA SA CHECKBOXES ---
// Issued For
$chk_official = ($ps['issued_for'] == 'Official Activity') ? '✔' : '&nbsp;&nbsp;&nbsp;';
$chk_personal = ($ps['issued_for'] == 'Personal Reason') ? '✔' : '&nbsp;&nbsp;&nbsp;';

// Purposes
$purp = $ps['purpose_type'];
$chk_coord = ($purp == 'To coordinate with') ? '✔' : '&nbsp;&nbsp;&nbsp;';
$chk_meet = ($purp == 'To attend Meeting/Conference') ? '✔' : '&nbsp;&nbsp;&nbsp;';
$chk_doc = ($purp == 'To Secure Documents') ? '✔' : '&nbsp;&nbsp;&nbsp;';
$chk_pers_matter = ($purp == 'To attend Personal Matter') ? '✔' : '&nbsp;&nbsp;&nbsp;';
$chk_others = ($purp == 'Others') ? '✔' : '&nbsp;&nbsp;&nbsp;';

// Specific Details Mapping
$spec_coord = ($purp == 'To coordinate with') ? $ps['specific_purpose'] : '';
$spec_meet = ($purp == 'To attend Meeting/Conference') ? $ps['specific_purpose'] : '';
$spec_doc = ($purp == 'To Secure Documents') ? $ps['specific_purpose'] : '';
$spec_pers = ($purp == 'To attend Personal Matter') ? $ps['specific_purpose'] : '';
$spec_others = ($purp == 'Others') ? $ps['specific_purpose'] : '';

// Time Formatting
$time_out = ($ps['time_departure'] && $ps['time_departure'] != '00:00:00') ? date("h:i A", strtotime($ps['time_departure'])) : '_______________';
$time_in = ($ps['time_return'] && $ps['time_return'] != '00:00:00') ? date("h:i A", strtotime($ps['time_return'])) : '_______________';

$emp_name = strtoupper($ps['first_name'] . ' ' . $ps['last_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Pass Slip - <?= $ps['ps_id'] ?></title>
    <style>
        @page { size: portrait; margin: 0.5in; }
        body { font-family: "Arial", sans-serif; color: #000; line-height: 1.3; font-size: 11pt; }
        
        .wrapper { width: 6.5in; margin: auto; border: 1px solid transparent; padding: 20px; }
        
        /* Header Layout */
        .header-box { display: flex; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 5px; }
        .header-logos img { height: 60px; margin-right: 15px; }
        .header-text { flex-grow: 1; }
        .header-title { font-weight: bold; font-size: 14pt; margin: 0; }
        .header-sub { font-size: 10pt; margin: 0; font-weight: bold;}
        .form-code { text-align: right; font-style: italic; font-weight: bold; font-size: 10pt; margin-top: 5px; margin-bottom: 20px;}

        /* Typography */
        h3 { text-align: center; text-decoration: underline; margin-bottom: 20px; font-size: 14pt;}
        .field-row { margin-bottom: 10px; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; font-weight: bold; }
        
        /* Checkbox styling mockup */
        .chk { display: inline-block; width: 15px; height: 12px; border-bottom: 1px solid #000; text-align: center; font-weight: bold; font-size: 12pt; line-height: 10px;}
        .indent-1 { margin-left: 30px; }
        .indent-2 { margin-left: 60px; }
        .blank-line { display: inline-block; border-bottom: 1px solid #000; min-width: 200px; padding-left: 5px; }
        .blank-line-long { display: inline-block; border-bottom: 1px solid #000; width: 80%; padding-left: 5px; }

        /* Signatures */
        .sig-box { margin-top: 25px; }
        .sig-line { border-bottom: 1px solid #000; width: 250px; text-align: center; font-weight: bold; padding-bottom: 2px; }
        .sig-label { font-size: 10pt; text-align: center; width: 250px; }

        /* Official Travel Cert Box */
        .cert-box { margin-top: 30px; }
        .cert-title { text-decoration: underline; font-weight: bold; }
        
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="wrapper">
        <div class="header-box">
            <div class="header-logos">
                <img src="./../../img/bagong-pilipinas.webp" alt="BP Logo">
                <img src="./../../img/marsu.png" alt="MarSU Logo">
            </div>
            <div class="header-text">
                <div class="header-title">MARINDUQUE STATE UNIVERSITY</div>
                <div class="header-sub">HUMAN RESOURCE MANAGEMENT UNIT</div>
            </div>
        </div>
        <div class="form-code">MarSU-HRMU Form 2024-1</div>

        <div class="field-row bold">
            Date: <span class="blank-line"><?= date("F d, Y", strtotime($ps['ps_date'])) ?></span>
        </div>

        <h3>PASS SLIP</h3>

        <div class="field-row bold">1. Issued for</div>
        <div class="field-row indent-1 bold">
            <span class="chk"><?= $chk_official ?></span> A. Official Activity<br>
            <span class="chk"><?= $chk_personal ?></span> B. Personal Reason
        </div>

        <div class="field-row bold">
            2. To: <span class="blank-line-long" style="width: 90%;"><?= $ps['destination'] ?></span>
        </div>

        <div class="field-row bold" style="margin-top: 15px;">
            3. You are hereby authorized to proceed to: <span class="underline">(Place to be visited)</span>
            <br>
            <span class="blank-line-long" style="width: 100%;"><?= $ps['destination'] ?></span><br>
            for the purpose as indicated:
        </div>
        
        <div class="field-row bold">(Check appropriate Purpose)</div>
        
        <div class="field-row indent-1 bold">A. 
            1 <span class="chk"><?= $chk_coord ?></span> To coordinate with 
            <span class="blank-line" style="width: 250px;"><?= $spec_coord ?></span>
        </div>
        <div class="field-row indent-2 bold">
            <span class="chk"><?= $chk_meet ?></span> To attend Meeting/conference 
            <span class="blank-line" style="width: 200px;"><?= $spec_meet ?></span>
        </div>
        <div class="field-row indent-2 bold">
            <span class="chk"><?= $chk_doc ?></span> To Secure Documents 
            <span class="blank-line" style="width: 240px;"><?= $spec_doc ?></span>
        </div>
        <div class="field-row indent-2 bold mt-2">
            <span class="chk"><?= $chk_others ?></span> Others 
            <span class="blank-line" style="width: 320px;"><?= $spec_others ?></span>
        </div>

        <div class="field-row indent-1 bold" style="margin-top: 10px;">B. 
            1 <span class="chk"><?= $chk_pers_matter ?></span> To attend Personal Matter 
            <span class="blank-line" style="width: 210px;"><?= $spec_pers ?></span>
        </div>

        <div class="field-row bold" style="margin-top: 20px;">
            4. Time of Departure <span class="blank-line"><?= $time_out ?></span>
        </div>
        <div class="field-row bold">
            5. Time of Return <span class="blank-line" style="width: 220px;"><?= $time_in ?></span>
        </div>

        <div class="field-row bold" style="margin-top: 20px;">6. Requested by:</div>
        <div class="sig-box indent-1">
            <div class="sig-line"><?= $emp_name ?></div>
            <div class="sig-label bold">Signature over Printed Name</div>
        </div>

        <div class="field-row bold" style="margin-top: 20px;">7. Approved:</div>
        <div class="sig-box indent-1">
            <div class="sig-line" style="min-height: 20px;">
                </div>
            <div class="sig-label bold">Immediate Supervisor / Admin</div>
        </div>

        <div class="cert-box">
            <div class="field-row"><span class="bold">8. <span class="cert-title">For Official Travel:</span></span></div>
            <div class="field-row indent-1 bold" style="text-indent: 30px; text-align: justify; padding-right: 20px;">
                I certify that the above official/employee personally appeared in this office on the date and time indicated above
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 40px; padding-right: 20px;">
                <div>
                    <div class="sig-line"></div>
                    <div class="sig-label bold">Name and Signature</div>
                    
                    <div class="sig-line" style="margin-top: 20px;"></div>
                    <div class="sig-label bold">Designation</div>
                </div>
            </div>
        </div>

    </div>

    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px;">
        <button onclick="window.print()" style="padding: 12px 25px; background: #800000; color: #fff; border: none; cursor: pointer; border-radius: 50px; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
            🖨️ Print Form
        </button>
    </div>

</body>
</html>