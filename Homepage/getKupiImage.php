<?php
require_once '../Homepage/dbkupi.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Query untuk ambil gambar dari MySQL
    $query = "SELECT K_IMAGE FROM KOPI WHERE KUPIID = ?";
    $stmt = $condb->prepare($query);
    
    if (!$stmt) {
        die("Failed to prepare statement: " . $condb->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($image);
        $stmt->fetch();

        if ($image) {
            // Tampilkan gambar
            header("Content-Type: image/png"); // atau image/png, tergantung format aslinya
            echo $image;
        } else {
            echo "No image found.";
        }
    } else {
        echo "Image not found.";
    }

    $stmt->close();
}
?>
