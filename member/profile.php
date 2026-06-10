<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireMember();
require_once '../includes/member_layout.php';

$mid = $_SESSION['member_id'];
$msg = $err = '';

// ១. ផ្នែកចាត់ចែងការបញ្ជូនទិន្នន័យ (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    // កែសម្រួល៖ បន្ថែមលក្ខខណ្ឌ ?? '' ដើម្បីការពារកុំឱ្យលោត Warning key class 
    $class = sanitize($_POST['class'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $old_image = $_POST['old_image'] ?? '';
    
    // គ្រប់គ្រងការ Upload រូបភាព
    $image_name = $old_image; 
    if (!empty($_FILES['profile_image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $image_name = 'user_' . uniqid() . '.' . $ext;
            $uploadDir = '../uploads/members/';
            
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $image_name)) {
                // លុបរូបភាពចាស់ចេញពី Folder បើមានរូបថ្មីជំនួស
                if ($old_image && file_exists($uploadDir . $old_image)) {
                    unlink($uploadDir . $old_image);
                }
            }
        } else {
            $err = 'ប្រភេទរូបភាពមិនត្រឹមត្រូវ! (អនុញ្ញាតតែ JPG, PNG, WEBP)';
        }
    }

    if (!$err) {
        $sql = "UPDATE members SET name=?, email=?, class=?, phone=?, address=?, image=? WHERE id=?";
        $params = "ssssssi";
        $vals = [$name, $email, $class, $phone, $address, $image_name, $mid];

        // ករណីអ្នកប្រើប្រាស់ចង់ប្តូរលេខសម្ងាត់
        if (!empty($_POST['new_password'])) {
            $current_pw_db = $conn->query("SELECT password FROM members WHERE id=$mid")->fetch_assoc()['password'];
            
            if (!password_verify($_POST['current_password'], $current_pw_db)) {
                $err = 'លេខសម្ងាត់បច្ចុប្បន្នមិនត្រឹមត្រូវ!';
            } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
                $err = 'លេខសម្ងាត់ថ្មីមិនដូចគ្នា!';
            } else {
                $pw_hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $sql = "UPDATE members SET name=?, email=?, class=?, phone=?, address=?, image=?, password=? WHERE id=?";
                $params = "sssssssi";
                $vals = [$name, $email, $class, $phone, $address, $image_name, $pw_hashed, $mid];
            }
        }

        if (!$err) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($params, ...$vals);
            if ($stmt->execute()) {
                $_SESSION['member_name'] = $name;
                $msg = 'បានរក្សាទុកដោយជោគជ័យ!';
            } else {
                $err = "កំហុស Database: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// ទាញទិន្នន័យសមាជិកបច្ចុប្បន្នមកបង្ហាញ
$member = $conn->query("SELECT * FROM members WHERE id=$mid")->fetch_assoc();
startMemberLayout('ព័ត៌មានខ្ញុំ');
?>

<?php if($msg): ?><div class="alert alert-success" style="margin: 10px 20px;">
  <i class="fas fa-check-circle"></i> <?=$msg?></div><?php endif; ?>
<?php if($err): ?><div class="alert alert-danger" style="margin: 10px 20px;">
  <i class="fas fa-times-circle"></i> <?=$err?></div><?php endif; ?>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; padding: 20px;">
  <div class="card" style="background: #f9e6f3; color: black; border: 1px solid #2d3238; border-radius: 10px;">
    <div class="card-header" style="border-bottom: 1px solid #2d3238; padding: 15px;">
        <h3 style="margin:0;"><i class="fas fa-user-edit" style="color:#27ae60; margin-right:8px"></i>កែប្រែព័ត៌មាន</h3>
    </div>
    <div style="padding:20px">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="old_image" value="<?= $member['image'] ?? '' ?>">
        
        <div style="text-align:center; margin-bottom:25px">
          <div style="position:relative; display:inline-block;">
            <div id="profilePreview" style="width:110px; height:110px; background:#2d3238; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:40px; color:white; overflow:hidden; border:3px solid #27ae60">
                <?php if(!empty($member['image']) && file_exists('../uploads/members/'.$member['image'])): ?>
                    <img src="../uploads/members/<?= $member['image'] ?>" style="width:100%; height:100%; object-fit:cover">
                <?php else: ?>
                    <?= mb_substr($member['name'] ?? 'U', 0, 1) ?>
                <?php endif; ?>
            </div>
            <label for="imgInput" style="position:absolute; bottom:5px; right:5px; background:#27ae60; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; border:2px solid #1a1d21">
                <i class="fas fa-camera" style="font-size:14px; color:white"></i>
            </label>
            <input type="file" id="imgInput" name="profile_image" style="display:none" accept="image/*">
          </div>
          <div style="margin-top:10px; font-weight:bold; color:blue;"><?= htmlspecialchars($member['id_student'] ?? '') ?></div>
        </div>

        <div style="margin-bottom:15px">
          <label style="display:block; font-size:13px; color:blue; margin-bottom:5px">ឈ្មោះពេញ</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($member['name'] ?? '') ?>" required style="background:white; color:black; border:1px solid #30363d;">
        </div>
        <div style="margin-bottom:15px">
          <label style="display:block; font-size:13px; color:blue; margin-bottom:5px">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($member['email'] ?? '') ?>" style="background:white; color:black;border:1px solid #30363d;">
        </div>
        <div style="margin-bottom:15px">
          <label style="display:block; font-size:13px; color:blue; margin-bottom:5px">ថ្នាក់</label>
          <input type="text" name="class" class="form-control" value="<?= htmlspecialchars($member['class'] ?? '') ?>" style="background:white; color:black; border:1px solid #30363d;">
        </div>
        <div style="margin-bottom:15px">
          <label style="display:block; font-size:13px; color:blue; margin-bottom:5px">លេខទូរស័ព្ទ</label>
          <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($member['phone'] ?? '') ?>" style="background:white; color:black; border:1px solid #30363d;">
        </div>
        
        <div style="margin-bottom:15px">
          <label style="display:block; font-size:13px; color:blue; margin-bottom:5px">អាសយដ្ឋាន</label>
          <textarea name="address" class="form-control" rows="2" style="background:white; color:black; border:1px solid #30363d;"><?= htmlspecialchars($member['address'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%; font-weight:bold;color:blue;">
          <i class="fas fa-save"></i> រក្សាទុកការផ្លាស់ប្តូរ</button>
      </form>
    </div>
  </div>

  <div class="card" style="background: #f9e6f3; color: black; border: 1px solid #2d3238; border-radius: 10px;">
    <div class="card-header" style="border-bottom: 1px solid #2d3238; padding: 15px;">
        <h3 style="margin:0;"><i class="fas fa-shield-alt" style="color:#27ae60; margin-right:8px"></i>សុវត្ថិភាពគណនី</h3>
    </div>
    <div style="padding:20px">
      <form method="POST">
        <input type="hidden" name="name" value="<?= htmlspecialchars($member['name'] ?? '') ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>">
        <input type="hidden" name="class" value="<?= htmlspecialchars($member['class'] ?? '') ?>">
        <input type="hidden" name="phone" value="<?= htmlspecialchars($member['phone'] ?? '') ?>">
        <input type="hidden" name="address" value="<?= htmlspecialchars($member['address'] ?? '') ?>">
        <input type="hidden" name="old_image" value="<?= $member['image'] ?? '' ?>">

        <div style="margin-bottom:15px">
          <label style="display:block; font-size:13px; color:blue; margin-bottom:5px">លេខសម្ងាត់បច្ចុប្បន្ន</label>
          <input type="password" name="current_password" class="form-control" placeholder="••••••••" style="background:white; color:black; border:1px solid #30363d;" required>
        </div>
        <div style="margin-bottom:15px">
          <label style="display:block; font-size:13px; color:blue; margin-bottom:5px">លេខសម្ងាត់ថ្មី</label>
          <input type="password" name="new_password" class="form-control" placeholder="••••••••" style="background:white; color:black; border:1px solid #30363d;" required>
        </div>
        <div style="margin-bottom:20px">
          <label style="display:block; font-size:13px; color:blue; margin-bottom:5px">បញ្ជាក់លេខសម្ងាត់ថ្មី</label>
          <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" style="background:white; color:black; border:1px solid #30363d;" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%; font-weight:bold;">
          <i class="fas fa-key"></i> បច្ចុប្បន្នភាពលេខសម្ងាត់</button>
      </form>

      <div style="margin-top:25px; padding:15px; background:rgba(39, 174, 96, 0.05); 
      border:1px dashed #27ae60; border-radius:10px">
        <div style="font-size:13px; display:grid; gap:8px">
          <div><i class="fas fa-calendar-alt" style="width:20px; color:#27ae60"></i> ថ្ងៃចូលរួម: 
          <span style="color:blue"><?= isset($member['created_at']) ? date('d-M-Y', strtotime($member['created_at'])) : '' ?></span></div>
          <div><i class="fas fa-venus-mars" style="width:20px; color:#27ae60"></i> ភេទ: 
          <span style="color:blue"><?= htmlspecialchars($member['gender'] ?? '') ?></span></div>
          <div><i class="fas fa-check-circle" style="width:20px; color:#27ae60"></i> ស្ថានភាព: 
          <span class="badge badge-success">Active</span></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
    // Preview រូបភាពភ្លាមៗនៅពេលរើស File
    document.getElementById('imgInput').onchange = function (evt) {
        const [file] = this.files;
        if (file) {
            const preview = document.getElementById('profilePreview');
            preview.innerHTML = `<img src="${URL.createObjectURL(file)}" style="width:100%; height:100%; object-fit:cover">`;
        }
    }
</script>

<?php endMemberLayout(); ?>