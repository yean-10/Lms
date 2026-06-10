<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $borrowed = $conn->query("SELECT COUNT(*) as c FROM borrows 
    WHERE member_id=$id AND status='Borrowed'")->fetch_assoc()['c'];
    if ($borrowed > 0) {
        $err = 'មិនអាចលុបបានទេ! សមាជិកនេះនៅមានសៀវភៅខ្ចី!';
    } else {
        if ($conn->query("DELETE FROM members WHERE id=$id")) {
            $msg = 'បានលុបដោយជោគជ័យ!';
        } else {
            $err = $conn->error;
        }
    }
}

$search = sanitize($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10; $offset = ($page-1)*$limit;
$where = $search ? "WHERE name LIKE '%$search%' OR username LIKE '%$search%' OR member_code LIKE '%$search%'" : "WHERE 1=1";
$total = $conn->query("SELECT COUNT(*) as c FROM members $where")->fetch_assoc()['c'];
$pages = ceil($total/$limit);
$members = $conn->query("SELECT m.*, (SELECT COUNT(*) FROM borrows WHERE member_id=m.id AND status='Borrowed') as active_borrows FROM members m $where ORDER BY m.id DESC LIMIT $limit OFFSET $offset");

startAdminLayout('គ្រប់គ្រងសមាជិក');
?>

<?php if($msg): ?><div class="alert alert-success">
  <i class="fas fa-check-circle"></i><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>
<?php if($err): ?><div class="alert alert-danger">
  <i class="fas fa-times-circle"></i><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

<div class="card">
  <div class="card-header" >
    
    <h3 style="margin: 0; ">
      <i class="fas fa-users" style="color:var(--primary); margin-right:8px"></i>បញ្ជីសមាជិក (<?= $total ?>)
    </h3>

    <div class="search-container">
      <form method="GET" style="display:flex; gap:0; margin: 0;">
        <div class="search-wrap">
          <input type="text" name="q" class="search-input" 
                 
                 placeholder="ស្វែងរក..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit" class="btn-info" 
                style="border-radius: 0 5px 5px 0; border: 1px solid #ccc; border-left: none; padding: 0 15px; background: #17a2b8; color: white; cursor: pointer; height: 40px;">
          <i class="fas fa-search"></i>
        </button>
      </form>

      <a href="member_add.php" class="button1 btn-success1" style="white-space: nowrap; margin: 0; height: 40px;
       display: inline-flex; align-items: center; box-sizing: border-box;">
        <i class="fas fa-plus"></i> បន្ថែមសមាជិក
      </a>
    </div>

  </div>

  <?php if($members->num_rows > 0): ?>
  <div class="table-responsive">
      <table style="margin-bottom: 0;">
        <thead>
          <tr>
            <th>លេខ</th><th>ឈ្មោះ</th><th>username</th><th>Email</th><th>ថ្នាក់</th>
            <th>ទូរស័ព្ទ</th><th>ភេទ</th><th>ខ្ចី</th><th>សកម្មភាព</th>
          </tr>
        </thead>
        <tbody>
        <?php while($m=$members->fetch_assoc()): ?>
        <tr>
          <td><span class="badge badge-info"><?= $m['member_code'] ?></span></td>
          <td>
            <div class="avatar" style="display:inline-flex; margin-right:8px">
              <?= mb_substr($m['name'], 0, 1) ?>
            </div>
            <?= htmlspecialchars($m['name']) ?>
          </td>
          <td style="color:var(--muted)"><?= htmlspecialchars($m['username']) ?></td>
          <td style="font-size:12px"><?= htmlspecialchars($m['email']) ?></td>
          <td><?= htmlspecialchars($m['class']) ?></td>
          <td><?= htmlspecialchars($m['phone']) ?></td>
          <td><?= $m['gender'] ?></td>
          <td><?= $m['active_borrows'] > 0 ? "<span class='badge badge-warning'>{$m['active_borrows']}</span>" : '<span style="color:var(--muted)">0</span>' ?></td>
          <td>
            <a href="member_edit.php?id=<?= $m['id'] ?>" 
               class="btn btn-warning btn-sm" 
               style="color: #230baa; border-color: #8b949e;"><i class="fas fa-edit"></i></a>
            <form method="POST" style="display:inline" onsubmit="return confirm('លុបសមាជិកនេះ?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $m['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm" 
                      style="color: #f85149;"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
  </div>
  <?php else: ?>
  <div class="empty-state">
    <i class="fas fa-user-slash"></i>
    <p>មិនមានសមាជិក</p>
  </div>
  <?php endif; ?>

  <?php if($pages > 1): ?>
    <div class="pagination" style="padding: 20px; display: flex; justify-content: center; gap: 5px;">
        <?php for($i=1; $i<=$pages; $i++): ?>
            <a href="?page=<?= $i ?>&q=<?= urlencode($search) ?>" 
               style="padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 13px; border: 1px solid #ddd; transition: all 0.2s;
               <?= $i == $page ? 'background:#3498db; color: white; border-color: #3498db; font-weight: bold;' : 'background:#f8f9fa; color:#333;' ?>">
               <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
  <?php endif; ?>

</div>

<?php endAdminLayout(); ?>