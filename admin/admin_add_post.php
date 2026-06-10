<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    
    // គ្រប់គ្រងការ Upload រូបភាព
    $image_name = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    
    // ចាប់យក Extension របស់រូបភាព (ឧទាហរណ៍៖ .jpg, .png)
    $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    
    // កំណត់ប្រភេទឯកសារដែលអនុញ្ញាតឱ្យ Upload
    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    
    // កំណត់ផ្លូវទៅកាន់ Folder រក្សាទុករូបភាពការ Post
    $upload_dir = '../uploads/posts/';
    
    // ពិនិត្យមើលបើគ្មាន Folder posts ទេ ឱ្យបង្កើតវាភ្លាម
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // ១. ពិនិត្យមើលថាតើឯកសារដែលបាន Upload ជារូបភាពពិតប្រាកដមែនឬទេ
    if (!in_array($image_ext, $allowed_extensions)) {
        $error = "ប្រភេទឯកសារមិនត្រឹមត្រូវឡើយ! អនុញ្ញាតតែរូបភាពទម្រង់ JPG, JPEG, PNG, GIF និង WEBP ប៉ុណ្ណោះ។";
    } else {
        // បង្កើតឈ្មោះរូបភាពថ្មីកុំឱ្យជាន់គ្នា
        $new_image_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $image_ext;
        $upload_path = $upload_dir . $new_image_name;

        if (move_uploaded_file($image_tmp, $upload_path)) {
            // បញ្ចូលទៅក្នុង Database
            $sql = "INSERT INTO posts (title, image) VALUES ('$title', '$new_image_name')";
            if ($conn->query($sql)) {
                header("Location: admin_posts.php");
                exit();
            } else {
                $error = "មានបញ្ហាក្នុងការបញ្ចូលទិន្នន័យ៖ " . $conn->error;
            }
        } else {
            $error = "ការ Upload រូបភាពមិនបានជោគជ័យឡើយ!";
        }
    }
}

// ហៅ Layout មកប្រើប្រាស់
startAdminLayout('បន្ថែម Post ថ្មី');
?>

<div class="return-container">
    <div class="custom-card">
        <div class="custom-card-header">
            <h3 style="font-family: 'Moul', cursive; font-size: 1.1rem;">
                <i class="fas fa-plus-circle"></i> បន្ថែម Post ថ្មី
            </h3>
            <a href="admin_posts.php" class="back-link"><i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ</a>
        </div>
        
        <div class="custom-card-body">
            <?php if(isset($error)): ?>
                <div class="alert-custom alert-danger-custom">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">ចំណងជើង Post (Title):</label>
                    <input type="text" name="title" class="form-control-custom" required placeholder="បញ្ចូលចំណងជើងរូបភាពសម្រាប់បង្ហាញ..." value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">ជ្រើសរើសរូបភាព (Image):</label>
                    <input type="file" name="image" id="imageInput" class="form-control-custom" accept="image/*" required onchange="previewImage(event)">
                    
                    <div style="margin-top: 15px;">
                        <p style="font-size: 12px; color: #64748b; margin-bottom: 5px;">រូបភាពគំរូ៖</p>
                        <img id="imagePreview" src="https://via.placeholder.com/300x150?text=No+Image+Selected" 
                             style="width: 100%; max-width: 350px; height: auto; border-radius: 8px; border: 2px dashed #ddd; object-fit: cover;">
                    </div>
                </div>
                
                <div class="btn-group">
                    <a href="admin_posts.php" class="btn-custom btn-cancel">
                        <i class="fas fa-times"></i> បោះបង់
                    </a>
                    <button type="submit" class="btn-custom btn-save" style="color: white;">
                        <i class="fas fa-save"></i> រក្សាទុកទិន្នន័យ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JavaScript សម្រាប់បង្ហាញរូបភាពភ្លាមៗនៅពេលដែល Admin រើសរូបភាពរួច
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('imagePreview');
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<?php 
// បិទ Layout រួម
endAdminLayout(); 
?>