<?php
session_start(); // Mulai session

// Cek apakah form dikirim via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Konfigurasi database MySQL
    $servername = "localhost";
    $username = "root";
    $password = ""; // default XAMPP kosong
    $dbname = "kopi"; // ganti sesuai nama database MySQL kamu

    // Koneksi ke MySQL
    $dbconn = new mysqli($servername, $username, $password, $dbname);

    // Cek koneksi
    if ($dbconn->connect_error) {
        die("Koneksi gagal: " . $dbconn->connect_error);
    }

    // Ambil data dari form
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Siapkan query
    $sql = "SELECT S_USERNAME, S_ROLE FROM STAFF WHERE S_USERNAME = ? AND S_PASS = ?";
    $stmt = $dbconn->prepare($sql);

    if (!$stmt) {
        die("Kesalahan SQL: " . $dbconn->error);
    }

    // Bind parameter (s = string)
    $stmt->bind_param("ss", $user, $pass);

    // Eksekusi query
    $stmt->execute();

    // Ambil hasil
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Login berhasil
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $row['S_USERNAME'];
        $_SESSION['s_role'] = $row['S_ROLE'];

        // Redirect sesuai role
        if ($row['S_ROLE'] === 'admin') {
            header("Location: ../Admin/a.kupi.php");
        } elseif ($row['S_ROLE'] === 'staff') {
            header("Location: s.manageOrder.php");
        } else {
            header("Location: ../Homepage/index.php");
        }
        exit();
    } else {
        // Login gagal
        $error = "Username atau password salah.";
        header("Location: s_login.php?error=" . urlencode($error));
        exit();
    }

    // Tutup koneksi
    $stmt->close();
    $dbconn->close();
}
?>
