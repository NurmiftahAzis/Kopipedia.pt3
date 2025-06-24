<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['custid'])) {
    header("Location: ../Customer/c.login.php");
    exit;
}

// Check if the cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: c.inCart.php");
    exit;
}

// Include the database connection file
include("../Homepage/dbkupi.php");

// Mapping of coffee_name to kupiID
$coffeeToKupiID = [
    'Americano' => 1,
    'Cappuccino' => 2,
    'Spanish Latte' => 3,
    'Salted Camy Frappe' => 4,
    'Espresso Frappe' => 5,
    'Salted Caramel Latte' => 6,
    'Buttercreme Latte' => 7,
    'Coconut Latte' => 8,
    'Hazelnut Latte' => 9,
    'Matcha Latte' => 10,
    'Nesloo' => 11,
    'Matcha Frappe' => 12,
    'Genmaicha Latte' => 13,
    'Biscoff Frappe' => 14,
    'Chocohazel Frappe' => 15,
    'Chocookies' => 16,
    'Buttercreme Choco' => 17,
    'Lemonade' => 18,
    'Cheesecream Matcha' => 19,
    'Ori Matcha' => 20,
    'Strawberry Frappe' => 21,
    'Yam Milk' => 22,
    'Coconut Shake' => 23,
];

// Retrieve order method and delivery time from the session
$orderMethod = $_SESSION['order_method'] ?? 'pickup';
$deliveryTime = $_SESSION['delivery_time'] ?? null; // Ensure this is set in c.delivery.php or c_pickup.php


    $deliveryTime = date('Y-m-d') . ' ' . $deliveryTime; // Combine current date with delivery time


// Loop through each item in the cart
foreach ($_SESSION['cart'] as $item) {
    // Assign kupiID based on coffee_name
    $coffeeName = $item['coffee_name'];
    $kupiID = $coffeeToKupiID[$coffeeName] ?? 0; // Default to 0 if coffee_name is not found

    // Retrieve other item details
    $quantity = $item['quantity'] ?? 1;
    $price = $item['price'];
    $subtotal = $quantity * $price; // Calculate subtotal

    // Prepare variables for binding
    $kupiMilk = $item['milk'] ?? 'N/A';
    $kupiType = $item['coffee_name'];
    $kupiSize = $item['size'] ?? 'N/A';
    $kupiCream = $item['cream'] ?? 'No';
    $kupiBean = $item['bean'] ?? 'N/A';
    $kupiDate = date('Y-m-d H:i:s'); // Current date and time
    $custID = $_SESSION['custid'];

    // Insert into ORDERTABLE with RETURNING clause
    $insertOrderTableSQL = "
        INSERT INTO ORDERTABLE (KUPIMILK, KUPITYPE, KUPISIZE, KUPICREAM, KUPIBEAN, KUPIDATE, CUSTID, STAFFID)
        VALUES (:kupiMilk, :kupiType, :kupiSize, :kupiCream, :kupiBean, TO_DATE(:kupiDate, 'YYYY-MM-DD HH24:MI:SS'), :custID, NULL)
        RETURNING ORDERID INTO :orderID
    ";

    $conn = new mysqli("localhost", "root", "", "kopipedia_db");
    $sql = "INSERT INTO orders (...) VALUES (...)";
    $conn->query($sql);
    $orderID = 0; // Initialize the variable

    // Bind variables for ORDERTABLE
    $stmt->bind_param($stmt, ':kupiMilk', $kupiMilk);
    $stmt->bind_param($stmt, ':kupiType', $kupiType);
    $stmt->bind_param($stmt, ':kupiSize', $kupiSize);
    $stmt->bind_param($stmt, ':kupiCream', $kupiCream);
    $stmt->bind_param($stmt, ':kupiBean', $kupiBean);
    $stmt->bind_param($stmt, ':kupiDate', $kupiDate);
    $stmt->bind_param($stmt, ':custID', $custID);
    $stmt->bind_param($stmt, ':orderID', $orderID, -1, SQLT_INT);

    // Execute the statement
    $result = $stmt->execute($stmt);

    if (!$result) {
        $e = $stmt->error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    }

    // Insert into ORDERDETAIL using the same $orderID
    $insertOrderDetailSQL = "
        INSERT INTO ORDERDETAIL (QUANTITY, PRICEPERORDER, SUBTOTAL, KUPIID, ORDERID)
        VALUES (:quantity, :pricePerOrder, :subtotal, :kupiID, :orderID)
    ";

    $stmtDetail = $conn->prepare($condb, $insertOrderDetailSQL);

    // Bind variables for ORDERDETAIL
    $stmt->bind_param($stmtDetail, ':quantity', $quantity);
    $stmt->bind_param($stmtDetail, ':pricePerOrder', $price);
    $stmt->bind_param($stmtDetail, ':subtotal', $subtotal);
    $stmt->bind_param($stmtDetail, ':kupiID', $kupiID);
    $stmt->bind_param($stmtDetail, ':orderID', $orderID);

    $resultDetail = $stmt->execute($stmtDetail);

    if (!$resultDetail) {
        $e = $stmt->error($stmtDetail);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    }

    // Debugging: Check the generated ORDERID
echo "Generated ORDERID for ORDERTABLE: " . $orderID . "<br>";

// Insert into DELIVERY if orderMethod is 'delivery'
if ($orderMethod === 'delivery' && $deliveryTime) {
    $insertDeliverySQL = "
        INSERT INTO DELIVERY (ORDERID, D_TIME, D_STATUS)
        VALUES (:orderID, TO_DATE(:deliveryTime, 'YYYY-MM-DD HH24:MI:SS'), 'Pending')
    ";

    $stmtDelivery = $conn->prepare($condb, $insertDeliverySQL);
    $stmt->bind_param($stmtDelivery, ':orderID', $orderID);
    $stmt->bind_param($stmtDelivery, ':deliveryTime', $deliveryTime);

    $resultDelivery = $stmt->execute($stmtDelivery);

    if (!$resultDelivery) {
        $e = $stmt->error($stmtDelivery);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    }
}

// Insert into PICKUP if orderMethod is 'pickup'
if ($orderMethod === 'pickup' && $deliveryTime) {
    echo "Inserting into DELIVERY or PICKUP with ORDERID: " . $orderID . "<br>";

    $insertPickupSQL = "
        INSERT INTO PICKUP (ORDERID, P_TIME, P_STATUS)
        VALUES (:orderID, TO_DATE(:deliveryTime, 'YYYY-MM-DD HH24:MI:SS'), 'Pending')
    ";
    echo "Inserting into DELIVERY or PICKUP with ORDERID: " . $orderID . "<br>";

    $stmtPickup = $conn->prepare($condb, $insertPickupSQL);
    $stmt->bind_param($stmtPickup, ':orderID', $orderID);
    $stmt->bind_param($stmtPickup, ':deliveryTime', $deliveryTime);

    $resultPickup = $stmt->execute($stmtPickup);

    if (!$resultPickup) {
        $e = $stmt->error($stmtPickup);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    }
}


   
}

// Commit the transaction
$stmt->execute();

// Close the connection
$conn->close();

// Clear the cart after the order is finalized
unset($_SESSION['cart']);

// Redirect to a success page
header("Location: c_orderSuccess.php");
exit;
?>