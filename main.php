<?php
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Minimum quantities
$minQuantities = [
    'fish_feed' => 10,
    'catfish' => 1,
    'materials' => 50
];

// Product names
$productNames = [
    'fish_feed' => 'Fish Feed',
    'catfish' => 'Catfish',
    'materials' => 'Materials'
];

// Response array
$response = ['success' => false, 'message' => ''];

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Sanitize and validate inputs
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $product = filter_input(INPUT_POST, 'product', FILTER_SANITIZE_STRING);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_FLOAT);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
    
    // Validate required fields
    if (empty($name) || empty($address) || empty($phone) || empty($product)) {
        throw new Exception('All required fields must be filled');
    }
    
    // Validate product type
    if (!array_key_exists($product, $minQuantities)) {
        throw new Exception('Invalid product selected');
    }
    
    // Validate quantity
    if ($quantity === false || $quantity < $minQuantities[$product]) {
        throw new Exception('Quantity does not meet minimum requirement for ' . $productNames[$product]);
    }
    
    // Format order date and time
    $orderDate = date('Y-m-d H:i:s');
    $orderNumber = 'FP' . date('Ymd') . rand(1000, 9999);
    
    // Prepare order data
    $orderData = [
        'order_number' => $orderNumber,
        'date' => $orderDate,
        'name' => $name,
        'address' => $address,
        'phone' => $phone,
        'product' => $productNames[$product],
        'quantity' => $quantity,
        'notes' => $notes
    ];
    
    // Save to file (you can modify this to save to database)
    $filename = 'orders.txt';
    $orderLine = sprintf(
        "%s | %s | %s | %s | %s | %s | %.2fkg | %s\n",
        $orderData['order_number'],
        $orderData['date'],
        $orderData['name'],
        $orderData['phone'],
        $orderData['address'],
        $orderData['product'],
        $orderData['quantity'],
        $orderData['notes'] ?: 'No notes'
    );
    
    if (file_put_contents($filename, $orderLine, FILE_APPEND | LOCK_EX) === false) {
        throw new Exception('Failed to save order. Please try again.');
    }
    
    // Optional: Send email notification (uncomment and configure)
    $to = 'khing.gr8t@gmail.com';
    $subject = 'New Order - ' . $orderNumber;
    $message = "New order received:\n\n" .
               "Order Number: {$orderData['order_number']}\n" .
               "Customer: {$orderData['name']}\n" .
               "Phone: {$orderData['phone']}\n" .
               "Product: {$orderData['product']}\n" .
               "Quantity: {$orderData['quantity']}kg\n" .
               "Address: {$orderData['address']}\n" .
               "Notes: " . ($orderData['notes'] ?: 'None');
    
    mail($to, $subject, $message);
    
    // Success response
    $response['success'] = true;
    $response['message'] = "Thank you! Your order #{$orderNumber} has been placed successfully. We will contact you shortly.";
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>