<?php
// បើកដំណើរការ Session ប្រសិនបើមិនទាន់បានបើក
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';
require_once '../includes/auth.php';

// ប្រសិនបើធ្លាប់បាន Login រួចហើយ (ទោះជា Admin ឬ Member) ឱ្យរុញទៅ Dashboard ភ្លាម
if (isset($_SESSION['admin_id']) || isset($_SESSION['member_id'])) { 
    header('Location: dashboard.php'); 
    exit; 
}

$error = '';
$username_val = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = function_exists('sanitize') ? sanitize($_POST['username'] ?? '') : htmlspecialchars(trim($_POST['username'] ?? ''));
    $username_val = $_POST['username'] ?? ''; 
    $password = $_POST['password'] ?? '';
    
    // =======================================================
    // ជំហានទី១៖ ពិនិត្យមើលនៅក្នុងតារាង `admin` មុនគេ
    // =======================================================
    $stmt_admin = $conn->prepare("SELECT id, password, full_name FROM admin WHERE username = ?");
    $stmt_admin->bind_param("s", $username);
    $stmt_admin->execute();
    $admin_result = $stmt_admin->get_result()->fetch_assoc();
    
    if ($admin_result) {
        // ផ្ទៀងផ្ទាត់លេខសម្ងាត់សម្រាប់ Admin (Plain Text យោងតាមកូដចាស់របស់បង)
        if ($password === $admin_result['password']) {
            $_SESSION['admin_id'] = $admin_result['id'];
            $_SESSION['admin_name'] = $admin_result['full_name'];
            $_SESSION['user_type'] = 'admin'; // សម្គាល់ប្រភេទអ្នកប្រើប្រាស់
            
            // ធ្វើបច្ចុប្បន្នភាពកាលបរិច្ឆេទចូលប្រើចុងក្រោយរបស់ Admin
            $conn->query("UPDATE admin SET last_login = NOW() WHERE id = {$admin_result['id']}");
            
            header('Location: ../admin/dashboard.php'); // ឬរុញទៅកាន់ ../admin/dashboard.php តាមរចនាសម្ព័ន្ធបង
            exit;
        } else {
            $error = 'ឈ្មោះអ្នកប្រើ ឬ លេខសម្ងាត់មិនត្រឹមត្រូវ!';
        }
    } else {
        // =======================================================
        // ជំហានទី២៖ បើរកមិនឃើញក្នុងតារាង Admin ទេ មកស្វែងរកក្នុងតារាង `members` ម្តង
        // =======================================================
        $stmt_member = $conn->prepare("SELECT id, password, name, member_code, role FROM members WHERE username = ?");
        $stmt_member->bind_param("s", $username);
        $stmt_member->execute();
        $member_result = $stmt_member->get_result()->fetch_assoc();
        
        if ($member_result && password_verify($password, $member_result['password'])) {
            $_SESSION['member_id'] = $member_result['id'];
            $_SESSION['member_name'] = $member_result['name'];
            $_SESSION['member_code'] = $member_result['id_student']; 
            $_SESSION['member_role'] = $member_result['role']; // 'student' ឬ 'teacher'      
            $_SESSION['user_type'] = 'member';
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'ឈ្មោះអ្នកប្រើ ឬ លេខសម្ងាត់មិនត្រឹមត្រូវ!';
        }
        $stmt_member->close();
    }
    $stmt_admin->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - KCIT Library System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;600;700&family=Siemreap&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Moul&display=swap" rel="stylesheet">

<style>
  *{margin:0;padding:0;box-sizing:border-box}
  
  body{ 
    font-family: 'Siemreap', 'Segoe UI', sans-serif; 
    min-height:100vh;
    background:linear-gradient(135deg,#84ADFA 0%,#F0F2EB 50%,#B0AF94 100%);
    display: flex;
    flex-direction: column;
  }

  /* ==========================================================================
     ១. រចនាបថ Menu Bar (Navigation Bar)
     ========================================================================== */
  .top-navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 30px;
    background: #ffffff;
    border-bottom: 1px solid #eaeaea;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    position: sticky;
    top: 0;
    z-index: 1000;
    gap: 20px;
    width: 100%;
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
  /* Menu List */
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
  }
  .nav-item.active a, .nav-item a:hover {
    color: #3f51b5;
    border-bottom-color: #3f51b5;
  }
  .nav-right-tools {
    display: flex;
    align-items: center;
    gap: 15px;
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
    font-size: 24px;
    color: #1a237e;
    background: none;
    border: none;
    outline: none;
    cursor: pointer;
  }

  /* ==========================================================================
     ២. រចនាបថ Login Box Content
     ========================================================================== */
  .login-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }
  .login-card{
    background:rgba(254, 254, 254, 0.65);backdrop-filter:blur(20px);
    border:1px solid rgba(255, 255, 255, 0.5);border-radius:24px;padding:48px 40px;
    width:420px;box-shadow:0 25px 50px rgba(0,0,0,0.15);
    animation:fadeIn .5s ease
  }
  @keyframes fadeIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
  
  .logo{text-align:center;margin-bottom:32px}
  .logo-icon{
    width:72px;height:72px;background:linear-gradient(135deg,#27ae60,#1e8449);
    border-radius:20px;display:inline-flex;align-items:center;justify-content:center;font-size:32px;
    color:white;margin-bottom:16px;box-shadow:0 8px 24px rgba(39,174,96,0.2)
  }
  .logo h1{color:blue;font-size:22px;font-family: 'Moul', cursive; }
  .logo p{color:rgba(245, 3, 3, 0.82);font-size:14px;margin-top:4px}
  
  .form-group{margin-bottom:20px}
  label{display:block;color:rgba(7, 4, 213, 0.91);font-size:13px;margin-bottom:8px;font-weight:bold}
  
  .input-wrap{position:relative}
  .input-wrap i.ic{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(6, 15, 194, 0.73);font-size:15px}

  input[type=text],input[type=password]{
    width:100%;padding:13px 42px;
    background:rgb(252, 252, 252);border:2px solid rgba(19, 14, 14, 0.12);
    border-radius:12px;color:black;font-size:14px;transition:all .3s;font-weight:bold;
  }
  input:focus{outline:none;border-color:#27ae60;background:rgba(238, 239, 223, 0.69)}
  
  .toggle-pwd{position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:rgba(6, 15, 194, 0.73)}
  
  .btn-login{
    width:100%;padding:14px;background:linear-gradient(135deg,#7de2a7,#74dfa1);
    font-family: 'Siemreap', 'Segoe UI', sans-serif;
    border:none;border-radius:12px;color:black;font-size:15px;
    font-weight:600;cursor:pointer;transition:all .3s;margin-top:8px
  }
  .btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(39,174,96,0.4)}
  
  .error{
    background:rgba(231,76,60,0.15);border:1px solid rgba(231,76,60,0.4);color:#e74c3c;
    padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:13px;display:flex;align-items:center;gap:8px
  }
  
  .footer-links{text-align:center;margin-top:24px;color:rgba(0, 0, 0, 0.95);font-size:13px;display:flex;flex-direction:column;gap:10px}
  .footer-links a{color:#27ae60;text-decoration:none;font-weight:600}
  .footer-links a:hover{text-decoration:underline}
  .divider{height:1px;background:rgba(0, 0, 0, 0.08);margin:10px 0}

  /* ==========================================================================
     ៣. Responsive Web Design សម្រាប់ Navbar
     ========================================================================== */
  @media (max-width: 992px) {
    .top-navbar { padding: 10px 20px; }
    .navbar-marquee { display: none; }
    .menu-toggle { display: block; order: 3; }
    .nav-right-container { margin-left: auto; }
    .nav-right-tools { order: 2; margin-right: 15px; }
    .nav-menu {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 100%; 
        left: 0; 
        width: 100%;
        background: #ffffff;
        box-shadow: 0 10px 15px rgba(0,0,0,0.05);
        border-top: 1px solid #eaeaea;
        padding: 15px 20px;
        box-sizing: border-box;
        gap: 10px;
        align-items: flex-start;
    }
    .nav-menu.show { display: flex; }
    .nav-item { width: 100%; }
    .nav-item a { width: 100%; padding: 10px 0; border-bottom: 1px solid #f5f5f5; }
  }

  @media (max-width: 768px) {
    .brand-kh { font-size: 13px; }
    .brand-en { font-size: 10px; }
    .nav-brand img { height: 45px; }
    .login-card { width: 100%; padding: 30px 20px; }
  }
</style>
</head>
<body>

<nav class="top-navbar">
    <div class="nav-brand">
        <img src="../uploads/logo.jpg" alt="Logo" onerror="this.src='https://via.placeholder.com/55?text=Logo'">
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
            <li class="nav-item"><a href="../home.php"><i class="fas fa-home"></i> Home</a></li>
            <li class="nav-item"><a href="../catalog.php"><i class="fas fa-book"></i> Books In Library</a></li>
            <li class="nav-item active"><a href="login.php"><i class="fas fa-sign-in-alt"></i> Sign In</a></li>
        </ul>

        <div class="nav-right-tools">
            <div class="lang-switch">
                <img src="https://flagcdn.com/w20/kh.png" alt="Khmer" title="ភាសាខ្មែរ">
                <img src="https://flagcdn.com/w20/us.png" alt="English" title="English">
            </div>
        </div>
    </div>
</nav>

<div class="login-container">
    <div class="login-card">
      <div class="logo">
        <div class="logo-icon"><i class="fas fa-book-open"></i></div>
        <h1>ប្រព័ន្ធបណ្ណាល័យ-KCIT</h1>
        <p>Library Management System</p>
      </div>
      
      <?php if($error): ?>
      <div class="error"><i class="fas fa-exclamation-circle"></i><?= $error ?></div>
      <?php endif; ?>
      
      <form method="POST">
        <div class="form-group">
          <label>ឈ្មោះអ្នកប្រើ</label>
          <div class="input-wrap">
            <i class="fas fa-user ic"></i>
            <input type="text" name="username" placeholder="Username" 
            value="<?= htmlspecialchars($username_val) ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label>លេខសម្ងាត់</label>
          <div class="input-wrap">
            <i class="fas fa-lock ic"></i>
            <input type="password" name="password" id="pwd" placeholder="••••••••" required>
            <i class="fas fa-eye toggle-pwd" onclick="togglePwd()"></i>
          </div>
        </div>
        <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> ចូលប្រព័ន្ធ</button>
      </form>

      <div class="footer-links">
        <div>បង្កើតគណនីថ្មី? <a href="register_member.php">សូមចុះឈ្មោះនៅទីនេះ</a></div>
      </div>
    </div>
</div>

<script>
// Toggle បង្ហាញ/លាក់ លេខសម្ងាត់
function togglePwd(){
  const p=document.getElementById('pwd'),
  i=document.querySelector('.toggle-pwd');
  p.type=p.type==='password'?'text':'password';
  i.className='fas fa-eye'+(p.type==='text'?'-slash':'')+' toggle-pwd';
}

// បើក/បិទ Menu នៅលើទូរស័ព្ទ (Mobile Menu Toggle)
const mobileMenuBtn = document.getElementById('mobile-menu-btn');
const navMenuList = document.getElementById('nav-menu-list');

if (mobileMenuBtn && navMenuList) {
    mobileMenuBtn.addEventListener('click', function() {
        navMenuList.classList.toggle('show');
    });
}
</script>
</body>
</html>