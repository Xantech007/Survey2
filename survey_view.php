<?php
session_start();
require 'db.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit;
}

$survey_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch survey
$stmt = $pdo->prepare("SELECT * FROM surveys WHERE id = ?");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();

if (!$survey) {
  echo "Survey not found.";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($survey['title']) ?> - Survey</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function toggleReply(id) {
      const box = document.getElementById('reply-box-' + id);
      box.classList.toggle('hidden');
    }

    async function loadComments() {
      const res = await fetch('get_comments.php?survey_id=<?= $survey_id ?>');
      const data = await res.text();
      document.getElementById('comment-section').innerHTML = data;
    }

    window.onload = () => {
      loadComments();
    };
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white p-6">
  <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-2"><?= htmlspecialchars($survey['title']) ?></h1>
    <p class="mb-4"><?= htmlspecialchars($survey['description']) ?></p>

    <!-- Add Comment -->
    <form action="add_comment.php" method="POST" class="mb-6">
      <input type="hidden" name="survey_id" value="<?= $survey_id ?>">
      <textarea name="content" rows="3" required placeholder="Add a comment..." class="w-full p-2 rounded border dark:bg-gray-700 dark:text-white"></textarea>
      <button type="submit" class="mt-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Post Comment</button>
    </form>

    <!-- Comment Section -->
    <div id="comment-section">
      <!-- Loaded by JS -->
    </div>
  </div>
</body>
</html>
