<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kopi Menu</title>
    <style>
        body {
            background-color: rgb(255, 191, 230);
            padding-top: 70px;
            font-family: 'Lucida Sans', sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .menu-title {
            text-align: center;
            color: #7a2005;
            font-size: 35px;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .menu-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .menu-item {
            flex: 0 0 300px;
            background-color: rgb(255, 238, 141);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .menu-item img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .menu-item h3 {
            color: #78350F;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .menu-item p {
            color: #333;
            margin-bottom: 10px;
            min-height: 60px;
        }

        .menu-item .price {
            color: #78350F;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .menu-item button {
            background: #78350F;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        .menu-item button:hover {
            background: #92400e;
        }

        .cart-button {
            position: fixed;
            top: 100px;
            right: 30px;
            background: none;
            border: none;
            cursor: pointer;
            z-index: 1000;
        }

        .cart-icon {
            width: 100px;
            height: 100px;
            transition: transform 0.2s;
        }

        .cart-button:hover .cart-icon {
            transform: scale(1.1);
        }
    </style>
</head>
<body>

<?php
// ==== SESSION DAN KONEKSI ====
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../Customer/c.login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "kopi";
$condb = new mysqli($host, $user, $pass, $dbname);

if ($condb->connect_error) {
    die("Koneksi gagal: " . $condb->connect_error);
}

$loggedin = isset($_SESSION['username']);

// ==== TOMBOL KERANJANG ====
if ($loggedin) {
    echo '<button class="cart-button" onclick="window.location.href=\'../Customer/c.inCartNew.php\'">
            <img src="../image/cart.png" alt="Cart" class="cart-icon">
          </button>';
}

// ==== AMBIL DATA MENU ====
$menuItems = [];
$query = "SELECT * FROM kupi ORDER BY kupiid";
$result = $condb->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $menuItems[] = $row;
    }
} else {
    echo "<!-- Query error: " . $condb->error . " -->";
}
?>

<!-- ==== TAMPILKAN MENU ==== -->
<!-- ==== TAMPILKAN MENU ==== -->
<div class="container">
    <h1 class="menu-title">Our Menu</h1>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div style="text-align: center; margin-bottom: 20px;">
            <a href="k_addMenu.php" style="padding: 10px 20px; background: #78350F; color: white; border-radius: 5px; text-decoration: none;">+ Tambah Menu</a>
        </div>
    <?php endif; ?>

    <div class="menu-grid">
        <?php if (empty($menuItems)): ?>
            <p style="text-align: center; color: red;">No menu items found.</p>
        <?php else: ?>
            <?php foreach ($menuItems as $item): ?>
                <div class="menu-item">
                    <img src="../image/<?= htmlspecialchars($item['K_IMAGE']) ?>" 
                         alt="<?= htmlspecialchars($item['K_NAME']) ?>">
                    <h3><?= htmlspecialchars($item['K_NAME']) ?></h3>
                    <p><?= htmlspecialchars($item['K_DESC']) ?></p>
                    <div class="price">Rp. <?= number_format($item['K_PRICE'], 2) ?></div>
                    <?php if ($loggedin): ?>
                        <form action="../Customer/c.addToCart.php" method="post">
                            <input type="hidden" name="item_id" value="<?= $item['KUPIID'] ?>">
                            <input type="hidden" name="item_name" value="<?= htmlspecialchars($item['K_NAME']) ?>">
                            <button type="submit">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <button onclick="window.location.href='../Customer/c.login.php';">Add to Cart</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>