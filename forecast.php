<?php
// forecast.php
include 'db.php';

// sanitize and set months (allowed values: 3,6,9,12)
$allowed_months = [3,6,9,12];
$months = 3;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['months'])) {
    $m_in = intval($_POST['months']);
    if (in_array($m_in, $allowed_months, true)) $months = $m_in;
}

// fetch sales and aggregate by month (YYYY-MM)
$conn = get_conn();
$sql = "SELECT sale_date, quantity, price FROM sales";
$res = $conn->query($sql);

$data = [];
if($res){
    while($r = $res->fetch_assoc()){
        if(empty($r['sale_date'])) continue;
        $m = date('Y-m', strtotime($r['sale_date']));
        if(!isset($data[$m])) $data[$m] = 0.0;
        $qty = is_numeric($r['quantity']) ? floatval($r['quantity']) : 0.0;
        $price = is_numeric($r['price']) ? floatval($r['price']) : 0.0;
        $data[$m] += $qty * $price;
    }
}
ksort($data);
$months_list = array_keys($data);
$revenues = array_values($data);

// forecast using simple moving average of last 3 months (or less if <3)
$forecast = [];
$message = '';
if(count($revenues) === 0){
    $message = 'No data to forecast. Please add sales first.';
} else {
    $n = count($revenues);
    // clone revenues so we can append iteratively
    $work = $revenues;
    // determine last month date
    $last_month = end($months_list);
    $last_dt = DateTime::createFromFormat('Y-m', $last_month);
    if(!$last_dt){
        // fallback: use current month if parsing failed
        $last_dt = new DateTime(date('Y-m-01'));
    }
    for($i=1; $i<=$months; $i++){
        // window: last 3 values from $work (or fewer if less available)
        $window = array_slice($work, max(0, count($work)-3), 3);
        $avg = array_sum($window) / max(1, count($window));
        // advance month
        $last_dt->modify('+1 month');
        $label = $last_dt->format('Y-m');
        $forecast[$label] = $avg;
        // append to work for iterative forecasting
        $work[] = $avg;
    }
}

// close connection
$conn->close();

// Prepare arrays for charts (historic months + revenues, and forecast months + values)
$historic_labels = array_values($months_list);
$historic_values = array_map('floatval', $revenues);

$forecast_labels = array_keys($forecast);
$forecast_values = array_map('floatval', array_values($forecast));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Forecast — Sales Forecast System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Font & Font Awesome -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    /* Premium embedded CSS (matching dashboard) */
    :root{
      --bg: #f4f6fb;
      --card-bg: #ffffff;
      --muted: #6c757d;
      --primary: #0d6efd;
      --soft-primary: rgba(13,110,253,0.08);
      --shadow: 0 8px 30px rgba(17,24,39,0.06);
      --radius-lg: 14px;
    }
    html,body{height:100%;font-family:'Poppins',sans-serif;background:linear-gradient(180deg,#f6f8fb 0%,var(--bg) 100%);color:#222;margin:0;}
    .app{display:flex;min-height:100vh;align-items:stretch;}
    .sidebar{width:260px;padding:22px;background:#fff;border-right:1px solid #e9eef6;box-shadow:var(--shadow);display:flex;flex-direction:column;position:fixed;height:100vh;z-index:1000;}
    .brand{display:flex;gap:12px;align-items:center;margin-bottom:22px;}
    .logo{width:56px;height:56px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:20px;color:#fff;background:linear-gradient(135deg,var(--primary),#0056d6);box-shadow:0 6px 18px rgba(13,110,253,0.18);}
    .brand-text h1{font-size:18px;margin:0;line-height:1;font-weight:600;}
    .brand-text h1 span{color:var(--primary);}
    .brand-text .small{font-size:12px;color:var(--muted);margin-top:4px;}
    .menu{display:flex;flex-direction:column;gap:8px;margin-top:16px;}
    .menu-item{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;text-decoration:none;color:#2b2b2b;font-weight:500;transition:all .18s ease;border-left:3px solid transparent;}
    .menu-item i{width:22px;text-align:center;color:var(--muted);font-size:16px;}
    .menu-item:hover{background:#f6f8ff;transform:translateX(6px);color:var(--primary);}
    .menu-item.active{background:linear-gradient(90deg,rgba(13,110,253,0.06),transparent);border-left-color:var(--primary);color:var(--primary);transform:none;}
    .sidebar-footer{margin-top:auto;font-size:13px;color:var(--muted);padding-top:18px;border-top:1px solid #f0f3f8;}

    main.main{margin-left:300px;padding:28px;flex:1;min-height:100vh;background:transparent;transition:margin .2s ease;}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;gap:12px;}
    .topbar h2{font-size:22px;margin:0;font-weight:700;}
    .topbar .muted{color:var(--muted);font-size:13px;margin-top:4px;}
    .search{position:relative;}
    .search input{width:280px;padding:10px 38px 10px 14px;border-radius:10px;border:1px solid #e6ecff;background:#fff;box-shadow:0 6px 20px rgba(13,110,253,0.03);outline:none;transition:box-shadow .12s ease,border-color .12s ease;}
    .search input:focus{box-shadow:0 10px 30px rgba(13,110,253,0.12);border-color:var(--primary);}
    .search i{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--muted);}
    .avatar-circle{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;background:linear-gradient(120deg,var(--primary),#0066e0);color:#fff;box-shadow:0 6px 18px rgba(13,110,253,0.14);}

    .card{background:var(--card-bg);border-radius:var(--radius-lg);padding:18px;box-shadow:0 10px 30px rgba(11,22,43,0.04);border:1px solid #eef4ff;}
    .card-title{font-weight:700;margin-bottom:12px;font-size:16px;color:#222;}
    .muted{color:var(--muted);font-size:13px;}

    .btn-primary{background:linear-gradient(90deg,var(--primary),#0066e0);border:none;box-shadow:0 8px 20px rgba(13,110,253,0.12);padding:8px 14px;border-radius:8px;font-weight:600;}
    .btn-outline{background:transparent;border:1px solid rgba(13,110,253,0.12);color:var(--primary);padding:8px 12px;border-radius:8px;font-weight:600;}

    .table thead th{border-bottom:1px solid #eef4ff;}
    .table tbody tr:hover{background:rgba(13,110,253,0.03);}

    .chart-card{height:320px;}
    .footer-note{text-align:center;margin-top:80px;color:var(--muted);font-size:13px;padding:18px 0;border-top:1px solid #f0f3f8;background:transparent;border-radius:0;}

    @media (max-width:1100px){
      main.main{margin-left:0;padding:18px;}
      .sidebar{position:relative;width:100%;height:auto;border-right:none;border-bottom:1px solid #f0f3f8;box-shadow:none;padding:14px;}
      .menu{flex-direction:row;align-items:center;flex-wrap:wrap;gap:6px;}
      .menu-item{padding:8px 10px;font-size:13px;}
      .chart-card{height:260px;}
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
        <a href="index.php" class="menu-item"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="add_sale.php" class="menu-item"><i class="fa-solid fa-plus-circle"></i> Add Sale</a>
        <a href="view_sales.php" class="menu-item"><i class="fa-solid fa-table-cells"></i> View Sales</a>
        <a href="forecast.php" class="menu-item active"><i class="fa-solid fa-chart-simple"></i> Forecast</a>
        <a href="export_csv.php" class="menu-item"><i class="fa-solid fa-file-csv"></i> Download CSV</a>
      </nav>

      <div class="sidebar-footer">Logged in as <strong>Admin</strong></div>
    </aside>

    <!-- Main -->
    <main class="main container-fluid">
      <header class="topbar">
        <div>
          <h2>Forecast</h2>
          <div class="muted">Simple moving-average forecast of future revenue</div>
        </div>

        <div style="display:flex;align-items:center;gap:12px;">
          <div class="search" style="min-width:220px;">
            <input type="search" placeholder="Search month or value..." id="searchInput">
            <i class="fa-solid fa-magnifying-glass"></i>
          </div>
          <div class="avatar-circle">A</div>
        </div>
      </header>

      <section class="content">
        <div class="row g-3 mb-3">
          <div class="col-12 col-lg-8">
            <div class="card">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <div class="card-title">Forecast (3-month moving average)</div>
                  <div class="muted">Iterative forecast for the next <strong><?= htmlspecialchars($months) ?></strong> months</div>
                </div>
                <div>
                  <form method="post" class="d-flex align-items-center gap-2">
                    <label for="months" class="muted mb-0">Months:</label>
                    <select name="months" id="months" class="form-select" style="width:auto;">
                      <option value="3" <?= $months==3 ? 'selected' : '' ?>>3</option>
                      <option value="6" <?= $months==6 ? 'selected' : '' ?>>6</option>
                      <option value="9" <?= $months==9 ? 'selected' : '' ?>>9</option>
                      <option value="12" <?= $months==12 ? 'selected' : '' ?>>12</option>
                    </select>
                    <button class="btn btn-primary" type="submit">Run</button>
                  </form>
                </div>
              </div>

              <?php if(!empty($message)): ?>
                <div class="alert alert-warning"><?= htmlspecialchars($message) ?></div>
              <?php endif; ?>

              <div class="chart-card mt-3">
                <canvas id="forecastChart" style="max-height:100%;"></canvas>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="card">
              <div class="card-title">Quick Info</div>
              <div class="mb-2">
                <strong>Historic months:</strong> <?= count($historic_labels) ?> <br>
                <strong>Forecast months:</strong> <?= count($forecast_labels) ?> <br>
              </div>
              <div class="muted">Method: Simple moving average of last 3 months (iterative)</div>
            </div>

            <div class="card mt-3">
              <div class="card-title">Export</div>
              <div class="d-grid gap-2">
                <a href="export_csv.php" class="btn btn-outline">Download CSV</a>
                <button id="copyForecastBtn" class="btn btn-primary">Copy Forecast (Clipboard)</button>
              </div>
            </div>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-title">Forecast Table</div>
          <?php if(!empty($forecast)): ?>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr><th>Month</th><th class="text-end">Forecasted Revenue (₹)</th></tr>
                </thead>
                <tbody id="forecastTableBody">
                  <?php foreach($forecast as $m => $v): ?>
                    <tr>
                      <td><?= htmlspecialchars($m) ?></td>
                      <td class="text-end"><?= '₹ '.number_format($v,2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="alert alert-info">No forecast available.</div>
          <?php endif; ?>
        </div>

        <div class="footer-note">
          © 2025 Sales Forecast System — Mini Project | Created by Priyam | Designed with ❤️ for analytics
        </div>
      </section>
    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Chart setup -->
  <script>
    // Prepare data from PHP
    const historicLabels = <?= json_encode($historic_labels) ?> || [];
    const historicValues = <?= json_encode($historic_values) ?> || [];
    const forecastLabels = <?= json_encode($forecast_labels) ?> || [];
    const forecastValues = <?= json_encode($forecast_values) ?> || [];

    // Combined display: historic + forecast for continuity on X axis
    const combinedLabels = historicLabels.concat(forecastLabels);
    const combinedHistoric = (new Array(historicLabels.length)).fill(null).map((_,i)=>historicValues[i]);
    // For combined dataset create an array matching combinedLabels where historic values are present else null
    const combinedHistoricForChart = combinedLabels.map((lab, idx) => {
      if(idx < historicLabels.length) return historicValues[idx];
      return null;
    });
    // Forecast series aligned on combinedLabels
    const combinedForecastForChart = combinedLabels.map((lab, idx) => {
      if(idx < historicLabels.length) return null;
      return forecastValues[idx - historicLabels.length] ?? null;
    });

    const ctx = document.getElementById('forecastChart').getContext('2d');
    const forecastChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: combinedLabels,
        datasets: [
          {
            label: 'Historic Revenue (₹)',
            data: combinedHistoricForChart,
            backgroundColor: 'rgba(80,88,255,0.20)',
            borderColor: 'rgba(80,88,255,0.28)',
            borderWidth: 1,
            type: 'bar'
          },
          {
            label: 'Forecast Revenue (₹)',
            data: combinedForecastForChart,
            backgroundColor: 'rgba(13,110,253,0.85)',
            borderColor: '#0d6efd',
            borderWidth: 1,
            type: 'bar'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { stacked: false, grid: { display: false } },
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value){ return '₹ ' + value; }
            }
          }
        },
        plugins: {
          legend: { display: true },
          tooltip: {
            callbacks: {
              label: function(ctx){
                const v = ctx.raw;
                if(v === null) return '';
                return ctx.dataset.label + ': ₹ ' + Number(v).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
              }
            }
          }
        }
      }
    });

    // Copy forecast to clipboard button
    document.getElementById('copyForecastBtn').addEventListener('click', function(){
      if(forecastLabels.length === 0){
        alert('No forecast to copy.');
        return;
      }
      let text = 'Forecast (Month -> Revenue):\n';
      forecastLabels.forEach((lab, i) => {
        text += lab + ' -> ₹ ' + Number(forecastValues[i]).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '\n';
      });
      navigator.clipboard.writeText(text).then(() => {
        alert('Forecast copied to clipboard.');
      }).catch(()=> alert('Could not copy.'));
    });

    // Basic search to filter table rows (month or number)
    const searchInput = document.getElementById('searchInput');
    searchInput && searchInput.addEventListener('input', function(e){
      const q = e.target.value.trim().toLowerCase();
      const tbody = document.getElementById('forecastTableBody');
      if(!tbody) return;
      Array.from(tbody.querySelectorAll('tr')).forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
      });
    });
  </script>
</body>
</html>
