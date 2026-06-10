<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireMember();
require_once '../includes/member_layout.php';

$search = sanitize($_GET['q'] ?? '');
$cat = sanitize($_GET['cat'] ?? '');
$status = sanitize($_GET['status'] ?? '');
$page = max(1,(int)($_GET['page']??1));
$limit = 12; 
$offset = ($page-1)*$limit;

$where = "WHERE 1=1";
if($search) $where .= " AND (title LIKE '%$search%' OR author LIKE '%$search%')";
if($cat) $where .= " AND category='$cat'";
if($status) $where .= " AND status='$status'";

$total = $conn->query("SELECT COUNT(*) as c FROM books $where")->fetch_assoc()['c'];
$pages = ceil($total/$limit);
$books = $conn->query("SELECT * FROM books $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
$cats = $conn->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND 
category!='' ORDER BY category");

startMemberLayout('ម៉ឺនុយសៀវភៅ');
?>

<div style="background: var(--card); padding: 24px; border-radius: 16px; border: 1px solid var(--border); margin-bottom: 30px;">
  <form method="GET" style="display:flex; gap:15px; flex-wrap:wrap; align-items:center">
    
    <div class="search-wrap" style="flex: 2; min-width: 250px; position: relative">
      <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; 
      transform: translateY(-50%); color: var(--muted); pointer-events: none;"></i>
      <input type="text" name="q" class="form-control" style="padding-left: 45px;" 
      placeholder="ស្វែងរកចំណងជើង ឬអ្នកនិពន្ធ..." value="<?= htmlspecialchars($search) ?>">
    </div>
    
    <div style="flex: 1; min-width: 160px;">
      <select name="cat" class="form-control">
        <option value="">ប្រភេទទាំងអស់</option>
        <?php while($c=$cats->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($c['category']) ?>" 
        <?= $cat==$c['category']?'selected':'' ?>><?= htmlspecialchars($c['category']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div style="flex: 0.8; min-width: 140px;">
      <select name="status" class="form-control">
        <option value="">ស្ថានភាព</option>
        <option value="Available" <?=$status=='Available'?'selected':''?>>មានក្នុងស្ដុក</option>
        <option value="Out of Stock" <?=$status=='Out of Stock'?'selected':''?>>អស់ស្ដុក</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">
      <i class="fas fa-search"></i> ស្វែងរក
    </button>
    
  </form>
  
  <div style="margin-top: 15px; font-size: 13px; color: var(--muted); 
  display: flex; align-items: center; gap: 8px;">
    <div style="width: 8px; height: 8px; background: var(--green); border-radius: 50%;"></div>
    បង្ហាញលទ្ធផលសរុប: <span style="color: red; font-size: 16px;font-weight: 700;"><?= $total ?></span> សៀវភៅ
  </div>
</div>

<?php if($books->num_rows > 0): ?>
<div class="books-grid">
<?php while($b=$books->fetch_assoc()): ?>
<div class="book-card">
  <div class="book-cover">
    <?php if($b['image']): ?>
    <img src="../uploads/books/<?= htmlspecialchars($b['image']) ?>" 
    alt="<?= htmlspecialchars($b['title']) ?>">
    <?php else: ?>
    <i class="fas fa-book" style="font-size: 50px; opacity: 0.1;"></i>
    <?php endif; ?>
    
    <div style="position:absolute; top:12px; right:12px">
      <?php if($b['status']==='Available'): ?>
        <span class="badge badge-success" 
        style="box-shadow: 0 4px 10px rgba(235, 208, 208, 0.63)">មាន</span>
      <?php else: ?>
        <span class="badge badge-danger">អស់</span>
      <?php endif; ?>
    </div>
  </div>
  
  <div class="book-info">
    <h4 class="book-title"><?= htmlspecialchars($b['title']) ?></h4>
    <p style="margin-bottom: 8px;"><i class="fas fa-pen-nib" 
    style="font-size: 10px; margin-right: 5px;"></i> <?= htmlspecialchars($b['author']) ?></p>
    
    <div style="margin-top: auto;">
        <div style="display:flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 11px; color: black; background: rgba(239, 220, 3, 0.61); padding: 2px 8px; border-radius: 4px;">
                <?= htmlspecialchars($b['category'] ?: 'ទូទៅ') ?>
            </span>
            <span style="font-size: 11px; color: var(--muted);">Qty: <?= $b['available_qty'] ?></span>
        </div>
        
        <?php if($b['status']==='Available'): ?>
            <a href="borrow_confirm.php?id=<?= $b['id'] ?>" class="btn btn-primary btn-sm" 
            style="width: 100%; margin-top: 12px; justify-content: center;">
                <i class="fas fa-hand-holding"></i> ខ្ចីសៀវភៅ
            </a>
        <?php else: ?>
             <button class="btn1 btn-sm" style="width: 100%; font-size: 13px; font-weight: 600; 
             padding: 8px 0; margin-top: 12px;
             background: #ffffff; color: #9f2222; border: 1px solid #e0e0e0; 
             border-radius: 4px; cursor: not-allowed; text-align: center; 
                            " disabled>
                               <i class="fas fa-ban"></i> ខ្ចីមិនបាន(អស់ពីស្តុក)
                            </button>
        <?php endif; ?>
    </div>
  </div>
</div>
<?php endwhile; ?>
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

<?php else: ?>
<div class="empty-state" style="background: var(--card); border: 1px solid var(--border); border-radius: 16px;">
    <i class="fas fa-search-minus" style="font-size: 50px; opacity: 0.1; margin-bottom: 20px;"></i>
    <p>មិនមានសៀវភៅដែលអ្នកកំពុងស្វែងរកឡើយ</p>
    <a href="catalog.php" style="color: var(--green); margin-top: 10px; display: inline-block;">មើលសៀវភៅទាំងអស់</a>
</div>
<?php endif; ?>

<?php endMemberLayout(); ?>