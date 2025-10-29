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
if($res){
    while($row = $res->fetch_assoc()){
        $monthly[] = $row;
    }
}

// Product-wise revenue (sorted by revenue desc)
$sql2 = "SELECT product_name, SUM(quantity*price) as revenue 
         FROM sales 
         GROUP BY product_name 
         ORDER BY revenue DESC";
$res2 = $conn->query($sql2);
$productwise = [];
if($res2){
    while($row = $res2->fetch_assoc()){
        $productwise[] = $row;
    }
}

$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sales Forecast — Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap (kept) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    /* ---------- Premium Embedded CSS (no external styles.css) ---------- */
    :root{
      --bg: #f4f6fb;
      --card-bg: #ffffff;
      --muted: #6c757d;
      --primary: #0d6efd;
      --soft-primary: rgba(13,110,253,0.08);
      --shadow: 0 8px 30px rgba(17,24,39,0.06);
      --radius-lg: 14px;
    }

    html,body{
      height:100%;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(180deg, #f6f8fb 0%, var(--bg) 100%);
      color:#222;
      margin:0;
    }

    .app{
      display:flex;
      min-height:100vh;
      align-items:stretch;
    }

    /* Sidebar */
    .sidebar{
      width:260px;
      padding:22px;
      background: #fff;
      border-right:1px solid #e9eef6;
      box-shadow: var(--shadow);
      display:flex;
      flex-direction:column;
      position:fixed;
      height:100vh;
      z-index:1000;
    }
    .brand{
      display:flex;
      gap:12px;
      align-items:center;
      margin-bottom:22px;
    }
    .logo{
      width:56px;
      height:56px;
      border-radius:12px;
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight:700;
      font-size:20px;
      color:#fff;
      background: linear-gradient(135deg, var(--primary), #0056d6);
      box-shadow: 0 6px 18px rgba(13,110,253,0.18);
    }
    .brand-text h1{
      font-size:18px;
      margin:0;
      line-height:1;
      font-weight:600;
    }
    .brand-text h1 span{ color: var(--primary); }
    .brand-text .small{ font-size:12px; color:var(--muted); margin-top:4px; }

    .menu{ display:flex; flex-direction:column; gap:8px; margin-top:16px; }
    .menu-item{
      display:flex;
      align-items:center;
      gap:12px;
      padding:10px 12px;
      border-radius:10px;
      text-decoration:none;
      color:#2b2b2b;
      font-weight:500;
      transition: all .18s ease;
      border-left: 3px solid transparent;
    }
    .menu-item i{ width:22px; text-align:center; color:var(--muted); font-size:16px; }
    .menu-item:hover{ background: #f6f8ff; transform: translateX(6px); color: var(--primary); }
    .menu-item.active{ background: linear-gradient(90deg, rgba(13,110,253,0.06), transparent); border-left-color: var(--primary); color: var(--primary); transform:none; }

    .sidebar-footer{ margin-top:auto; font-size:13px; color:var(--muted); padding-top:18px; border-top:1px solid #f0f3f8; }

    /* Main content */
    main.main{
      margin-left:300px; /* leave space for sidebar */
      padding:28px;
      flex:1;
      min-height:100vh;
      background: transparent;
      transition: margin .2s ease;
    }

    /* Topbar */
    .topbar{ display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:12px; }
    .topbar h2{ font-size:22px; margin:0; font-weight:700; }
    .topbar .muted{ color:var(--muted); font-size:13px; margin-top:4px; }

    .search{ position:relative; }
    .search input{
      width:280px;
      padding:10px 38px 10px 14px;
      border-radius:10px;
      border:1px solid #e6ecff;
      background: #fff;
      box-shadow: 0 6px 20px rgba(13,110,253,0.03);
      outline:none;
      transition: box-shadow .12s ease, border-color .12s ease;
    }
    .search input:focus{ box-shadow: 0 10px 30px rgba(13,110,253,0.12); border-color: var(--primary); }
    .search i{ position:absolute; right:12px; top:50%; transform:translateY(-50%); color:var(--muted); }

    .avatar-circle{
      width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;
      background: linear-gradient(120deg,var(--primary),#0066e0); color:#fff; box-shadow: 0 6px 18px rgba(13,110,253,0.14);
    }

    /* Cards & layout */
    .cards-row, .charts-row{ display:flex; gap:20px; margin-bottom:20px; flex-wrap:wrap; align-items:stretch; }
    .card{
      background: var(--card-bg);
      border-radius: var(--radius-lg);
      padding:18px;
      box-shadow: 0 10px 30px rgba(11,22,43,0.04);
      border: 1px solid #eef4ff;
      flex: 1 1 300px;
    }
    .stat-card{ display:flex; justify-content:space-between; align-items:center; gap:12px; min-width:280px; }
    .stat-title{ font-size:13px; color:var(--muted); margin-bottom:6px; }
    .stat-value{ font-size:1.9rem; font-weight:700; color:var(--primary); }
    .stat-sub{ font-size:12px; color:#9aa3b2; margin-top:8px; }

    .icon-lg{ font-size:2.6rem; color:rgba(13,110,253,0.9); opacity:0.95; }

    .card-title{ font-weight:700; margin-bottom:10px; font-size:16px; color:#222; }
    .card-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    .card-body{ position:relative; height:260px; }

    .btn-primary{
      background: linear-gradient(90deg,var(--primary),#0066e0);
      border:none;
      box-shadow: 0 8px 20px rgba(13,110,253,0.12);
      padding:8px 14px;
      border-radius:8px;
      font-weight:600;
    }
    .btn-outline{
      background:transparent;
      border:1px solid rgba(13,110,253,0.12);
      color:var(--primary);
      padding:8px 12px;
      border-radius:8px;
      font-weight:600;
    }

    .footer-note{ text-align:center; margin-top:200px; color:var(--muted); font-size:13px; padding:18px 0; border-top:1px solid #f0f3f8; background:transparent; border-radius:0; }

    /* Responsive */
    @media (max-width: 1100px){
      main.main{ margin-left:0; padding:18px; }
      .sidebar{ position:relative; width:100%; height:auto; border-right:none; border-bottom:1px solid #f0f3f8; box-shadow:none; padding:14px; }
      .menu{ flex-direction:row; align-items:center; flex-wrap:wrap; gap:6px; }
      .menu-item{ padding:8px 10px; font-size:13px; }
      .cards-row, .charts-row{ flex-direction:column; }
      .search input{ width:100%; }
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
        <div class="small">Small Retail</div>
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
  <main class="main container-fluid">
    <header class="topbar">
      <div>
        <h2>Dashboard</h2>
        <div class="muted">Overview of monthly revenue & forecasts</div>
      </div>

      <div style="display:flex;align-items:center;gap:12px;">
        <div class="search" style="min-width:220px;">
          <input type="search" placeholder="Search product or month..." id="searchInput">
          <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <div class="avatar-circle">A</div>
      </div>
    </header>

    <section class="content">
      <!-- Cards -->
      <div class="cards-row mb-3">
        <div class="card stat-card">
          <div>
            <div class="stat-title">Total Revenue</div>
            <?php
              $total = 0.0;
              foreach($monthly as $m){
                  $total += floatval($m['revenue']);
              }
              $total_fmt = '₹ '.number_format($total,2);
            ?>
            <div class="stat-value"><?= htmlspecialchars($total_fmt) ?></div>
            <div class="stat-sub">Since records began</div>
          </div>
          <div><i class="fa-solid fa-wallet icon-lg"></i></div>
        </div>

        <div class="card" style="flex:0 0 260px;">
          <div class="card-title">Quick Actions</div>
          <a href="add_sale.php" class="btn btn-primary mb-2 w-100">Add Sale</a>
          <a href="forecast.php" class="btn btn-outline w-100">Run Forecast</a>
        </div>

        <div class="card" style="flex:0 0 220px;">
          <div class="card-title">Data</div>
          <div><strong><?= count($monthly) ?></strong> months</div>
          <div><strong><?= count($productwise) ?></strong> products</div>
        </div>
      </div>

      <!-- Charts -->
      <div class="charts-row">
        <div class="card chart-card">
          <div class="card-header">
            <h3>Monthly Revenue</h3>
            <small class="muted">Aggregated by month</small>
          </div>
          <div class="card-body">
            <canvas id="monthlyChart" style="max-height:100%;"></canvas>
          </div>
        </div>

        <div class="card chart-card">
          <div class="card-header">
            <h3>Product-wise Revenue</h3>
            <small class="muted">Total per product</small>
          </div>
          <div class="card-body">
            <canvas id="productChart" style="max-height:100%;"></canvas>
          </div>
        </div>
      </div>

      <div class="footer-note">
        © 2025 Sales Forecast System — Mini Project | Created by Priyam | Designed with ❤️ for analytics
      </div>
    </section>
  </main>
</div>

<!-- Bootstrap JS (kept) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart JS setup -->
<script>
  // Data arrays (from PHP)
  const monthlyLabels = <?= json_encode(array_map(function($m){ return $m['month']; }, $monthly)) ?> || [];
  const monthlyData = <?= json_encode(array_map(function($m){ return (float)$m['revenue']; }, $monthly)) ?> || [];

  const productLabels = <?= json_encode(array_map(function($p){ return $p['product_name']; }, $productwise)) ?> || [];
  const productData = <?= json_encode(array_map(function($p){ return (float)$p['revenue']; }, $productwise)) ?> || [];

  // Monthly line chart
  const ctxMonthly = document.getElementById('monthlyChart').getContext('2d');
  new Chart(ctxMonthly, {
    type: 'line',
    data: {
      labels: monthlyLabels,
      datasets: [{
        label: 'Revenue (₹)',
        data: monthlyData,
        borderColor: '#0d6efd',
        backgroundColor: 'rgba(13,110,253,0.12)',
        tension: 0.35,
        fill: true,
        pointRadius: 3,
        pointHoverRadius: 6,
        pointBackgroundColor: '#fff',
        pointBorderColor: '#0d6efd',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(ctx){
              let v = ctx.raw;
              return '₹ ' + Number(v).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
            }
          }
        }
      },
      scales: {
        x: { display: true, grid: { display: false } },
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(200,210,230,0.6)' },
          ticks: {
            callback: function(value){
              return '₹ ' + value;
            }
          }
        }
      }
    }
  });

  // Product bar chart
  const ctxProduct = document.getElementById('productChart').getContext('2d');
  new Chart(ctxProduct, {
    type: 'bar',
    data: {
      labels: productLabels,
      datasets: [{
        label: 'Revenue (₹)',
        data: productData,
        backgroundColor: 'rgba(13,110,253,0.85)',
        borderRadius: 8,
        barPercentage: 0.6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(ctx){
              return '₹ ' + Number(ctx.raw).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
            }
          }
        }
      },
      scales: {
        x: { ticks: { autoSkip: false }, grid: { display: false } },
        y: { beginAtZero: true }
      }
    }
  });

  // Simple search filter for product/month labels (client-side)
  document.getElementById('searchInput').addEventListener('input', function(e){
    const q = e.target.value.trim().toLowerCase();
    if(!q){
      // reset charts (simply show original data)
      // easiest approach: reload page to reset (or ideally re-render using stored arrays).
      // We'll re-render using stored arrays:
      renderCharts(monthlyLabels, monthlyData, productLabels, productData);
      return;
    }

    // Filter monthly and product arrays
    const filteredMonths = monthlyLabels.map((lab, idx) => ({lab, val: monthlyData[idx]}))
      .filter(x => x.lab.toLowerCase().includes(q) || String(x.val).includes(q));

    const filteredProducts = productLabels.map((lab, idx) => ({lab, val: productData[idx]}))
      .filter(x => lab.toLowerCase().includes(q) || String(x.val).includes(q));

    const mLabs = filteredMonths.map(x => x.lab);
    const mVals = filteredMonths.map(x => x.val);

    const pLabs = filteredProducts.map(x => x.lab);
    const pVals = filteredProducts.map(x => x.val);

    renderCharts(mLabs, mVals, pLabs, pVals);
  });

  // Helper to re-render charts with new data
  let monthlyChartInstance, productChartInstance;
  function renderCharts(mLabs, mVals, pLabs, pVals){
    // destroy existing if present
    if(monthlyChartInstance) monthlyChartInstance.destroy();
    if(productChartInstance) productChartInstance.destroy();

    const ctxM = document.getElementById('monthlyChart').getContext('2d');
    monthlyChartInstance = new Chart(ctxM, {
      type: 'line',
      data: {
        labels: mLabs,
        datasets: [{
          label: 'Revenue (₹)',
          data: mVals,
          borderColor: '#0d6efd',
          backgroundColor: 'rgba(13,110,253,0.12)',
          tension: 0.35,
          fill: true,
          pointRadius: 3,
          pointHoverRadius: 6,
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
      }
    });

    const ctxP = document.getElementById('productChart').getContext('2d');
    productChartInstance = new Chart(ctxP, {
      type: 'bar',
      data: {
        labels: pLabs,
        datasets: [{ label: 'Revenue (₹)', data: pVals, backgroundColor: 'rgba(13,110,253,0.85)', borderRadius:8 }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
      }
    });
  }

  // initialize instances for later destroy (so re-render works)
  // store initial chart instances (already created above) by calling renderCharts once
  // but to avoid duplication we will only call renderCharts if instances are undefined
  (function initInstances(){
    // if charts exist already (created above), set references by creating and assigning
    // We'll reuse the already-created charts by destroying the initial ones created above and call renderCharts
    // First destroy potentially existing Chart objects created earlier
    Chart.getChart('monthlyChart')?.destroy();
    Chart.getChart('productChart')?.destroy();
    renderCharts(monthlyLabels, monthlyData, productLabels, productData);
  })();
</script>
</body>
</html>
