<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = (int)$_POST['member_id'];
    $book_id = (int)$_POST['book_id'];
    $borrow_date = $_POST['borrow_date'];
    $return_date = $_POST['return_date'];

    // ពិនិត្យមើលចំនួនសៀវភៅដែលអាចខ្ចីបាន
    $book = $conn->query("SELECT available_qty FROM books WHERE id=$book_id")->fetch_assoc();
    if (!$book || $book['available_qty'] < 1) {
        $err = 'សៀវភៅនេះអស់ស្ដុករួចហើយ!';
    } else {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO borrows (member_id, book_id, borrow_date, return_date, status) VALUES (?, ?, ?, ?, 'Borrowed')");
            $stmt->bind_param("iiss", $member_id, $book_id, $borrow_date, $return_date);
            $stmt->execute();

            // កាត់ចំនួនសៀវភៅក្នុងស្តុក
            $conn->query("UPDATE books SET available_qty = available_qty - 1, status = CASE WHEN available_qty-1 <= 0 THEN 'Out of Stock' ELSE 'Available' END WHERE id = $book_id");

            $conn->commit();
            header("Location: action_borrow.php?msg=" . urlencode("បានខ្ចីដោយជោគជ័យ!"));
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $err = "មានបញ្ហា៖ " . $conn->error;
        }
    }
}

$members = $conn->query("SELECT id, member_code, name FROM members ORDER BY name");
$books = $conn->query("SELECT id, title, available_qty FROM books WHERE available_qty > 0 ORDER BY title");

startAdminLayout('ខ្ចីសៀវភៅថ្មី');
?>



<div class="borrow-card">
    <div class="borrow-header">
        <h3><i class="fas fa-book-reader" style="color: #3498db;"></i> ខ្ចីសៀវភៅថ្មី</h3>
        <a href="action_borrow.php" 
        style="font-size: 14px; color: #eb0c0c; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ
        </a>
    </div>

    <div style="padding: 20px;">
        <?php if($err): ?>
            <div class="alert alert-danger" style="padding:15px; border-radius:10px; margin-bottom:20px;">
                <i class="fas fa-exclamation-triangle"></i> <?= $err ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group" style="margin-bottom: 20px;">
                <label>សមាជិកអ្នកខ្ចី *</label>
                <select name="member_id" class="form-control-custom" required>
                    <option value="">-- ជ្រើសរើសសមាជិក --</option>
                    <?php while($m = $members->fetch_assoc()): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars("[{$m['member_code']}] {$m['name']}") ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label>សៀវភៅដែលត្រូវខ្ចី *</label>
                <select name="book_id" class="form-control-custom" required>
                    <option value="">-- ជ្រើសរើសសៀវភៅ --</option>
                    <?php while($b = $books->fetch_assoc()): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['title']) ?> (ស្តុក: <?= $b['available_qty'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="display:flex; gap:20px; margin-bottom: 25px;">
                <div class="form-group" style="flex:1">
                    <label>ថ្ងៃខ្ចី</label>
                    <input type="date" name="borrow_date" class="form-control-custom" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group" style="flex:1">
                    <label>ថ្ងៃត្រូវសងត្រឡប់</label>
                    <input type="date" name="return_date" class="form-control-custom" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                </div>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle" style="font-size: 1.2rem;"></i>
                <span><strong>សម្គាល់៖</strong> ប្រព័ន្ធនឹងពិន័យ $0.125 (៥០០៛) ក្នុងមួយថ្ងៃ ប្រសិនបើប្រគល់សៀវភៅយឺតជាងកាលកំណត់។</span>
            </div>

            <div class="btn-group-custom">
                <a href="action_borrow.php" class="btn-back"> <i class="fas fa-close"></i> បោះបង់</a>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check-circle"></i> បញ្ជាក់ការខ្ចី
                </button>
            </div>
        </form>
    </div>
</div>

<?php endAdminLayout(); ?>