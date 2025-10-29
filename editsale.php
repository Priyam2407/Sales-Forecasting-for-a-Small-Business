<?php
session_start();
include 'db.php';
$conn = get_conn();

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid ID");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $product = $_POST['product_name'];
  $qty = $_POST['quantity'];
  $price = $_POST['price'];
  $date = $_POST['sale_date'];

  $stmt = $conn->prepare("UPDATE sales SET product_name=?, quantity=?, price=?, sale_date=? WHERE sale_id=?");
  // Use 'sddsi' (string, double, double, string, integer) - assuming quantity can be decimal? No, quantity is 'i' (integer)
  // Correct types: 'sidsi' (string, integer, double, string, integer)
  $stmt->bind_param("sidsi", $product, $qty, $price, $date, $id);
  $stmt->execute();
  
  // Set the success message
  $_SESSION['message'] = "Sale updated successfully!"; 

  // Redirect to the view page
  header("Location: view_sales.php"); 
  exit;
}

// Fetch the existing sale data to pre-fill the form
$res = $conn->prepare("SELECT * FROM sales WHERE sale_id=?");
$res->bind_param("i", $id);
$res->execute();
$sale = $res->get_result()->fetch_assoc();
$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Sale</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <link href="assets/css/styles.css" rel="stylesheet">
  
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

      .card { border-radius:14px; padding:16px; background:#fff; box-shadow:0 4px 20px rgba(0,0,0,0.05); color:#333; }
      .footer-note { text-align: center; margin-top: 20px; color: #777; font-size: 13px; padding: 20px; }

      @media (max-width:1000px){
        .main{margin-left:0;padding:16px;}
        .sidebar{width:100%;position:relative;height:auto;}
      }
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
      <div class="brand">
        <div class="logo">SF</div>
        <div class="brand-text">
          <h1>Sales<span>Forecast</span></h1>
          <p class="small">Small Retail</p>
        </div>
      </div>
      <nav class="menu">
        <a href="index.php" class="menu-item"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="add_sale.php" class="menu-item"><i class="fa-solid fa-plus-circle"></i> Add Sale</a>
        <a href="view_sales.php" class="menu-item active"><i class="fa-solid fa-table-cells"></i> View Sales</a>
        <a href="forecast.php" class="menu-item"><i class="fa-solid fa-chart-simple"></i> Forecast</a>
        <a href="export_csv.php" class="menu-item"><i class="fa-solid fa-file-csv"></i> Download CSV</a>
      </nav>
      <div class="sidebar-footer">Logged in as <strong>Admin</strong></div>
    </aside> 

    <main class="main">
      <div class="container mt-5 card">
        <h2>Edit Sale</h2>
        <form method="post" class="mt-3">
          <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" name="product_name" value="<?= htmlspecialchars($sale['product_name']) ?>" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" value="<?= $sale['quantity'] ?>" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="number" step="0.01" name="price" value="<?= $sale['price'] ?>" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Sale Date</label>
            <input type="date" name="sale_date" value="<?= $sale['sale_date'] ?>" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-success">Update</button>
          <a href="view_sales.php" class="btn btn-secondary">Cancel</a>
        </form>
      </div>
      
      <div class="footer-note">
          © 2025 Sales Forecast System — Mini Project | Created by Priyam | Designed with ❤️ for analytics
      </div>
    </main>
    </div>
</body>
</html>
