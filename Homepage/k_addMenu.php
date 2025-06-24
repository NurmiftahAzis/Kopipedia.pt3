<?php
// Proses jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Koneksi ke MySQL
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "kopi";
    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Ambil data dari form
    $name = $_POST['k_name'];
    $desc = $_POST['k_desc'];
    $price = $_POST['k_price'];

    // Ambil file gambar & ubah jadi binary
    $image = addslashes(file_get_contents($_FILES['k_image']['tmp_name']));

    // Simpan ke database
    $sql = "INSERT INTO kupi (k_name, k_desc, k_price, k_image) 
            VALUES ('$name', '$desc', '$price', '$image')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Menu kopi berhasil ditambahkan!'); window.location.href='MenuV2.php';</script>";
    } else {
        echo "Gagal menyimpan: " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Menu Kopi</title>
</head>
<body style="font-family: Arial; margin: 50px;">
    <h2>Tambah Menu Kopi Baru</h2>
    <form action="k_addMenu.php" method="post" enctype="multipart/form-data">
        <label>Nama Kopi:</label><br>
        <input type="text" name="k_name" required><br><br>

        <label>Deskripsi:</label><br>
        <textarea name="k_desc" rows="4" cols="40" required></textarea><br><br>

        <label>Harga (RM):</label><br>
        <input type="number" step="0.01" name="k_price" required><br><br>

        <label>Gambar:</label><br>
        <input type="file" name="k_image" accept="image/*" required><br><br>

        <button type="submit">Simpan Menu</button>
    </form>
</body>
</html>
