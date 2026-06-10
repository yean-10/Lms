<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

// ==========================================================================
// ១. ផ្នែកចាត់ចែងសកម្មភាព (Action Handler): Approve, Reject, Delete
// ==========================================================================
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $borrow_id = (int)$_GET['id'];

    // ទាញយកព័ត៌មាននៃការខ្ចីនេះជាមុនសិន
    $check_res = $conn->query("SELECT * FROM borrows WHERE id = $borrow_id");
    $borrow_data = $check_res->fetch_assoc();

    if ($borrow_data) {
        $book_id = $borrow_data['book_id'];

        if ($action === 'approve') {
            // ឆែកមើលថាតើសៀវភៅនៅមានក្នុងស្តុក (Available) ដែរឬទេ
            $book_res = $conn->query("SELECT available_qty FROM books WHERE id = $book_id 
            AND status = 'Available'");
            $book_data = $book_res->fetch_assoc();

            if ($book_data && $book_data['available_qty'] > 0) {
                $conn->begin_transaction();
                try {
                    // កែប្រែស្ថានភាពទៅជា 'Borrowed'
                    $conn->query("UPDATE borrows SET status = 'Borrowed' WHERE id = $borrow_id");
                    
                    // ដកចំនួនសៀវភៅចេញពីស្តុក -1
                    $conn->query("UPDATE books SET available_qty = available_qty - 1 WHERE id = $book_id");

                    $conn->commit();
                    header("Location: action_borrow.php?msg=" . urlencode("បានអនុម័តការខ្ចីសៀវភៅដោយជោគជ័យ និងបានកាត់ស្តុកសៀវភៅរួចរាល់!"));
                    exit;
                } catch (Exception $e) {
                    $conn->rollback();
                    header("Location: action_borrow.php?err=" . urlencode("មានបញ្ហាបច្ចេកទេស៖ " . $e->getMessage()));
                    exit;
                }
            } else {
                header("Location: action_borrow.php?err=" . urlencode("មិនអាចអនុម័តបានទេ! សៀវភៅនេះអស់ពីស្តុកហើយ។"));
                exit;
            }

        } elseif ($action === 'reject') {
            // កែប្រែស្ថានភាពទៅជា 'Rejected'
            $update = $conn->query("UPDATE borrows SET status = 'Rejected' WHERE id = $borrow_id");
            
            if ($update) {
                header("Location: action_borrow.php?msg=" . urlencode("បានបដិសេធពាក្យស្នើសុំខ្ចីសៀវភៅនេះរួចរាល់!"));
            } else {
                header("Location: action_borrow.php?err=" . urlencode("មិនអាច Reject បានទេ! មូលហេតុ៖ " . $conn->error));
            }
            exit;

        } elseif ($action === 'delete') {
            $conn->query("DELETE FROM borrows WHERE id = $borrow_id");
            header("Location: action_borrow.php?msg=" . urlencode("បានលុបទិន្នន័យរួចរាល់!"));
            exit;
        }
    } else {
        header("Location: action_borrow.php?err=" . urlencode("រកមិនឃើញទិន្នន័យការខ្ចីឡើយ។"));
        exit;
    }
}

// ==========================================================================
// ២. ផ្នែកទាញយកទិន្នន័យមកបង្ហាញ (UI & Data Fetching)
// ==========================================================================
require_once '../includes/admin_layout.php';

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';

$filter = sanitize($_GET['filter'] ?? '');
$search = sanitize($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12; 
$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";
if ($filter === 'pending') $where .= " AND (b.status='Pending' OR b.status IS NULL OR b.status='')"; 
elseif ($filter === 'borrowed') $where .= " AND b.status='Borrowed'";
elseif ($filter === 'returned') $where .= " AND b.status='Returned'";
elseif ($filter === 'rejected') $where .= " AND b.status='Rejected'";
elseif ($filter === 'overdue') $where .= " AND b.status='Borrowed' AND b.return_date < CURDATE()";

if ($search) $where .= " AND (m.name LIKE '%$search%' OR bk.title LIKE '%$search%' OR m.member_code LIKE '%$search%')";

$total_res = $conn->query("SELECT COUNT(*) as c FROM borrows b JOIN members m ON b.member_id=m.id JOIN books bk ON b.book_id=bk.id $where");
$total = $total_res->fetch_assoc()['c'];
$pages = ceil($total / $limit);

$borrows = $conn->query("
  SELECT b.*, m.name as member_name, m.member_code, bk.title as book_title, bk.author
  FROM borrows b 
  JOIN members m ON b.member_id=m.id 
  JOIN books bk ON b.book_id=bk.id
  $where 
  ORDER BY b.id DESC 
  LIMIT $limit OFFSET $offset
");

startAdminLayout('សកម្មភាពការខ្ចី/សង');
?>

<?php if($msg): ?>
    <div class="alert alert-success" style="padding: 12px; 
    background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 15px;">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if($err): ?>
    <div class="alert alert-danger" style="padding: 12px; background: #f8d7da; 
    color: #721c24; border-radius: 8px; margin-bottom: 15px;">
    <i class="fas fa-times-circle"></i> <?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-hand-holding-heart" style="color:var(--primary);margin-right:8px"></i>ការខ្ចីសៀវភៅ (<?= $total ?>)</h3>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <form method="GET" style="display:flex;gap:8px;align-items:center">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" class="search-input" 
                          
                           placeholder="ស្វែងរក..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <select name="filter" class="form-select-custom" 
                        style="width:150px;" 
                        onchange="this.form.submit()">
                    <option value="">ទាំងអស់</option>
                    <option value="pending" <?= $filter=='pending'?'selected':'' ?>>រង់ចាំការអនុម័ត</option>
                    <option value="borrowed" <?= $filter=='borrowed'?'selected':'' ?>>កំពុងខ្ចី</option>
                    <option value="overdue" <?= $filter=='overdue'?'selected':'' ?>>លើសកំណត់</option>
                    <option value="returned" <?= $filter=='returned'?'selected':'' ?>>បានប្រគល់</option>
                    <option value="rejected" <?= $filter=='rejected'?'selected':'' ?>>បានបដិសេធ</option>
                </select>
                <button type="submit" class="btn-action btn-search"><i class="fas fa-search"></i></button>
            </form>
            <a href="borrow_add.php" class="btn-action btn-add">
                <i class="fas fa-plus-circle" style="margin-right:5px"></i> ខ្ចីថ្មី
            </a>
        </div>
    </div>

    <?php if($borrows && $borrows->num_rows > 0): ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ល.រ</th>
                        <th>សមាជិក</th>
                        <th>សៀវភៅ</th>
                        <th>ថ្ងៃខ្ចី/ត្រូវសង</th>
                        <th>ថ្ងៃបានសង</th>
                        <th>ប្រាក់ពិន័យ</th>
                        <th>ស្ថានភាព</th>
                        <th>សកម្មភាព</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($b = $borrows->fetch_assoc()):
                        $status = $b['status'];
                        $isOverdue = $status === 'Borrowed' && $b['return_date'] < date('Y-m-d');
                    ?>
                        <tr>
                            <td><?= $b['id'] ?></td>
                            <td>
                                <div style="font-size:12px;color:var(--muted)"><?= $b['member_code'] ?></div>
                                <?= htmlspecialchars($b['member_name']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($b['book_title']) ?>
                                <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($b['author']) ?></div>
                            </td>
                            <td>
                                <div style="font-size: 13px; color: #2ecc71;">📥 <b>ខ្ចី៖</b> <?= $b['borrow_date'] ?></div>
                                <div style="font-size: 13px; <?= $isOverdue ? 'color:red;font-weight:600' : 'color: #e67e22;' ?>">📤 <b>ត្រូវសង៖</b> <?= $b['return_date'] ?></div>
                            </td>
                            <td>
                                <?php if(!empty($b['actual_return_date'])): ?>
                                    <span class="badge" style="background-color: #2ecc71; color: white;"><i class="fas fa-calendar-check"></i> <?= $b['actual_return_date'] ?></span>
                                <?php else: ?>
                                    <span style="color: #aaa; font-style: italic; font-size: 12px;">មិនទាន់សង</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(isset($b['fine_amount']) && $b['fine_amount'] > 0): ?>
                                    <strong style="color: #d63031; font-size: 14px;">$<?= number_format($b['fine_amount'], 2) ?></strong>
                                <?php else: ?>
                                    <span style="color: #2ecc71; font-size: 13px;">$0.00</span>
                                <?php endif; ?>
                            </td>
                            
                            <td style="font-family:'Segoe UI','Khmer OS',sans-serif;font-weight: bold;">
                                <?php if($status === 'Pending' || empty($status)): ?>
                                    <span class="badge" style="background-color: #f39c12; color: white;">⏳ រង់ចាំអនុម័ត</span>
                                <?php elseif($status === 'Rejected'): ?>
                                    <span class="badge" style="background-color: #7f8c8d; color: white;">❌ បដិសេធ</span>
                                <?php elseif($isOverdue): ?>
                                    <span class="badge" style="background-color: #d63031; color: white;">⚠️ លើសកំណត់</span>
                                <?php elseif($status === 'Borrowed'): ?>
                                    <span class="badge" style="background-color: #2980b9; color: white;">📖 កំពុងខ្ចី</span>
                                <?php else: ?>
                                    <span class="badge" style="background-color: #2ecc71; color: white;">✅ បានប្រគល់</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <?php if($status === 'Pending' || empty($status)): ?>
                                        <a href="action_borrow.php?action=approve&id=<?= $b['id'] ?>" class="btn-sm-action btn-approve" onclick="return confirm('តើអ្នកពិតជាចង់អនុម័តឱ្យខ្ចីមែនទេ? (វានឹងកាត់ស្តុកសៀវភៅចេញ ១ ក្បាល)')">
                                            <i class="fas fa-check"></i> Approve
                                        </a>
                                        <a href="action_borrow.php?action=reject&id=<?= $b['id'] ?>" class="btn-sm-action btn-reject" onclick="return confirm('តើអ្នកពិតជាចង់បដិសេធមែនទេ?')">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                        <a href="action_borrow.php?action=delete&id=<?= $b['id'] ?>" class="btn-sm-action btn-delete" onclick="return confirm('តើអ្នកពិតជាចង់លុបទិន្នន័យនេះមែនទេ?')">
                                            <i class="fas fa-trash"></i> លុប
                                        </a>

                                    <?php elseif($status === 'Borrowed'): ?>
                                        <a href="return_book.php?id=<?= $b['id'] ?>" class="btn-sm-action" 
                                           style="background-color: #9b59b6; color:white; ">
                                            <i class="fas fa-undo"></i> ទទួលសៀវភៅសង
                                        </a>

                                    <?php else: ?>
                                        <span style="color:green; font-size: 13px;"><i class="fas fa-check-double"></i> រួចរាល់</span>
                                        <?php if(isset($b['fine_amount']) && $b['fine_amount'] > 0): ?>
                                            <button onclick="printInvoice(<?= htmlspecialchars(json_encode($b)) ?>)" 
                                                    class="btn-sm-action" style="background: #f4a661; 
                                                    color: black !important;">
                                                <i class="fas fa-print"></i> វិក្កយបត្រ
                                            </button>
                                        <?php endif; ?>
                                        <a href="action_borrow.php?action=delete&id=<?= $b['id'] ?>" class="btn-sm-action btn-delete" style="padding: 5px 8px;" onclick="return confirm('តើអ្នកពិតជាចង់លុបទិន្នន័យនេះមែនទេ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state" style="text-align:center; padding:40px; color:#aaa;">
            <i class="fas fa-inbox" style="font-size:40px; margin-bottom:10px;"></i>
            <p>មិនមានទិន្នន័យ</p>
        </div>
    <?php endif; ?>

    <?php if($pages > 1): ?>
        <div class="pagination" style="padding: 20px; display: flex; justify-content: center; gap: 5px;">
            <?php for($i=1; $i<=$pages; $i++): ?>
                <a href="?page=<?=$i?>&q=<?=urlencode($search)?>&filter=<?=urlencode($filter)?>" 
                   style="padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 13px; 
                   <?= $i==$page ? 'background:#3498db; color:white;' : 'background:#f8f9fa; color:#333;' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<div id="printSection"></div>

<script>
function printInvoice(data) {
    const printSection = document.getElementById('printSection');
    const qrMerchantText = `https://wa.me/8559798285111?text=Payment_Fine_ID_${data.id}_Amount_$` + data.fine_amount;
    const qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(qrMerchantText)}`;

    printSection.innerHTML = `
        <div class="receipt-title">បណ្ណាល័យ KCIT</div>
        <div class="receipt-header">
            វិក្កយបត្រពិន័យលើសកំណត់<br>
            កាលបរិច្ឆេទសង៖ ${data.actual_return_date || 'N/A'}<br>
            លេខប្រតិបត្តិការ៖ #BR-${data.id}
        </div>
        <div class="receipt-row"><span>អត្តសញ្ញាណ៖</span><strong>${data.member_code}</strong></div>
        <div class="receipt-row"><span>ឈ្មោះនិស្សិត៖</span><strong>${data.member_name}</strong></div>
        <div class="receipt-row" style="margin-top: 5px;">
            <span>សៀវភៅខ្ចី៖</span>
            <span style="text-align: right; max-width: 60%; font-size: 11px;">${data.book_title}</span>
        </div>
        <div class="receipt-row"><span>ថ្ងៃត្រូវសង៖</span><span>${data.return_date}</span></div>
        <div class="receipt-row receipt-total">
            <span>ទឹកប្រាក់ត្រូវបង់សរុប៖</span>
            <span>$${parseFloat(data.fine_amount).toFixed(2)}</span>
        </div>
        <div class="qr-container">
            <p style="margin-bottom: 5px; font-size: 11px;">ស្កែនដើម្បីបង់ប្រាក់ (KHQR)</p>
            <img id="qrCodeImage" src="${qrImageUrl}" alt="KHQR Payment">
        </div>
        <div class="receipt-footer">
            សូមអរគុណ! សូមរក្សាទុកវិក្កយបត្រនេះ<br>
            ព័ត៌មានបន្ថែម៖ 097 982 85111
        </div>
    `;

    const img = document.getElementById('qrCodeImage');
    img.onload = function() { setTimeout(() => { window.print(); }, 200); };
    if (img.complete) { img.onload(); }
}
</script>

<?php endAdminLayout(); ?>