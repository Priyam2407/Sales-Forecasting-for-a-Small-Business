<?php
include 'db.php';
$months = isset($_POST['months']) ? intval($_POST['months']) : 3;
$conn = get_conn();
$sql = "SELECT sale_date, quantity, price FROM sales";
$res = $conn->query($sql);
$data = [];
while($r = $res->fetch_assoc()){
    $m = date('Y-m', strtotime($r['sale_date']));
    if(!isset($data[$m])) $data[$m]=0;
    $data[$m] += floatval($r['quantity'])*floatval($r['price']);
}
ksort($data);
$months_list = array_keys($data);
$revenues = array_values($data);
// Use simple moving average of last 3 months
$n = count($revenues);
$forecast = [];
if($n==0){
    $message = 'No data to forecast. Add sales first.';
} else {
    $last_month = end($months_list);
    $last_dt = DateTime::createFromFormat('Y-m', $last_month);
    for($i=1;$i<=$months;$i++){
        // compute avg of last 3 available months (or all if <3)
        $window = array_slice($revenues, max(0, $n-3), 3);
        $avg = array_sum($window)/count($window);
        $last_dt->modify('+1 month');
        $forecast[$last_dt->format('Y-m')] = $avg;
        // append to revenues to allow iterative forecasting
        $revenues[] = $avg;
        $n++;
    }
}
$conn->close();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Forecast</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
   <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Styles -->
  <link rel="stylesheet" href="assets/css/styles.css">
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
<div class="container">
  <h2>Forecast (Simple 3-month Moving Average)</h2>
  <?php if(isset($message)) echo '<div class="alert alert-warning">'.$message.'</div>'; ?>
  <?php if(!empty($forecast)){ ?>
    <h4>Forecasted Revenue</h4>
    <table class="table">
      <thead><tr><th>Month</th><th>Forecasted Revenue</th></tr></thead>
      <tbody>
        <?php foreach($forecast as $m=>$v){ ?>
          <tr><td><?= $m ?></td><td>₹ <?= number_format($v,2) ?></td></tr>
        <?php } ?>
      </tbody>
    </table>
  <?php } ?>
  <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
</div>
<div class="footer-note">
    © 2025 Sales Forecast System — Mini Project | Created by Priyam | Designed with ❤️ for analytics
</div>
</body>
</html>
