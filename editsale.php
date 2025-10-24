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
</head>
<body>
<div class="container mt-5">
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
</body>
</html>