<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'create') {
    $date_received = sanitize($_POST['date_received']);
    $time_received = sanitize($_POST['time_received']);
    $supplier = sanitize($_POST['supplier']);
    $driver_name = sanitize($_POST['driver_name']);
    $total_boxes = (int)$_POST['total_boxes'];
    
    $query = "INSERT INTO shipments (date_received, time_received, supplier, driver_name, total_boxes) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $date_received, $time_received, $supplier, $driver_name, $total_boxes);
    
    if ($stmt->execute()) {
        header("Location: index.php?tab=shipments&success=Shipment created successfully");
    } else {
        header("Location: shipment_form.php?error=Failed to create shipment");
    }
    exit();
}

elseif ($action === 'update') {
    $shipment_id = (int)$_POST['shipment_id'];
    $date_received = sanitize($_POST['date_received']);
    $time_received = sanitize($_POST['time_received']);
    $supplier = sanitize($_POST['supplier']);
    $driver_name = sanitize($_POST['driver_name']);
    $total_boxes = (int)$_POST['total_boxes'];
    
    $query = "UPDATE shipments SET date_received = ?, time_received = ?, supplier = ?, driver_name = ?, total_boxes = ? 
              WHERE shipment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssii", $date_received, $time_received, $supplier, $driver_name, $total_boxes, $shipment_id);
    
    if ($stmt->execute()) {
        header("Location: index.php?tab=shipments&success=Shipment updated successfully");
    } else {
        header("Location: shipment_form.php?id=$shipment_id&error=Failed to update shipment");
    }
    exit();
}

elseif ($action === 'delete') {
    $shipment_id = (int)$_GET['id'];
    
    $query = "DELETE FROM shipments WHERE shipment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $shipment_id);
    
    if ($stmt->execute()) {
        header("Location: index.php?tab=shipments&success=Shipment deleted successfully");
    } else {
        header("Location: index.php?tab=shipments&error=Failed to delete shipment");
    }
    exit();
}

header("Location: index.php?tab=shipments");
exit();
?>
