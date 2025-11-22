<?php
include __DIR__ . '/../../includes/auth_check.php';
checkRole('Admin');
include __DIR__ . '/../../includes/db_connect.php';

$type = $_GET['type'] ?? 'sales';

// Simple CSV export (can be enhanced with PDF library)
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="report_' . date('Y-m-d_H-i-s') . '.csv"');

$output = fopen('php://output', 'w');

if ($type === 'sales') {
    fputcsv($output, ['Transaction ID', 'Date & Time', 'Cashier', 'Payment Type', 'Amount']);
    
    $period = $_GET['period'] ?? 'daily';
    $start_date = $_GET['start_date'] ?? date('Y-m-d');
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    $payment_type = $_GET['payment_type'] ?? 'All';
    
    $query = "SELECT t.transaction_id, t.transaction_date, u.name, t.payment_type, t.total_amount
              FROM transactions t
              JOIN users u ON t.user_id = u.user_id
              WHERE 1=1";
    
    if ($period === 'daily') {
        $query .= " AND DATE(t.transaction_date) = '$start_date'";
    } elseif ($period === 'custom') {
        $query .= " AND DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";
    }
    
    if ($payment_type !== 'All') {
        $payment_type = sanitize($payment_type);
        $query .= " AND t.payment_type = '$payment_type'";
    }
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            '#' . str_pad($row['transaction_id'], 6, '0', STR_PAD_LEFT),
            date('M d, Y H:i', strtotime($row['transaction_date'])),
            $row['name'],
            $row['payment_type'],
            '₱' . number_format($row['total_amount'], 2)
        ]);
    }
}

fclose($output);
exit();
?>
