<?php


$data = json_decode(file_get_contents('php://input'), true);

$_SESSION['cart'] = $data['cart'] ?? [];
$_SESSION['discount'] = $data['discount'] ?? 0;

echo json_encode(['success' => true]);
?>
