<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$search = sanitize($_GET['q'] ?? '');
$cat = sanitize($_GET['cat'] ?? '');
$status = sanitize($_GET['status'] ?? '');
$page = max(1,(int)($_GET['page']??1));
$limit = 10; // បង្ហាញ ១០ ក្បាលក្នុងមួយទំព័រ
$offset = ($page-1)*$limit;

$where = "WHERE 1=1";
if($search) $where .= " AND (title LIKE '%$search%' OR author LIKE '%$search%')";
if($cat) $where .= " AND category='$cat'";
if($status) $where .= " AND status='$status'";

$total = $conn->query("SELECT COUNT(*) as c FROM books $where")->fetch_assoc()['c'];
$pages = ceil($total/$limit);
$books = $conn->query("SELECT * FROM books $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
$cats = $conn->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category!='' ORDER BY category");

?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog - វិទ្យាស្ថានបច្ចេកវិទ្យាកំពង់ឈើទាល</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;600;700&family=Siemreap&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Moul&display=swap" rel="stylesheet">
    
    <style>
        :root {
            font-family: 'Siemreap', 'Segoe UI', sans-serif;
            --primary-color: #2c3e50;
            --accent-color: #d32f2f;
            --card-bg: #ffffff;
            --border-color: #eaeaea;
            --text-muted: #757575;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #fcfcfc;
        }

        /* ==========================================================================
           ១. រចនាបថ Menu Bar (Navigation Bar) - កែប្រែឱ្យដូចទំព័រដើម និងមាន Marquee
           ========================================================================== */
   .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 30px;
            background: #ffffff;
            border-bottom: 1px solid #eaeaea;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            
            /* កូដបន្ថែមដើម្បីធ្វើឱ្យ Menu នៅជាប់ខាងលើ */
            position: sticky;
            top: 0;
            z-index: 1000; /* បង្កើន z-index ឱ្យខ្ពស់ជាង Slide caption ឬ content ផ្សេងទៀត */
            
            gap: 20px;
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
        .nav-brand img {
            height: 55px;
            width: auto;
        }
        .brand-text {
            display: flex;
            flex-direction: column;
        }
        .brand-kh {
            font-family: 'Moul', cursive;
            font-size: 15px;
            color: #1a237e;
            font-weight: bold;
            line-height: 1.3;
        }
        .brand-en {
            font-size: 12px;
            color: #555;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* ផ្នែកអក្សររត់កណ្ដាល */
        .navbar-marquee {
            flex: 1;
            display: flex;
            align-items: center;
            background-color: #f5f5f5;
            padding: 6px 15px;
            border-radius: 30px;
            overflow: hidden;
            margin: 0 15px;
        }
        .navbar-marquee marquee {
            font-size: 14px;
            color: #d13ca4;
            font-weight: bold;
        }
        .navbar-marquee i {
            margin-right: 8px;
            color: #3f51b5;
        }

        /* រុញ Menu និងឧបករណ៍ទៅខាងស្ដាំបង្អស់ */
        .nav-right-container {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-shrink: 0;
        }
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 20px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .nav-item a {
            text-decoration: none;
            color: #555;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 4px;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        .nav-item.active a, .nav-item a:hover {
            color: #3f51b5;
            border-bottom-color: #3f51b5;
        }
        .nav-right-tools {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
        }
        .lang-switch {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .lang-switch img {
            width: 24px;
            height: 16px;
            object-fit: cover;
            border-radius: 2px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            cursor: pointer;
        }

        .menu-toggle {
            display: none;
            font-size: 22px;
            color: #1a237e;
            background: none;
            border: none;
            cursor: pointer;
            outline: none;
        }

        /* ==========================================================================
           ២. រចនាបថ Content Body Container
           ========================================================================== */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 25px 20px;
            box-sizing: border-box;
        }
        .header-wrapper {
            display: flex; 
            justify-content: space-between; 
            align-items: flex-end; 
            flex-wrap: wrap; 
            gap: 15px; 
            margin-bottom: 25px;
        }
        .page-title {
            font-family: 'Siemreap', 'Segoe UI', sans-serif;
            font-size: 28px;
            color: #000;
            border-left: 4px solid #3f51b5;
            padding-left: 15px;
            margin: 0 0 5px 0;
        }
        .search-container {
            position: relative;
            width: 100%;
            max-width: 400px;
        }
        .search-container input {
            width: 100%;
            padding: 11px 15px 11px 40px;
            border: 1px solid var(--border-color);
            border-radius: 30px;
            font-size: 14px;
            box-sizing: border-box;
            outline: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }
        .search-container input:focus {
            border-color: #3f51b5;
            box-shadow: 0 2px 10px rgba(63, 81, 181, 0.1);
        }
        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        /* Grid ប្រព័ន្ធបង្ហាញសៀវភៅ */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-top: 25px;
        }
        
        .book-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .book-cover-wrap {
            position: relative;
            width: 100%;
            padding-top: 135%; 
            background: #f9f9f9;
            border-bottom: 1px solid #eee;
        }
        .book-cover-wrap img {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover;
        }
        .book-info-box {
            padding: 12px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .book-title {
            font-size: 14px;
            font-weight: 500;
            color: #333;
            line-height: 1.4;
            padding: 2px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 43px;
        }
        .book-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: auto;
        }
        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .dot-available { background-color: #2ecc71; }
        .dot-unavailable { background-color: #e74c3c; }

        /* ==========================================================================
           ៣. Responsive Design
           ========================================================================== */
        @media (max-width: 1024px) {
            .books-grid { grid-template-columns: repeat(4, 1fr); }
        }

        @media (max-width: 992px) {
            .top-navbar { padding: 10px 20px; }
            .navbar-marquee { display: none; } /* លាក់អក្សររត់លើអេក្រង់តូច */
            .menu-toggle { display: block; order: 3; }
            .nav-right-container { margin-left: auto; }
            .nav-right-tools { order: 2; margin-right: 15px; }
            .nav-menu {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%; left: 0; width: 100%;
                background: #ffffff;
                border-top: 1px solid #eaeaea;
                box-shadow: 0 8px 15px rgba(0,0,0,0.05);
                padding: 15px 20px;
                box-sizing: border-box;
                gap: 10px;
                align-items: flex-start;
            }
            .nav-menu.show { display: flex; }
            .nav-item { width: 100%; }
            .nav-item a { width: 100%; padding: 10px 0; border-bottom: 1px solid #f9f9f9; }
            .books-grid { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 600px) {
            .header-wrapper { flex-direction: column; align-items: flex-start; }
            .search-container { max-width: 100%; }
            .books-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .page-title { font-size: 22px; }
            .brand-kh { font-size: 13px; }
            .brand-en { font-size: 10px; }
            .nav-brand img { height: 42px; }
        }
    </style>
</head>
<body>

<nav class="top-navbar">
    <div class="nav-brand">
        <img src="uploads/logo.jpg" alt="Logo" onerror="this.src='https://via.placeholder.com/55?text=Logo'">
        <div class="brand-text">
            <span class="brand-kh">វិទ្យាស្ថានបច្ចេកវិទ្យាកំពង់ឈើទាល</span>
            <span class="brand-en">Kampong Chheuteal Institute of Technology</span>
        </div>
    </div>

    <div class="navbar-marquee">
        <i class="fas fa-bullhorn"></i>
        <marquee behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();">
            &nbsp;សូមស្វាគមន៍មកកាន់បណ្ណាល័យ នៃវិទ្យាស្ថានបច្ចេកវិទ្យាកំពង់ឈើទាល! 📣 ត្រៀមខ្លួនសម្រាប់អនាគតដ៏ភ្លឺស្វាង! ក្លាយជាអ្នកជំនាញបច្ចេកទេសដែលកំពុងពេញនិយមនៅលើទីផ្សារការងារ ជាមួយវិទ្យាស្ថានបច្ចេកវិទ្យាកំពង់ឈើទាល! តើប្អូនៗ កំពុងស្វែងរកការសិក្សាជំនាញពិតប្រាកដដែលធានាឱកាសការងារមែនទេ? 🎓 ជ្រើសរើសសិក្សាលើជំនាញដែលវិទ្យាស្ថានបើកបណ្តុះបណ្តាល ទាំងថ្នាក់ បរិញ្ញាបត្ររង និងថ្នាក់បរិញ្ញាបត្រ ដូចជា ៖⚡ អនុភាពអគ្គិសនី 🐄 វិទ្យាសាស្ត្រសត្វ🌾 វិទ្យាសាស្ត្រដំណាំ 🏨 គ្រប់គ្រងទេសចរណ៍ និងសណ្ឋាគារ 💻 កុំព្យូទ័រពាណិជ្ជកម្ម និងជំនាញ 🔧 អេឡិចត្រូនិច ✨ អ្វីដែលប្អូននឹងទទួលបាន៖ 👉 ជំនាញវិជ្ជាជីវៈច្បាស់លាស់៖ សិក្សាពីគ្រូបង្រៀនមានបទពិសោធន៍។ 👉 ការសិក្សាផ្សារភ្ជាប់នឹងការងារ៖ អនុវត្តផ្ទាល់ដើម្បីពង្រឹងចំណេះដឹង។ 👉 ការច្នៃប្រឌិត និងដំណោះស្រាយបញ្ហា៖ បណ្តុះគំនិតថ្មីៗស្របតាមយុគសម័យបច្ចេកវិទ្យា។ 👉 ឱកាសការងារធំទូលាយ៖ មានលទ្ធភាពទទួលបានការងារភ្លាមៗ ជាមួយដៃគូររបស់វិទ្យាស្ថាន ក្រោយបញ្ចប់ការសិក្សា ។ កុំបង្អង់យូរ! ឆ្ពោះទៅកាន់ភាពជោគជ័យជាមួយជំនាញច្បាស់លាស់។ 📍 ព័ត៌មានលម្អិតទំនាក់ទំនង៖ ការិយាល័យសិក្សា និងកិច្ចការសិស្សនិស្សិត Telegram: https://t.me/+vkk0_ZdOwv42ZTFl 📞 0125 303 12 / 069 606 363 / 085 488 988
        </marquee>
    </div>

    <div class="nav-right-container">
        <button class="menu-toggle" id="mobile-menu-btn" aria-label="Toggle Menu">
            <i class="fas fa-bars"></i>
        </button>

        <ul class="nav-menu" id="nav-menu-list">
            <li class="nav-item">
                <a href="home.php"><i class="fas fa-home"></i> Home</a>
            </li>
            <li class="nav-item active">
                <a href="catalog.php"><i class="fas fa-book"></i> Books In Library</a>
            </li>
            <li class="nav-item">
                <a href="member/login.php"><i class="fas fa-sign-in-alt"></i> Sign In</a>
            </li>
        </ul>

        <div class="nav-right-tools">
            <div class="lang-switch">
                <img src="https://flagcdn.com/w20/kh.png" alt="Khmer" title="ភាសាខ្មែរ">
                <img src="https://flagcdn.com/w20/us.png" alt="English" title="English">
            </div>
        </div>
    </div>
</nav>

<div class="main-container">

    <div class="header-wrapper">
        <div>
            <h1 class="page-title">Books In Library</h1>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Find the physical book in KCIT Library</p>
        </div>
        
        <div class="search-container">
            <form method="GET" action="">
                <i class="fas fa-search"></i>
                <input type="text" name="q" placeholder="Search Title, Author, or ISBN..." value="<?= htmlspecialchars($search) ?>">
            </form>
        </div>
    </div>

    <?php if($books->num_rows > 0): ?>
        <div class="books-grid">
        <?php while($b=$books->fetch_assoc()): ?>
            <div class="book-card">
                <div class="book-cover-wrap">
                    <?php if($b['image']): ?>
                        <img src="uploads/books/<?= htmlspecialchars($b['image']) ?>" alt="<?= htmlspecialchars($b['title']) ?>">
                    <?php else: ?>
                        <div style="position:absolute; top:0; left:0; width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f5f5f5;">
                            <i class="fas fa-book" style="font-size: 40px; color: #ccc;"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="book-info-box">
                    <div class="book-title" title="<?= htmlspecialchars($b['title']) ?>">
                        <?= htmlspecialchars($b['title']) ?>
                    </div>
                    
                    <div class="book-meta">
                        <div style="display: flex; align-items: center;">
                            <span class="status-dot <?= $b['status']==='Available' ? 'dot-available' : 'dot-unavailable' ?>"></span>
                            <span><?= $b['available_qty'] ?>/<?= $b['available_qty'] ?></span>
                        </div>
                        <div style="font-size: 11px; font-weight: bold; color: #999;">
                            <?= htmlspecialchars($b['publish_year'] ?? '2023') ?>
                        </div>
                    </div>
                    
                    <div style="margin-top: 10px;">
                        <?php if($b['status']==='Available' && $b['available_qty'] > 0): ?>
                            <a href="member/login.php?id=<?= $b['id'] ?>" 
                               style="display: block; width: 100%; text-align: center; font-size: 13px; font-weight: 600; color: #1a73e8; text-decoration: none; padding: 8px 0; border: 1px solid #1a73e8; border-radius: 4px; transition: background 0.2s; font-family: 'Siemreap', sans-serif;">
                                ខ្ចីសៀវភៅ
                            </a>
                        <?php else: ?>
                            <button style="width: 100%; font-size: 13px; font-weight: 600; padding: 8px 0; background: #ffffff; color: #b0b0b0; border: 1px solid #e0e0e0; border-radius: 4px; cursor: not-allowed; text-align: center; font-family: 'Siemreap', sans-serif;" disabled>
                                អស់ពីស្តុក
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        </div>

        <?php if($pages > 1): ?>
            <div style="margin-top: 30px; display: flex; justify-content: center; gap: 5px; flex-wrap: wrap;">
                <?php for($i=1; $i<=$pages; $i++): ?>
                    <a href="?page=<?=$i?>&q=<?=urlencode($search)?>&cat=<?=urlencode($cat)?>" 
                       style="padding: 8px 14px; border-radius: 4px; text-decoration: none; font-size: 13px; <?= $i==$page ? 'background:#3f51b5; color:white;' : 'background:#f8f9fa; color:#333; border:1px solid #ddd;' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div style="text-align: center; padding: 50px; color: var(--text-muted);">
            <i class="fas fa-search-minus" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
            <p style="font-family: 'Siemreap', sans-serif;">មិនមានសៀវភៅដែលអ្នកកំពុងស្វែងរកឡើយ។</p>
        </div>
    <?php endif; ?>

</div>

<script>
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
        var menu = document.getElementById('nav-menu-list');
        menu.classList.toggle('show');
        
        var icon = this.querySelector('i');
        if (menu.classList.contains('show')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-xmark');
        } else {
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-bars');
        }
    });
</script>
</body>
</html>