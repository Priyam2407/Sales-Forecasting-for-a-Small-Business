<?php
include 'db.php';
$conn = get_conn();

// Monthly revenue
$sql = "SELECT DATE_FORMAT(sale_date, '%Y-%m') as month, SUM(quantity*price) as revenue 
        FROM sales 
        GROUP BY month 
        ORDER BY month";
$res = $conn->query($sql);
$monthly = [];
while($row = $res->fetch_assoc()){
    $monthly[] = $row;
}

// Product-wise revenue (sorted by revenue desc)
$sql2 = "SELECT product_name, SUM(quantity*price) as revenue 
         FROM sales 
         GROUP BY product_name 
         ORDER BY revenue DESC";
$res2 = $conn->query($sql2);
$productwise = [];
while($row = $res2->fetch_assoc()){
    $productwise[] = $row;
}

$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sales Forecast — Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Styles -->
  <link rel="stylesheet" href="assets/css/styles.css">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body { font-family: 'Poppins', sans-serif; background: #f5f7fa; color: #333; margin: 0; }
    .app { display: flex; min-height: 100vh; }
    .sidebar { width: 260px; padding: 22px; background: #fff; border-right:1px solid #e0e0e0; box-shadow:0 4px 20px rgba(0,0,0,0.05); display:flex; flex-direction:column; position:fixed; height:100vh; }
    .logo { width:52px;height:52px;border-radius:12px;background:#007bff;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:20px; }
    .brand-text h1 { font-size:18px; margin-bottom:2px; }
    .brand-text h1 span { color:#007bff; }
    .brand-text .small { font-size:12px; color:#555; }
    .menu { display:flex; flex-direction:column; gap:6px; margin-top:30px; }
    .menu-item { display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;color:#333;text-decoration:none;font-weight:500;font-size:14px; transition: all .18s ease; }
    .menu-item i { width:22px; text-align:center; color:#555; }
    .menu-item:hover { transform: translateX(4px); background:#f0f0f0; }
    .menu-item.active { background:#e7f1ff; border-left:3px solid #007bff; }
    .sidebar-footer { margin-top:auto; font-size:13px; color:#666; }

    .main { flex:1; margin-left:270px; padding:20px; background:#f5f7fa; }
    .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
    .topbar h2 { font-size:20px; font-weight:600; }
    .topbar .muted { color:#555; font-size:13px; margin-top:4px; }
    .search { position:relative; }
    .search input { width:240px; padding:8px 34px 8px 12px; border-radius:10px; border:1px solid #ddd; outline:none; box-shadow:0 2px 6px rgba(0,0,0,0.05); }
    .search i { position:absolute; right:10px; top:50%; transform:translateY(-50%); color:#aaa; }
    .avatar-circle { width:40px;height:40px;border-radius:50%;background:#007bff;color:#fff;display:flex;align-items:center;justify-content:center; }

    .cards-row, .charts-row { display:flex; gap:20px; margin-bottom:20px; flex-wrap:wrap; }
    .card { border-radius:14px; padding:16px; background:#fff; box-shadow:0 4px 20px rgba(0,0,0,0.05); color:#333; }
    .stat-card { flex:2 1 300px; display:flex; justify-content:space-between; align-items:center; }
    .small-card { flex:1 1 200px; }
    .chart-card { flex:1 1 500px; padding:18px; }
    .stat-title { font-size:13px; color:#666; margin-bottom:6px; }
    .stat-value { font-size:1.8rem; font-weight:600; color:#007bff; }
    .stat-sub { font-size:12px; color:#999; margin-top:8px; }
    .icon-lg { font-size:2.5rem; color:#007bff; }

    .card-title { font-weight:600; margin-bottom:10px; }
    .btn { padding:8px 15px; border-radius:6px; text-decoration:none; color:#fff; background:#007bff; margin-right:5px; }
    .btn-outline { border:1px solid #007bff; color:#007bff; background:transparent; }
  
    .card-header { display:flex; justify-content:space-between; align-items:center; }
    .card-header h3 { margin:0; font-size:16px; color:#222; }
    .card-body { position:relative; height:240px; }

    @media (max-width:1000px){
      .cards-row, .charts-row{flex-direction:column;}
      .main{margin-left:0;padding:16px;}
      .sidebar{width:100%;position:relative;height:auto;}
    }
  </style>
</head>
<body>
<div class="app">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand">
      <div class="logo">SF</div>
      <div class="brand-text">
        <h1>Sales<span>Forecast</span></h1>
        <p class="small">Small Retail</p>
      </div>
    </div>
    <nav class="menu">
      <a href="index.php" class="menu-item active"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
      <a href="add_sale.php" class="menu-item"><i class="fa-solid fa-plus-circle"></i> Add Sale</a>
      <a href="view_sales.php" class="menu-item"><i class="fa-solid fa-table-cells"></i> View Sales</a>
      <a href="forecast.php" class="menu-item"><i class="fa-solid fa-chart-simple"></i> Forecast</a>
      <a href="export_csv.php" class="menu-item"><i class="fa-solid fa-file-csv"></i> Download CSV</a>
    </nav>
    <div class="sidebar-footer">Logged in as <strong>Admin</strong></div>
  </aside>

  <!-- Main -->
  <main class="main">
    <header class="topbar">
      <div>
        <h2>Dashboard</h2>
        <p class="muted">Overview of monthly revenue & forecasts</p>
      </div>
      <div style="display:flex;align-items:center;gap:10px;">
        <div class="search"><input type="search" placeholder="Search product or month..."><i class="fa-solid fa-magnifying-glass"></i></div>
        <div class="avatar-circle">A</div>
      </div>
    </header>

    <section class="content">
      <!-- Cards -->
      <div class="cards-row">
        <div class="card stat-card">
          <div>
            <div class="stat-title">Total Revenue</div>
            <?php 
              $total=0; 
              foreach($monthly as $m){ $total+=floatval($m['revenue']); } 
              $total_fmt='₹ '.number_format($total,2); 
            ?>
            <div class="stat-value"><?= $total_fmt ?></div>
            <div class="stat-sub">Since records began</div>
          </div>
          <div><i class="fa-solid fa-wallet icon-lg"></i></div>
        </div>
        <div class="card small-card">
          <div class="card-title">Quick Actions</div>
          <a href="add_sale.php" class="btn">Add Sale</a>
          <a href="forecast.php" class="btn btn-outline">Run Forecast</a>
        </div>
        <div class="card small-card">
          <div class="card-title">Data</div>
          <div><strong><?= count($monthly) ?></strong> months</div>
          <div><strong><?= count($productwise) ?></strong> products</div>
        </div>
      </div>

      <!-- Charts -->
      <div class="charts-row">
        <div class="card chart-card">
          <div class="card-header"><h3>Monthly Revenue</h3><small class="muted">Aggregated by month</small></div>
          <div class="card-body"><canvas id="monthlyChart"></canvas></div>
        </div>
        <div class="card chart-card">
          <div class="card-header"><h3>Product-wise Revenue</h3><small class="muted">Total per product</small></div>
          <div class="card-body"><canvas id="productChart"></canvas></div>
        </div>
      </div>

     <div class="footer-note">
    © 2025 Sales Forecast System — Mini Project | Created by Priyam | Designed with ❤️ for analytics
</div>

    </section>
  </main>
</div>

<!-- Chart JS -->
<script>
const monthlyLabels = <?= json_encode(array_column($monthly, 'month')) ?>;
const monthlyData = <?= json_encode(array_map('floatval', array_column($monthly, 'revenue'))) ?>;
const productLabels = <?= json_encode(array_column($productwise, 'product_name')) ?>;
const productData = <?= json_encode(array_map('floatval', array_column($productwise, 'revenue'))) ?>;

new Chart(document.getElementById('monthlyChart'), {
  type: 'line',
  data: {
    labels: monthlyLabels,
    datasets: [{
      label: 'Revenue (₹)',
      data: monthlyData,
      borderColor: '#007bff',
      backgroundColor: 'rgba(0,123,255,0.15)',
      tension: 0.3,
      fill: true
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});

new Chart(document.getElementById('productChart'), {
  type: 'bar',
  data: {
    labels: productLabels,
    datasets: [{
      label: 'Revenue (₹)',
      data: productData,
      backgroundColor: 'rgba(0,123,255,0.7)'
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});
</script>
</body>
</html>
