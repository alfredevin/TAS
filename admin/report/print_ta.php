<?php
include './../../config.php';

if (!isset($_GET['id'])) {
    die("Invalid Request");
}
$ta_id = $_GET['id'];

// Kunin ang data mula sa database
$query = "SELECT t.*, e.first_name as app_fname, e.last_name as app_lname, e.position_name as app_pos 
          FROM ta_tbl t 
          LEFT JOIN employee_tbl e ON t.approver_id = e.employee_id 
          WHERE t.ta_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ta_id);
$stmt->execute();
$ta = $stmt->get_result()->fetch_assoc();

// Kunin ang listahan ng mga Travelers
$t_query = "SELECT e.first_name, e.last_name FROM ta_participants_tbl tp 
            JOIN employee_tbl e ON tp.employee_id = e.employee_id 
            WHERE tp.ta_id = ?"; // Siguraduhing tama ang column name ng foreign key mo
$t_stmt = $conn->prepare($t_query);
$t_stmt->bind_param("i", $ta_id);
$t_stmt->execute();
$travelers_res = $t_stmt->get_result();
$names = [];
while ($row = $travelers_res->fetch_assoc()) {
    $names[] = "MR. " . strtoupper($row['first_name'] . " " . $row['last_name']);
}

function formatDate($date)
{
    return date("F d, Y", strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: portrait;
            margin: 0.5in;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            color: #000;
            line-height: 1.15;
            font-size: 12pt;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 7.5in;
            margin: auto;
        }

        /* Exact Header Replication */
        .header-container {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .logos {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-bp {
            width: 85px;
            height: auto;
        }

        /* Bagong Pilipinas */
        .logo-marsu {
            width: 85px;
            height: auto;
        }

        /* MarSU Seal */
        .header-text {
            margin-left: 15px;
        }

        .uni-name {
            font-size: 20pt;
            font-weight: bold;
            margin: 0;
            color: #000;
            letter-spacing: 1px;
        }

        .campus-name {
            font-size: 13pt;
            font-weight: bold;
            margin: 0;
        }

        .red-line {
            border-top: 3px solid #800000;
            margin-top: 2px;
            width: 100%;
        }

        .office-title {
            font-weight: bold;
            margin-top: 30px;
            font-size: 13pt;
        }

        .memo-no {
            font-weight: bold;
            margin-top: 15px;
            text-decoration: underline;
            margin-bottom: 25px;
        }

        /* Meta table style */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .meta-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .meta-label {
            width: 100px;
        }

        .meta-value {
            font-weight: bold;
        }

        .meta-value-underline {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 300px;
        }

        .hr-thick {
            border-top: 2px solid #000;
            margin: 15px 0;
        }

        .body-content {
            text-align: justify;
            margin-top: 25px;
            font-size: 12pt;
            line-height: 1.5;
        }

        .indent {
            margin-left: 50px;
        }

        .signature-section {
            margin-top: 50px;
            margin-left: 45%;
            line-height: 1.2;
        }

        .approver-name {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 0;
            font-size: 13pt;
        }

        /* Certificate of Appearance Styling */
        .cert-section {
            margin-top: 40px;
            border-top: 1px solid #000;
            padding-top: 20px;
        }

        .cert-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            font-size: 13pt;
            margin-bottom: 15px;
            display: block;
        }

        .cert-body {
            text-align: justify;
            line-height: 1.4;
            margin-bottom: 20px;
        }

        .cert-footer-layout {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .cert-column {
            width: 45%;
            /* Para may pagitan sa gitna */
        }

        .blank-line {
            border-bottom: 1px solid #000 !important;
            height: 25px;
            /* Spacing ng bawat linya */
            margin-bottom: 8px;
        }

        .sig-label {
            text-align: center;
            font-size: 9pt;
            margin-top: 5px;
            line-height: 1.2;
        }


        /* Footer Replication */
        .footer {
            position: fixed;
            bottom: 0.4in;
            /* Taasan natin ng kaunti para hindi ma-clip ng printer */
            left: 0;
            right: 0;
            width: 7.5in;
            margin: 0 auto;
            /* I-center ang fixed element */
            border-top: 2px solid #800000 !important;
            /* Maroon line gaya ng nasa picture */
            padding-top: 8px;
            font-size: 9pt;
            color: #000;
            /* Pure black para malinaw sa print */
            background-color: white;
            /* Para hindi mag-overlap sa content */
        }

        /* Dagdagan ang padding ng wrapper para hindi mag-overlap ang text sa footer */
        .wrapper {
            width: 7.5in;
            margin: auto;
            padding-bottom: 1.5in;
            /* Space para sa fixed footer */
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="wrapper">
        <div class="header-container">
            <div class="logos">
                <img src="./../../img/bagong-pilipinas.webp" class="logo-bp">
                <img src="./../../img/marsu.png" class="logo-marsu">
            </div>
            <div class="header-text">
                <div class="uni-name">MARINDUQUE STATE UNIVERSITY</div>
                <div class="campus-name">SANTA CRUZ CAMPUS</div>
            </div>
        </div>
        <div class="red-line"></div>

        <div class="office-title">OFFICE OF THE CAMPUS DIRECTOR</div>
        <div class="memo-no">MEMORANDUM NO. <?= $ta['memo_no'] ?></div>

        <table class="meta-table">
            <tr>
                <td class="meta-label">DATE</td>
                <td style="width: 20px;">:</td>
                <td class="meta-value"><?= formatDate($ta['date_issued']) ?></td>
            </tr>
            <tr>
                <td class="meta-label">FROM</td>
                <td>:</td>
                <td class="meta-value">
                    Prof. DIOSDADO P. ZULUETA, DPA<br>
                    <span style="font-weight: normal;">SUC President III</span><br><br>
                    <span style="font-style: italic; font-weight: normal;">By the Authority of the University President:</span><br><br>
                    <?= strtoupper($ta['app_fname'] . " " . $ta['app_lname']) ?><br>
                    <span style="font-weight: normal;"><?= $ta['app_pos'] ?></span>
                </td>
            </tr>
            <tr>
                <td class="meta-label">TO</td>
                <td>:</td>
                <td class="meta-value">
                    <div  >
                        <?php echo implode("<br>", $names); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="meta-label">SUBJECT</td>
                <td>:</td>
                <td class="meta-value">LOCAL TRAVEL AUTHORITY</td>
            </tr>
        </table>

        <div class="hr-thick"></div>

        <div class="body-content">
            <p class=""> &nbsp; &nbsp; &nbsp; In the exigency of service, please be informed that you are hereby authorized to go to
                <span class="underline-bold"><?= $ta['destination'] ?></span> on
                <span class="underline-bold"><?= formatDate($ta['travel_date']) ?></span>, to
                <span class="underline-bold"><?= $ta['task'] ?></span>.
            </p>

            <p class="">&nbsp; &nbsp; &nbsp;You are expected to return to your official workstation upon completion of the said purposes, unless this office informs you of an extension due to other official business.</p>

            <p class="">For information and compliance.</p>
        </div>

        <div class="cert-section">
            <span class="cert-title">CERTIFICATE OF APPEARANCE</span>

            <div class="cert-body">
                <p style="text-indent: 50px;">
                    This is to certify that <b><u><span><?= implode(", ", $names) ?></span></u></b> personally appeared in this office to transact official business on <b><u><span><?= formatDate($ta['travel_date']) ?></span></u></b>.
                </p>
                <p>This certificate is issued with travel itinerary.</p>
            </div>

            <div class="cert-footer-layout">
                <div class="cert-column">
                    <div class="blank-line"></div>
                    <div class="blank-line"></div>
                    <div class="blank-line"></div>
                    <div class="blank-line"></div>
                </div>

                <div class="cert-column">
                    <div class="blank-line"></div>
                    <div class="blank-line"></div>
                    <div class="blank-line"></div>
                    <div class="blank-line"></div>
                    <div class="sig-label">

                        ________________________________________
                        <br>
                        (Signature Over Printed Name of Head/<br>
                        Representative of Office/Agency Visited)
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Container ng Footer */
            .footer {
                position: fixed;
                bottom: 0.3in;
                /* Distansya mula sa ibaba ng papel */
                left: 0;
                right: 0;
                width: 7.5in;
                /* Kapareho ng lapad ng iyong wrapper */
                margin: 0 auto;
                border-top: 2px solid #800000 !important;
                /* Maroon line replication */
                padding-top: 8px;
                background-color: white;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            /* Logic para sa paghati (Flexbox) */
            .footer-cols {
                display: flex;
                justify-content: space-between;
                /* Hahatiin ang content sa magkabilang dulo */
                align-items: flex-start;
            }

            .footer-left {
                text-align: left;
                font-size: 9pt;
                line-height: 1.4;
            }

            .footer-right {
                text-align: right;
                font-size: 9pt;
                line-height: 1.4;
            }
        </style>
        <div class="footer">
            <div class="footer-cols">
                <div class="footer-left">
                    📍 Matalaba, Santa Cruz, Marinduque 4902 Philippines<br>
                    ✉️ cdstacruz@marsu.edu.ph
                </div>

                <div class="footer-right">
                    🌐 www.marsu.edu.ph<br>
                    📞 (042) 753 0052
                </div>
            </div>
        </div>
    </div>

    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #012970; color: #fff; border: none; cursor: pointer; border-radius: 5px;">Print Document</button>
    </div>

</body>

</html>