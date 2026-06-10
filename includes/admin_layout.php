<?php
// Shared admin layout - call startAdminLayout($title) at top of page
function startAdminLayout($title = 'Dashboard') {
    global $conn; // ប្រើប្រាស់ database connection
    
    // ទាញយកព័ត៌មាន Admin ពី Database ដើម្បីបង្ហាញរូបភាពថ្មីៗជានិច្ច
    $admin_id = $_SESSION['admin_id'] ?? 0;
    $admin_data = $conn->query("SELECT full_name, image FROM admin WHERE id=$admin_id")->fetch_assoc();

    $admin_name = $admin_data['name'] ?? ($_SESSION['admin_name'] ?? 'Admin');
    $admin_image = $admin_data['image'] ?? '';
    
    $page = basename($_SERVER['PHP_SELF'], '.php');

    // បង្កើត variable សម្រាប់ឆែកមើលថា តើ Menu មួយណាដែលត្រូវ Active
    $dash_act = ($page == 'dashboard') ? 'active' : '';
    $book_act = ($page == 'books' || $page == 'add_book') ? 'active' : '';
    $memb_act = ($page == 'members' || $page == 'members1')   ? 'active' : '';
    $borr_act = ($page == 'borrows' || $page == 'action_borrow')   ? 'active' : '';
    $repr_act = ($page == 'reports')   ? 'active' : '';
    $active_profile = ($page == 'profile' || $page == 'admin_profile') ? 'active' : '';
    
    // បន្ថែមលក្ខខណ្ឌ Active សម្រាប់ប្រព័ន្ធគ្រប់គ្រងការ Post (Add/Edit ក៏ភ្លឺដែរ)
    $post_act = (in_array($page, ['admin_posts', 'admin_add_post', 'admin_edit_post'])) ? 'active' : '';
    
    // រៀបចំការបង្ហាញរូបភាព (បើគ្មានរូប ឱ្យបង្ហាញ Icon)
    $avatar_html = "";
    if ($admin_image && file_exists("../uploads/admin/" . $admin_image)) {
        $avatar_html = '<img src="../uploads/admin/' . $admin_image . '" 
        style="width:100%;height:100%;object-fit:cover;">';
    } else {
        $avatar_html = '<i class="fas fa-user-shield"></i>';
    }

    echo <<<HTML
<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>$title - Library Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;600;700&family=Siemreap&display=swap"
rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Moul&display=swap" 
rel="stylesheet">

<style>
  *{margin:0;padding:0;box-sizing:border-box}
  :root{
    --primary:#e94560;
    --dark:#180161;
    --sidebar:#d8d6e0;
    --accent:#0f3460;
    --text:black;
    --muted:red;
    --card:rgba(193, 26, 26, 0.05);
    --border:rgba(1, 4, 26, 0.1);
    --success:green;
    --warning:#f39c12;
    --info:yellow;
  }
  body{ font-family: 'Siemreap', 'Segoe UI', sans-serif;
  background: #ffffff; color:var(--text);display:flex; min-height:100vh}
  
  /* Sidebar */
  .sidebar{width:260px;background:var(--sidebar);border-right:1px solid var(--border);
  display:flex;
  flex-direction:column;position:fixed;height:100vh;z-index:1000;transition:.3s}
  .sidebar-brand{padding:24px 20px;border-bottom:1px solid var(--border);display:flex;
  align-items:center;gap:12px}
  
  .brand-icon{width:44px;height:44px;background:linear-gradient(135deg,var(--primary),var(--accent));
  border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
  .brand-text h2{font-size:15px;font-weight:700;color:black}
  .brand-text p{font-size:11px;color:var(--muted)}

  .nav{flex:1;padding:16px 12px;overflow-y:auto}
  .nav-section{font-size:10px;text-transform:uppercase;letter-spacing:1.5px;
  color:var(--muted);padding:12px 8px 6px;font-weight:600}

  .nav-item{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:10px;
  color:black;text-decoration:none;font-size:13.5px;margin-bottom:2px;transition:.2s}
  .nav-item:hover{background:rgba(255,255,255,0.07);color:blue; }
  .nav-item.active{background:linear-gradient(135deg,rgb(252, 196, 206),rgba(184, 204, 229, 0.3));
  color:blue;border:1px solid rgba(233,69,96,0.3); }
  .nav-item i{width:18px;text-align:center;font-size:14px;color:blue;}
  
  .sidebar-footer{padding:16px;border-top:1px solid var(--border)}
  .user-card{display:flex;align-items:center;gap:10px;text-decoration:none;}
  .user-avatar{width:36px;height:36px;background:linear-gradient(135deg,var(--primary),var(--accent));
  border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;overflow:hidden;}
  .user-info{flex:1;min-width:0}
  .user-info p{font-size:12px;color:black;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .user-info span{font-size:11px;color:var(--muted)}
  
  .btn-logout{color:var(--muted);font-size:14px;background:none;border:none;cursor:pointer;transition:.2s}
  .btn-logout:hover{color:var(--primary)}

  /* Main Content */
  .main{flex:1;margin-left:260px;display:flex;flex-direction:column;min-height:100vh;width: 100%;}
  .topbar{background:rgba(224, 223, 223, 0.8);
  backdrop-filter:blur(10px);border-bottom:1px solid var(--border);
  padding:0 24px;height:60px;display:flex;align-items:center;
  justify-content:space-between;position:sticky;top:0;z-index:90}
  .page-title{font-size:16px;font-family: 'Moul', cursive; }
  .content{padding:24px;flex:1}

  /* Components */
  .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px}
  .stat-card{background:var(--card);border:1px solid var(--border);border-radius:16px;
  padding:20px;display:flex;align-items:center;gap:16px;transition:.3s}
  .stat-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:22px}
  .stat-icon.red{background:rgba(233,69,96,0.15);color:var(--primary)}
  .stat-icon.blue{background:rgba(41,128,185,0.15);color:#2980b9}
  .stat-icon.green{background:rgba(39,174,96,0.15);color:#27ae60}
  .stat-icon.yellow{background:rgba(243,156,18,0.15);color:#f39c12}
  .stat-icon.purple{background:rgba(155,89,182,0.15);color:#9b59b6}

  .card{background:var(--card);border:1px solid var(--border);
  font-family: 'Siemreap', 'Segoe UI', sans-serif;
  border-radius:16px;
  overflow:hidden}

  .card-header{ font-family: 'Siemreap', 'Segoe UI', sans-serif;
  padding:16px 20px;
  border-bottom:1px solid var(--border);
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

  table{width:100%;border-collapse:collapse}
  th{padding:12px;text-align:left;font-size:14px;color: red;
  text-transform:uppercase;color:var(--muted);border-bottom:1px solid var(--border)}
  td{padding:12px;font-size:13px;
  border-bottom:1px solid rgba(227, 37, 43, 0.05)}
  
  .badge{padding:4px 10px; border-radius:99px; font-size:11px; font-weight:600; 
   font-family: 'Siemreap', 'Segoe UI', sans-serif;}
  .badge-success{background:rgba(39,174,96,0.2);color:#27ae60;
   font-family: 'Siemreap', 'Segoe UI', sans-serif;}
  .badge-danger{background:rgba(233,69,96,0.2);color:var(--primary);
   font-family: 'Siemreap', 'Segoe UI', sans-serif;}
  .badge-warning{background:rgba(161, 158, 154, 0.2);color:blue;
   font-family: 'Siemreap', 'Segoe UI', sans-serif;}

  .book-thumb {
      width: 45px;
      height: 60px;
      object-fit: cover;
      border-radius: 4px;
      border: 1px solid var(--border);
      display: block;
      margin: 0 auto;
  }
  .img-preview {
      width: 100px;
      height: 140px;
      object-fit: cover;
      border-radius: 6px;
      margin-top: 8px;
      border: 1px dashed var(--muted);
  }

  /* ==========================================================================
     ការកែសម្រួលបន្ថែមសម្រាប់ប៊ូតុង Menu លើទូរស័ព្ទ (Responsive Sidebar & Button)
     ========================================================================== */
  #menuBtn {
    display: none; /* លាក់ប៊ូតុង Menu លើអេក្រង់កុំព្យូទ័រ */
  }

  /* ស្រទាប់ខ្មៅងងឹតនៅពេលបើក Menu លើទូរស័ព្ទ (Overlay) */
  .sidebar-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.4);
    z-index: 999;
    display: none;
  }

  @media(max-width:768px){
    #menuBtn {
      display: block !important; /* បង្ហាញប៊ូតុង Menu លើទូរស័ព្ទ */
    }
    .sidebar{
      transform:translateX(-100%); /* លាក់ Sidebar ទៅឆ្វេង */
    }
    .sidebar.open{
      transform:translateX(0); /* បង្ហាញ Sidebar មកវិញនៅពេលមាន Class .open */
    }
    .main{
      margin-left:0 !important; /* ពង្រីក Main content ឱ្យពេញអេក្រង់ទូរស័ព្ទ */
    }
    .sidebar-overlay.show {
      display: block; /* បង្ហាញស្រទាប់ Overlay ពេលទាញ Menu មក */
    }
  }

      .search-wrap { display: flex; align-items: center; background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 0 12px; transition: all 0.3s; }
    .search-wrap:focus-within { border-color: #3498db; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1); background: #fff; }
    .search-wrap i { color: #95a5a6; margin-right: 8px; }
    .search-input {   font-family: 'Siemreap', 'Segoe UI', sans-serif;
      border: none; background: transparent; padding: 8px 0; outline: none; width: 200px; font-size: 14px; }
    .form-select-custom { padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px; background: #f8f9fa;
     font-family: 'Siemreap', 'Segoe UI', sans-serif;
      font-size: 14px; cursor: pointer; outline: none; }
    .btn-action {  font-family: 'Moul', cursive; 
     display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; 
      font-size: 14px; text-decoration: none; transition: all 0.2s; border: none; cursor: pointer; }
    .btn-add { background: #12e719; color: blue !important; }
    .btn-add:hover { background: #52f48b; transform: translateY(-1px); }
    .btn-search { background: #2c3e50; color: white; padding: 8px 12px; }
    .badge { padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; display: inline-block; }
    .btn-sm-action { padding: 6px 12px; font-size: 12px; 
    font-family: 'Siemreap', 'Segoe UI', sans-serif;
    border-radius: 6px; text-decoration: none; color: white !important; 
    font-weight: bold; display: inline-flex; align-items: center; gap: 4px; 
    border: none; cursor: pointer; }
    .btn-approve { background-color: #00b894; }
    .btn-approve:hover { background-color: #009475; }
    .btn-reject { background-color: #e67e22; }
    .btn-reject:hover { background-color: #d35400; }
    .btn-delete { background-color: #d63031; }
    .btn-delete:hover { background-color: #b32424; }
    #printSection { display: none; }
    @media print {
        body * { visibility: hidden; }
        #printSection, #printSection * { visibility: visible; }
        #printSection { display: block !important; position: absolute; left: 50%; top: 20px; transform: translateX(-50%); width: 80mm; background: #fff; padding: 15px; color: #000; font-family: 'Khmer OS Battambang', 'Segoe UI', sans-serif; font-size: 12px; }
        .receipt-title { text-align: center; font-weight: bold; font-size: 15px; margin-bottom: 3px; }
        .receipt-header { text-align: center; font-size: 11px; margin-bottom: 15px; border-bottom: 1px dashed #000; padding-bottom: 8px; }
        .receipt-row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .receipt-total { border-top: 1px dashed #000; margin-top: 10px; padding-top: 8px; font-weight: bold; font-size: 13px; }
        .receipt-footer { text-align: center; margin-top: 15px; font-size: 10px; border-top: 1px dashed #000; padding-top: 8px; }
        .qr-container { text-align: center; margin: 15px 0; }
        .qr-container img { width: 140px; height: 140px; display: inline-block; }
    }

 .form-control {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px 15px;
     font-family: 'Siemreap', 'Segoe UI', sans-serif;
        font-size: 14px;
        background-color: #ffffff;
        margin-top: 5px;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: #4a90e2;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(34, 62, 95, 0.1);
        outline: none;
    }
       /* រចនាប័ទ្មប៊ូតុង */
    .btn-custom {font-family: 'Siemreap', 'Segoe UI', sans-serif;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        border: none;
    }

    .button1 {font-family: 'Siemreap', 'Segoe UI', sans-serif;
    border: none;
    color: black;
    padding: 10px 15px; /* កែសម្រួល padding ឱ្យសមល្មម */
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
    margin: 4px 2px;
    transition-duration: 0.4s;
    cursor: pointer;
  }

  .btn-secondary { 
    background: var(--green); color: red; border: 1px solid #ff3636; 
    padding: 10px 0px; 
    border-radius: 10px; font-weight: 30; cursor: pointer; display: flex;
    align-items: center; gap: 8px; transition: 0.3s; justify-content: center; 
  }
  .btn-secondary:hover { background: #f5baba; transform: translateY(-2px); }

  .btn-success1 { 
    background: var(--green); color: blue; border: 1px solid #103def; 
    padding: 10px 15px; justify-content: center;
    border-radius: 10px; font-weight: 60; cursor: pointer; display: flex;
    align-items: center; gap: 8px; transition: 0.3s; 
  }
  .btn-success1:hover { background: #44f950; transform: translateY(-2px); }

  /* ==========================================================================
     បន្ថែមរចនាប័ទ្មសម្រាប់ជំនួយ Responsive លើទូរស័ព្ទ
     ========================================================================== */
  .table-responsive {
    width: 100%;
    overflow-x: auto; /* បង្កើតរបារអូសទៅឆ្វេងស្តាំស្វ័យប្រវត្តលើទូរស័ព្ទ */
    -webkit-overflow-scrolling: touch;
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap; /* ការពារកុំឱ្យខ្លឹមសារក្នុង Cell ធ្លាក់ជួររញ៉េរញ៉ៃ */
  }

  .search-container {
    display: flex; 
    gap: 10px; 
    align-items: center;
    flex-wrap: wrap; /* ធ្លាក់ជួរស្អាតនៅលើអេក្រង់តូច */
  }

  @media (max-width: 576px) {
    .card-header {
      flex-direction: column;
      align-items: flex-start ;
      gap: 10px ;
    }
    .search-container, .search-container form {
      width: 100%;
    }
    .search-wrap, .search-input {
      flex: 1;
      width: 100% ;
    }
    .btn-success1 {
      width: 100%;
      justify-content: center;
    }
  }

    .form-control-custom { font-family: 'Siemreap', 'Segoe UI', sans-serif;
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #dcdde1;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
        background: #fdfdfd;
    }

    .form-control-custom:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        outline: none;
        background: #fff;
    }
    .btn-save {font-family: 'Siemreap', 'Segoe UI', sans-serif;
        background: #27ae60;
        color: black;
        padding: 10px 25px;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-save:hover {
        background: #219150;
        transform: translateY(-2px);
    }

  .profile-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 10px 10px;
        font-family: 'Siemreap', 'Segoe UI', sans-serif;
    }
    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 1.3fr;
        gap: 30px;
        align-items: start;
    }
    .card-custom {
        background: #ffffff;
        border-radius: 20px;
        padding: 40px 30px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.05);
        text-align: center;
    }
    .card-custom-header {
        text-align: left;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        padding-bottom: 15px;
        margin-bottom: 25px;
    }
    .card-custom-header h3 {
        font-size: 18px;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .avatar-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 25px;
    }
    .preview-box {
        width: 140px;
        height: 140px;
        border-radius: 50%; /* ប្តូរជាវង់មូលដើម្បីឱ្យមើលទៅលេចធ្លោបែបប្រវត្តិរូបពិតៗ */
        background: #f8f9fa;
        border: 4px solid #fff;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.3s;
    }
    .camera-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: #e94560;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 3px solid #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        transition: 0.2s;
    }
    .camera-btn:hover {
        transform: scale(1.1);
        background: #d2334c;
    }
    .form-group-custom {
        text-align: left;
        margin-bottom: 20px;
    }
    .form-group-custom label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
    }
    .form-input-custom {font-family: 'Siemreap', 'Segoe UI', sans-serif;
        width: 100%;
        padding: 12px 16px;
        background: #f8f9fa;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 14px;
        color: #333;
        transition: all 0.3s ease;
        outline: none;
    }
    .form-input-custom:focus {
        border-color: #e94560;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(233, 69, 96, 0.1);
    }
    .form-input-custom:disabled {
        background: #edf2f7;
        color: #a0aec0;
        cursor: not-allowed;
    }
    .btn-save {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #e94560, #ba2d44);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 6px 18px rgba(233, 69, 96, 0.3);
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(233, 69, 96, 0.4);
    }
    .alert-custom {
        padding: 14px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .alert-success-custom {
        background: #e6f7ed;
        color: #1e7e34;
        border: 1px solid #c3e6cb;
    }
    .alert-danger-custom {
        background: #fdf2f2;
        color: #9b1c1c;
        border: 1px solid #f8b4b4;
    }
      /* រចនាប័ទ្ម Card និងពុម្ពអក្សរ */
    .borrow-card {
        max-width: 80%;
        margin: 15px auto;
        background: #f5f0f0;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        font-family: 'Siemreap', 'Segoe UI', sans-serif;
        border: none;
    }

    .borrow-header {
        background: #f8f9fa;
        padding: 20px 25px;
        border-bottom: 1px solid #eee;
        border-radius: 15px 15px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .borrow-header h3 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.3rem;
        
    }

    /* រចនាប័ទ្ម Input និង Select */
    .form-group label {font-family: 'Siemreap', 'Segoe UI', sans-serif;
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #1804f1;
        font-size: 0.95rem;
    }

    .form-control-custom {font-family: 'Siemreap', 'Segoe UI', sans-serif;
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #dcdde1;
        border-radius: 10px;
        font-size: 14px;
        background: #fdfdfd;
        transition: all 0.3s;
        box-sizing: border-box; /* ការពារកុំឱ្យរីកទំហំលើស parent */
    }

    .form-control-custom:focus {
        border-color: #3498db;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        outline: none;
    }

    /* រចនាប័ទ្ម Info Box */
    .info-box {
        background: rgba(243, 156, 18, 0.08);
        border: 1px dashed rgba(243, 156, 18, 0.4);
        border-radius: 10px;
        padding: 15px;
        color: #d35400;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* រចនាប័ទ្មប៊ូតុង */
    .btn-group-custom {font-family: 'Siemreap', 'Segoe UI', sans-serif;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 25px;
        border-top: 1px solid #eee;
        padding-top: 20px;
    }

    .btn-submit {font-family: 'Siemreap', 'Segoe UI', sans-serif;
        background: #3498db;
        color: black;
        padding: 12px 30px;
        border: none;
        border-radius: 10px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-submit:hover {
        background: #2980b9;
        transform: translateY(-2px);
    }

    .btn-back {
        background: #fff;
        color: #e74c3c;
        padding: 12px 30px;
        border: 1px solid #e74c3c;
        border-radius: 10px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.3s;
        text-align: center;
    }

    .btn-back:hover {
        background: #e74c3c;
        color: white;
    }

        /* ==========================================================================
       កែសម្រួល Container ដើម្បីរុញ Card មកចំកណ្តាល និងបំបាត់លំហទំនេរខាងស្តាំ
       ========================================================================== */
    .return-container {
        font-family: 'Siemreap', 'Segoe UI', sans-serif;
        width: 100%;
        display: flex;
        justify-content: center; /* រុញកាតមកចំកណ្តាលផ្ទាំងខាងស្តាំ */
        padding: 10px 0;
    }

    .custom-card {
        width: 100%;
        max-width: 900px; /* កំណត់ទទឹងកាតឱ្យសមល្មមមើលទៅមានរបៀប */
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        overflow: hidden;
        border: none;
    }

    .custom-card-header {
        background: linear-gradient(135deg, #cfd8ff 0%, #ddb8fd 100%);
        color: blue;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .custom-card-header h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
    }

    /* កែសម្រួលផ្នែកផ្ទុក Form ខាងក្នុង កុំឱ្យជាន់ជាមួយ Style display: flex របស់ Layout ចាស់ */
    .custom-card .custom-card-body {
        background: #ffffff;
        display: block !important; /* បិទ display: flex ចាស់ចោល */
        padding: 20px;
    }

    /* រៀបចំប្រអប់បង្ហាញព័ត៌មានឱ្យស្អាត និងលាតពេញទទឹងកាត */
    .info-box {
        background: #f8fafc;
        border-left: 5px solid #667eea;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 10px;
        width: 100%;
    }

    .info-item {
        display: flex;
        margin-bottom: 4px;
        border-bottom: 1px dashed #e2e8f0;
        padding-bottom: 2px;
    }

    .info-label {
        width: 120px;
        color: #64748b;
        font-weight: 600;
    }

    .info-value {
        color: #1e293b;
        font-weight: 700;
    }

    .form-group {
        margin-bottom: 15px;
        width: 100%;
    }

    .form-label {
        font-weight: 600;
        display: block;
        margin-bottom: 8px;
        color: #475569;
    }

    .custom-input {
        width: 100%;
        border-radius: 8px;
        padding: 12px;
        border: 2px solid #e2e8f0;
        transition: all 0.3s;
        box-sizing: border-box;
    }

    .custom-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-group {
        width: 100%;
        display: flex;
        gap: 15px;
        margin-top: 25px;
        border-top: 1px solid #eee;
        padding-top: 20px;
    }

    .btn-custom {
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: 0.3s;
        border: none;
        cursor: pointer;
    }

    .btn-save { 
        font-family: 'Siemreap', 'Segoe UI', sans-serif;
        background: #1a73e8; /* ប្តូរពណ៌ប៊ូតុងបញ្ជាក់ឱ្យដិតស្អាត */
        color: white; 
        flex: 2; 
        justify-content: center; 
    }
    .btn-save:hover { background: #0056b3; }

    .btn-cancel { background: #f1f5f9; color: #f30404; flex: 1; 
    justify-content: center; border: 1px solid #ddd; }
    .btn-cancel:hover { background: #e2e8f0; }
    
    .back-link {
        color: rgba(119, 3, 3, 0.8);
        text-decoration: none;
        font-size: 1rem;
    }
    .back-link:hover { color: red; }

    @media (max-width: 768px) {
        .custom-card {
            margin: 10px;
        }
        .btn-group {
            flex-direction: column-reverse; /* លើទូរស័ព្ទឱ្យប៊ូតុងបោះបង់នៅក្រោម */
        }
    }


</style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="fas fa-book-open"></i></div>
    <div class="brand-text">
      <h2>Library Management System-KCIT</h2>
      <p>Admin Panel</p>
    </div>
  </div>
  <nav class="nav">
    <div class="nav-section">Menu</div>
    <a href="dashboard.php" class="nav-item $dash_act">
      <i class="fas fa-tachometer-alt"></i> ផ្ទាំងព័ត៌មាន
    </a>

    <div class="nav-section">ការគ្រប់គ្រង</div>
    <a href="books.php" class="nav-item $book_act">
      <i class="fas fa-book"></i> គ្រប់គ្រងសៀវភៅ
    </a>
    <a href="members1.php" class="nav-item $memb_act">
      <i class="fas fa-users"></i> គ្រប់គ្រងសមាជិក
    </a>
 
       <a href="action_borrow.php" class="nav-item $borr_act">
      <i class="fas fa-hand-holding-heart"></i> សកម្មភាពការខ្ចី/សង
    </a>

    <a href="reports.php" class="nav-item $repr_act">
      <i class="fas fa-chart-bar"></i> របាយការណ៍
    </a>

<a href="admin_posts.php" class="nav-item $post_act">
      <i class="fas fa-newspaper"></i> គ្រប់គ្រងការ Post
    </a>

    <div class="nav-section">ប្រព័ន្ធ</div>
    <a href="admin_profile.php" class="nav-item $active_profile">
      <i class="fas fa-user"></i> ព័ត៌មានខ្ញុំ</a>
   
    <a href="../member/logout.php" class="nav-item" style="color:red;">
      <i class="fas fa-sign-out-alt"></i> ចាកចេញ
    </a>
  </nav>
<div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar">
          $avatar_html
      </div>
      <div class="user-info">
        <p>$admin_name</p>
        <span>Administrator</span>
      </div>
      <a href="../member/logout.php" class="btn-logout" title="Logout">
        <i class="fas fa-power-off"></i></a>
    </div>
  </div>
</aside>
<main class="main">
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button type="button" style="background:none; border:none; color:blue; font-size:20px; 
      cursor:pointer; padding: 5px;" id="menuBtn">
        <i class="fas fa-bars"></i>
      </button>
      <span class="page-title">$title</span>
    </div>
  </div>
  <div class="content">
HTML;
}

function endAdminLayout() {
    echo <<<HTML
  </div>
</main>
<script>
  const menuBtn = document.getElementById('menuBtn');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');

  // Logic សម្រាប់ចុចបើក និងបិទ Menu លើទូរស័ព្ទ
  if (menuBtn && sidebar && overlay) {
    menuBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      sidebar.classList.toggle('open');
      overlay.classList.toggle('show');
    });

    // ចុចលើផ្ទៃងងឹត (Overlay) ដើម្បីបិទ Menu ទៅវិញ
    overlay.addEventListener('click', function() {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    });
  }
  
  // Auto-dismiss alerts if any
  setTimeout(()=>{ 
      document.querySelectorAll('.alert').forEach(a => a.style.display='none'); 
  }, 4000);
</script>
</body>
</html>
HTML;
}
?>