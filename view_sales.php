<?php
session_start();
include 'db.php';
$conn = get_conn();

// Session Message Handling
$message = ''; 
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Fetch all sales
$res = $conn->query("SELECT * FROM sales ORDER BY sale_id DESC");
$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>View Sales</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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
          <h2>View Sales</h2>

          <?php if (!empty($message)) : ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <strong>Success!</strong> <?= htmlspecialchars($message) ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
          <?php endif; ?>

          <table class="table table-striped mt-3">
              <thead>
                  <tr>
                      <th>ID</th>
                      <th>Date</th>
                      <th>Product</th>
                      <th>Quantity</th>
                      <th>Price (₹)</th>
                      <th>Actions</th>
                  </tr>
              </thead>
              <tbody>
                  <?php while($r = $res->fetch_assoc()) : ?>
                  <tr>
                      <td><?= $r['sale_id'] ?></td>
                      <td><?= $r['sale_date'] ?></td>
                      <td><?= htmlspecialchars($r['product_name']) ?></td>
                      <td><?= $r['quantity'] ?></td>
                      <td><?= number_format($r['price'], 2) ?></td>
                      <td>
                          <a href="editsale.php?id=<?= $r['sale_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                          <a href="deletesale.php?id=<?= $r['sale_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this sale?');">Delete</a>
                      </td>
                  </tr>
                  <?php endwhile; ?>
              </tbody>
          </table>
          
          <a href="index.php" class="btn btn-info mt-3">Go to Dashboard</a>
      </div>
      
      <div class="footer-note">
          © 2025 Sales Forecast System — Mini Project | Created by Priyam | Designed with ❤️ for analytics
      </div>
    </main>
  </div>
</body>
</html>
