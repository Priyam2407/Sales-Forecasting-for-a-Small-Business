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
    $stmt->bind_param('ssid', $date, $product, $qty, $price); 
    $ok = $stmt->execute();

    if($ok) $msg = 'Sale added successfully';
    else $msg = 'Insert failed: ' . $conn->error;

    $stmt->close(); 
    $conn->close();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Sale</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root {
  --primary-color: #0d6efd;
  --secondary-bg: #e9ecef;
  --text-color: #212529;
  --light-bg: #f8f9fa;
  --card-bg: #fff;
  --shadow: 0 6px 20px rgba(0,0,0,0.08);
}

body {
  font-family: 'Poppins', sans-serif;
  margin:0;
  background: var(--light-bg);
  color: var(--text-color);
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* App Wrapper */
.app {
  display: flex;
  flex: 1;
}

/* Sidebar */
.sidebar {
  width: 260px;
  padding: 25px 20px;
  background: var(--card-bg);
  border-right: 1px solid #e0e0e0;
  box-shadow: var(--shadow);
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  z-index: 1000;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 35px;
}

.logo {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  background: var(--primary-color);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 18px;
}

.brand-text h1 { font-size: 20px; margin-bottom:2px; }
.brand-text h1 span { color: var(--primary-color); }
.brand-text .small { font-size:12px; color:#6c757d; }

/* Sidebar Menu */
.menu { display:flex; flex-direction:column; gap:6px; margin-top:25px; }
.menu-item { display:flex; align-items:center; gap:12px; padding:12px 15px; border-radius:10px; color:#495057; text-decoration:none; font-weight:500; font-size:15px; transition: all .2s ease; }
.menu-item i { width:22px; text-align:center; color:#6c757d; font-size:18px; }
.menu-item:hover { background: var(--secondary-bg); color: var(--primary-color); transform: translateX(5px);}
.menu-item.active { background:#e7f1ff; border-left:4px solid var(--primary-color); color:var(--primary-color); }
.sidebar-footer { margin-top:auto; font-size:13px; color:#6c757d; padding-top:20px; border-top:1px solid #f0f0f0; text-align:center; }

/* Main Content */
.content-wrapper { display:flex; flex:1; margin-left:260px; flex-direction: column; }
.main {
  flex: 1;
  padding: 40px 30px;
  display:flex;
  flex-direction: column;
}

.main h2 { font-size:24px; font-weight:600; margin-bottom:25px; }

/* Form */
form { background: var(--card-bg); padding:25px; border-radius:15px; box-shadow: var(--shadow);}
form label { font-weight:500; font-size:14px; color:#555; }
input.form-control { border-radius:10px; border:1px solid #ccc; transition: all .3s ease; }
input.form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 5px rgba(13,110,253,0.3);}
button.btn-primary { border-radius:10px; font-weight:500; transition: all .3s ease; }
button.btn-primary:hover { background:#0b5ed7; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

/* Footer */
.footer-note {
  text-align:center;
  color:#6c757d;
  font-size:13px;
  padding:20px 0;
  width:100%;
  border-top:1px solid #f0f0f0;
  margin-top:auto; /* push footer to bottom */
}

/* Responsive */
@media(max-width:1000px){
  .sidebar { width:100%; position:relative; height:auto; border-right:none; border-bottom:1px solid #e0e0e0; }
  .content-wrapper { margin-left:0; }
  .menu { flex-direction:row; flex-wrap:wrap; justify-content:space-around; }
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
      <a href="index.php" class="menu-item"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
      <a href="add_sale.php" class="menu-item active"><i class="fa-solid fa-plus-circle"></i> Add Sale</a>
      <a href="view_sales.php" class="menu-item"><i class="fa-solid fa-table-cells"></i> View Sales</a>
      <a href="forecast.php" class="menu-item"><i class="fa-solid fa-chart-simple"></i> Forecast</a>
      <a href="export_csv.php" class="menu-item"><i class="fa-solid fa-file-csv"></i> Download CSV</a>
    </nav>
    <div class="sidebar-footer">Logged in as <strong>Admin</strong></div>
  </aside>

  <!-- Main Content -->
  <div class="content-wrapper">
    <div class="main">
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

      <div class="footer-note">
        © 2025 Sales Forecast System — Mini Project | Created by Priyam | Designed with ❤️ for analytics
      </div>
    </div>
  </div>
</div>
</body>
</html>
