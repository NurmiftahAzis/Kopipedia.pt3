<?php
// Include session & koneksi MySQL
require_once '../Homepage/session.php';
include("../Homepage/dbkupi.php"); // Pastikan file ini pakai MySQLi

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query cek username dan password
    $sql = "SELECT * FROM customer WHERE c_username = ? AND c_pass = ?";
    $stmt = $condb->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ss", $username, $password); // "ss" = string, string
        $stmt->execute();
        $result = $stmt->get_result();

        // Jika user ditemukan
        if ($row = $result->fetch_assoc()) {
            // Simpan ke session
            $_SESSION['custid']   = $row['CUSTID'];
            $_SESSION['username'] = $row['C_USERNAME'];
            $_SESSION['password'] = $row['C_PASS'];
            $_SESSION['phonenum'] = $row['C_PHONENUM'];
            $_SESSION['email']    = $row['C_EMAIL'];
            $_SESSION['address']  = $row['C_ADDRESS'];

            // Redirect ke homepage
            header("Location: ../Homepage/index.php");
            exit();
        } else {
            // Login gagal
            $_SESSION['error'] = "Incorrect username or password.";
            header("Location: c_login.php");
            exit();
        }

        $stmt->close();
    } else {
        die("Error preparing statement: " . $condb->error);
    }
}
?>