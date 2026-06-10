<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

// Stats
$stats = [];
$stats['books'] = $conn->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'];
$stats['members'] = $conn->query("SELECT COUNT(*) as c FROM members")->fetch_assoc()['c'];
$stats['borrowed'] = $conn->query("SELECT COUNT(*) as c FROM borrows WHERE status='Borrowed'")->fetch_assoc()['c'];
$stats['overdue'] = $conn->query("SELECT COUNT(*) as c FROM borrows WHERE status='Borrowed' AND return_date < CURDATE()")->fetch_assoc()['c'];
$stats['fines'] = $conn->query("SELECT COALESCE(SUM(fine_amount),0) as c FROM borrows")->fetch_assoc()['c'];

// Recent borrows
$recent = $conn->query("
  SELECT b.id, m.name as member_name,m.class, bk.title, b.borrow_date, b.return_date, b.status,
         CASE WHEN b.status='Borrowed' AND b.return_date < CURDATE() THEN 'Overdue' ELSE b.status END as display_status
  FROM borrows b
  JOIN members m ON b.member_id = m.id
  JOIN books bk ON b.book_id = bk.id
  ORDER BY b.id DESC LIMIT 8
");

startAdminLayout('ផ្ទាំងព័ត៌មាន');
?>
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-book"></i></div>
    <div class="stat-info"><h3><?= $stats['books'] ?></h3><p>សៀវភៅសរុប</p></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-users"></i></div>
    <div class="stat-info"><h3><?= $stats['members'] ?></h3><p>សមាជិកសរុប</p></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon yellow"><i class="fas fa-hand-holding"></i></div>
    <div class="stat-info"><h3><?= $stats['borrowed'] ?></h3><p>កំពុងខ្ចី</p></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
    <div class="stat-info"><h3><?= $stats['overdue'] ?></h3><p>លើសកំណត់</p></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fas fa-dollar-sign"></i></div>
    <div class="stat-info"><h3>$<?= number_format($stats['fines'],2) ?></h3><p>ប្រាក់ពិន័យ</p></div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-clock" style="color:var(--primary);margin-right:8px"></i>ការខ្ចីថ្មីៗ</h3>
    <a href="action_borrow.php" class="btn btn-info btn-sm">មើលទាំងអស់</a>
  </div>
  <?php if ($recent->num_rows > 0): ?>
  <table>
    <thead><tr>
      <th>#</th>
      <th>សមាជិក</th>
      <th>សៀវភៅ</th>
      <th>ថ្ងៃខ្ចី</th>
      <th>ថ្ងៃត្រូវសង</th>
      <th>ស្ថានភាព</th>
  </tr></thead>
    <tbody>
    <?php while($r = $recent->fetch_assoc()): ?>
    <tr>
      <td><?= $r['id'] ?></td>
      <td><?= htmlspecialchars($r['member_name']) ?>
      <div style="font-size:11px;color:var(--muted)"><?= $r['class'] ?></div>
    </td>
      <td><?= htmlspecialchars($r['title']) ?></td>
      <td><?= $r['borrow_date'] ?></td>
      <td><?= $r['return_date'] ?></td>
      <td>
        <?php if($r['display_status'] === 'Overdue'): ?>
          <span class="badge badge-danger">លើសកំណត់</span>
        <?php elseif($r['display_status'] === 'Borrowed'): ?>
          <span class="badge badge-warning">កំពុងខ្ចី</span>
        <?php else: ?>
          <span class="badge badge-success">បានប្រគល់</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="empty-state"><i class="fas fa-inbox"></i><p>មិនមានទិន្នន័យ</p></div>
  <?php endif; ?>
</div>
<?php endAdminLayout(); ?>
