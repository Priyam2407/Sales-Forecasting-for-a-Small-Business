<?php
include 'db.php';
$conn = get_conn();
$res = $conn->query("SELECT * FROM sales ORDER BY sale_date");
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sales_export.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array('sale_id','sale_date','product_name','quantity','price','created_at'));
while($r = $res->fetch_assoc()){
    fputcsv($output, $r);
}
fclose($output);
$conn->close();
exit;
?>