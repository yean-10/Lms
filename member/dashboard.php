<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireMember();
require_once '../includes/member_layout.php';

$mid = $_SESSION['member_id'];
$fine_per_day = 0.125; // កំណត់តម្លៃពិន័យក្នុងមួយថ្ងៃ 500៛

// ១. ទាញយកស្ថិតិ និងគណនាប្រាក់ពិន័យដែលមិនទាន់បង់ (Unpaid Fines)
$unpaid_fine_res = $conn->query("
    SELECT SUM(DATEDIFF(CURDATE(), return_date) * $fine_per_day) as total_unpaid 
    FROM borrows 
    WHERE member_id = $mid AND status = 'Borrowed' AND return_date < CURDATE()
");
$unpaid_fine = $unpaid_fine_res->fetch_assoc()['total_unpaid'] ?? 0;

$stats = [
    'total'   => $conn->query("SELECT COUNT(*) as c FROM borrows WHERE member_id=$mid")->fetch_assoc()['c'],
    'active'  => $conn->query("SELECT COUNT(*) as c FROM borrows WHERE member_id=$mid AND status='Borrowed'")->fetch_assoc()['c'],
    'overdue' => $conn->query("SELECT COUNT(*) as c FROM borrows WHERE member_id=$mid AND status='Borrowed' 
    AND return_date < CURDATE()")->fetch_assoc()['c'],
    'paid_fines' => $conn->query("SELECT COALESCE(SUM(fine_amount),0) as c FROM borrows WHERE member_id=$mid")->fetch_assoc()['c'],
];

// ២. ទាញយកបញ្ជីសៀវភៅដែលកំពុងខ្ចី
$active_borrows = $conn->query("
    SELECT b.*, bk.title, bk.author, bk.image,
    CASE WHEN b.return_date < CURDATE() THEN 1 ELSE 0 END as is_overdue,
    DATEDIFF(b.return_date, CURDATE()) as days_left
    FROM borrows b JOIN books bk ON b.book_id=bk.id
    WHERE b.member_id=$mid AND b.status='Borrowed'
    ORDER BY b.return_date ASC
");

startMemberLayout('ផ្ទាំងព័ត៌មាន');
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-history"></i></div>
        <div class="stat-info">
            <h3><?= $stats['total'] ?></h3>
            <p>ការខ្ចីសរុប</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-book-reader"></i></div>
        <div class="stat-info">
            <h3><?= $stats['active'] ?></h3>
            <p>កំពុងខ្ចី</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <h3><?= $stats['overdue'] ?></h3>
            <p>លើសកំណត់</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-hand-holding-usd"></i></div>
        <div class="stat-info">
            <h3 style="color: <?= $unpaid_fine > 0 ? '#e74c3c' : 'inherit' ?>;">
                $<?= number_format($unpaid_fine, 2) ?>
            </h3>
            <p>ពិន័យត្រូវបង់</p>
        </div>
    </div>
</div>

<?php if($unpaid_fine > 0): ?>
<div style="background: rgba(248, 220, 147, 0.57); border: 1px solid rgba(231, 76, 60, 0.2); 
padding: 15px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
    <i class="fas fa-exclamation-circle" style="color: #e74c3c; font-size: 20px;"></i>
    <span style="color: red; font-size: 14px;">
        អ្នកមានប្រាក់ពិន័យចំនួន **$<?= number_format($unpaid_fine, 2) ?>** (≈ <?= number_format($unpaid_fine * 4000) ?> ៛)
         ដែលមិនទាន់ទូទាត់។ សូមមេត្តាយកសៀវភៅមកសងវិញឱ្យបានឆាប់!
    </span>
</div>
<?php endif; ?>

<div class="card" style="background: var(--card); border: 1px solid var(--border); border-radius: 16px;">
    <div class="card-header" style="padding: 20px; border-bottom: 1px solid var(--border); display: flex; 
    justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 600; color: black;">
            <i class="fas fa-list-ul" style="color: var(--green); margin-right: 10px;"></i> 
            សៀវភៅដែលកំពុងខ្ចី
        </h3>
        <a href="catalog.php" class="btn btn-primary btn-sm" 
        style="background: var(--green); border: none; 
        padding: 8px 15px; border-radius: 8px; color: red; 
        text-decoration: none; font-size: 16px;">
            <i class="fas fa-plus"></i> ខ្ចីបន្ថែម
        </a>
    </div>

    <div class="card-body" style="padding: 0;  ">
        <?php if($active_borrows->num_rows > 0): ?>
            <table style="width: 100%; border-collapse: collapse">
                <thead>
                    <tr style="text-align: left; background: rgba(255,255,255,0.02);">
                        <th style="padding: 15px; color: black; font-size: 14px; font-weight: bold;">ព័ត៌មានសៀវភៅ</th>
                        <th style="padding: 15px; color: black; font-size: 14px; font-weight: bold;">ថ្ងៃខ្ចី</th>
                        <th style="padding: 15px; color: black; font-size: 14px; font-weight: bold;">ថ្ងៃត្រូវសង</th>
                        <th style="padding: 15px; color: black; font-size: 14px; font-weight: bold;">ស្ថានភាព</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($b = $active_borrows->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div style="width: 45px; height: 60px; background: #1a222c; border-radius: 6px; overflow: hidden; flex-shrink: 0;">
                                    <?php if($b['image']): ?>
                                        <img src="../uploads/books/<?= $b['image'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; opacity: 0.2;">
                                            <i class="fas fa-book"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="color: blue; font-weight: 600; font-size: 14px;"><?= htmlspecialchars($b['title']) ?></div>
                                    <div style="color: var(--muted); font-size: 12px;"><?= htmlspecialchars($b['author']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 15px; font-size: 13px; color: #000000;"><?= $b['borrow_date'] ?></td>
                        <td style="padding: 15px; font-size: 13px; color: <?= $b['is_overdue'] ? '#400fd3' : '#f20c0c' ?>;
                         font-weight: <?= $b['is_overdue'] ? '600' : '400' ?>;">
                            <?= $b['return_date'] ?>
                        </td>
                        <td style="padding: 15px;">
                            <?php if($b['is_overdue']): ?>
                                <span style="background: rgba(241, 197, 192, 0.8); color: #e83622; padding: 4px 10px;
                                 border-radius: 20px; 
                                font-size: 11px; font-weight: 600;">លើស <?= abs($b['days_left']) ?> ថ្ងៃ</span>
                            <?php elseif($b['days_left'] <= 3): ?>
                                <span style="background: rgba(240, 225, 201, 0.91); color: #f39c12; 
                                padding: 4px 10px; border-radius: 20px; 
                                font-size: 11px; font-weight: 600;">នៅសល់ <?= $b['days_left'] ?> ថ្ងៃ</span>
                            <?php else: ?>
                                <span style="background: rgba(186, 255, 214, 0.97); color: green; 
                                padding: 4px 10px; border-radius: 20px; 
                                font-size: 11px; font-weight: 600;">កំពុងខ្ចី</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 60px 20px; text-align: center; color: var(--muted);">
                <i class="fas fa-book-open" style="font-size: 40px; margin-bottom: 15px; opacity: 0.2;"></i>
                <p>មិនទាន់មានសៀវភៅដែលកំពុងខ្ចីនៅឡើយទេ</p>
                <a href="catalog.php" style="color: var(--green); text-decoration: none; font-size: 13px; margin-top: 10px; 
                display: inline-block;">ទៅកាន់បញ្ជីសៀវភៅ →</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php endMemberLayout(); ?>