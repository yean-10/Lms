<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/admin_layout.php';

// Monthly borrow stats (last 6 months)
$monthly = $conn->query("
  SELECT DATE_FORMAT(borrow_date,'%Y-%m') as month, COUNT(*) as total
  FROM borrows WHERE borrow_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
  GROUP BY month ORDER BY month ASC
");
$monthlyData = []; while($r=$monthly->fetch_assoc()) $monthlyData[] = $r;

// Top books
$topBooks = $conn->query("
  SELECT bk.title, bk.author, COUNT(*) as times
  FROM borrows b JOIN books bk ON b.book_id=bk.id
  GROUP BY b.book_id ORDER BY times DESC LIMIT 5
");

// Top members
$topMembers = $conn->query("
  SELECT m.name, m.member_code,m.class, COUNT(*) as times, SUM(b.fine_amount) as total_fine
  FROM borrows b JOIN members m ON b.member_id=m.id
  GROUP BY b.member_id ORDER BY times DESC LIMIT 5
");

// Category stats
$catStats = $conn->query("
  SELECT bk.category, COUNT(*) as times FROM borrows b
  JOIN books bk ON b.book_id=bk.id WHERE bk.category IS NOT NULL AND bk.category!=''
  GROUP BY bk.category ORDER BY times DESC LIMIT 6
");

// Overview
$overview = [
  'total_borrows' => $conn->query("SELECT COUNT(*) as c FROM borrows")->fetch_assoc()['c'],
  'total_returned' => $conn->query("SELECT COUNT(*) as c FROM borrows WHERE status='Returned'")->fetch_assoc()['c'],
  'total_overdue' => $conn->query("SELECT COUNT(*) as c FROM borrows WHERE status='Borrowed' AND return_date < CURDATE()")->fetch_assoc()['c'],
  'total_fines' => $conn->query("SELECT COALESCE(SUM(fine_amount),0) as c FROM borrows")->fetch_assoc()['c'],
];

startAdminLayout('របាយការណ៍');
?>

<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-book-reader"></i></div>
    <div class="stat-info"><h3><?= $overview['total_borrows'] ?></h3><p>ការខ្ចីសរុប</p></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
    <div class="stat-info"><h3><?= $overview['total_returned'] ?></h3><p>បានប្រគល់</p></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fas fa-clock"></i></div>
    <div class="stat-info"><h3><?= $overview['total_overdue'] ?></h3><p>លើសកំណត់</p></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon yellow"><i class="fas fa-dollar-sign"></i></div>
    <div class="stat-info"><h3>$<?= number_format($overview['total_fines'],2) ?></h3><p>ប្រាក់ពិន័យ</p></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
  <div class="card">
    <div class="card-header"><h3><i class="fas fa-chart-bar" 
    style="color:var(--primary);margin-right:8px"></i>ការខ្ចីប្រចាំខែ</h3></div>
    <div class="card-body">
      <canvas id="monthlyChart" height="200"></canvas>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3><i class="fas fa-chart-pie" 
    style="color:var(--primary);margin-right:8px"></i>ប្រភេទសៀវភៅ
  </h3>
</div>
    <div class="card-body">
      <canvas id="catChart" height="200"></canvas>
    </div>
  </div>
</div>


<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
  <div class="card">
    <div class="card-header">
      <h3><i class="fas fa-trophy" 
    style="color:#f39c12;margin-right:8px"></i>សៀវភៅពេញនិយម</h3></div>
    <table>
      <thead><tr><th>#</th><th>ចំណងជើង</th><th>ចំនួន</th></tr></thead>
      <tbody>
      <?php $i=1; while($r=$topBooks->fetch_assoc()): ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($r['title']) ?><div style="font-size:11px;color:var(--muted)">
          <?= htmlspecialchars($r['author']) ?></div></td>
        <td><span class="badge badge-info"><?= $r['times'] ?></span></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <div class="card">
    <div class="card-header">
      <h3><i class="fas fa-star" 
    style="color:#f39c12;margin-right:8px"></i>សមាជិកសកម្ម</h3></div>
    <table>
      <thead><tr>
        <th>ឈ្មោះ</th>
      <th>ថ្នាក់</th>
      <th>ចំនួនខ្ចី</th>
      <th>ពិន័យ</th>
    </tr></thead>
      <tbody>
      <?php while($r=$topMembers->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($r['name']) ?>
        <div style="font-size:11px;color:var(--muted)"><?= $r['member_code'] ?></div>
      </td>
       <td><?= htmlspecialchars($r['class']) ?></td>
        <td><span class="badge badge-info"><?= $r['times'] ?></span></td>
        <td><?= $r['total_fine'] > 0 ? "<span class='badge badge-danger'>\${$r['total_fine']}</span>" : '-' ?></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
const monthlyData = <?= json_encode($monthlyData) ?>;
const catData = <?php
  $cats = []; $counts = [];
  $result = $conn->query("SELECT bk.category, COUNT(*) as times FROM borrows b JOIN books bk 
  ON b.book_id=bk.id WHERE bk.category IS NOT NULL AND bk.category!='' GROUP BY bk.category 
  ORDER BY times DESC LIMIT 6");
  while($r=$result->fetch_assoc()){ $cats[]=$r['category']; 
  $counts[]=$r['times']; }
  echo json_encode(['labels'=>$cats,'data'=>$counts]);
?>;

Chart.defaults.color = 'rgba(3, 0, 0, 0.6)';
Chart.defaults.borderColor = 'rgba(0, 0, 0, 0.06)';

new Chart(document.getElementById('monthlyChart'), {
  type: 'bar',
  data: {
    labels: monthlyData.map(d=>d.month),
    datasets:[{ label:'ការខ្ចី', data: monthlyData.map(d=>d.total),
      backgroundColor:'rgba(5, 3, 21, 0.5)', 
      borderColor:'#6c2531', borderWidth:2, borderRadius:6 }]
  },
  options:{ plugins:{legend:{display:false}}, scales:{ x:{grid:{display:false}}, y:{grid:{color:'rgba(255,255,255,0.06)'}, 
  beginAtZero:true, ticks:{precision:0} } } }
});

new Chart(document.getElementById('catChart'), {
  type: 'doughnut',
  data: {
    labels: catData.labels,
    datasets:[{ data: catData.data,
      backgroundColor:['rgba(233,69,96,0.7)','rgba(41,128,185,0.7)',
      'rgba(39,174,96,0.7)','rgba(243,156,18,0.7)',
      'rgba(155,89,182,0.7)','rgba(26,188,156,0.7)'],
      borderWidth:2,hoverOffset: 10 }]
  },
  options:{ 
    plugins:{ 
      legend:{
         position:'right', 
  labels:{ 
    padding:12, 
  font:{family:['Siemreap','Khmer OS','sans-serif'], size:12} 
} 

 }, 
 
 tooltip: {
                backgroundColor: 'rgba(0,0,0,0.7)',
                bodyFont: { family: ['Siemreap','Khmer OS','sans-serif'] }
            }
        },

        layout: {
            padding: {
                left: 10,
                right: 10,
                top: 10,
                bottom: 10
            }
        },

  cutout:'65%' }
});
</script>
<?php endAdminLayout(); ?>
