<?php
// Include the necessary files
require_once '../Homepage/session.php'; // session handling
require_once '../Homepage/dbkupi.php';  // MySQLi database connection

// Check if form submitted
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $phone    = $_POST['phone'];
    $email    = $_POST['email'];
    $role     = $_POST['role'];
    $adminid  = null;

    // Jika role = admin, adminid = 1
    if ($role === 'admin') {
        $adminid = 1;
    }

    // SQL untuk insert staff baru
    $sql = "INSERT INTO staff (s_username, s_pass, s_phonenum, s_email, adminid, s_role)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $condb->prepare($sql);
    if ($stmt === false) {
        echo "Error preparing statement: " . $condb->error;
        exit;
    }

    $stmt->bind_param("ssssis", $username, $password, $phone, $email, $adminid, $role);

    if ($stmt->execute()) {
        // Berhasil
        echo "
        <div class='fixed top-0 right-0 mt-4 mr-4 max-w-xs w-full bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md'>
            <strong class='font-bold'>Success!</strong>
            <span>Your staff member has been successfully registered.</span>
        </div>";

        header("refresh:2;url=../Staff/s_login.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $condb->close();
}
?>
