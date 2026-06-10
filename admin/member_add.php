<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $gender = $_POST['gender'];
    $class = sanitize($_POST['class']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $code = generateMemberCode();

    $stmt = $conn->prepare("INSERT INTO members (member_code,name,username,password,email,gender,class,phone,address) 
    VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssssss", $code,$name,$username,$password,$email,$gender,$class,$phone,$address);
    
    if ($stmt->execute()) {
        header("Location: members1.php?msg=" . urlencode("បានបន្ថែមសមាជិកដោយជោគជ័យ!"));
        exit();
    } else {
        $err = "កំហុស៖ " . $conn->error;
    }
}

startAdminLayout('បន្ថែមសមាជិក');
?>
<style>
    /* រចនាប័ទ្មទូទៅសម្រាប់ Input និង Select */
   

    label {
        font-weight: 600;
        color: black;
      
        font-size: 14px;
    }

    /* រចនាប័ទ្មប៊ូតុង */
    .btn-custom {
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

    .btn-primary-custom {
    
        background-color: #1a73e8; 
        color: white; 
    }

    .btn-primary-custom:hover {
        background-color: #0056b3;
        transform: translateY(-1px);
    }

    .btn-danger-custom {
        background-color: #fff;
        color: #dc3545;
        border: 1px solid #dc3545;
    }

    .btn-danger-custom:hover {
        background-color: #dc3545;
        color: white;
    }

    /* ==========================================================================
       កែសម្រួលដើម្បីឱ្យ Card រត់មកចំកណ្តាលទំព័រ និងលុបបំបាត់ចន្លោះទំនេរចោល
       ========================================================================== */
    .card-container {
        width: 100%;
        display: flex;
        justify-content: center; /* រុញ Card ឱ្យមកចំកណ្តាលផ្ទាំង */
        align-items: center;
        padding: 10px 0;
    }

    .card {
        width: 100%;
        max-width: 850px; /* កំណត់ទំហំទទឹងរបស់ Form ឱ្យសមល្មមមើលទៅស្អាត */
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        background: #ffffff;
        overflow: hidden;
    }
    
    .card-header {
        background-color: #fdf6f6; 
        border-bottom: 1px solid #f1dede;
        padding: 20px;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* កែប្រែសភាព card-body ដើមរបស់ Layout កុំឱ្យប៉ះពាល់ដល់ការរៀបជួរ Form */
    .card .custom-card-body {
        background: #ffffff;
        display: block !important; /* បិទ display: flex របស់ layout ចាស់ចេញ */
        padding: 30px;
    }

    /* រៀបចំការធ្លាក់ជួរស្វ័យប្រវត្តិនៅលើអេក្រង់ទូរស័ព្ទ */
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 15px !important;
            margin-bottom: 15px !important;
        }
        .card {
            max-width: 100%;
            margin: 10px;
        }
    }
</style>

<div class="card-container">
    <div class="card">
        <div class="card-header">
            <h3 style="margin:0; color:blue;">
                <i class="fas fa-user-plus" style="color:#007bff"></i> បន្ថែមសមាជិកថ្មី</h3>
            <a href="members1.php" class="btn-custom" 
            style="background:#f0f0f0; color:red; padding: 5px 15px; font-size: 13px;">
                <i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ
            </a>
        </div>
        
        <div class="custom-card-body">
            <?php if($err): ?><div class="alert alert-danger" style="border-radius:8px; margin-bottom: 20px;">
                <?= $err ?></div><?php endif; ?>
            
            <form method="POST">
                <div class="form-row" style="display:flex; gap:20px; margin-bottom:20px;">
                    <div style="flex:1">
                        <label>ឈ្មោះ <span style="color:red">*</span></label>
                        <input type="text" name="name" class="form-control" required style="width:100%" placeholder="បញ្ចូលឈ្មោះពេញ">
                    </div>
                    <div style="flex:1">
                        <label>Username <span style="color:red">*</span></label>
                        <input type="text" name="username" class="form-control" required style="width:100%" placeholder="ឈ្មោះអ្នកប្រើប្រាស់">
                    </div>
                </div>

                <div class="form-row" style="display:flex; gap:20px; margin-bottom:20px;">
                    <div style="flex:1">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" style="width:100%" placeholder="ឧទាហរណ៍៖ info@gmail.com">
                    </div>
                    <div style="flex:1">
                        <label>ភេទ</label>
                        <select name="gender" class="form-control" style="width:100%">
                            <option value="Male">ប្រុស</option>
                            <option value="Female">ស្រី</option>
                            <option value="Other">ផ្សេងៗ</option>
                        </select>
                    </div>
                    <div style="flex:1">
                        <label>ថ្នាក់</label>
                        <input type="text" name="class" class="form-control" style="width:100%" placeholder="កុំព្យូទ័រឆ្នាំ១">
                    </div>
                </div>

                <div class="form-row" style="display:flex; gap:20px; margin-bottom:20px;">
                    <div style="flex:1">
                        <label>ទូរស័ព្ទ</label>
                        <input type="text" name="phone" class="form-control" style="width:100%" placeholder="លេខទូរស័ព្ទ">
                    </div>
                    <div style="flex:1">
                        <label>លេខសម្ងាត់ <span style="color:red">*</span></label>
                        <input type="password" name="password" class="form-control" required style="width:100%" placeholder="បញ្ចូលលេខសម្ងាត់">
                    </div>
                </div>

                <div style="margin-bottom:25px;">
                    <label>អាសយដ្ឋាន</label>
                    <textarea name="address" class="form-control" rows="3" style="width:100%" placeholder="បញ្ជាក់ទីតាំងបច្ចុប្បន្ន..."></textarea>
                </div>

                <div style="display:flex; justify-content: flex-end; gap: 15px; border-top: 1px solid #eee; padding-top: 20px;">
                    <a href="members1.php" class="btn-custom btn-danger-custom"> <i class="fas fa-close"></i> បោះបង់</a>
                    <button type="submit" class="btn-custom btn-primary-custom">
                        <i class="fas fa-save"></i> រក្សាទុកទិន្នន័យ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endAdminLayout(); ?>