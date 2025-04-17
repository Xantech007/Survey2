<?php
session_start();
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script>
    function toggleDarkMode() {
      document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    }

    window.onload = () => {
      if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
      }
    };
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex items-center justify-center">

  <!-- Navbar -->
  <div class="absolute top-0 left-0 w-full bg-white dark:bg-gray-800 shadow-md p-4 flex justify-between items-center">
    <h1 class="text-xl font-bold text-gray-800 dark:text-gray-200">Survey Portal</h1>
    <div class="flex items-center space-x-4">
      <a href="index.php" class="text-sm text-gray-600 dark:text-gray-300 hover:underline">Login</a>
      <button onclick="toggleDarkMode()" class="text-gray-600 dark:text-gray-300">
        <i class="fas fa-moon text-xl"></i>
      </button>
    </div>
  </div>

  <!-- Form Card -->
  <div class="w-full max-w-md mt-20 p-6 rounded-lg shadow-lg bg-white dark:bg-gray-800">
    <h1 class="text-2xl font-bold text-center mb-4 text-gray-800 dark:text-white">Register</h1>

    <?php if (!empty($errors['user_exist'])): ?>
      <div class="bg-red-500 text-white text-center p-2 rounded mb-3">
        <?= htmlspecialchars($errors['user_exist']) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="user-account.php" class="space-y-4">
      <!-- Name -->
      <div class="relative">
        <i class="fas fa-user absolute left-3 top-3 text-gray-500"></i>
        <input type="text" name="name" placeholder="Name" required
          class="w-full pl-10 p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
        <?php if (!empty($errors['name'])): ?>
          <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['name']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Email -->
      <div class="relative">
        <i class="fas fa-envelope absolute left-3 top-3 text-gray-500"></i>
        <input type="email" name="email" placeholder="Email" required
          class="w-full pl-10 p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
        <?php if (!empty($errors['email'])): ?>
          <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['email']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Password -->
      <div class="relative">
        <i class="fas fa-lock absolute left-3 top-3 text-gray-500"></i>
        <input id="password" type="password" name="password" placeholder="Password" required
          class="w-full pl-10 p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
        <i id="eye" class="fa fa-eye absolute right-3 top-3 text-gray-500 cursor-pointer"></i>
        <?php if (!empty($errors['password'])): ?>
          <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['password']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Confirm Password -->
      <div class="relative">
        <i class="fas fa-lock absolute left-3 top-3 text-gray-500"></i>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required
          class="w-full pl-10 p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
        <?php if (!empty($errors['confirm_password'])): ?>
          <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['confirm_password']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Submit Button -->
      <input type="submit" name="signup" value="Sign Up"
        class="w-full bg-indigo-500 hover:bg-indigo-600 text-white p-2 rounded-lg cursor-pointer transition">
    </form>

    <!-- Or Divider -->
    <p class="text-center my-4 text-gray-500 dark:text-gray-300">---------- or ----------</p>

    <!-- Social Icons -->
    <div class="flex justify-center space-x-4">
      <i class="fab fa-google text-blue-500 text-2xl cursor-pointer"></i>
      <i class="fab fa-facebook text-blue-700 text-2xl cursor-pointer"></i>
    </div>

    <!-- Already have account -->
    <div class="text-center mt-4 text-sm text-gray-700 dark:text-gray-300">
      <p>Already have an account? <a href="index.php" class="text-blue-500 hover:underline">Sign In</a></p>
    </div>
  </div>

  <script>
    document.getElementById('eye').addEventListener('click', function () {
      const password = document.getElementById('password');
      this.classList.toggle('fa-eye-slash');
      password.type = password.type === 'password' ? 'text' : 'password';
    });
  </script>
</body>
</html>
