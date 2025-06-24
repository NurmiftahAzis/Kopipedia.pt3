<?php  
session_start(); // Start the session  

// Check if the form is submitted  
if ($_SERVER['REQUEST_METHOD'] == 'POST') {  
    // Database connection (MySQL)
    $servername = "localhost";
    $username = "root";
    $password = ""; // default password kosong di XAMPP
    $dbname = "kopi"; // ganti sesuai nama database kamu

    // Create connection  
    $dbconn = new mysqli($servername, $username, $password, $dbname);

    // Check connection  
    if ($dbconn->connect_error) {
        die("Connection failed: " . $dbconn->connect_error);
    }

    // Get the username and password from the form  
    $user = $_POST['username'];  
    $pass = $_POST['password'];  

    // Prepare the SQL statement  
    $sql = "SELECT S_USERNAME, S_ROLE FROM STAFF WHERE S_USERNAME = ? AND S_PASS = ?";
    $stmt = $dbconn->prepare($sql);

    if (!$stmt) {
        die("SQL error: " . $dbconn->error);
    }

    // Bind parameters  
    $stmt->bind_param("ss", $user, $pass);

    // Execute the statement  
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a matching record was found  
    if ($row = $result->fetch_assoc()) {
        // Login successful  
        $_SESSION['username'] = $row['S_USERNAME'];
        $_SESSION['s_role'] = $row['S_ROLE'];

        // Redirect based on role
        if ($row['S_ROLE'] === 'admin') {
            header("Location: ../adminV2/a.sales.php");
        } else {
            header("Location: s.manageOrder.php");
        }
        exit();  
    } else {  
        // Login failed  
        $error = "Invalid username or password.";  
        header("Location: s_login.php?error=" . urlencode($error));  
        exit();  
    }

    // Cleanup  
    $stmt->close();
    $dbconn->close();
}  
?>
