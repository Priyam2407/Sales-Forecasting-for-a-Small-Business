<?php
include 'db.php';
$msg = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $date = $_POST['sale_date'];
    $product = $_POST['product_name'];
    $qty = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $conn = get_conn();
    $stmt = $conn->prepare("INSERT INTO sales (sale_date, product_name, quantity, price) VALUES (?,?,?,?)");
    $stmt->bind_param('ssii', $date, $product, $qty, $price); // note: price as integer? better as double, but keep simple
    // fix bind types: use ssid
    $stmt = $conn->prepare("INSERT INTO sales (sale_date, product_name, quantity, price) VALUES (?,?,?,?)");
    $stmt->bind_param('ssis', $date, $product, $qty, $price);
    $ok = $stmt->execute();
    if($ok) $msg = 'Sale added successfully';
    else $msg = 'Insert failed: ' . $conn->error;
    $stmt->close(); $conn->close();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Sale</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <link href="assets/css/styles.css" rel="stylesheet">
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
  <h2>Add Sale</h2>
  <?php if($msg) echo '<div class="alert alert-info">'.$msg.'</div>'; ?>
  <form method="post" class="row g-3">
    <div class="col-md-3">
      <label>Sale Date</label>
      <input type="date" name="sale_date" class="form-control" required>
    </div>
    <div class="col-md-3">
      <label>Product Name</label>
      <input type="text" name="product_name" class="form-control" required>
    </div>
    <div class="col-md-2">
      <label>Quantity</label>
      <input type="number" name="quantity" class="form-control" min="1" required>
    </div>
    <div class="col-md-2">
      <label>Price</label>
      <input type="number" step="0.01" name="price" class="form-control" required>
    </div>
    <div class="col-md-2">
      <label>&nbsp;</label>
      <button class="btn btn-primary d-block">Add</button>
    </div>
  </form>
</div>
<div class="footer-note">
    © 2025 Sales Forecast System — Mini Project | Created by Priyam | Designed with ❤️ for analytics
</div>
</body>
</html>
