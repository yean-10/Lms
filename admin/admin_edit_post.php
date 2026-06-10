<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

if (!isset($_GET['id'])) {
    header("Location: admin_posts.php");
    exit();
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM posts WHERE id = $id");
$post = $result->fetch_assoc();

if (!$post) {
    die("រកមិនឃើញទិន្នន័យឡើយ!");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $new_image_name = $post['image']; // រក្សាឈ្មោះចាស់បើមិនដូររូប
    $upload_dir = '../uploads/posts/';

    // ប្រសិនបើមានការជ្រើសរើសរូបភាពថ្មី
    if (!empty($_FILES['image']['name'])) {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $new_image_name = time() . '_' . $image_name;
        
        // --- ផ្នែកដែលបានបន្ថែមដើម្បីដោះស្រាយ Error ---
        // បើមិនទាន់មាន Folder "posts" ទេ ឱ្យប្រព័ន្ធបង្កើតអូតូភ្លាម
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        // ----------------------------------------

        if (move_uploaded_file($image_tmp, $upload_dir . $new_image_name)) {
            // លុបរូបចាស់ចោលពី Folder (លុះត្រាតែ Upload រូបថ្មីចូលជោគជ័យ)
            $old_image_path = $upload_dir . $post['image'];
            if (!empty($post['image']) && file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        } else {
            $error = "ការ Upload រូបភាពថ្មីមិនបានជោគជ័យឡើយ!";
        }
    }

    // ប្រសិនបើគ្មាន Error ទើបធ្វើការ Update ទៅ Database
    if (!isset($error)) {
        $sql = "UPDATE posts SET title = '$title', image = '$new_image_name' WHERE id = $id";
        if ($conn->query($sql)) {
            header("Location: admin_posts.php");
            exit();
        } else {
            $error = "ការកែប្រែមានបញ្ហា៖ " . $conn->error;
        }
    }
}

// ហៅ Layout មកប្រើប្រាស់
startAdminLayout('កែប្រែ Post');
?>

<div class="return-container">
    <div class="custom-card">
        <div class="custom-card-header">
            <h3 style="font-family: 'Moul', cursive; font-size: 1.1rem;">
                <i class="fas fa-edit"></i> កែប្រែ Post
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
                    <input type="text" name="title" class="form-control-custom" value="<?= htmlspecialchars($post['title']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">រូបភាពបច្ចុប្បន្ន៖</label>
                    <div style="margin-top: 5px;">
                        <img id="imagePreview" src="../uploads/posts/<?= htmlspecialchars($post['image']) ?>" 
                             style="width: 100%; max-width: 350px; height: auto; border-radius: 8px; border: 1px solid var(--border); object-fit: cover;"
                             onerror="this.src='https://via.placeholder.com/350x180?text=No+Image'">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ប្តូររូបភាពថ្មី (ទុកទទេបើមិនចង់ប្តូរ)៖</label>
                    <input type="file" name="image" class="form-control-custom" accept="image/*" onchange="previewImage(event)">
                </div>
                
                <div class="btn-group">
                    <a href="admin_posts.php" class="btn-custom btn-cancel">
                        <i class="fas fa-times"></i> បោះបង់
                    </a>
                    <button type="submit" class="btn-custom btn-save" style="color: white; background: #1565c0;">
                        <i class="fas fa-sync-alt"></i> ធ្វើបច្ចុប្បន្នភាព
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JavaScript សម្រាប់ដូររូបភាព Preview ភ្លាមៗពេល Admin រើសរូបភាពថ្មី
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