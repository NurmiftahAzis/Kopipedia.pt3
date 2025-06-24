<?php
// dbkupi.php - koneksi ke MySQL (XAMPP)

// Konfigurasi koneksi
$host = "localhost";
$user = "root";         // default XAMPP username
$pass = "";             // default XAMPP password (kosong)
$dbname = "kopi";       // ganti sesuai nama database kamu

// Buat koneksi
$condb = new mysqli($host, $user, $pass, $dbname);

// Cek koneksi
if ($condb->connect_error) {
    die("Koneksi ke MySQL gagal: " . $condb->connect_error);
}
?>
