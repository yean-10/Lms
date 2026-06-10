<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin(); // ធានាថាមានតែ Admin ទើបអាចចូលបាន
require_once '../includes/admin_layout.php';

$admin_id = $_SESSION['admin_id'];
$msg = $err = '';

// ១. ផ្នែកចាត់ចែងការ Update ទិន្នន័យ (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ប្រើ full_name ជំនួស name ឱ្យត្រូវតាម Database
    $full_name = sanitize($_POST['full_name']); 
    $email = sanitize($_POST['email']);
    $old_image = $_POST['old_image'];

    // គ្រប់គ្រងការ Upload រូបភាព
    $image_name = $old_image;
    if (!empty($_FILES['profile_image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $image_name = 'admin_' . uniqid() . '.' . $ext;
            $uploadDir = '../uploads/admin/';
            
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $image_name)) {
                if ($old_image && file_exists($uploadDir . $old_image)) {
                    unlink($uploadDir . $old_image);
                }
            }
        } else {
            $err = 'ប្រភេទរូបភាពមិនត្រឹមត្រូវ!';
        }
    }

    if (!$err) {
        $sql = "UPDATE admin SET full_name=?, email=?, image=? WHERE id=?";
        $params = "sssi";
        $vals = [$full_name, $email, $image_name, $admin_id];

        if (!empty($_POST['new_password'])) {
            $res = $conn->query("SELECT password FROM admin WHERE id=$admin_id");
            $admin_db = $res->fetch_assoc();
            
            if (!password_verify($_POST['current_password'], $admin_db['password'])) {
                $err = 'លេខសម្ងាត់បច្ចុប្បន្នមិនត្រឹមត្រូវ!';
            } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
                $err = 'លេខសម្ងាត់ថ្មីមិនដូចគ្នា!';
            } else {
                $pw_hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $sql = "UPDATE admin SET full_name=?, email=?, image=?, password=? WHERE id=?";
                $params = "ssssi";
                $vals = [$full_name, $email, $image_name, $pw_hashed, $admin_id];
            }
        }

        if (!$err) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($params, ...$vals);
            if ($stmt->execute()) {
                $_SESSION['admin_name'] = $full_name; 
                $msg = 'បានបច្ចុប្បន្នភាពទិន្នន័យដោយជោគជ័យ!';
            } else {
                $err = $conn->error;
            }
        }
    }
}

// ទាញទិន្នន័យ Admin មកបង្ហាញក្នុង Form
$res = $conn->query("SELECT * FROM admin WHERE id=$admin_id");
$admin = $res->fetch_assoc();

startAdminLayout('កំណត់កម្រងព័ត៌មាន');
?>


<div class="profile-container">
    <?php if($msg): ?>
    <div class="alert-custom alert-success-custom">
        <i class="fas fa-check-circle" style="font-size: 16px;"></i> <?=$msg?>
    </div>
    <?php endif; ?>
    
    <?php if($err): ?>
    <div class="alert-custom alert-danger-custom">
        <i class="fas fa-exclamation-triangle" style="font-size: 16px;"></i> <?=$err?>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="profileForm">
        <input type="hidden" name="old_image" value="<?= $admin['image'] ?>">

        <div class="profile-grid">
            
            <div class="card-custom">
                <div class="avatar-wrapper">
                    <div id="previewBox" class="preview-box">
                        <?php if($admin['image'] && file_exists('../uploads/admin/'.$admin['image'])): ?>
                            <img src="../uploads/admin/<?= $admin['image'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user-shield" style="font-size: 55px; color: #cbd5e1;"></i>
                        <?php endif; ?>
                    </div>
                    <label for="imgInput" class="camera-btn">
                        <i class="fas fa-camera" style="color: white; font-size: 14px;"></i>
                    </label>
                    <input type="file" id="imgInput" name="profile_image" style="display: none;" accept="image/*">
                </div>

                <h3 style="font-size: 20px; color: #1e293b; margin-bottom: 4px; font-weight: 700;"><?= htmlspecialchars($admin['full_name']) ?></h3>
                <p style="color: #e94560; font-size: 13px; margin-bottom: 25px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Admin Panel Account</p>
                
                <hr style="border: none; border-top: 1px solid #f1f5f9; margin-bottom: 25px;">
                
                <div class="form-group-custom">
                    <label>ឈ្មោះគណនី (Username)</label>
                    <input type="text" class="form-input-custom" value="<?= $admin['username'] ?>" disabled>
                </div>
                
                <div class="form-group-custom">
                    <label>ឈ្មោះពេញ</label>
                    <input type="text" name="full_name" class="form-input-custom" value="<?= htmlspecialchars($admin['full_name']) ?>" required>
                </div>

                <div class="form-group-custom" style="margin-bottom: 0;">
                    <label>អ៊ីមែល</label>
                    <input type="email" name="email" class="form-input-custom" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
                </div>
            </div>

            <div class="card-custom">
                <div class="card-custom-header">
                    <h3><i class="fas fa-shield-alt" style="color: #f39c12;"></i> សុវត្ថិភាពគណនី</h3>
                </div>
                
                <div class="form-group-custom">
                    <label>លេខសម្ងាត់បច្ចុប្បន្ន</label>
                    <input type="password" name="current_password" class="form-input-custom" placeholder="បញ្ចូលដើម្បីផ្ទៀងផ្ទាត់ការផ្លាស់ប្តូរ">
                </div>

                <div class="form-group-custom">
                    <label>លេខសម្ងាត់ថ្មី</label>
                    <input type="password" name="new_password" class="form-input-custom" placeholder="ទុកឱ្យទំនេរបើមិនចង់ផ្លាស់ប្តូរ">
                </div>

                <div class="form-group-custom" style="margin-bottom: 30px;">
                    <label>បញ្ជាក់លេខសម្ងាត់ថ្មី</label>
                    <input type="password" name="confirm_password" class="form-input-custom" placeholder="បញ្ចូលលេខសម្ងាត់ថ្មីម្តងទៀត">
                </div>

                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> រក្សាទុកការផ្លាស់ប្តូរ
                </button>
            </div>

        </div>
    </form>
</div>

<script>
    document.getElementById('imgInput').onchange = function (evt) {
        const [file] = this.files;
        if (file) {
            const preview = document.getElementById('previewBox');
            preview.innerHTML = `<img src="${URL.createObjectURL(file)}" style="width: 100%; height: 100%; object-fit: cover;">`;
        }
    }
</script>

<?php endAdminLayout(); ?>