<?php
session_start();
require 'dbConnect.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$notification_count = 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM responses sr
                       INNER JOIN surveys s ON sr.survey_id = s.id
                       WHERE s.user_id = ?");
$stmt->execute([$user_id]);
$notification_count = $stmt->fetchColumn();

// Get surveys
$category = $_GET['category'] ?? 'all';
$query = "SELECT s.*, 
          (SELECT COUNT(*) FROM votes WHERE survey_id = s.id AND vote_type = 'up') as upvotes,
          (SELECT COUNT(*) FROM votes WHERE survey_id = s.id AND vote_type = 'down') as downvotes,
          (SELECT COUNT(*) FROM comments WHERE survey_id = s.id) as comment_count
          FROM surveys s";

if ($category !== 'all') {
  $query .= " WHERE category = ?";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$category]);
} else {
  $stmt = $pdo->query($query);
}

$surveys = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Survey Feed</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">

  <!-- Top bar -->
  <div class="w-full p-4 bg-white dark:bg-gray-800 shadow-md flex justify-between items-center">
    <h1 class="text-2xl font-bold">Survey Portal</h1>
    <div class="flex items-center space-x-4">
      <a href="formcreate.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">Create Survey</a>
      <a href="notifications.php" class="relative">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-800 dark:text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405C18.05 14.85 18 14.548 18 14.25V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.25c0 .298-.05.6-.145.879L4.5 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <?php if ($notification_count > 0): ?>
        <span class="absolute -top-2 -right-2 text-xs px-2 py-0.5 bg-red-500 text-white rounded-full">
          <?= htmlspecialchars($notification_count) ?>
        </span>
        <?php endif; ?>
      </a>
      <button onclick="toggleDarkMode()" class="text-gray-800 dark:text-gray-200">
        🌗
      </button>
      <a href="logout.php" class="text-red-600 dark:text-red-400 font-semibold">Logout</a>
    </div>
  </div>

  <div class="flex">
    <!-- Sidebar -->
    <aside class="w-64 p-4 bg-gray-50 dark:bg-gray-800 border-r dark:border-gray-700 min-h-screen">
      <h2 class="text-lg font-semibold mb-4">Categories</h2>
      <?php
      $categories = ['all', 'sports', 'tech', 'IT', 'stocks', 'food', 'diet', 'movies', 'series'];
      foreach ($categories as $cat): ?>
        <a href="?category=<?= urlencode($cat) ?>" class="block px-2 py-1 mb-1 rounded hover:bg-blue-100 dark:hover:bg-blue-900 <?= $category === $cat ? 'font-bold text-blue-600 dark:text-blue-400' : '' ?>">
          <?= ucfirst($cat) ?>
        </a>
      <?php endforeach; ?>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-6">
      <h2 class="text-xl font-semibold mb-4">Surveys <?= $category !== 'all' ? 'in ' . ucfirst($category) : '' ?></h2>

      <?php if (empty($surveys)): ?>
        <p>No surveys found.</p>
      <?php endif; ?>

      <div class="space-y-6">
        <?php foreach ($surveys as $survey): ?>
          <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded">
                <?= htmlspecialchars($survey['category'] ?? 'Uncategorized') ?>
              </span>
              <span class="text-xs text-gray-500">Public</span>
              </span>
            </div>
            <h3 class="text-lg font-bold"><?= htmlspecialchars($survey['title']) ?></h3>
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-2"><?= htmlspecialchars($survey['description']) ?></p>
            <?php if (!empty($survey['image_path'])): ?>
              <img src="uploads/<?= htmlspecialchars($survey['image_path']) ?>" alt="Survey Image" class="w-full max-w-md rounded mb-3">
            <?php endif; ?>
            <div class="flex items-center space-x-4 mb-2">
              <form action="vote.php" method="POST" class="inline">
                <input type="hidden" name="survey_id" value="<?= $survey['id'] ?>">
                <input type="hidden" name="vote_type" value="up">
                <button type="submit" class="text-green-600 dark:text-green-400 hover:underline">⬆ <?= $survey['upvotes'] ?></button>
              </form>
              <form action="vote.php" method="POST" class="inline">
                <input type="hidden" name="survey_id" value="<?= $survey['id'] ?>">
                <input type="hidden" name="vote_type" value="down">
                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">⬇ <?= $survey['downvotes'] ?></button>
              </form>
              <span class="text-sm"><?= $survey['comment_count'] ?> Comments</span>
            </div>

            <!-- Comment form -->
            <form action="comment.php" method="POST" class="mt-2">
              <input type="hidden" name="survey_id" value="<?= $survey['id'] ?>">
              <textarea name="comment" rows="2" class="w-full p-2 border rounded dark:bg-gray-700 dark:text-white" placeholder="Add a comment..." required></textarea>
              <button type="submit" class="mt-2 px-4 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Comment</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>
<footer class="bg-white dark:bg-gray-800 mt-10 py-4 border-t dark:border-gray-700">
  <div class="max-w-screen-xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between text-gray-700 dark:text-gray-300">
    <p class="text-sm">&copy; <?= date('Y') ?> Survey Portal. Contact us at <a href="mailto:contact@surveyportal.com" class="underline text-blue-500">contact@surveyportal.com</a></p>
    <div class="flex space-x-4 mt-2 md:mt-0">
      <a href="https://instagram.com" target="_blank" class="hover:text-pink-600">
        <i class="fab fa-instagram fa-lg"></i>
      </a>
      <a href="https://linkedin.com" target="_blank" class="hover:text-blue-600">
        <i class="fab fa-linkedin fa-lg"></i>
      </a>
    </div>
  </div>
</footer>

</body>
</html>
