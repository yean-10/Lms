<?php
function startMemberLayout($title = 'Dashboard') {
    // ១. ទាញយកព័ត៌មានបន្ថែមពី Database ដើម្បីទទួលបានរូបភាពថ្មីៗជានិច្ច
    global $conn;
    $mid = $_SESSION['member_id'] ?? 0;
    $member_data = $conn->query("SELECT name, member_code, image FROM 
    members WHERE id=$mid")->fetch_assoc();

    $name = $member_data['name'] ?? 'Member';
    $code = $member_data['member_code'] ?? '';
    $image = $member_data['image'] ?? '';
    $page = basename($_SERVER['PHP_SELF'], '.php');

    $active_dash = ($page == 'dashboard') ? 'active' : '';
    $active_catalog = ($page == 'catalog') ? 'active' : '';
    $active_borrows = ($page == 'my_borrows') ? 'active' : '';
    $active_profile = ($page == 'profile') ? 'active' : '';

    // ២. រៀបចំការបង្ហាញរូបភាព (បើគ្មានរូប ឱ្យបង្ហាញអក្សរកាត់ឈ្មោះ)
    $avatar_html = "";
    if ($image && file_exists("../uploads/members/" . $image)) {
        $avatar_html = '<img src="../uploads/members/' . $image . '" 
        style="width:100%;height:100%;object-fit:cover;border-radius:10px;">';
    } else {
        $initial = mb_substr($name, 0, 1);
        $avatar_html = $initial;
    }

    echo <<<HTML
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title - Library</title>
    <link rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;600;700&family=Siemreap&display=swap"
rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Moul&display=swap" rel="stylesheet">

    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{--green:#76e0a0;
        --dark:#cbd4e2;
        --sidebar:#f8dce0;
        --card:rgba(144, 7, 7, 0.04);
        --border:rgba(0, 0, 0, 0.08);
        --text:black;
        --muted:black;
        --primary:black}
        body{ font-family: 'Siemreap', 'Segoe UI', sans-serif;
        background:#f7f0f1;color:var(--text);
        display:flex;min-height:100vh;}

    .sidebar {
    width: 185px; /* ប្តូរពី 240px ទៅ 260px ឬ 280px */
    background: var(--sidebar);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    position: fixed;
    height: 100vh;
    z-index: 100;
}
 .main {
    flex: 1;
    margin-left: 185px; /* ប្តូរឱ្យស្មើនឹងទំហំទទឹងរបស់ Sidebar ខាងលើ */
    display: flex;
    flex-direction: column;
}
        .sidebar-brand{padding:20px 16px;border-bottom:1px solid var(--border);display:flex;
        align-items:center;gap:12px}
        .brand-icon{width:40px;height:40px;background:linear-gradient(135deg,var(--green),#1d1de2);
        border-radius:12px;display:flex;align-items:center;justify-content:center;
        font-size:18px;color:#f0f8ff}
        .brand-text h2{font-family:  cursive; 
            font-size:15px;color:blue}
        .brand-text p{
            font-size:11px;color:red}
        .nav{flex:1;padding:12px 8px;overflow-y:auto}
        .nav-section{ font-family: 'Moul', cursive; 
            font-size:12px;text-transform:uppercase;letter-spacing:1.5px;
        color:var(--muted);padding:10px 8px 4px;}
.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px; /* បង្កើន padding ឆ្វេង-ស្តាំ ទៅ 16px ដើម្បីឱ្យដេញចូលក្នុងស្អាត */
    border-radius: 8px;
    color: black;
    text-decoration: none;
    font-size: 14px;
    margin-bottom: 2px;
    transition: .2s;
}
        .nav-item:hover{background:rgba(249, 246, 226, 0.85);color:black}
        .nav-item.active{background:rgba(255, 255, 255, 0.78);color:blue;
        border-left:3px solid var(--green);padding-left:9px}
        .nav-item i{width:16px;text-align:center}
        .sidebar-footer{padding:14px;border-top:1px solid var(--border)}
        .user-card{display:flex;align-items:center;gap:10px}
        .user-avatar{width:34px;height:34px;background:linear-gradient(135deg,var(--green),#1e8449);
        border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:14px;
        font-weight:700;flex-shrink:0;color:black}
        .user-info p{font-size:12px;color:black;font-weight:600}
        .user-info span{font-size:11px;color:var(--muted)}


        .topbar{  background:rgba(254, 209, 251, 0.9);backdrop-filter:blur(10px);
        border-bottom:1px solid var(--border);
        padding:0 24px;height:56px;display:flex;align-items:center;
        justify-content:space-between;position:sticky;top:0;z-index:90}
        .page-title{ font-family: 'Moul', cursive; 
        font-size:16px;}
        .topbar-logout{color:red;font-size:13px;text-decoration:none;display:flex;align-items:center;gap:6px;
        padding:7px 14px;background:rgba(210, 209, 209, 0.05);border-radius:8px;transition:.2s}
        .topbar-logout:hover{color:black;background:rgba(247, 63, 146, 0.7)}
        .content{padding:20px;flex:1}

        /* Stats & Books Grid Styles */
 .card{background:var(--card);
 border:0px solid var(--border);
 font-family: 'Siemreap', 'Segoe UI', sans-serif;
  border-radius:16px;
  overflow:hidden;}
  .card-header{font-family: 'Siemreap', 'Segoe UI', sans-serif;
  padding:16px 20px;
  border-bottom:2px solid var(--border);
  display:flex;
  align-items:center;
  justify-content:space-between;}
    .card-body {
    background: #ffffff;
    border-radius: 16px;display:flex;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: none; align-items:center;
    margin-bottom: 20px;
    padding:16px 20px;
  }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit,
         minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px; }
        .stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; 
        padding: 20px; 
        display: flex; align-items: center; gap: 16px; transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); border-color: rgba(39,174,96,0.3); }
        .stat-icon { width: 54px; height: 54px; border-radius: 14px;
        display: flex; font-family: 'Moul', cursive; 
        align-items: center; justify-content: center; font-size: 22px; }
        .stat-icon.blue { background: rgba(41, 128, 185, 0.2); color: #3498db; }
        .stat-icon.green { background: rgba(39, 174, 96, 0.2); color: var(--green); }
        .stat-icon.red { background: rgba(231, 76, 60, 0.2); color: #e74c3c; }
        .stat-icon.yellow { background: rgba(243, 156, 18, 0.2); color: #f1c40f; }
        .stat-info h3 { font-size: 24px; font-weight: 700; color: blue; }
        .stat-info p { font-size: 13px; color: var(--muted);font-family: 'Moul', cursive;  }

        .books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
         gap: 20px; margin-top: 20px; }
        .book-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; 
        overflow: hidden; transition: 0.3s; display: flex; flex-direction: column; }
        .book-card:hover { transform: translateY(-8px); border-color: rgba(17, 22, 19, 0.4);
         box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .book-cover { height: 260px; background: #ee89c1; display: flex; align-items: center; 
        justify-content: center; position: relative; overflow: hidden; }
        .book-cover img { width: 100%; height: 100%; object-fit: cover; }
        .book-info { padding: 16px; flex: 1; display: flex; flex-direction: column; }
        .book-info h4 { font-size: 14px; font-weight: 600; color: blue; margin-bottom: 6px; 
        line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .book-info p { font-size: 12px; color: var(--muted); }

        .form-control { width: 100%; padding: 10px 14px; background: rgba(255, 255, 255, 0.88); 
        border: 0px solid var(--border); border-radius: 10px; 
        color:blue; font-size: 14px; margin-top: 5px;
         transition: all 0.3s ease; outline: none; 
         box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
       font-family: 'Siemreap', 'Segoe UI', sans-serif;}

    .form-control:focus {
        border-color: #4a90e2;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        outline: none;
    }
    .btn1{font-family: 'Siemreap', 'Segoe UI', sans-serif;}
     select.form-control option { background: #f4f5f6; color: #9d0404; }
        .btn-primary { background: var(--green); color: black; border: none; padding: 10px 20px;
        font-family: 'Siemreap', 'Segoe UI', sans-serif;
         border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-primary:hover { background: #22ac5c; transform: translateY(-2px); }

        .badge { padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600; }
        .badge-success { background: rgba(44, 44, 36, 0.2); color: blue; }
        .badge-danger { background: rgba(231, 76, 60, 0.2); color: #e74c3c; }
        .badge-warning { background: rgba(243, 156, 18, 0.2); color: #151310; }

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
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-book-open"></i></div>
        <div class="brand-text">
            <h2>Library-KCIT</h2>
            <p>Member Portal</p>
        </div>
    </div>
    <nav class="nav">
        <div class="nav-section">Menu</div>
        <a href="dashboard.php" class="nav-item $active_dash"><i class="fas fa-home"></i> ផ្ទាំងព័ត៌មាន</a>
        <a href="catalog.php" class="nav-item $active_catalog"><i class="fas fa-book"></i> ម៉ឺនុយសៀវភៅ</a>
        <a href="my_borrows.php" class="nav-item $active_borrows"><i class="fas fa-bookmark"></i> ការខ្ចីរបស់ខ្ញុំ</a>
        <div class="nav-section">ប្រព័ន្ធ</div>
        <a href="profile.php" class="nav-item $active_profile"><i class="fas fa-user"></i> ព័ត៌មានខ្ញុំ</a>
        <a href="logout.php" class="nav-item" style="color:red;"><i class="fas fa-sign-out-alt"></i>
         ចាកចេញ</a>
    </nav>
    <div class="sidebar-footer">
        <a href="profile.php" class="user-card">
            <div class="user-avatar">
                $avatar_html
            </div>
            <div class="user-info">
                <p>$name</p>
                <span>$code</span>
            </div>
        </a>
    </div>
</aside>
<main class="main">
    <div class="topbar">
        <span class="page-title">$title</span>
        <a href="logout.php" class="topbar-logout">
            <i class="fas fa-sign-out-alt"></i> ចាកចេញ</a>
    </div>
    <div class="content">
HTML;
}

function endMemberLayout() {
    // ត្រួតពិនិត្យសារជោគជ័យពី Session
    $success_script = "";
    if (isset($_SESSION['success'])) {
        $msg = addslashes($_SESSION['success']);
        $success_script = "
        <script>
            Swal.fire({
                icon: 'success',
                title: 'ជោគជ័យ!',
                text: '$msg',
                timer: 3000,
                showConfirmButton: false,
                background: '#1a1d21',
                color: '#f60404',
                iconColor: '#27ae60'
            });
        </script>";
        unset($_SESSION['success']);
    }

    echo <<<HTML
    </div>
</main>
$success_script
</body>
</html>
HTML;
}
?>