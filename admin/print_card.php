<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);

// ទាញទិន្នន័យសមាជិក និងព័ត៌មានបន្ថែមពីតារាង students
$query = "SELECT m.*, s.birth, s.latang 
          FROM members m 
          LEFT JOIN students s ON m.member_code = s.id_student 
          WHERE m.id = $id LIMIT 1";

$result = $conn->query($query);
if ($result->num_rows == 0) {
    die("រកមិនឃើញទិន្នន័យសមាជិកឡើយ!");
}
$m = $result->fetch_assoc();

// កំណត់ការបកប្រែភេទជាភាសាខ្មែរ
$gender_kh = ($m['gender'] == 'Male') ? 'ប្រុស' : (($m['gender'] == 'Female') ? 'ស្រី' : 'ផ្សេងៗ');
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <title>កាតសម្គាល់ខ្លួនសមាជិក - <?= htmlspecialchars($m['name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Moul&family=Siemreap&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Siemreap', sans-serif;
            margin: 0;
            padding: 10px;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 90vh;
        }
        
        /* ទំហំកាតស្តង់ដារបង្រួមទាក់ទាញ */
        .card-container {
            width: 310px;
            height: 430px; /* កំណត់កម្ពស់ល្មមដើម្បីកុំឱ្យហែកចន្លោះ */
            background: #ffffff;
            border: 4px double #0d2570;
            border-radius: 12px;
            padding: 12px 8px;
            box-sizing: border-box;
            position: relative;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* ផ្ទៃ Background Logo ព្រាលៗនៅចំកណ្តាលកាត */
        .card-container::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 220px;
            height: 220px;
            background: url('../uploads/logo.jpg') no-repeat center; 
            background-size: contain;
            opacity: 0.15;
            z-index: 1;
        }

        /* រៀបចំ Header ឱ្យមាន Logo នៅខាងឆ្វេង និងអក្សរនៅកណ្តាល */
        .header-wrap {
            display: flex;
            align-items: center;
            border-bottom: 2px dotted #ff0000;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        .logo-box {
            width: 65px;
            height: 65px;
            margin-right: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .header-text {
            flex: 1;
            text-align: center;
            padding-right: 15px; /* បង្កើនតុល្យភាពឱ្យអក្សរចំកណ្តាលល្អ */
        }
        .country-title {
            font-family: 'Moul', cursive;
            font-size: 10.5px;
            color: #0d2570;
            margin: 0 0 4px 0; /* កាត់បន្ថយ margin ដែលធ្វើឱ្យហែក */
            line-height: 1.3;
        }
        .institute-title {text-align: left;
            font-family: 'Moul', cursive;
            font-size: 11px;
            color: #1b7e3b;
            margin: 0 0 1px 0; /* កាត់បន្ថយចន្លោះកុំឱ្យធ្លាក់ពេក */
        }
        .institute-eng {text-align: left;
            font-size: 9.5px;
            font-weight: bold;
            color: #0d2570;
            margin: 0;
        }

        /* ផ្នែកចំណងជើងកាត */
        .card-title-area {
            text-align: center;
            margin: 5px 0 8px 0;
        }
        .card-title {
            font-family: 'Moul', cursive;
            font-size: 15px;
            color: #ff0000;
            margin: 0;
            text-shadow: 0.3px 0.3px #000;
        }
        .info-id {
            font-weight: bold;
            font-size: 12px;
            color: #0d2570;
            margin-top: 2px;
        }

        /* ផ្នែកព័ត៌មានប្រកិតគ្នាស្អាត */
        .info-body {
            position: relative;
            z-index: 2;
            padding-left: 5px;
            margin-bottom: 5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 6px; /* រក្សាគម្លាតជួរឱ្យល្មមសមសួន */
            font-size: 12.5px;
            line-height: 1.4;
        }
        .info-label {
            width: 115px;
            color: #333;
        }
        .info-value {
            flex: 1;
            color: #000;
            font-weight: bold;
        }

        /* ផ្នែកបាតកាត (រូបថត និងហត្ថលេខា) */
        .footer-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            position: relative;
            z-index: 2;
            margin-top: auto; /* រុញផ្នែក footer ទៅបាតកាតជានិច្ច */
        }
        .photo-box {
            width: 90px;
            height: 105px;
            border: 1px solid #333;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 10px;
            color: #666;
            background: #fafafa;
            margin-bottom: 3px;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .signature-section {
            text-align: center;
            font-size: 9.5px;
            color: #000;
            padding-right: 5px;
        }
        .date-text {
            font-style: italic;
            margin-bottom: 4px; /* កាត់បន្ថយគម្លាតស៊ីញ៉េ */
        }
        .manager-title {
            font-family: 'Moul', cursive;
            font-size: 10px;
            margin: 0 0 45px 0; /* បង្កើតចន្លោះទំនេរ 45px សម្រាប់ចុះហត្ថលេខា */
        }
        .manager-name {
            font-family: 'Moul', cursive;
            font-size: 10px; text-align: right;
            color: #ff0000;
            margin: 0;
        }
        .expiry-text {
            font-size: 9.5px;
            color: #ff0000;
            font-weight: bold;
            margin-top: 2px;
        }

        /* លាក់ប៊ូតុងពេលព្រីន */
        @media print {
            body {
                background: none;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            .card-container {
                box-shadow: none;
                border: 4px double #0d2570;
                page-break-inside: avoid;
            }
            .no-print {
                display: none;
            }
        }
        .btn-trigger-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #05bd52;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Siemreap', sans-serif;
            font-size: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 10;
        }
    </style>
</head>
<body>

    <button class="btn-trigger-print no-print" onclick="window.print()">
        <i class="fas fa-print"></i> ចុចទីនេះដើម្បីបោះពុម្ព (Print)
    </button>

    <div class="card-container">
        <div>
            <div class="header-wrap">
                <div class="logo-box">
                    <img src="../uploads/logo.jpg" onerror="this.src='https://via.placeholder.com/55?text=Logo';" alt="Logo">
                </div>
                <div class="header-text">
                    <p class="country-title">ព្រះរាជាណាចក្រកម្ពុជា</p>
                    <p class="country-title" style="font-size: 8.5px;">ជាតិ សាសនា ព្រះមហាក្សត្រ</p>
                    <p class="institute-title">វិទ្យាស្ថានបច្គេកវិទ្យាកំពង់ឈើទាល</p>
                    <p class="institute-eng">Kampong Chheuteal Institute of Technology</p>
                </div>
            </div>

            <div class="card-title-area">
                <div class="card-title">ប័ណ្ណសម្គាល់ខ្លួនសមាជិក</div>
                <div class="info-id">អត្តលេខ: <?= htmlspecialchars($m['member_code']) ?></div>
            </div>

            <div class="info-body">
                <div class="info-row">
                    <div class="info-label">- គោត្តនាម នាម :</div>
                    <div class="info-value"><?= htmlspecialchars($m['name']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">&nbsp;&nbsp;អក្សរឡាតាំង :</div>
                    <div class="info-value"><?= htmlspecialchars($m['latang'] ?? 'LY SIYHA') ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">- ភេទ :</div>
                    <div class="info-value"><?= $gender_kh ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">- ថ្ងៃខែឆ្នាំកំណើត :</div>
                    <div class="info-value"><?= htmlspecialchars($m['birth'] ?? '១៦ / កញ្ញា / ២០០៧') ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">- មុខជំនាញ/ថ្នាក់ :</div>
                    <div class="info-value"><?= htmlspecialchars($m['class'] ?? 'N/A') ?></div>
                </div>
            </div>
        </div>

        <div class="footer-section">
            <div>
                <div class="photo-box">
                    <?php if(!empty($m['image'])): ?>
                        <img src="../uploads/members/<?= htmlspecialchars($m['image']) ?>" alt="Photo">
                    <?php else: ?>
                        Photo<br>3x4
                    <?php endif; ?>
                </div>
                <div class="expiry-text">ផុតកំណត់: 31/12/2027</div>
            </div>
            
            <div class="signature-section">
                <div class="date-text">កំពង់ឈើទាល, ថ្ងៃទី <?= date('d') ?> ខែ <?= date('m') ?> ឆ្នាំ <?= date('Y') ?></div>
                <p class="manager-title">នាយកវិទ្យាស្ថាន</p>
                <p class="manager-name">បណ្ឌិត ប៉ិច សៀង</p>
            </div>
        </div>
    </div>

</body>
</html>