<?php
session_start();
require 'dbConnect.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $title = $_POST['title'];
  $description = $_POST['description'];
  $category = $_POST['category'];
  $imagePath = '';

  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $filename = basename($_FILES['image']['name']);
    $imagePath = $uploadDir . uniqid() . "_" . $filename;
    move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
  }

  $stmt = $pdo->prepare("INSERT INTO surveys (user_id, title, description, category, image_path) VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([$user_id, $title, $description, $category, $imagePath]);

  header("Location: home.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Survey</title>
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
    };
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white min-h-screen flex flex-col">

  <!-- Top bar -->
  <div class="w-full p-4 bg-white dark:bg-gray-800 shadow-md flex justify-between items-center">
    <h1 class="text-2xl font-bold">Create Survey</h1>
    <div class="flex items-center space-x-4">
      <a href="home.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Home</a>
      <button onclick="toggleDarkMode()" class="text-gray-800 dark:text-gray-200">🌗</button>
      <a href="logout.php" class="text-red-600 dark:text-red-400 font-semibold">Logout</a>
    </div>
  </div>

  <!-- Main content -->
  <main class="flex-grow flex items-center justify-center px-4 py-10">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md w-full max-w-xl">
      <h2 class="text-xl font-bold mb-6">New Survey Details</h2>
      <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
          <label class="block mb-1 font-medium">Title</label>
          <input type="text" name="title" required class="w-full px-4 py-2 border rounded-md dark:bg-gray-700 dark:text-white">
        </div>

        <div>
          <label class="block mb-1 font-medium">Description</label>
          <textarea name="description" rows="4" required class="w-full px-4 py-2 border rounded-md dark:bg-gray-700 dark:text-white"></textarea>
        </div>

        <div>
          <label class="block mb-1 font-medium">Category</label>
          <select name="category" class="w-full px-4 py-2 border rounded-md dark:bg-gray-700 dark:text-white">
            <option value="sports">Sports</option>
            <option value="tech">Tech</option>
            <option value="IT">IT</option>
            <option value="stocks">Stocks</option>
            <option value="food">Food</option>
            <option value="diet">Diet</option>
            <option value="movies">Movies</option>
            <option value="series">Series</option>
          </select>
        </div>

        <div>
          <label class="block mb-1 font-medium">Image (optional)</label>
          <input type="file" name="image" accept="image/*" onchange="previewImage(event)"
                 class="w-full px-4 py-2 border rounded-md dark:bg-gray-700 dark:text-white">
          <img id="preview" src="#" alt="Preview" class="hidden mt-2 w-48 rounded shadow">
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-md font-semibold">
          Create Survey
        </button>
      </form>
    </div>
  </main>

  <!-- Footer -->
  <footer class="w-full bg-gray-200 dark:bg-gray-800 text-center py-4">
    <p class="text-gray-700 dark:text-gray-300">Contact us at <a href="mailto:support@surveyapp.com" class="underline">support@surveyapp.com</a></p>
    <div class="flex justify-center mt-2 space-x-4 text-2xl">
      <a href="https://instagram.com" target="_blank" class="text-pink-500 hover:scale-110 transition"><i class="fab fa-instagram"></i></a>
      <a href="https://linkedin.com" target="_blank" class="text-blue-700 hover:scale-110 transition"><i class="fab fa-linkedin"></i></a>
    </div>
  </footer>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
  <script>
    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function() {
        const output = document.getElementById('preview');
        output.src = reader.result;
        output.classList.remove('hidden');
      };
      reader.readAsDataURL(event.target.files[0]);
    }
  </script>
</body>
</html>
