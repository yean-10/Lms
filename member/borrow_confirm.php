<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireMember();
require_once '../includes/member_layout.php';

$mid = $_SESSION['member_id'];
$book_id = (int)($_GET['id'] ?? 0);
$max_books = 3; // កំណត់ចំនួនអតិបរមាដែលអាចខ្ចីបាន

// ១. ឆែកមើលចំនួនសៀវភៅដែលសមាជិកកំពុងខ្ចី ឬកំពុងរង់ចាំការអនុម័ត (មិនទាន់សង)
$count_res = $conn->query("SELECT COUNT(*) as borrowed_count FROM borrows WHERE member_id = $mid AND status IN ('Borrowed', 'Pending')");
$borrowed_count = $count_res->fetch_assoc()['borrowed_count'];

// ២. ទាញយកព័ត៌មានសៀវភៅ
$book = $conn->query("SELECT * FROM books WHERE id = $book_id AND status = 'Available'")->fetch_assoc();

// បង្កើត Variable សម្រាប់ឆែកលក្ខខណ្ឌហាមឃាត់
$is_limit_reached = ($borrowed_count >= $max_books);
$can_borrow = ($book && !$is_limit_reached);

// ប្រសិនបើរកមិនឃើញសៀវភៅទាល់តែសោះ
if (!$book) {
    header("Location: catalog.php"); 
    exit;
}

// កំណត់ថ្ងៃត្រូវសង (៧ ថ្ងៃបន្ទាប់)
$return_date = date('Y-m-d', strtotime('+7 days'));

// នៅពេលសមាជិកចុចប៊ូតុង "បញ្ជាក់ការខ្ចី"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $can_borrow) {
    $conn->begin_transaction();
    try {
        // កែប្រែត្រង់នេះ៖ បញ្ចូលទិន្នន័យដោយដាក់ status ទៅជា 'Pending' (រង់ចាំ) ជំនួសឱ្យ 'Borrowed'
        // និងមិនទាន់ដកចំនួន available_qty ចេញពី Table books ភ្លាមៗទេ (ដកពេល Admin អនុម័ត)
        $conn->query("INSERT INTO borrows (member_id, book_id, borrow_date, return_date, status) 
                      VALUES ($mid, $book_id, CURDATE(), '$return_date', 'Pending')");
        
        $conn->commit();
        $_SESSION['success'] = "ការស្នើសុំខ្ចីសៀវភៅ '" . addslashes($book['title']) . "' ត្រូវបានបញ្ជូន! សូមរង់ចាំការពិនិត្យ និងអនុម័ត (Approve) ពីបណ្ណារក្ស (Admin) សិន។";
        header("Location: my_borrows.php");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "មានបញ្ហាបច្ចេកទេស៖ " . $e->getMessage();
    }
}
startMemberLayout('បញ្ជាក់ការខ្ចីសៀវភៅ');
?>

<div style="max-width: 600px; margin: 0 auto;">
    
    <?php if(isset($error)): ?>
        <div style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 1px solid rgba(231, 76, 60, 0.2);">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if($is_limit_reached): ?>
    <div style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; padding: 20px; border-radius: 16px; border: 1px solid rgba(231, 76, 60, 0.2); margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
        <i class="fas fa-exclamation-circle" style="font-size: 24px;"></i>
        <div>
            <strong style="display: block; margin-bottom: 4px;">មិនអាចស្នើសុំខ្ចីបានទេ!</strong>
            <span style="font-size: 13px; opacity: 0.8;">អ្នកបានខ្ចី ឬកំពុងស្នើសុំសរុបចំនួន <?= $borrowed_count ?> ក្បាលរួចហើយ។ សមាជិកម្នាក់អាចខ្ចីបានអតិបរមាត្រឹម <?= $max_books ?> ក្បាលប៉ុណ្ណោះ។</span>
        </div>
    </div>
    <?php endif; ?>

    <div class="card" style="background: var(--card); border: 1px solid var(--border); 
    border-radius: 20px; overflow: hidden; <?= $is_limit_reached ? 'opacity: 0.6;' : '' ?>">
        <div style="padding: 30px; text-align: center; background: rgba(39, 174, 96, 0.05); 
        border-bottom: 1px solid var(--border);">
            <i class="fas fa-book-reader" style="font-size: 40px; color: green; 
            margin-bottom: 15px;"></i>
            <h2 style="color: blue; font-size: 20px;">ព័ត៌មានសៀវភៅត្រូវខ្ចី</h2>
        </div>

        <div style="padding: 30px; <?= $is_limit_reached ? 'pointer-events: none;' : '' ?>">
            <div style="display: flex; gap: 20px; margin-bottom: 25px;">
                <div style="width: 100px; height: 140px; background: #1a222c; border-radius: 10px; overflow: hidden; border: 1px solid var(--border);">
                    <?php if($book['image']): ?>
                        <img src="../uploads/books/<?= $book['image'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="height: 100%; display: flex; align-items: center; justify-content: center; opacity: 0.2;"><i class="fas fa-book fa-2x"></i></div>
                    <?php endif; ?>
                </div>
                <div style="flex: 1;">
                    <h3 style="color: black; font-size: 18px; margin-bottom: 5px;">
                        <?= htmlspecialchars($book['title']) ?></h3>
                    <p style="color: green; font-size: 14px;">
                        <?= htmlspecialchars($book['author']) ?></p>
                    <div style="margin-top: 10px; font-size: 13px; color: #666;">
                        <i class="fas fa-calendar-alt"></i> ថ្ងៃត្រូវសងត្រឡប់៖ <strong style="color: #27ae60;"><?= $return_date ?></strong> (ទុករយៈពេល ៧ថ្ងៃ)
                    </div>
                </div>
            </div>

            <?php if(!$is_limit_reached): ?>
            <form method="POST" style="margin-top: 30px; display: flex; gap: 15px;">
                <a href="catalog.php" style="flex: 1; 
                text-align: center; 
                background: rgba(44, 42, 42, 0.05); 
                color: red; text-decoration: none;
                 padding: 12px;font-family:'Segoe UI','Khmer OS',sans-serif;
                 border-radius: 12px; border: 1px solid var(--border); 
                 pointer-events: auto;"> <i class="btn fas fa-arrow-left"></i> ត្រឡប់ក្រោយ</a>
                <button type="submit" class="btn btn-primary" style="flex: 2; 
                justify-content: center; padding: 12px; border-radius: 12px; background: #27ae60; border-color: #27ae60;">
                    <i class="fas fa-paper-plane"></i> ផ្ញើពាក្យស្នើសុំខ្ចី
                </button>
            </form>
            <?php endif; ?>
        </div>

        <?php if($is_limit_reached): ?>
        <div style="padding: 0 30px 30px 30px;">
            <a href="catalog.php" class="btn" style="display: block; text-align: center;
             background: var(--green); color: red; text-decoration: none; 
             padding: 12px; border-radius: 12px; font-weight: 600;">
                <i class="fas fa-arrow-left"></i> ត្រឡប់ទៅមើលបញ្ជីសៀវភៅវិញ
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php endMemberLayout(); ?>