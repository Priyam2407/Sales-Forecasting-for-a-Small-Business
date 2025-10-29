<?php
session_start();
include 'db.php';
$conn = get_conn();

if (!isset($_GET['id'])) {
  die("Invalid request");
}

$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM sales WHERE sale_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Check if a row was actually deleted
if ($stmt->affected_rows > 0) {
    $_SESSION['message'] = "Sale deleted successfully!";
} else {
    $_SESSION['message'] = "Error: Could not delete sale or sale ID was not found.";
}

$conn->close();
header("Location: view_sales.php");
exit;
?>
