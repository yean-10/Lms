<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

$id = (int)($_GET['id'] ?? 0);
$res = $conn->query("SELECT b.*, m.name as member_name, m.class, bk.title as book_title 
                     FROM borrows b 
                     JOIN members m ON b.member_id = m.id 
                     JOIN books bk ON b.book_id = bk.id 
                     WHERE b.id = $id AND b.status = 'Borrowed'");
$borrow = $res->fetch_assoc();

if (!$borrow) {
    header("Location: action_borrow.php?err=" . urlencode("មិនរកឃើញទិន្នន័យខ្ចី ឬសៀវភៅបានសងរួចហើយ!"));
    exit();
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual_return_date_str = $_POST['actual_return_date'];
    
    $return_date = new DateTime($borrow['return_date']);
    $ret_date = new DateTime($actual_return_date_str);
    $fine_amount = 0;
    if ($ret_date > $return_date) {
        $days = $ret_date->diff($return_date)->days;
        $fine_amount = $days * 0.125; // កំណត់តម្លៃពិន័យក្នុងមួយថ្ងៃ 500៛
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE borrows SET actual_return_date=?, fine_amount=?, status='Returned' WHERE id=?");
        $stmt->bind_param("sdi", $actual_return_date_str, $fine_amount, $id);
        $stmt->execute();

        $conn->query("UPDATE books SET available_qty = available_qty + 1 WHERE id=" . $borrow['book_id']);

        $conn->commit();
        header("Location: action_borrow.php?msg=" . urlencode("បានប្រគល់សៀវភៅជោគជ័យ!"));
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $err = "កំហុស៖ " . $e->getMessage();
    }
}

startAdminLayout('ប្រគល់សៀវភៅ');
?>

<div class="return-container">
    <div class="custom-card">
        <div class="custom-card-header">
            <h3><i class="fas fa-undo-alt"></i> ប្រគល់សៀវភៅវិញ</h3>
            <a href="action_borrow.php" class="back-link"><i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ</a>
        </div>

        <div class="custom-card-body">
            <?php if($err): ?>
                <div class="alert alert-danger" style="border-radius: 10px; margin-bottom: 10px;">
                    <?= $err ?></div>
            <?php endif; ?>
            
            <div class="info-box">
                <div class="info-item">
                    <span class="info-label">សមាជិក</span>
                    <span class="info-value"><?= htmlspecialchars($borrow['member_name']) ?></span>
                </div></div> 
<div class="info-box">
                <div class="info-item">
                    <span class="info-label">ថ្នាក់</span>
                    <span class="info-value"><?= htmlspecialchars($borrow['class']) ?></span>
                </div>

</div> 
            <div class="info-box">

                <div class="info-item">
                    <span class="info-label">សៀវភៅ</span>
                    <span class="info-value" style="color: #764ba2;"><?= htmlspecialchars($borrow['book_title']) ?></span>
                </div></div>
                 <div class="info-box">
                <div class="info-item" style="border-bottom: none; padding-bottom: 0;">
                    <span class="info-label">ថ្ងៃត្រូវសង</span>
                    <span class="info-value" style="color: red;"><?= date('d-M-Y', strtotime($borrow['return_date'])) ?></span>
                </div>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">ថ្ងៃដែលបានប្រគល់មកវិញ <span style="color:red">*</span></label>
                    <input type="date" name="actual_return_date" 
                           class="form-control custom-input" 
                           value="<?= date('Y-m-d') ?>" 
                           required>
                </div>

                <div class="btn-group">
                    <a href="action_borrow.php" class="btn-custom btn-cancel">
                        <i class="fas fa-times"></i> បោះបង់
                    </a>
                    <button type="submit" class="btn-custom btn-save">
                        <i class="fas fa-check-circle"></i> បញ្ជាក់ការប្រគល់
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endAdminLayout(); ?>