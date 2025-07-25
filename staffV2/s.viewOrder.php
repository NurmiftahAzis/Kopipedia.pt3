<?php
require_once '../Homepage/session.php';
require_once '../Homepage/dbkupi.php';

if (!isset($_GET['id'])) {
    header('Location: s.manageOrder.php');
    exit;
}

$orderId = $_GET['id'];

// Ambil data order
$sql = "SELECT * FROM ORDERTABLE WHERE ORDERID = ?";
$stmt = $dbconn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: s.manageOrder.php');
    exit;
}

// Inisialisasi customer
$order['C_USERNAME'] = '';
$order['C_EMAIL'] = '';
$order['C_PHONENUM'] = '';

// Ambil data customer
if (!empty($order['CUSTID'])) {
    $sql = "SELECT * FROM CUSTOMER WHERE CUSTID = ?";
    $stmt = $dbconn->prepare($sql);
    $stmt->bind_param("i", $order['CUSTID']);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    if ($customer) {
        $order['C_USERNAME'] = $customer['C_USERNAME'];
        $order['C_EMAIL'] = $customer['C_EMAIL'];
        $order['C_PHONENUM'] = $customer['C_PHONENUM'];
    }
    $stmt->close();
}

// Ambil status order
$status = 'Processing';
$isDelivery = false;

// Cek status delivery
$sql = "SELECT D_STATUS FROM DELIVERY WHERE ORDERID = ?";
$stmt = $dbconn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$delivery = $result->fetch_assoc();
$stmt->close();

if ($delivery && !empty($delivery['D_STATUS'])) {
    $status = $delivery['D_STATUS'];
    $isDelivery = true;
} else {
    // Cek status pickup
    $sql = "SELECT P_STATUS FROM PICKUP WHERE ORDERID = ?";
    $stmt = $dbconn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $pickup = $result->fetch_assoc();
    if ($pickup && !empty($pickup['P_STATUS'])) {
        $status = $pickup['P_STATUS'];
    }
    $stmt->close();
}

// Ambil detail item pesanan
$sql = "
    SELECT 
        k.K_NAME,
        k.K_DESC,
        od.QUANTITY,
        od.SUBTOTAL,
        o.KUPISIZE,
        o.KUPITYPE,
        o.KUPIBEAN,
        o.KUPIMILK
    FROM ORDERDETAIL od
    JOIN ORDERTABLE o ON od.ORDERID = o.ORDERID
    JOIN KUPI k ON od.KUPIID = k.KUPIID
    WHERE od.ORDERID = ?
";
$stmt = $dbconn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$totalAmount = 0;
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $totalAmount += $row['SUBTOTAL'];
}
$stmt->close();

// Handle aksi update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'reject') {
        $sql = $isDelivery 
            ? "UPDATE DELIVERY SET D_STATUS = 'Rejected' WHERE ORDERID = ?" 
            : "UPDATE PICKUP SET P_STATUS = 'Rejected' WHERE ORDERID = ?";
    } elseif ($action === 'ready') {
        $sql = $isDelivery 
            ? "UPDATE DELIVERY SET D_STATUS = 'Ready for Delivery' WHERE ORDERID = ?" 
            : "UPDATE PICKUP SET P_STATUS = 'Ready for Pickup' WHERE ORDERID = ?";
    } elseif ($action === 'onway' && $isDelivery) {
        $sql = "UPDATE DELIVERY SET D_STATUS = 'On the Way' WHERE ORDERID = ?";
    } elseif ($action === 'complete') {
        $sql = $isDelivery 
            ? "UPDATE DELIVERY SET D_STATUS = 'Completed' WHERE ORDERID = ?" 
            : "UPDATE PICKUP SET P_STATUS = 'Completed' WHERE ORDERID = ?";
    }

    if (isset($sql)) {
        $stmt = $dbconn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        if ($stmt->execute()) {
            header("Location: s.viewOrder.php?id=" . $orderId);
            exit;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #f0f4f8;
            padding-top: 70px;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="font-sans text-gray-700">
    <?php include '../Homepage/header.php'; ?>
    
    <div class="p-8">
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-pink-700 text-3xl font-bold">Order Details</h2>
                <div class="flex gap-2">
                    <?php if ($status !== 'Rejected' && $status !== 'Completed'): ?>
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" onclick="return confirm('Are you sure you want to reject this order?')"
                                class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                Reject Order
                            </button>
                        </form>
                        
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="ready">
                            <button type="submit" 
                                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                <?php echo $isDelivery ? 'Ready for Delivery' : 'Ready for Pickup'; ?>
                            </button>
                        </form>
                        
                        <?php if ($isDelivery && $status === 'Ready for Delivery'): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="onway">
                                <button type="submit"
                                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    Mark as On the Way
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if ($status === 'Ready for Pickup' || $status === 'On the Way'): ?>
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="complete">
                            <button type="submit" 
                                class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                                Mark as Completed
                            </button>
                        </form>
                    <?php endif; ?>
                    <a href="s.manageOrder.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Back to Orders
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- Order Summary -->
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Order Information</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-gray-600">Order ID</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($order['ORDERID'] ?? ''); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Date</p>
                                <p class="font-semibold"><?php echo isset($order['KUPIDATE']) ? date('Y-m-d H:i', strtotime($order['KUPIDATE'])) : ''; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Status</p>
                                <p class="font-semibold">
                                    <span class="px-2 py-1 rounded text-sm
                                        <?php 
                                        switch($status) {
                                            case 'Pending':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'Ready for Pickup':
                                            case 'Ready for Delivery':
                                            case 'On the Way':
                                                echo 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'Completed':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'Rejected':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600">Total Amount</p>
                                <p class="font-semibold">Rp. <?php echo number_format($totalAmount, 2); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Order Type</p>
                                <p class="font-semibold"><?php echo $isDelivery ? 'Delivery' : 'Pickup'; ?></p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Customer Information</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-gray-600">Name</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($order['C_USERNAME'] ?? ''); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Email</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($order['C_EMAIL'] ?? ''); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Phone</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($order['C_PHONENUM'] ?? ''); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Order Items</h3>
                    <table class="min-w-full border border-pink-100">
                        <thead class="bg-pink-50">
                            <tr>
                                <th class="text-left px-4 py-2 border-b">Item</th>
                                <th class="text-left px-4 py-2 border-b">Customization</th>
                                <th class="text-center px-4 py-2 border-b">Quantity</th>
                                <th class="text-right px-4 py-2 border-b">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2">
                                        <div class="font-semibold">
                                            <?php 
                                            echo htmlspecialchars($item['K_NAME']);
                                            if (!empty($item['KUPITYPE'])) {
                                                echo ' ' . htmlspecialchars($item['KUPITYPE']) . '';
                                            }
                                            ?>
                                        </div>
                                        <?php if (!empty($item['K_DESC'])): ?>
                                            <div class="text-sm text-gray-600"><?php echo htmlspecialchars($item['K_DESC']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="text-sm">
                                            <?php if (!empty($item['KUPISIZE'])): ?>
                                                Size: <?php echo htmlspecialchars($item['KUPISIZE']); ?><br>
                                            <?php endif; ?>
                                            <?php if (!empty($item['KUPIBEAN'])): ?>
                                                Bean: <?php echo htmlspecialchars($item['KUPIBEAN']); ?><br>
                                            <?php endif; ?>
                                            <?php if (!empty($item['KUPIMILK'])): ?>
                                                Milk: <?php echo htmlspecialchars($item['KUPIMILK']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center px-4 py-2"><?php echo htmlspecialchars($item['QUANTITY']); ?></td>
                                    <td class="text-right px-4 py-2">Rp. <?php echo number_format($item['SUBTOTAL'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="bg-pink-50">
                                <td colspan="3" class="text-right px-4 py-2 font-semibold">Total:</td>
                                <td class="text-right px-4 py-2 font-semibold">Rp. <?php echo number_format($totalAmount, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>