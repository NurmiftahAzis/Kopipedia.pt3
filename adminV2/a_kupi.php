<?php
require_once '../Homepage/session.php';
require_once '../Homepage/dbkupi.php';

function fetchKupi($currentPage, $itemsPerPage)
{
    global $condb;

    $offset = ($currentPage - 1) * $itemsPerPage;

    // Query untuk data paginated dari tabel kupi
    $sql = "
        SELECT kupiID, k_name, k_price, k_desc 
        FROM kupi
        ORDER BY kupiID
        LIMIT ? OFFSET ?
    ";

    $stmt = $condb->prepare($sql);
    $stmt->bind_param("ii", $itemsPerPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $kupiRecords = [];
    while ($row = $result->fetch_assoc()) {
        $kupiRecords[] = $row;
    }
    $stmt->close();

    // Query total data
    $countQuery = "SELECT COUNT(*) AS total FROM kupi";
    $countResult = $condb->query($countQuery);
    $totalRow = $countResult->fetch_assoc();
    $totalPages = ceil($totalRow['total'] / $itemsPerPage);

    // Output sebagai JSON
    echo json_encode(['kupi' => $kupiRecords, 'totalPages' => $totalPages]);
    exit;
}

// Tangani permintaan AJAX
if (isset($_GET['page'])) {
    $currentPage = (int)$_GET['page'];
    $itemsPerPage = 10;
    fetchKupi($currentPage, $itemsPerPage);
}
?>
