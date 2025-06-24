<?php
// Konfigurasi database MySQL
$host = "localhost";      // Host MySQL
$username = "root";       // Username MySQL (default XAMPP)
$password = "";           // Password MySQL (kosong secara default)
$database = "kopi";       // Nama database MySQL kamu

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
} else {
    echo "MySQL database connected successfully!";
}

// Tutup koneksi
$conn->close();
?>
