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
    display:flex;
    align-items:center;
    justify-content:center;
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
  
  .input-wrap { font-family: 'Siemreap', 'Segoe UI', sans-serif;
    position:relative;
}
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
  
  /* បន្ថែមស្ទីលសម្រាប់សារផ្តល់ដំណឹងតូចៗ */
  .status-msg { font-size: 11px; margin-top: 4px; display: block; }
</style>
</head>
<body>

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
        <span id="search-status" class="status-msg"></span>
      </div>

      <div class="form-group full-width">
        <label>ឈ្មោះពេញ</label>
        <div class="input-wrap">
          <i class="fas fa-user-tag ic"></i>
          <input type="text" name="name" id="name" placeholder="បញ្ជាក់ឈ្មោះពិតរបស់អ្នក" required>
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
          <select name="gender" id="gender">
            <option value="Male">ប្រុស</option>
            <option value="Female">ស្រី</option>
            <option value="Other">ផ្សេងៗ</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>ជំនាញ (Major)</label>
        <div class="input-wrap">
          <i class="fas fa-graduation-cap ic"></i>
          <input type="text" name="major" id="major" placeholder="ជំនាញសិក្សា">
        </div>
      </div>

      <div class="form-group full-width">
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
    មានគណនីរួចហើយ? <a href="login.php">ត្រឡប់ទៅចូលប្រព័ន្ធវិញ</a>
  </div>
</div>

<script>
document.getElementById('member_code').addEventListener('input', function() {
    let idStudent = this.value.trim();
    let role = document.getElementById('role').value;
    let statusSpan = document.getElementById('search-status');

    // ដំណើរការតែនៅពេលដែលជ្រើសរើសតួនាទីជា "student" និងមានវាយលេខសម្គាល់
    if (role === 'student' && idStudent.length > 0) {
        statusSpan.style.color = 'orange';
        statusSpan.innerText = 'កំពុងស្វែងរកទិន្នន័យ...';

        // ហៅទៅកាន់ API fetch_student.php
        fetch('fetch_student.php?id_student=' + encodeURIComponent(idStudent))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // បំពេញទិន្នន័យចូល Form ស្វ័យប្រវត្តិ
                    document.getElementById('name').value = data.name;
                    document.getElementById('major').value = data.major;
                    
                    // កំណត់តម្លៃ ភេទ (ប្រុស/ស្រី)
                    let genderSelect = document.getElementById('gender');
                    if(data.sex === 'Male' || data.sex === 'Female' || data.sex === 'Other') {
                        genderSelect.value = data.sex;
                    }

                    statusSpan.style.color = 'green';
                    statusSpan.innerText = '✓ រកឃើញទិន្នន័យនិស្សិត!';
                } else {
                    // បើមិនរកឃើញ ទុកឱ្យអ្នកប្រើប្រាស់បំពេញដោយខ្លួនឯង
                    statusSpan.style.color = 'red';
                    statusSpan.innerText = '✕ ' + data.message;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusSpan.style.color = 'red';
                statusSpan.innerText = 'មានបញ្ហាក្នុងការភ្ជាប់ទៅកាន់ប្រព័ន្ធ';
            });
    } else {
        statusSpan.innerText = '';
    }
});

// លុបសារផ្តល់ដំណឹងពេលប្តូរតួនាទី
document.getElementById('role').addEventListener('change', function() {
    document.getElementById('search-status').innerText = '';
});
</script>

</body>
</html>