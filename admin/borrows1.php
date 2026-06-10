<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';

$filter = sanitize($_GET['filter'] ?? '');
$search = sanitize($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12; 
$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";
if ($filter === 'borrowed') $where .= " AND b.status='Borrowed'";
elseif ($filter === 'returned') $where .= " AND b.status='Returned'";
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

startAdminLayout('ការប្រគល់');
?>

<?php if($msg): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if($err): ?>
    <div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<style>
    /* រចនាប័ទ្មសម្រាប់ Search Bar និង Form Control */
    .search-wrap {
        position: relative;
        display: flex;
        align-items: center;
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 0 12px;
        transition: all 0.3s;
    }

    .search-wrap:focus-within {
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        background: #fff;
    }

    .search-wrap i {
        color: #95a5a6;
        margin-right: 8px;
    }

    .search-input {
        border: none;
        background: transparent;
        padding: 8px 0;
        outline: none;
        width: 200px;
        font-size: 14px;
    }

    .form-select-custom {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #f8f9fa;
        font-family: 'Khmer OS Battambang', sans-serif;
        font-size: 14px;
        cursor: pointer;
        outline: none;
    }

    /* រចនាប័ទ្មប៊ូតុង */
    .btn-action {
        font-family:'Segoe UI','Khmer OS',sans-serif;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-add {
        background: #12e719;
        color: blue !important;
    }

    .btn-add:hover {
        background: #52f48b;
        transform: translateY(-1px);
    }

    .btn-search {
        background: #2c3e50;
        color: white;
        padding: 8px 12px;
    }

    .badge {
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    /* ==========================================================================
       រចនាប័ទ្មសម្រាប់បោះពុម្ភវិក្កយបត្រ (Print Layout)
       ========================================================================== */
    #printSection {
        display: none;
    }

    @media print {
        body * {
            visibility: hidden;
        }
        #printSection, #printSection * {
            visibility: visible;
        }
        #printSection {
            display: block !important;
            position: absolute;
            left: 50%;
            top: 20px;
            transform: translateX(-50%);
            width: 80mm; /* ទំហំសម្រាប់ Receipt Printer */
            background: #fff;
            padding: 15px;
            color: #000;
            font-family: 'Khmer OS Battambang', 'Segoe UI', sans-serif;
            font-size: 12px;
            border: none;
        }
        .receipt-title {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 3px;
        }
        .receipt-header {
            text-align: center;
            font-size: 11px;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }
        .receipt-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .receipt-total {
            border-top: 1px dashed #000;
            margin-top: 10px;
            padding-top: 8px;
            font-weight: bold;
            font-size: 13px;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 8px;
        }
        .qr-container {
            text-align: center;
            margin: 15px 0;
        }
        .qr-container img {
            width: 140px;
            height: 140px;
            display: inline-block;
        }
    }
</style>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-hand-holding-heart" style="color:var(--primary);margin-right:8px"></i>ការខ្ចីសៀវភៅ (<?= $total ?>)</h3>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <form method="GET" style="display:flex;gap:8px;align-items:center">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" class="search-input" 
                           style="font-family:'Segoe UI','Khmer OS',sans-serif;"
                           placeholder="ស្វែងរក..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <select name="filter" class="form-select-custom" 
                        style="width:130px;font-family:'Segoe UI','Khmer OS',sans-serif;" 
                        onchange="this.form.submit()">
                    <option value="">ទាំងអស់</option>
                    <option value="borrowed" <?= $filter=='borrowed'?'selected':'' ?>>កំពុងខ្ចី</option>
                    <option value="overdue" <?= $filter=='overdue'?'selected':'' ?>>លើសកំណត់</option>
                    <option value="returned" <?= $filter=='returned'?'selected':'' ?>>បានប្រគល់</option>
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
                        <th>ថ្ងៃខ្ចី</th>
                        <th>ត្រូវសង</th>
                        <th>បានសង</th>
                        <th>ពិន័យ</th>
                        <th>ស្ថានភាព</th>
                        <th>សកម្មភាព</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($b = $borrows->fetch_assoc()):
                        $isOverdue = $b['status'] === 'Borrowed' && $b['return_date'] < date('Y-m-d');
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
                            <td><?= $b['borrow_date'] ?></td>
                            <td style="<?= $isOverdue ? 'color:var(--primary);font-weight:600' : '' ?>">
                                <?= $b['return_date'] ?>
                            </td>
                            <td style="color:var(--muted)"><?= $b['actual_return_date'] ?: '-' ?></td>
                            <td style="padding:15px;">
                                <?= $b['fine_amount'] > 0 ? "<span style='color:#e74c3c; font-weight:bold;'>\${$b['fine_amount']}</span>" : 
                                '<span style="color:#bdc3c7;">$0.00</span>' ?>
                            </td>
                            
                            <td style="font-family:'Segoe UI','Khmer OS',sans-serif;font-weight: bold;">
                                <?php if($isOverdue): ?>
                                    <span class="badge badge-danger">លើសកំណត់</span>
                                <?php elseif($b['status'] === 'Borrowed'): ?>
                                    <span class="badge badge-warning">កំពុងខ្ចី</span>
                                <?php else: ?>
                                    <span class="badge badge-success">បានប្រគល់</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($b['status'] === 'Borrowed'): ?>
                                    <a href="return_book.php?id=<?= $b['id'] ?>" class="btn btn-success btn-sm" 
                                       style="color:Purple; font-family:'Segoe UI','Khmer OS',sans-serif; font-weight: bold; text-decoration:none;">
                                        <i class="fas fa-undo"></i> ប្រគល់
                                    </a>
                                <?php else: ?>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-check-double" style="color:blue;" title="រួចរាល់"></i>
                                        <?php if($b['fine_amount'] > 0): ?>
                                            <button onclick="printInvoice(<?= htmlspecialchars(json_encode($b)) ?>)" 
                                                    class="btn btn-sm" 
                                                    style="background: #f4a661; color: black;font-family:'Segoe UI','Khmer OS',sans-serif;
                                                     border: none; border-radius: 4px; padding: 2px 8px; font-size: 11px; cursor: pointer;">
                                                <i class="fas fa-print"></i> វិក្កយបត្រ
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>មិនមានទិន្នន័យ</p>
        </div>
    <?php endif; ?>

    <?php if($pages > 1): ?>
        <div class="pagination" style="padding: 20px; display: flex; justify-content: center; gap: 5px;">
            <?php for($i=1; $i<=$pages; $i++): ?>
                <a href="?page=<?=$i?>&q=<?=urlencode($search)?>&filter=<?=urlencode($filter)?>" 
                   style="padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 13px; 
                   <?= $i==$page ? 'background:#3498db; color:#white;' : 'background:#f8f9fa; color:#333;' ?>">
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
    
    // បង្កើតតំណភ្ជាប់ QR Code (អ្នកអាចប្តូរ text នេះទៅតាមអ្វីដែលអ្នកចង់បាន)
    const qrMerchantText = `https://wa.me/8559798285111?text=Payment_Fine_ID_${data.id}_Amount_$${data.fine_amount}`;
    const qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(qrMerchantText)}`;

    // រៀបចំគ្រោងឆ្អឹង HTML របស់វិក្កយបត្រ
    printSection.innerHTML = `
        <div class="receipt-title">បណ្ណាល័យ KCIT</div>
        <div class="receipt-header">
            វិក្កយបត្រពិន័យលើសកំណត់<br>
            កាលបរិច្ឆេទសង៖ ${data.actual_return_date}<br>
            លេខប្រតិបត្តិការ៖ #BR-${data.id}
        </div>
        <div class="receipt-row">
            <span>អត្តសញ្ញាណ៖</span>
            <strong>${data.member_code}</strong>
        </div>
        <div class="receipt-row">
            <span>ឈ្មោះនិស្សិត៖</span>
            <strong>${data.member_name}</strong>
        </div>
        <div class="receipt-row" style="margin-top: 5px;">
            <span>សៀវភៅខ្ចី៖</span>
            <span style="text-align: right; max-width: 60%; font-size: 11px;">${data.book_title}</span>
        </div>
        <div class="receipt-row">
            <span>ថ្ងៃត្រូវសង៖</span>
            <span>${data.return_date}</span>
        </div>
        
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

    // ==========================================================================
    // ដំណោះស្រាយគន្លឹះ៖ បង្ខំឱ្យ Browser រង់ចាំរូបភាព QR Code ទាញមកចប់ ១០០% ទើបព្រីន
    // ==========================================================================
    const img = document.getElementById('qrCodeImage');
    img.onload = function() {
        // បើករង់ចាំ 200ms ទៀតដើម្បីឱ្យច្បាស់ថា Render រូបភាពទាន់នៅលើអេក្រង់
        setTimeout(() => {
            window.print();
        }, 200);
    };

    // ករណីបើវាមានស្រាប់ក្នុង Cache វានឹងដំណើរការភ្លាមៗ
    if (img.complete) {
        img.onload();
    }
}
</script>

<?php endAdminLayout(); ?>