<?php
session_start();
require 'dbConnect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    header("Location: home.php");
    exit;
  } else {
    $error = "Invalid email or password.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Survey Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function toggleDarkMode() {
      document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    }

    window.onload = () => {
      if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
      }
    }
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex items-center justify-center">
  <div class="absolute top-0 left-0 w-full bg-white dark:bg-gray-800 shadow-md p-4 flex justify-between items-center">
    <h1 class="text-xl font-bold text-gray-800 dark:text-gray-200">Survey Portal</h1>
    <button onclick="toggleDarkMode()" class="text-gray-600 dark:text-gray-300">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0112 21.75 9.75 9.75 0 0112 2.25c.35 0 .694.018 1.032.053a0.75 0.75 0 01.268 1.415 7.5 7.5 0 1010.49 10.49 0.75 0.75 0 01-1.415.268c-.035.338-.053.682-.053 1.032z" />
      </svg>
    </button>
  </div>

  <div class="w-full max-w-md p-8 mt-16 bg-white rounded-lg shadow-md dark:bg-gray-800">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800 dark:text-white">Login to Survey Portal</h2>

    <?php if ($error): ?>
      <p class="mb-4 text-red-500 text-sm text-center"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white">
      <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white">
      <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600">Login</button>
    </form>
    <p class="mt-4 text-center text-gray-600 dark:text-gray-300">Don't have an account? <a href="register.php" class="text-blue-500 hover:underline">Register here</a></p>
  </div>
</body>
</html>
