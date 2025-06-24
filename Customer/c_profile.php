<?php
include '../Homepage/dbkupi.php'; // Koneksi MySQLi
require_once '../Homepage/session.php';

// Cek apakah sudah login
$custid = $_SESSION['custid'] ?? null;
if (!$custid) {
    header('Location: ../Customer/c.login.php');
    exit();
}

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $phonenum = $_POST['phonenum'];
    $email    = $_POST['email'];
    $address  = $_POST['address'];

    // Update query
    $sql = "UPDATE customer 
            SET c_username = ?, c_pass = ?, c_phonenum = ?, c_email = ?, c_address = ? 
            WHERE custid = ?";
    $stmt = $condb->prepare($sql);
    $stmt->bind_param("sssssi", $username, $password, $phonenum, $email, $address, $custid);

    if ($stmt->execute()) {
        // Update session data
        $_SESSION['username'] = $username;
        $_SESSION['password'] = $password;
        $_SESSION['phonenum'] = $phonenum;
        $_SESSION['email']    = $email;
        $_SESSION['address']  = $address;

        header('Location: ../Customer/c.profile.php');
        exit();
    } else {
        echo "Failed to update profile: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch Order History
$sql = "SELECT ot.orderid, ot.kupidate AS orderdate, SUM(od.subtotal) AS totalamount
        FROM orderdetail od
        JOIN ordertable ot ON od.orderid = ot.orderid
        WHERE ot.custid = ?
        GROUP BY ot.orderid, ot.kupidate
        ORDER BY ot.kupidate DESC";

$stmt = $condb->prepare($sql);
$stmt->bind_param("i", $custid);
$stmt->execute();
$result = $stmt->get_result();

$orderHistory = [];
while ($row = $result->fetch_assoc()) {
    $orderHistory[] = [
        'id'     => $row['orderid'],
        'date'   => $row['orderdate'],
        'amount' => $row['totalamount']
    ];
}

$stmt->close();
?>
