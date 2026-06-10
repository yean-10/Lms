<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

$msg = $err = '';

// ទាញយកប្រភេទសៀវភៅដែលមានស្រាប់
$categories = $conn->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $category = sanitize($_POST['category']);
    $isbn = sanitize($_POST['isbn']);
    $total_qty = (int)$_POST['total_qty'];
    $available_qty = (int)$_POST['available_qty'];
    $status = $available_qty > 0 ? 'Available' : 'Out of Stock';
    
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $newname = uniqid('book_') . '.' . $ext;
            $uploadDir = '../uploads/books/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newname)) {
                $image = $newname;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO books (title,author,category,isbn,image,total_qty,available_qty,status) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssiis", $title,$author,$category,$isbn,$image,$total_qty,$available_qty,$status);
    
    if ($stmt->execute()) {
        header("Location: books.php?msg=" . urlencode("បានបន្ថែមសៀវភៅដោយជោគជ័យ!"));
        exit();
    } else {
        $err = $conn->error;
    }
}

startAdminLayout('បន្ថែមសៀវភៅថ្មី');
?>


<style>
    /* រចនាប័ទ្មសម្រាប់ Card */
    .custom-card {
        max-width: 850px;
        margin: 30px auto;
        background: #f9eaea;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        overflow: hidden;
       
    }

    .custom-card-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #eee;
    }

    .custom-card-header h3 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.5rem;
    }

    /* រចនាប័ទ្មសម្រាប់ Input, Select, Number */
    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #34495e;
    }



    /* រចនាប័ទ្មសម្រាប់ Row (ចែកជា ២ កូឡោន) */
    .form-row-custom {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .col-6-custom {
        flex: 1;
        min-width: 300px; /* សម្រាប់ Responsive */
    }

    /* រចនាប័ទ្មសម្រាប់ Buttons */
    .btn-container {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        padding: 20px;
        background: #f4f4ea;
        border-top: 1px solid #eee;
    }


    .btn-cancel {
        background: #fff;
        color: #e74c3c;
        padding: 10px 25px;
        border: 1px solid #e74c3c;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.3s;
    }

    .btn-cancel:hover {
        background: #e74c3c;
        color: white;
    }
</style>

<div class="custom-card">
    <div class="custom-card-header">
        <h3><i class="fas fa-plus-circle" style="color: #27ae60;"></i> បន្ថែមសៀវភៅថ្មី</h3>
    </div>
    
    <form method="POST" enctype="multipart/form-data" style="padding: 25px;">
        
        <div class="form-row-custom">
            <div class="col-6-custom form-group">
                <label>ចំណងជើងសៀវភៅ *</label>
                <input type="text" name="title" class="form-control-custom" required placeholder="បញ្ចូលចំណងជើង">
            </div>
            <div class="col-6-custom form-group">
                <label>អ្នកនិពន្ធ</label>
                <input type="text" name="author" class="form-control-custom" placeholder="ឈ្មោះអ្នកនិពន្ធ">
            </div>
        </div>

        <div class="form-row-custom">
            <div class="col-6-custom form-group">
                <label>ប្រភេទ (Category)</label>
                <input type="text" name="category" class="form-control-custom" list="category-list" 
                placeholder="ជ្រើសរើស ឬវាយថ្មី...">
                <datalist id="category-list">
                    <?php while($row = $categories->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['category']) ?>">
                    <?php endwhile; ?>
                </datalist>
            </div>
            <div class="col-6-custom form-group">
                <label>លេខ ISBN</label>
                <input type="text" name="isbn" class="form-control-custom" placeholder="ឧទាហរណ៍៖ 978-3-16-148410-0">
            </div>
        </div>

        <div class="form-row-custom">
            <div class="col-6-custom form-group">
                <label>ចំនួនសរុប</label>
                <input type="number" name="total_qty" class="form-control-custom" value="1" min="1">
            </div>
            <div class="col-6-custom form-group">
                <label>ចំនួននៅសល់</label>
                <input type="number" name="available_qty" class="form-control-custom" value="1" min="0">
            </div>
        </div>

        <div class="form-group">
            <label>រូបភាពគម្របសៀវភៅ</label>
            <input type="file" name="image" id="bookImage" class="form-control-custom" accept="image/*">
            
            <div id="preview-container" style="margin-top: 15px; display: none;">
                <p style="font-size: 12px; color: #7f8c8d;">រូបភាពដែលអ្នកបានជ្រើសរើស៖</p>
                <img id="imagePreview" src="#" alt="Preview" style="max-width: 150px; 
                border-radius: 8px; border: 1px solid #f75050; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            </div>
        </div>

        <div class="btn-container">
            <a href="books.php" class="btn-cancel"><i class="fas fa-close"></i> បោះបង់</a>
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> រក្សាទុកសៀវភៅ
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('bookImage').onchange = function (evt) {
        const [file] = this.files;
        const previewContainer = document.getElementById('preview-container');
        const imagePreview = document.getElementById('imagePreview');

        if (file) {
            previewContainer.style.display = 'block';
            imagePreview.src = URL.createObjectURL(file);
        } else {
            previewContainer.style.display = 'none';
        }
    }
</script>

<?php endAdminLayout(); ?>