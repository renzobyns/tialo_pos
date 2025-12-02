<?php
session_start();

// Include necessary files - paths are relative to the front controller (public/index.php)
require_once '../src/includes/db_connect.php';
require_once '../src/includes/auth_check.php';

// Ensure the user is an Admin
if (!isAdmin()) {
    // If not an admin, redirect to a 'not authorized' page or the dashboard
    header("Location: /index.php?page=dashboard&error=not_authorized");
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the installment_id is set
    if (isset($_POST['installment_id'])) {
        $installment_id = filter_var($_POST['installment_id'], FILTER_VALIDATE_INT);

        if ($installment_id) {
            // Prepare the update statement
            $query = "UPDATE installments SET status = 'Paid' WHERE installment_id = ?";
            
            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param("i", $installment_id);
                
                if ($stmt->execute()) {
                    // Success: redirect back to the installment report with a success message
                    header("Location: /index.php?page=reports&tab=installments&success=payment_marked_paid");
                    exit();
                } else {
                    // Error executing statement
                    header("Location: /index.php?page=reports&tab=installments&error=update_failed");
                    exit();
                }
                $stmt->close();
            } else {
                // Error preparing statement
                header("Location: /index.php?page=reports&tab=installments&error=prepare_failed");
                exit();
            }
        } else {
            // Invalid installment ID
            header("Location: /index.php?page=reports&tab=installments&error=invalid_id");
            exit();
        }
    } else {
        // installment_id not provided
        header("Location: /index.php?page=reports&tab=installments&error=no_id_provided");
        exit();
    }
} else {
    // If not a POST request, redirect
    header("Location: /index.php?page=reports&tab=installments");
    exit();
}

$conn->close();
?>
