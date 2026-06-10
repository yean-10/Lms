<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

// មុខងារលុប Post (Delete)
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    // លុបរូបភាពចេញពី Folder uploads មុននឹងលុបទិន្នន័យពី DB (ប្រើផ្លូវ Relative ឱ្យច្បាស់លាស់)
    $img_query = $conn->query("SELECT image FROM posts WHERE id = $id");
    if ($img_query && $row = $img_query->fetch_assoc()) {
        $img_path = '../uploads/posts/' . $row['image']; // កែសម្រួលតាមទីតាំងជាក់ស្តែងរបស់អ្នក
        if (!empty($row['image']) && file_exists($img_path)) {
            unlink($img_path);
        }
    }
    
    $conn->query("DELETE FROM posts WHERE id = $id");
    header("Location: admin_posts.php");
    exit();
}

// ទាញយក Post ទាំងអស់មកបង្ហាញ
$result = $conn->query("SELECT * FROM posts ORDER BY id DESC");

// ចាប់ផ្តើមហៅប្រើប្រាស់ Admin Layout រួម
startAdminLayout('គ្រប់គ្រងការ Post');
?>

<div class="card">
    <div class="card-header">
        <h3 style="font-family: 'Moul', cursive; font-size: 1.1rem; 
        color: var(--dark);">
            <i class="fas fa-newspaper" style="color: blue;">
            </i> ផ្ទាំងគ្រប់គ្រងការ Posts
        </h3>
        <div class="search-container">
            <a href="admin_add_post.php" class="btn-action btn-add">
                <i class="fas fa-plus"></i> បន្ថែម Post ថ្មី
            </a>
        </div>
    </div>

    <div class="table-responsive" style="background: #ffffff; 
    padding: 15px;">
        <table>
            <thead>
                <tr>
                    <th style="width: 150px; text-align: center;">រូបភាព</th>
                    <th>ចំណងជើង (Title)</th>
                    <th style="width: 200px; text-align: center;">សកម្មភាព (Actions)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="text-align: center;">
                                <img src="../uploads/posts/<?= htmlspecialchars($row['image']) ?>" 
                                     class="book-thumb" 
                                     style="width: 90px; height: 55px; border-radius: 6px;"
                                     onerror="this.src='https://via.placeholder.com/90x55?text=No+Image'">
                            </td>
                            <td style="white-space: normal; font-weight: 600; color: #2c3e50;">
                                <?= htmlspecialchars($row['title']) ?>
                            </td>
                            <td style="text-align: center;">
                                <a href="admin_edit_post.php?id=<?= $row['id'] ?>" class="btn-sm-action btn-approve" style="background-color: #1565c0;">
                                    <i class="fas fa-edit"></i> កែប្រែ
                                </a>
                                <a href="admin_posts.php?delete_id=<?= $row['id'] ?>" class="btn-sm-action btn-delete" onclick="return confirm('តើអ្នកពិតជាចង់លុប Post នេះមែនទេ?')">
                                    <i class="fas fa-trash"></i> លុប
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--muted); padding: 30px;">
                            <i class="fas fa-folder-open" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                            មិនទាន់មានទិន្នន័យការ Post ឡើយទេ
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
// បិទ Layout រួម
endAdminLayout(); 
?>