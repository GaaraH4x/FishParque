<?php
// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

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
        $response['message'] = 'Invalid request method';
        echo json_encode($response);
        exit;
    }
    
    // Get POST data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $product = isset($_POST['product']) ? trim($_POST['product']) : '';
    $quantity = isset($_POST['quantity']) ? floatval($_POST['quantity']) : 0;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    
    // Validate required fields
    if (empty($name)) {
        $response['message'] = 'Name is required';
        echo json_encode($response);
        exit;
    }
    
    if (empty($address)) {
        $response['message'] = 'Address is required';
        echo json_encode($response);
        exit;
    }
    
    if (empty($phone)) {
        $response['message'] = 'Phone number is required';
        echo json_encode($response);
        exit;
    }
    
    if (empty($product)) {
        $response['message'] = 'Please select a product';
        echo json_encode($response);
        exit;
    }
    
    // Validate product type
    if (!array_key_exists($product, $minQuantities)) {
        $response['message'] = 'Invalid product selected';
        echo json_encode($response);
        exit;
    }
    
    // Validate quantity
    if ($quantity < $minQuantities[$product]) {
        $response['message'] = 'Quantity does not meet minimum requirement for ' . $productNames[$product] . ' (Min: ' . $minQuantities[$product] . 'kg)';
        echo json_encode($response);
        exit;
    }
    
    // Format order date and time
    $orderDate = date('Y-m-d H:i:s');
    $orderNumber = 'FP' . date('Ymd') . rand(1000, 9999);
    
    // Prepare order data for saving
    $orderLine = sprintf(
        "Order #%s | Date: %s | Name: %s | Phone: %s | Address: %s | Product: %s | Quantity: %.2fkg | Notes: %s\n",
        $orderNumber,
        $orderDate,
        $name,
        $phone,
        $address,
        $productNames[$product],
        $quantity,
        !empty($notes) ? $notes : 'None'
    );
    
    // Save to file
    $filename = 'orders.txt';
    $saved = @file_put_contents($filename, $orderLine, FILE_APPEND | LOCK_EX);
    
    if ($saved === false) {
        $response['message'] = 'Unable to save order. Please check file permissions or contact support.';
        echo json_encode($response);
        exit;
    }
    
    // Optional: Send email notification (uncomment and configure)
    /*
    $to = 'your-email@fishparque.com';
    $subject = 'New Order - ' . $orderNumber;
    $emailMessage = "New order received:\n\n" .
               "Order Number: {$orderNumber}\n" .
               "Customer: {$name}\n" .
               "Phone: {$phone}\n" .
               "Product: {$productNames[$product]}\n" .
               "Quantity: {$quantity}kg\n" .
               "Address: {$address}\n" .
               "Notes: " . (!empty($notes) ? $notes : 'None');
    
    $headers = 'From: orders@fishparque.com' . "\r\n" .
               'Reply-To: orders@fishparque.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    mail($to, $subject, $emailMessage, $headers);
    */
    
    // Success response
    $response['success'] = true;
    $response['message'] = "Thank you! Your order #{$orderNumber} has been placed successfully. We will contact you shortly.";
    
} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

// Send response
echo json_encode($response);
exit;
?>