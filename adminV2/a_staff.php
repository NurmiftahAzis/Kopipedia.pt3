<?php
function fetchStaff($currentPage, $itemsPerPage)
{
    global $condb; // Gunakan koneksi global

    $offset = ($currentPage - 1) * $itemsPerPage;

    // Query ambil data staff dengan LIMIT/OFFSET
    $sql = "
        SELECT staffID, s_username, s_email, s_phonenum, adminid
        FROM staff
        ORDER BY staffID
        LIMIT ? OFFSET ?
    ";

    $stmt = $condb->prepare($sql);
    $stmt->bind_param("ii", $itemsPerPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $staff = [];
    while ($row = $result->fetch_assoc()) {
        $staff[] = $row;
    }
    $stmt->close();

    // Hitung total data untuk pagination
    $countQuery = "SELECT COUNT(*) AS total FROM staff";
    $countResult = $condb->query($countQuery);
    $totalRow = $countResult->fetch_assoc();
    $totalPages = ceil($totalRow['total'] / $itemsPerPage);

    return ['staff' => $staff, 'totalPages' => $totalPages];
}
?>
