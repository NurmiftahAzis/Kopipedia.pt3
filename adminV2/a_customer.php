<?php
function fetchCustomers($currentPage, $itemsPerPage)
{
    global $condb; // Koneksi MySQLi

    $offset = ($currentPage - 1) * $itemsPerPage;

    // SQL untuk ambil data customer + jumlah pesanan
    $sql = "
        SELECT 
            c.custID, 
            c.c_username, 
            c.c_email,
            c.c_phonenum,
            COUNT(o.orderID) AS total_orders
        FROM customer c
        LEFT JOIN ordertable o ON c.custID = o.custID
        GROUP BY c.custID, c.c_username, c.c_email, c.c_phonenum
        ORDER BY c.custID
        LIMIT ? OFFSET ?
    ";

    $stmt = $condb->prepare($sql);
    $stmt->bind_param("ii", $itemsPerPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }

    $stmt->close();

    // Hitung total customer
    $countQuery = "SELECT COUNT(*) AS total FROM customer";
    $countResult = $condb->query($countQuery);
    $totalRow = $countResult->fetch_assoc();
    $totalPages = ceil($totalRow['total'] / $itemsPerPage);

    return ['customers' => $customers, 'totalPages' => $totalPages];
}
?>
