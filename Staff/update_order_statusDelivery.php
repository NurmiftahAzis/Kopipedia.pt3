<?php
// update_order_status.php

// Koneksi ke MySQL (XAMPP)
$servername = "localhost";
$username = "root";
$password = ""; // default XAMPP
$dbname = "kopi"; // Ganti sesuai nama database kamu

// Buat koneksi
$dbconn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($dbconn->connect_error) {
    die(json_encode(['success' => false, 'error' => $dbconn->connect_error]));
}

// Ambil data dari POST request
$orderId = $_POST['orderId'];
$status = $_POST['status'];

// Prepare statement SQL
$sql = "UPDATE DELIVERY SET D_STATUS = ? WHERE ORDERID = ?";
$stmt = $dbconn->prepare($sql);

// Periksa jika prepare gagal
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $dbconn->error]);
    exit;
}

// Bind parameter (s = string, i = integer)
$stmt->bind_param("si", $status, $orderId);

// Jalankan statement
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

// Tutup koneksi
$stmt->close();
$dbconn->close();
?>