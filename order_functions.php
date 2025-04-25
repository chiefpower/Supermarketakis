<?php
function createGuestOrderWithItems(PDO $pdo, array $userData, array $orderItems, float $totalPrice): array {
    try {
        // Start transaction
        $pdo->beginTransaction();
       // file_put_contents('debug_post.txt', print_r( $userData, true));
        // 1. Create a guest user
        $randomSuffix = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $guestUsername = 'guest_' . $randomSuffix;
        $dummyPassword = bin2hex(random_bytes(16));

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$guestUsername, $userData['email'], $dummyPassword]);
        $userId = $pdo->lastInsertId();

        // 2. Insert profile details
        $stmt = $pdo->prepare("
            INSERT INTO user_profiles (
                user_id, first_name, last_name, phone, address_line_1, address_line_2,
                country, zip_code, notes, preferred_store_id, is_guest
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE)
        ");
        $stmt->execute([
            $userId,
            $userData['first_name'],
            $userData['last_name'],
            $userData['phone'],
            $userData['address_line_1'],
            $userData['address_line_2'] ?? null,
            $userData['country'],
            $userData['zip_code'],
            $userData['notes'] ?? null,
            $userData['preferred_store_id'] ?? null
        ]);
        //file_put_contents('debug_post.txt', print_r( $userData, true));
        // 3. Create the order
        $stmt = $pdo->prepare("INSERT INTO user_orders (user_id, total_price) VALUES (?, ?)");
        $stmt->execute([$userId, $totalPrice]);
        $orderId = $pdo->lastInsertId();

        // 4. Insert order items
        $stmt = $pdo->prepare("INSERT INTO user_order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($orderItems as $item) {
            $stmt->execute([
                $orderId,
                $item['id'],
                $item['quantity'],
                $item['price']
            ]);
        }

        // Commit transaction
        $pdo->commit();

        return [
            'success' => true,
            'user_id' => $userId,
            'order_id' => $orderId
        ];

    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
?>