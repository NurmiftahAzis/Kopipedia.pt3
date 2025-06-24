<<?php
require_once '../Homepage/session.php';
require_once '../Homepage/dbkupi.php';

// Cek login staff
if (!isset($_SESSION['username'])) {
    header("Location: s_login.php");
    exit();
}

$username = $_SESSION['username'];

// Ambil filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Ambil STAFFID berdasarkan username
$staff_sql = "SELECT STAFFID FROM STAFF WHERE S_USERNAME = ?";
$staff_stmt = $dbconn->prepare($staff_sql);
$staff_stmt->bind_param("s", $username);
$staff_stmt->execute();
$staff_result = $staff_stmt->get_result();
$staff = $staff_result->fetch_assoc();
$staffId = $staff['STAFFID'];
$staff_stmt->close();

// Pagination
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Hitung total data untuk pagination
$countQuery = "SELECT COUNT(*) AS total FROM ORDERTABLE WHERE STAFFID = ?";
$countStmt = $dbconn->prepare($countQuery);
$countStmt->bind_param("i", $staffId);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRow = $countResult->fetch_assoc();
$totalPages = ceil($totalRow['total'] / $itemsPerPage);
$countStmt->close();

// Filter tambahan untuk query utama
$filterCondition = "";
if ($filter === 'delivery') {
    $filterCondition = "AND d.ORDERID IS NOT NULL";
} elseif ($filter === 'pickup') {
    $filterCondition = "AND p.ORDERID IS NOT NULL";
}

// Ambil data order dengan filter dan pagination
$sql = "
    SELECT 
        o.ORDERID,
        o.KUPIDATE,
        CASE 
            WHEN d.ORDERID IS NOT NULL THEN 'Delivery'
            WHEN p.ORDERID IS NOT NULL THEN 'Pickup'
            ELSE 'Unknown'
        END AS ORDER_TYPE,
        COALESCE(d.D_STATUS, p.P_STATUS, 'Processing') AS STATUS
    FROM ORDERTABLE o
    LEFT JOIN DELIVERY d ON o.ORDERID = d.ORDERID
    LEFT JOIN PICKUP p ON o.ORDERID = p.ORDERID
    WHERE o.STAFFID = ?
    $filterCondition
    ORDER BY o.KUPIDATE DESC
    LIMIT ? OFFSET ?
";

$stmt = $dbconn->prepare($sql);
$stmt->bind_param("iii", $staffId, $itemsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$orders = array();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-image: url(../image/bgDel.png);
            background-color: #f0f4f8;
            padding-top: 70px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 10px;
        }
        .pagination a {
            width: 10px;
            height: 10px;
            background: rgb(114, 113, 113);
            border-radius: 50%;
            cursor: pointer;
        }
        .pagination a.active {
            background-color: #ec4899;
            color: white;
            border: 1px solid #ec4899;
        }
        .pagination a:hover:not(.active) {
            background-color: rgb(191, 41, 129);
        }
    </style>
</head>
<body class="font-sans text-gray-700">
    <?php include '../Homepage/header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-pink-700 text-3xl font-bold">My Assigned Orders</h2>
                <div class="flex items-center space-x-2">
                    <label for="filter" class="text-gray-700">Filter by:</label>
                    <select id="filter" onchange="window.location.href='?filter=' + this.value" 
                            class="border border-gray-300 rounded px-3 py-1 focus:outline-none focus:border-pink-500">
                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Orders</option>
                        <option value="delivery" <?php echo $filter === 'delivery' ? 'selected' : ''; ?>>Delivery Only</option>
                        <option value="pickup" <?php echo $filter === 'pickup' ? 'selected' : ''; ?>>Pickup Only</option>
                    </select>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php 
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php 
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-pink-100">
                        <thead class="bg-pink-100">
                            <tr>
                                <th class="text-left px-4 py-2 border-b">Order ID</th>
                                <th class="text-left px-4 py-2 border-b">Date</th>
                                <th class="text-center px-4 py-2 border-b">Order Type</th>
                                <th class="text-center px-4 py-2 border-b">Status</th>
                                <th class="text-center px-4 py-2 border-b">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-gray-500">No orders assigned to you yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr class="border-b hover:bg-pink-50">
                                        <td class="text-left px-4 py-2"><?php echo htmlspecialchars($order['ORDERID']); ?></td>
                                        <td class="text-left px-4 py-2"><?php echo date('Y-m-d H:i', strtotime($order['KUPIDATE'])); ?></td>
                                        <td class="text-center px-4 py-2">
                                            <span class="px-2 py-1 rounded text-sm <?php echo $order['ORDER_TYPE'] === 'Delivery' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                                <?php echo htmlspecialchars($order['ORDER_TYPE']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center px-4 py-2">
                                            <span class="px-2 py-1 rounded text-sm
                                                <?php 
                                                switch($order['STATUS']) {
                                                    case 'Pending':
                                                        echo 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    case 'Ready':
                                                    case 'Completed':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'Cancelled':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                }
                                                ?>">
                                                <?php echo htmlspecialchars($order['STATUS']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center px-4 py-2">
                                            <?php if ($order['ORDER_TYPE'] === 'Delivery'): ?>
                                                <a href="s.viewOrder.php?id=<?php echo $order['ORDERID']; ?>" 
                                                   class="inline-block px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                                    View & Update
                                                </a>
                                            <?php else: ?>
                                                <a href="s.viewOrder.php?id=<?php echo $order['ORDERID']; ?>" 
                                                   class="inline-block px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                                    View & Update
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php
                        for ($i = 1; $i <= $totalPages; $i++) {
                            $activeClass = $i === $currentPage ? 'active' : '';
                            echo "<a href='?page=$i' class='$activeClass'></a>";
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
