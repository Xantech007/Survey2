<?php
session_start();
require 'dbConnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Survey Notifications</title>
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
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white min-h-screen">

  <div class="w-full p-4 bg-white dark:bg-gray-800 shadow-md flex justify-between items-center">
    <h1 class="text-xl font-bold">Your Survey Notifications</h1>
    <div class="flex items-center space-x-4">
      <a href="home.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">← Back to Home</a>
      <button onclick="toggleDarkMode()" class="text-xl">🌗</button>
    </div>
  </div>

  <div class="p-6">
    <?php
    $stmt = $pdo->prepare("SELECT id, title FROM surveys WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $surveys = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($surveys)) {
        echo "<p>You haven’t created any surveys yet.</p>";
    } else {
        foreach ($surveys as $survey) {
            echo '<div class="mb-8 p-4 bg-white dark:bg-gray-800 rounded shadow">';
            echo '<h2 class="text-lg font-semibold mb-3">' . htmlspecialchars($survey['title']) . '</h2>';

            // Get responses with comments sorted by latest
            $stmt = $pdo->prepare("
                SELECT r.response_text, r.created_at, u.name AS responder_name,
                       c.id AS comment_id, c.comment_text, c.user_id AS comment_user_id, c.created_at AS comment_time, cu.name AS commenter_name
                FROM responses r
                JOIN users u ON r.user_id = u.id
                LEFT JOIN comments c ON c.survey_id = r.survey_id
                LEFT JOIN users cu ON c.user_id = cu.id
                WHERE r.survey_id = ?
                ORDER BY r.created_at DESC, c.created_at DESC
            ");
            $stmt->execute([$survey['id']]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                echo "<p class='text-gray-500'>No responses yet.</p>";
            } else {
                echo '<ul class="space-y-4">';
                foreach ($rows as $row) {
                    echo '<li class="border-t pt-2">';
                    echo '<p><span class="font-semibold">' . htmlspecialchars($row['responder_name']) . '</span> responded:</p>';
                    echo '<p class="text-sm italic text-gray-600 dark:text-gray-300">' . htmlspecialchars($row['response_text']) . '</p>';
                    echo '<p class="text-xs text-gray-400">' . date("F j, Y, g:i a", strtotime($row['created_at'])) . '</p>';

                    if (!empty($row['comment_text'])) {
                        echo '<div class="mt-2 ml-4 border-l-2 pl-3 border-blue-300">';
                        echo '<p><span class="font-semibold text-blue-600 dark:text-blue-300">' . htmlspecialchars($row['commenter_name']) . '</span> commented:</p>';
                        echo '<p class="text-sm">' . htmlspecialchars($row['comment_text']) . '</p>';
                        echo '<p class="text-xs text-gray-400">' . date("F j, Y, g:i a", strtotime($row['comment_time'])) . '</p>';

                        // If the current user made this comment, show Edit/Delete
                        if ($row['comment_user_id'] == $user_id) {
                            echo '<form action="edit-comment.php" method="GET" class="inline-block mr-2">';
                            echo '<input type="hidden" name="comment_id" value="' . $row['comment_id'] . '">';
                            echo '<button class="text-yellow-500 hover:underline text-sm">Edit</button>';
                            echo '</form>';

                            echo '<form action="delete-comment.php" method="POST" class="inline-block">';
                            echo '<input type="hidden" name="comment_id" value="' . $row['comment_id'] . '">';
                            echo '<button class="text-red-500 hover:underline text-sm" onclick="return confirm(\'Delete this comment?\')">Delete</button>';
                            echo '</form>';
                        }

                        echo '</div>';
                    }

                    echo '</li>';
                }
                echo '</ul>';
            }
            echo '</div>';
        }
    }
    ?>
  </div>
</body>
</html>
