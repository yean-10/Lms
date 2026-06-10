<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ចុះឈ្មោះសមាជិក - Library System</title>
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
    background:linear-gradient(135deg,#f3a3f3 0%,#b59df8 50%,#f8b59d 100%);
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
     ២. រចនាបថ Register Container & Card
     ========================================================================== */
  .register-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
  }

  .register-card{
    font-family: 'Siemreap', 'Segoe UI', sans-serif;
    background:rgba(247, 243, 243, 1);
    backdrop-filter:blur(20px);
    border:1px solid rgba(0, 0, 0, 0.95);
    border-radius:24px;
    padding:40px;
    width:100%;
    max-width:550px;
    box-shadow:0 25px 50px rgba(0, 0, 0, 0.4);
    animation:fadeIn .5s ease;
  }
  @keyframes fadeIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
  
  .header{text-align:center;margin-bottom:30px}
  .header i{ font-size: 40px; color: #05bd52; margin-bottom: 15px; text-shadow: 0 0 15px rgba(8, 54, 28, 0.4); }
  .header h1{color:black;font-size:24px;font-family: 'Moul', cursive;}
  .header p{color:green;font-size:14px;margin-top:5px}

  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
  .full-width { grid-column: span 2; }
  .form-group{margin-bottom:18px}
  label{display:block;color:blue;font-size:13px; margin-bottom:8px;font-weight:500}
  
  .input-wrap { font-family: 'Siemreap', 'Segoe UI', sans-serif; position:relative; }
  .input-wrap i.ic {
    position:absolute;left:14px;top:50%;
    transform:translateY(-50%);
    color:rgba(250, 10, 34, 0.76);
    font-size:15px;
  }
  
  input, select, textarea {
    font-family: 'Siemreap', 'Segoe UI', sans-serif;
    width:100%;
    padding:12px 14px 12px 42px;
    background:rgba(226, 66, 66, 0.07);
    border:1px solid rgba(237, 190, 125, 0.87);
    border-radius:12px;
    color:black;
    font-size:14px;
    transition:all .3s;
  }
  select option { background: #fefdf4; color: black; font-family: 'Siemreap', sans-serif;}
  textarea { padding-left: 15px; height: 80px; font-family: 'Siemreap', sans-serif;}
  
  input:focus, select:focus, textarea:focus {
    outline:none;
    border-color:#27ae60;
    background:rgba(212, 184, 184, 0.86);
    box-shadow: 0 0 10px rgba(174, 234, 199, 0.1);
  }

  .btn-register{
    font-family: 'Siemreap', 'Segoe UI', sans-serif;
    width:100%;
    padding:14px;
    background:linear-gradient(135deg,#27ae60,#1e8449);
    border:none;
    border-radius:12px;
    color:black;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:all .3s;
    margin-top:10px;
  }
  .btn-register:hover{ transform:translateY(-2px); box-shadow:0 8px 24px rgba(39,174,96,0.4); }
  .back-to-login { text-align:center; margin-top:20px; color:rgba(0, 0, 0, 0.5); font-size:13px; }
  .back-to-login a { color:red; text-decoration:none; font-weight:600; }

  /* ==========================================================================
     ៣. Responsive Web Design សម្រាប់ Navbar និង Card
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
    .form-grid { grid-template-columns: 1fr; gap: 0; }
    .full-width { grid-column: span 1; }
    .register-card { padding: 25px 20px; }
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
            <li class="nav-item"><a href="login.php"><i class="fas fa-sign-in-alt"></i> Sign In</a></li>
        </ul>

        <div class="nav-right-tools">
            <div class="lang-switch">
                <img src="https://flagcdn.com/w20/kh.png" alt="Khmer" title="ភាសាខ្មែរ">
                <img src="https://flagcdn.com/w20/us.png" alt="English" title="English">
            </div>
        </div>
    </div>
</nav>

<div class="register-container">
    <div class="register-card">
      <div class="header">
        <i class="fas fa-user-plus"></i>
        <h1>ចុះឈ្មោះសមាជិក</h1>
        <p>បង្កើតគណនីថ្មីដើម្បីប្រើប្រាស់បណ្ណាល័យ</p>
      </div>

      <form action="save_member.php" method="POST">
        <div class="form-grid">

          <div class="form-group full-width">
            <label for="role">តួនាទី / Role</label>
            <div class="input-wrap">
              <i class="fas fa-user-shield ic"></i>
              <select name="role" id="role" required>
                <option value="">-- សូមជ្រើសរើសតួនាទី --</option>
                <option value="student">និស្សិត (Student)</option>
                <option value="teacher">គ្រូបង្រៀន (Teacher)</option>
              </select>
            </div>
          </div>

          <div class="form-group full-width">
            <label for="member_code">លេខសម្គាល់សមាជិក (ID និស្សិត/គ្រូ)</label>
            <div class="input-wrap">
              <i class="fas fa-id-card ic"></i>
              <input type="text" name="member_code" id="member_code" required placeholder="ឧទាហរណ៍: 25021401">
            </div>
          </div>

          <div class="form-group full-width">
            <label>ឈ្មោះពេញ</label>
            <div class="input-wrap">
              <i class="fas fa-user-tag ic"></i>
              <input type="text" name="name" placeholder="បញ្ជាក់ឈ្មោះពិតរបស់អ្នក" required>
            </div>
          </div>

          <div class="form-group">
            <label>ឈ្មោះអ្នកប្រើ (Username)</label>
            <div class="input-wrap">
              <i class="fas fa-user ic"></i>
              <input type="text" name="username" placeholder="Username" required>
            </div>
          </div>

          <div class="form-group">
            <label>លេខសម្ងាត់</label>
            <div class="input-wrap">
              <i class="fas fa-lock ic"></i>
              <input type="password" name="password" placeholder="••••••••" required>
            </div>
          </div>

          <div class="form-group full-width">
            <label>អ៊ីមែល</label>
            <div class="input-wrap">
              <i class="fas fa-envelope ic"></i>
              <input type="email" name="email" placeholder="example@mail.com" required>
            </div>
          </div>

          <div class="form-group">
            <label>ភេទ</label>
            <div class="input-wrap">
              <i class="fas fa-venus-mars ic"></i>
              <select name="gender">
                <option value="Male">ប្រុស</option>
                <option value="Female">ស្រី</option>
                <option value="Other">ផ្សេងៗ</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>លេខទូរស័ព្ទ</label>
            <div class="input-wrap">
              <i class="fas fa-phone ic"></i>
              <input type="text" name="phone" placeholder="012xxxxxx">
            </div>
          </div>

          <div class="form-group full-width">
            <label>អាសយដ្ឋាន</label>
            <textarea name="address" placeholder="រៀបរាប់ពីទីលំនៅបច្ចុប្បន្ន..."></textarea>
          </div>

        </div>

        <button type="submit" class="btn-register">
          <i class="fas fa-check-circle"></i> បង្កើតគណនី
        </button>
      </form>

      <div class="back-to-login" style="color:black;">
        មានគណនីរួចហើយ? <a href="login.php"> ត្រឡប់ទៅចូលប្រព័ន្ធវិញ</a>
      </div>
    </div>
</div>

<script>
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