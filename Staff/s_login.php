<?php require_once '../Homepage/session.php'; ?>
<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Login Page</title>  
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">  
    <style>
        body {
            background-image: url(../image/bgDel.png);
            background-size: cover;
            color: #444;
        }
        .login-container {
            background:rgb(255, 248, 207);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .login-title {
            color: #7a2005;
            font-family: 'Roboto', sans-serif;
        }
        .input-label {
            font-family: 'Roboto', sans-serif;
            color: #7a2005;
        }
        .login-button {
            background-color: #7a2005;
            transition: background-color 0.3s ease;
        }
        .login-button:hover {
            background-color: #5c1603;
        }
        .register-link {
            color: #7a2005;
            text-decoration: underline;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .register-link:hover {
            color: #D97706;
        }
    </style>
</head>  
<body class="bg-gray-100">  
    <?php include '../Homepage/header.php'; ?>  
    <div class="flex items-center justify-center h-screen">  
        <div class="login-container p-8 rounded-lg shadow-lg w-full max-w-sm">  
            <h2 class="text-2xl font-bold mb-6 text-center login-title">Staff Login</h2>  
            <form action="staffLogin.php" method="POST">  
                <div class="mb-4">  
                    <label for="username" class="block text-sm font-medium input-label">Username</label>  
                    <input type="text" id="username" name="username" required class="mt-1 p-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">  
                </div>  
                <div class="mb-6">  
                    <label for="password" class="block text-sm font-medium input-label">Password</label>  
                    <input type="password" id="password" name="password" required class="mt-1 p-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">  
                </div>  
                <button type="submit" class="w-full text-white p-2 rounded-md login-button focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">Login</button>  
            </form>
            <button onclick="window.location.href='../Customer/c.login.php'" class="w-full mt-4 text-white p-2 rounded-md bg-amber-500 hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50">Customer Login</button>
        </div>  
    </div>  
</body>
</html>