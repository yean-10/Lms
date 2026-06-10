<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

$id = (int)($_GET['id'] ?? 0);
$res = $conn->query("SELECT * FROM members WHERE id = $id");
$m = $res->fetch_assoc();

if (!$m) {
    header("Location: members1.php?err=" . urlencode("មិនរកឃើញសមាជិក!"));
    exit();
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $gender = $_POST['gender'];
    $class = sanitize($_POST['class']);
     $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    $sql = "UPDATE members SET name=?, email=?, gender=?, class=?, phone=?, address=? WHERE id=?";
    $params = "ssssssi";
    $vals = [$name, $email, $gender, $class, $phone, $address, $id];
    
    if (!empty($_POST['password'])) {
        $pw = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE members SET name=?, email=?, gender=?, class=?, phone=?, address=?, password=? WHERE id=?";
        $params = "sssssssi";
        $vals = [$name, $email, $gender, $class, $phone, $address, $pw, $id];
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($params, ...$vals);
    if ($stmt->execute()) {
        header("Location: members1.php?msg=" . urlencode("បានកែប្រែដោយជោគជ័យ!"));
        exit();
    } else {
        $err = $conn->error;
    }
}
startAdminLayout('កែប្រែសមាជិក');
?>
<style>
.card { background: #fbf1f8; border-radius: 12px; border: 0px solid #2d3238; padding: 20px; }

 .form-control {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px 15px;
        font-family: 'Segoe UI', 'Khmer OS', sans-serif;
        font-size: 14px;
        transition: all 0.3s ease;
        background-color: #ffffff;
        margin-top: 5px;
    }

    .form-control:focus {
        border-color: #4a90e2;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        outline: none;
    }

.button1 {  border: none;
  color: black;
  padding: 16px 3px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 4px 2px;
  transition-duration: 0.4s;
  cursor: pointer;
}
.btn-secondary { background: var(--green); color: red; border: 1px solid #ff3636; 
     padding: 10px 0px;font-family:'Segoe UI','Khmer OS',sans-serif;
         border-radius: 10px; font-weight: 300; cursor: pointer; display: flex;
          align-items: center; gap: 8px; transition: 0.3s;justify-content: center; }
 .btn-secondary:hover { background: #f5baba; transform: translateY(-2px); }

 .btn-success1 { background: var(--green); color: blue; border: 1px solid #103def; 
 padding: 10px 0px;font-family:'Segoe UI','Khmer OS',sans-serif;justify-content: center;
         border-radius: 10px; font-weight: 600; cursor: pointer; display: flex;
         align-items: center; gap: 8px; transition: 0.3s; }
.btn-success1:hover { background: #44f950; transform: translateY(-2px); }

 </style>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <h3 style="color:blue;"><i class="fas fa-user-edit"></i> កែប្រែសមាជិក: <?= htmlspecialchars($m['name']) ?></h3>
            <a href="members1.php" class="button1 btn-secondary" ><i class="fas fa-undo"></i> ត្រឡប់ក្រោយ</a>
        </div>
        <div class="card-body" style="padding: 20px;">
            <?php if($err): ?><div class="alert alert-danger"><?= $err ?></div><?php endif; ?>
            
            <form method="POST">
                <div class="form-row" style="display:flex; gap:15px; margin-bottom:15px;">
                    <div style="flex:1">
                        <label>ឈ្មោះ *</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($m['name']) ?>" required 
                        style="width:100%">
                    </div>
                    <div style="flex:1">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($m['email']) ?>" 
                        style="width:100%">
                    </div>
                </div>

                <div class="form-row" style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label>ភេទ</label>
                        <select name="gender" class="form-control" style="width:100%">
                            <option value="Male" <?= $m['gender'] == 'Male' ? 'selected' : '' ?>>ប្រុស</option>
                            <option value="Female" <?= $m['gender'] == 'Female' ? 'selected' : '' ?>>ស្រី</option>
                            <option value="Other" <?= $m['gender'] == 'Other' ? 'selected' : '' ?>>ផ្សេង</option>
                        </select>
                    </div>
                     <div>
                        <label>ថ្នាក់</label>
                        <input type="text" name="class" class="form-control" 
                        value="<?= htmlspecialchars($m['class']) ?>"
                         style="width:100%">
                    </div>
                   
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
 <div>
                        <label>ទូរស័ព្ទ</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($m['phone']) ?>"
                         style="width:100%">
                    </div>

                    <div>
                    <label>លេខសម្ងាត់ថ្មី (ទុកចោលបើមិនប្តូរ)</label>
                    <input type="password" name="password" class="form-control" placeholder="******" style="width:100%">
                </div>
  </div>
                  <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label>អាសយដ្ឋាន</label>
                    <textarea name="address" class="form-control" rows="3" style="width:100%">
                        <?= htmlspecialchars($m['address']) ?></textarea>
                </div> 
            </div>

                <div style="display:grid; justify-content: center; gap: 10px; grid-template-columns: 1fr 2fr;">
                    <a href="members1.php" class="button1 btn-secondary"><i class="fa fa-close"></i> បោះបង់</a>
                    <button type="submit" class="button1 btn-success1"><i class="fas fa-save"></i> រក្សាទុក</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endAdminLayout(); ?>