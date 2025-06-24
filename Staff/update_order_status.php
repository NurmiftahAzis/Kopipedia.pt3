<?php
// update_order_status.php (MySQL version)

// Database connection info
$servername = "localhost";
$username = "root";         // default username di XAMPP
$password = "";             // default password kosong
$dbname = "kopi";           // ganti sesuai nama database kamu

// Buat koneksi
$dbconn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($dbconn->connect_error) {
    die(json_encode(['success' => false, 'error' => $dbconn->connect_error]));
}

// Ambil data dari request
$orderId = $_POST['orderId'];
$status = $_POST['status'];

// Siapkan query
$sql = "UPDATE PICKUP SET P_STATUS = ? WHERE ORDERID = ?";
$stmt = $dbconn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $dbconn->error]);
    exit;
}

// Bind parameter: status = string, orderId = int
$stmt->bind_param("si", $status, $orderId);

// Eksekusi query
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

// Tutup koneksi
$stmt->close();
$dbconn->close();
?>
