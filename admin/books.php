<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

$msg = $err = '';

// --- Logic គ្រប់គ្រងទិន្នន័យ ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Logic សម្រាប់កែប្រែ (Edit) នៅរក្សាទុកក្នុង Modal ដដែលដើម្បីភាពងាយស្រួល
    if ($action === 'edit') {
        $id = (int)$_POST['id'];
        $title = sanitize($_POST['title']);
        $author = sanitize($_POST['author']);
        $category = sanitize($_POST['category']);
        $isbn = sanitize($_POST['isbn']);
        $total_qty = (int)$_POST['total_qty'];
        $available_qty = (int)$_POST['available_qty'];
        $status = $available_qty > 0 ? 'Available' : 'Out of Stock';
        
        $image = $_POST['old_image'] ?? '';
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

        $stmt = $conn->prepare("UPDATE books SET title=?, author=?, category=?, isbn=?, image=?, total_qty=?, available_qty=?, status=? WHERE id=?");
        $stmt->bind_param("sssssiisi", $title, $author, $category, $isbn, $image, $total_qty, $available_qty, $status, $id);
        $stmt->execute() ? $msg = 'បានកែប្រែដោយជោគជ័យ!' : $err = $conn->error;
    }

    // Logic សម្រាប់លុប (Delete) ជាមួយការការពារ Foreign Key Error
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        try {
            $conn->query("DELETE FROM books WHERE id=$id");
            $msg = 'បានលុបដោយជោគជ័យ!';
        } catch (mysqli_sql_exception $e) {
            $err = "មិនអាចលុបបានទេ! សៀវភៅនេះមានជាប់ពាក់ព័ន្ធនឹងប្រវត្តិខ្ចីសៀវភៅរបស់សមាជិក។";
        }
    }
}

// --- ការទាញទិន្នន័យមកបង្ហាញ ---
$search = sanitize($_GET['q'] ?? '');
$cat = sanitize($_GET['cat'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10; 
$offset = ($page-1)*$limit;

$where = "WHERE 1=1";
if ($search) $where .= " AND (title LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%')";
if ($cat) $where .= " AND category='$cat'";

$total = $conn->query("SELECT COUNT(*) as c FROM books $where")->fetch_assoc()['c'];
$pages = ceil($total/$limit);
$books = $conn->query("SELECT * FROM books $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
$categories = $conn->query("SELECT DISTINCT category FROM books WHERE 
category IS NOT NULL AND category != '' ORDER BY category");

startAdminLayout('គ្រប់គ្រងសៀវភៅ');
?>

<style>
    /* រចនាប័ទ្មទូទៅសម្រាប់ Input, Select, Textarea */
    .form-control, .search-input, select.form-control {
        background: #ffffff ;
        border: 1px solid #d1d5db ;
        color: #1f2937 ;
        border-radius: 8px ;
        padding: 10px 15px ;
        font-size: 14px ;
        transition: all 0.3s ease;
        outline: none ;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
    }

    /* ពេល Click លើ Input (Focus State) */
    .form-control:focus, .search-input:focus {
        border-color: #3b82f6 ;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) ;
    }

    /* រចនាប័ទ្ម Search Wrap */
    .search-wrap {
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 2px 12px;
        display: flex;
        align-items: center;
        transition: 0.3s;
    }

    .search-wrap:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .search-input {
        border: none ;
        box-shadow: none ;
        width: 100%;
    }

    /* រចនាប័ទ្មប៊ូតុង Button */
    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center; text-align: center;
        gap: 8px; transition-duration: 0.4s;
        transition: all 0.2s ease;
        border: none;
        
    }  
    .button1 {
  border: none;
  color: black;border-radius: 8px;
  padding: 16px 3px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 4px 2px;
  transition-duration: 0.4s;
  cursor: pointer;
}

    .btn-primary { background: #5286f9; color: black; }
    .btn-primary:hover { background: #3256b9; transform: translateY(-1px); }

    .btn-success { background: #10b98f; color: black; }
    .btn-success:hover { background: #059669; transform: translateY(-1px); }

    /* ប៊ូតុងក្នុង Modal (បោះបង់ & រក្សាទុក) */
    .btn-secondary {
        background: #f4cecc ;
        color: red ;
        border: 1px solid #d1d5db ;
    }
    .btn-secondary:hover {
        background: #daa4a4 ;
        color: #ef4444 ; /* ពណ៌អក្សរក្រហមពេល hover ដូចរូបភាព */
    }

    .btn-success1 {
        background: #2563eb ;
        color: white ;
        border: none ;
        font-weight: 600 ;
    }
    .btn-success1:hover { background: #1d4ed8 ; }

    /* រចនាប័ទ្ម Textarea */
    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    /* Card & Modal Adjustments */
    .card {
        background: #ffffff;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(164, 98, 98, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .modal-content {
        background: #ffffff;
        border: none;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    /* CSS Styles */
    .book-thumb { width: 40px; height: 55px; object-fit: cover; border-radius: 4px; 
    box-shadow: 0 4px 8px rgba(217, 210, 210, 0.3); }
    .img-preview { width: 100px; height: 110px; object-fit: cover; border-radius: 5px; 
    border: 2px solid #ddd; 
    margin-bottom: 10px; }
    .card { background: #f3f4f6; border-radius: 12px; border: 0px solid #2d3238; padding: 20px; }
    .search-wrap { background: #eff2f6; border: 1px solid #1b1c1f; border-radius: 8px; 
    padding: 5px 12px; display: flex; align-items: center; }
    .search-input { background: transparent; border: none; color: black; padding: 8px; outline: none; width: 200px; }
    table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
    tbody tr { background: #fef7f7; transition: transform 0.2s; }
    tbody tr:hover { transform: scale(1.005); background: #c2bbbb; }
    tbody td { padding: 12px; color: #070707; border-top: 0px solid #1d2e42; 
    border-bottom: 0px solid #2d3238; }
    tbody td:first-child { border-left: 0px solid #2d3238; border-radius: 8px 0 0 8px; }
    tbody td:last-child { border-right: 0px solid #2d3238; border-radius: 0 8px 8px 0; }
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: bold; }
    .badge-success { background: rgba(15, 242, 52, 0.2); color: #149c26; 
    border: 1px solid rgba(63, 185, 80, 0.3); }
    .badge-danger { background: rgba(204, 186, 185, 0.2); color: #f85149; border: 1px solid rgba(248, 81, 73, 0.3); }
    .btn-action { background: #f4eff3; color: #000000; border: 1px solid #1f2328; 
    padding: 6px 10px; border-radius: 6px; cursor: pointer; }
    .btn-action:hover { color: #230baa; border-color: #f102f5; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0;
     width: 100%; height: 100%; 
    background: rgba(241, 237, 237, 0.8); }
    .modal.show { display: flex; align-items: center; justify-content: center; }
    .modal-content { background: #f9ede7; width: 90%; max-width: 700px; border-radius: 12px; 
    border: 0px solid #2d3238; color: blue; }
    .form-control { background: #fbfafe; border: 0px solid #39394f; color: black; 
    border-radius: 6px; padding: 10px; width: 100%; }

    .button1 {
  border: none;
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



</style>

<?php if($msg || isset($_GET['msg'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> 
    <?= $msg ?: htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>
<?php if($err): ?>
    <div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $err ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header" style="display:flex; justify-content: space-between; 
    align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
        <h3 style="margin:0; color: blue;"><i class="fas fa-book-open text-primary"></i> បញ្ជីសៀវភៅ 
        <small style="font-size:14px; color:black;">(សរុប <?= $total ?>)</small></h3>
        
        <div style="display:flex; gap:10px; flex-wrap: wrap; align-items: center;">
            <form method="GET" style="display:flex; gap:8px;">
                <div class="search-wrap">
                    <i class="fas fa-search" style="color: #03254b;"></i>
                    <input type="text" name="q" class="search-input" 
                    placeholder="ស្វែងរក..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <select name="cat" class="form-control" style="width:140px">
                    <option value="">ប្រភេទទាំងអស់</option>
                    <?php 
                    $categories->data_seek(0);
                    while($c=$categories->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($c['category']) ?>" 
                        <?= $cat==$c['category']?'selected':'' ?>><?= htmlspecialchars($c['category']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </form>
            <a href="add_book.php" class=" btn btn-success"><i class="fas fa-plus"></i> បន្ថែមថ្មី</a>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th width="60">រូបភាព</th>
                    <th>ព័ត៌មានសៀវភៅ</th>
                    <th>ប្រភេទ</th>
                    <th>ISBN</th>
                    <th class="text-center">ចំនួន</th>
                    <th>ស្ថានភាព</th>
                    <th width="100">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                <?php if($books->num_rows > 0): ?>
                    <?php while($b=$books->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center">
                            <?php if($b['image']): ?>
                                <img class="book-thumb" src="../uploads/books/<?= htmlspecialchars($b['image']) ?>" alt="Cover">
                            <?php else: ?>
                                <div style="width:40px; height:55px; 
                                background:#30363d; border-radius:4px; display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-book" style="color:#8b949e;"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($b['title']) ?></div>
                            <div style="font-size: 12px; color: #be4141;"><?= htmlspecialchars($b['author']) ?></div>
                        </td>
                        <td><span style="background: rgba(203, 223, 251, 0.15); 
                        color: #066aec; padding: 2px 8px; border-radius: 4px; font-size: 11px;">
                        <?= htmlspecialchars($b['category']) ?></span></td>
                        <td style="font-family: monospace; color: #011a35;"><?= htmlspecialchars($b['isbn']) ?></td>
                        <td class="text-center">
                            <span style="color: #000000;"><?= $b['total_qty'] ?></span> 
                            <small style="color: #0621f1;">/ <?= $b['available_qty'] ?></small>
                        </td>
                        <td>
                            <span class="badge <?= $b['status']==='Available'?'badge-success':'badge-danger' ?>">
                                <?= $b['status']==='Available'?'មាន':'អស់' ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-btns" style="display: flex; gap: 5px;">
                                <button onclick='editBook(<?= json_encode($b) ?>)' class="btn-action"><i class="fas fa-edit"></i></button>
                                <form method="POST" style="display:inline" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបសៀវភៅនេះមែនទេ?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="btn-action" style="color: #eb281e;"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center" style="padding:40px; color:#8b949e;">មិនមានទិន្នន័យ។</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

     <?php if($pages > 1): ?>
        <div class="pagination" style="padding: 20px; display: flex; justify-content: center; gap: 5px;">
            <?php for($i=1; $i<=$pages; $i++): ?>
                <a href="?page=<?=$i?>&q=<?=urlencode($search)?>&cat=<?=urlencode($cat)?>" 
                   style="padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 13px; 
                   <?= $i==$page ? 'background:#3498db; color:#white;' : 'background:#f8f9fa; color:#333;' ?>">
                   <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

</div>

<div id="modalEdit" class="modal">
    <div class="modal-content">
        <div style="padding: 10px; border-bottom: 1px solid #2d3036; display: flex;
         justify-content: space-between;">
            <h2 style="margin:0"><i class="fas fa-edit text-primary"></i> កែប្រែព័ត៌មានសៀវភៅ</h2>
            <span style="cursor:pointer;font-size:30px;color:red;" onclick="closeModal('modalEdit')">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data" style="padding: 20px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editId">
            <input type="hidden" name="old_image" id="editOldImage">
            
            <div id="currentImageContainer" style="text-align:center; margin-bottom: 15px;"></div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label>ចំណងជើងសៀវភៅ *</label>
                    <input type="text" name="title" id="editTitle" class="form-control" required>
                </div>
                <div>
                    <label>អ្នកនិពន្ធ</label>
                    <input type="text" name="author" id="editAuthor" class="form-control">
                </div>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;"> 
<div>
    <label>ប្រភេទ</label>
    <select name="category" id="editCategory" class="form-control">
        <option value="">-- ជ្រើសរើសប្រភេទ --</option>
        <?php 
        $categories->data_seek(0);
        while($c = $categories->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($c['category']) ?>" 
                <?= (isset($category) && $category == $c['category']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['category']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>
             
                <div>
                    <label>ISBN</label>
                    <input type="text" name="isbn" id="editIsbn" class="form-control">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label>ចំនួនសរុប</label>
                    <input type="number" name="total_qty" id="editTotalQty" class="form-control" min="0">
                </div>
                <div>
                    <label>ចំនួននៅសល់</label>
                    <input type="number" name="available_qty" id="editAvailQty" class="form-control" min="0">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label>ប្តូររូបភាពថ្មី</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
 
            <div style="display:grid; grid-template-columns: 1fr 2fr; gap: 15px; margin-bottom: 15px;">
                <button type="button" class="button1 btn-secondary" style=" color:red; "
                onclick="closeModal('modalEdit')"> បោះបង់ <i class="fa fa-close"></i></button>
                <button type="submit" class="button1 btn-success1"><i class="fas fa-save"></i> រក្សាទុកការផ្លាស់ប្តូរ</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function editBook(b) {
    document.getElementById('editId').value = b.id;
    document.getElementById('editTitle').value = b.title;
    document.getElementById('editAuthor').value = b.author || '';
    document.getElementById('editCategory').value = b.category || '';
    document.getElementById('editIsbn').value = b.isbn || '';
    document.getElementById('editTotalQty').value = b.total_qty;
    document.getElementById('editAvailQty').value = b.available_qty;
    document.getElementById('editOldImage').value = b.image || '';
    
    const imgCont = document.getElementById('currentImageContainer');
    if(b.image) {
        imgCont.innerHTML = `<img src="../uploads/books/${b.image}" 
        class="img-preview"><br><small style="color:#8b949e">រូបភាពបច្ចុប្បន្ន</small>`;
    } else {
        imgCont.innerHTML = `<div style="width:80px;height:110px;background:#30363d;margin:auto;
        display:flex;align-items:center;justify-content:center;border-radius:5px;">
        <i class="fas fa-image fa-2x" style="color:#8b949e"></i></div>`;
    }
    openModal('modalEdit');
}
</script>

<?php endAdminLayout(); ?>