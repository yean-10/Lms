<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireMember();
require_once '../includes/member_layout.php';

$mid = $_SESSION['member_id'];
$filter = sanitize($_GET['filter'] ?? '');
$page = max(1,(int)($_GET['page']??1));
$limit = 10; $offset = ($page-1)*$limit;

// កំណត់តម្លៃពិន័យក្នុងមួយថ្ងៃ (ឧទាហរណ៍ $0.125)
$fine_per_day = 0.125; 

$where = "WHERE b.member_id=$mid";
if($filter === 'active') $where .= " AND b.status='Borrowed'";
elseif($filter === 'returned') $where .= " AND b.status='Returned'";
// កែសម្រួលត្រង់ filter៖ ឱ្យទាញយកទាំង status Pending និងពួកដែលទទេមកបង្ហាញជាមួយគ្នា
elseif($filter === 'pending') $where .= " AND (b.status='Pending' OR b.status='' OR b.status IS NULL)"; 
elseif($filter === 'overdue') $where .= " AND b.status='Borrowed' AND b.return_date < CURDATE()";

$total_res = $conn->query("SELECT COUNT(*) as c FROM borrows b $where");
$total = $total_res ? $total_res->fetch_assoc()['c'] : 0;
$pages = ceil($total/$limit);

// SQL Query ដែលមានការគណនាថ្ងៃលើសកំណត់ (Overdue Days) 
$borrows = $conn->query("
  SELECT b.*, bk.title, bk.author, bk.image,
  CASE WHEN b.status='Borrowed' AND b.return_date < CURDATE() THEN 1 ELSE 0 END as is_overdue,
  CASE 
    WHEN b.status='Borrowed' AND b.return_date < CURDATE() THEN DATEDIFF(CURDATE(), b.return_date) 
    ELSE 0 
  END as overdue_days
  FROM borrows b 
  JOIN books bk ON b.book_id=bk.id
  $where ORDER BY b.id DESC LIMIT $limit OFFSET $offset
");

startMemberLayout('ការខ្ចីរបស់ខ្ញុំ');
?>

<div style="display:flex; gap:10px; margin-bottom:25px; flex-wrap:wrap">
  <?php 
  $tabs = [''=>'ទាំងអស់', 'pending'=>'រង់ចាំ', 'active'=>'កំពុងខ្ចី', 'overdue'=>'លើសកំណត់', 'returned'=>'បានប្រគល់'];
  foreach($tabs as $k => $v): 
    $isActive = ($filter === $k);
  ?>
  <a href="?filter=<?=$k?>" class="btn" style="
    text-decoration: none;
    padding: 8px 18px;
    border-radius: 10px;
    font-size: 14px;
    transition: 0.3s;
    background: <?=$isActive ? 'var(--green)' : 'rgba(255,255,255,0.05)'?>;
    color: <?=$isActive ? 'red' : 'var(--muted)'?>;
    border: 1px solid <?=$isActive ? 'var(--green)' : 'var(--border)'?>;
  ">
    <?=$v?>
  </a>
  <?php endforeach; ?>
</div>

<div class="card" style="background: var(--card); border: 1px solid var(--border); border-radius: 16px; overflow: hidden;">
  <div class="card-header" style="padding: 20px; border-bottom: 1px solid var(--border);">
    <h3 style="font-size: 16px; color: blue;">
        <i class="fas fa-history" style="color:red; margin-right:8px"></i>ប្រវត្តិការខ្ចីសរុប (<?= $total ?>)
    </h3>
  </div>

  <?php if($borrows && $borrows->num_rows > 0): ?>
  <div style="overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr style="text-align: left; background: rgba(241, 237, 199, 0.85);">
          <th style="padding: 15px; color: var(--muted); font-size: 12px;">សៀវភៅ</th>
          <th style="padding: 15px; color: var(--muted); font-size: 12px;">ថ្ងៃខ្ចី / ត្រូវសង</th>
          <th style="padding: 15px; color: var(--muted); font-size: 12px;">ថ្ងៃបានសង</th>
          <th style="padding: 15px; color: var(--muted); font-size: 12px;">ប្រាក់ពិន័យ</th>
          <th style="padding: 15px; color: var(--muted); font-size: 12px;">ស្ថានភាព</th>
        </tr>
      </thead>
      <tbody>
        <?php while($b=$borrows->fetch_assoc()): 
            $days = $b['overdue_days'];
            $fine_usd = $days * $fine_per_day;
            $fine_riel = $fine_usd * 4000; 
        ?>
        <tr style="border-bottom: 1px solid var(--border);">
          <td style="padding: 15px;">
            <div style="display: flex; align-items: center; gap: 12px;">
              <div style="width: 35px; height: 45px; background: #1a222c; border-radius: 4px; overflow: hidden;">
                <?php if($b['image']): ?>
                  <img src="../uploads/books/<?= $b['image'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                  <div style="text-align: center; line-height: 45px; opacity: 0.2;"><i class="fas fa-book"></i></div>
                <?php endif; ?>
              </div>
              <div>
                <div style="color: #008c40; font-weight: 600; font-size: 13px;"><?= htmlspecialchars($b['title']) ?></div>
                <div style="color: var(--muted); font-size: 11px;"><?= htmlspecialchars($b['author']) ?></div>
              </div>
            </div>
          </td>
          <td style="padding: 15px; font-size: 13px;">
            <div style="color: #0b4bec;"><?= $b['borrow_date'] ?></div>
            <div style="color: <?= $b['is_overdue'] ? '#dd2b17' : 'var(--muted)' ?>; font-size: 11px;">
              <i class="far fa-clock"></i> <?= $b['return_date'] ?>
            </div>
          </td>
          <td style="padding: 15px; font-size: 13px; color: var(--muted);">
            <?= $b['actual_return_date'] ?: '<span style="opacity:0.3">---</span>' ?>
          </td>
          <td style="padding: 15px;">
            <?php if($days > 0 && $b['status'] === 'Borrowed'): ?>
                <div style="color: #e74c3c; font-weight: bold; font-size: 13px;">
                    $<?= number_format($fine_usd, 2) ?>
                    <div style="font-size: 10px; color: var(--muted); font-weight: normal;"><?= number_format($fine_riel) ?> ៛</div>
                </div>
            <?php elseif($b['fine_amount'] > 0): ?>
                <div style="color: var(--green); font-weight: bold; font-size: 13px;">
                    $<?= number_format($b['fine_amount'], 2) ?>
                </div>
            <?php else: ?>
                <span style="opacity: 0.3; color: var(--muted)">-</span>
            <?php endif; ?>
          </td>
          <td style="padding: 15px;">
            <?php if($b['status'] === 'Pending' || $b['status'] === '' || is_null($b['status'])): ?>
              <span class="badge" style="background-color: #f39c12; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 500; display: inline-block;">រង់ចាំ</span>
            <?php elseif($b['is_overdue']): ?>
              <span class="badge badge-danger" style="background-color: #dd2b17; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px; display: inline-block;">លើស <?= $days ?> ថ្ងៃ</span>
            <?php elseif($b['status'] === 'Borrowed'): ?>
              <span class="badge badge-warning" style="background-color: #3498db; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px; display: inline-block;">កំពុងខ្ចី</span>
            <?php elseif($b['status'] === 'Returned'): ?>
              <span class="badge badge-success" style="background-color: #2ecc71; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px; display: inline-block;">បានប្រគល់</span>
            <?php else: ?>
              <span class="badge" style="background-color: #95a5a6; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px; display: inline-block;"><?= htmlspecialchars($b['status']) ?></span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="empty-state" style="padding: 60px; text-align: center; color: var(--muted);">
    <i class="fas fa-bookmark" style="font-size: 60px; opacity: 0.1; margin-bottom: 15px;color: red;"></i>
    <p>មិនមានប្រវត្តិខ្ចីសៀវភៅក្នុងបញ្ជីនេះទេ</p>
  </div>
  <?php endif; ?>

  <?php if($pages > 1): ?>
  <div class="pagination" style="padding: 20px;">
    <?php for($i=1;$i<=$pages;$i++): ?>
    <a href="?page=<?=$i?>&filter=<?=urlencode($filter)?>" class="page-btn <?=$i==$page?'active':''?>"><?=$i?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<?php endMemberLayout(); ?>