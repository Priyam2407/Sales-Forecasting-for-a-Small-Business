<?php
session_start(); // **MUST** be at the very top
include 'db.php';
$conn = get_conn();

// Session Message Handling
$message = ''; 
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after reading
}
// End Session Message Handling

$res = $conn->query("SELECT * FROM sales ORDER BY sale_id DESC");
$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>View Sales</title>
     <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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

<div class="container mt-5">
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
    
    <a href="index.php" class="btn btn-info">Go to Dashboard</a>
</div>
<div class="footer-note">
    © 2025 Sales Forecast System — Mini Project | Created by Priyam | Designed with ❤️ for analytics
</div>
</body>
</html>
