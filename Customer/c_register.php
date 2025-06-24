<<?php
require_once '../Homepage/session.php';
include("../Homepage/dbkupi.php");

// Cek apakah form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $password = $_POST['password'];
    $address  = $_POST['address'];

    // Cek apakah username sudah ada
    $check_sql = "SELECT COUNT(*) AS count FROM customer WHERE c_username = ?";
    $check_stmt = $condb->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        echo "User already registered!";
    } else {
        // Insert data baru ke tabel customer
        $sql = "INSERT INTO customer (c_username, c_email, c_phonenum, c_pass, c_address) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $condb->prepare($sql);
        $stmt->bind_param("sssss", $username, $email, $phone, $password, $address);

        if ($stmt->execute()) {
            // Ambil ID customer yang baru dibuat
            $custid = $stmt->insert_id;

            // Simpan ke session
            $_SESSION['custid']   = $custid;
            $_SESSION['username'] = $username;
            $_SESSION['password'] = $password;
            $_SESSION['email']    = $email;
            $_SESSION['phonenum'] = $phone;
            $_SESSION['address']  = $address;

            // Redirect ke homepage
            header("Location: ../Homepage/index.php");
            exit();
        } else {
            echo "Failed to register: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>