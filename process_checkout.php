<?php
// Database connection (PDO instance)
include 'db.php'; 

// Fnction for order creation, contains the 'createGuestOrderWithItems' function
include 'order_functions.php'; 

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $userData = [
        'first_name'    => htmlspecialchars($_POST['firstName']),
        'last_name'     => htmlspecialchars($_POST['lastName']),
        'email'         => htmlspecialchars($_POST['email']),
        'phone'         => htmlspecialchars($_POST['phone']),
        'address_line_1'=> htmlspecialchars($_POST['addressLine1']),
        'address_line_2'=> isset($_POST['addressLine2']) ? htmlspecialchars($_POST['addressLine2']) : '',
        'country'       => htmlspecialchars($_POST['country']),
        'zip_code'      => htmlspecialchars($_POST['zipCode']),
        'notes'         => isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : '',
        'promo_code'    => isset($_POST['promoCode']) ? htmlspecialchars($_POST['promoCode']) : ''
        
    ];
    //file_put_contents('debug_post.txt', print_r($_POST, true));
    // Decoding the JSON into an associative array
    $orderItems = json_decode($_POST['cartItems'], true);
    
    // Check if decoding failed or it's empty
    if (empty($orderItems) || !is_array($orderItems)) {     
        echo json_encode([
            'success' => false,
            'error' => 'Cart is empty'
        ]);
        exit;
    }
    // Product IDs and their quantities and prices (should be passed from the cart, etc.)
   // $orderItems = [
   //     ['product_id' => 1, 'quantity' => 2, 'price' => 3.50],
   //     ['product_id' => 2, 'quantity' => 1, 'price' => 10.00]
   // ];

    // Calculate the total price of the order
    $totalPrice = 0;
    
    foreach ($orderItems as $item) {
        $totalPrice += $item['quantity'] * $item['price'];
    }

    // Check if the email already exists in the 'users' table
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$userData['email']]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        // If email exists, return error as JSON response
        echo json_encode([
            'success' => false,
            'error' => "The email address " . $userData['email'] . " is already registered. Please login."
        ]);
        exit();
    }

    // Call the function to create the guest user, order, and order items
    $result = createGuestOrderWithItems($pdo, $userData, $orderItems, $totalPrice);

    // Handle the result
    if ($result['success']) {
        // Redirect or display a success message
        //echo "Order created successfully! Your order ID is: " . $result['order_id'];
        echo json_encode([
            'success' => true,
            'order_id' => $result['order_id']
        ]);
        // Optionally, send the confirmation email (already handled in the function)
        // You can also redirect the user to a success page
       // header("Location: order_confirmation.php?order_id=" . $result['order_id']);
        exit();
    } else {
        // Handle failure
       // echo json_encode(['error' => $error]);
        echo json_encode([
            'success' => false,
            'error' => $result['error']
        ]);
    }
}
?>
