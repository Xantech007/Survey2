<?php
session_start();
require 'dbConnect.php';

if (!isset($_GET['survey_id'])) {
  exit("Survey ID missing.");
}

$survey_id = $_GET['survey_id'];
$user_id = $_SESSION['user_id'] ?? null;

// Fetch comments and replies
$stmt = $pdo->prepare("
  SELECT c.*, u.email 
  FROM comments c
  JOIN users u ON c.user_id = u.id
  WHERE survey_id = ?
  ORDER BY created_at ASC
");
$stmt->execute([$survey_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize into nested structure
$tree = [];
$map = [];

foreach ($comments as $comment) {
  $comment['replies'] = [];
  $map[$comment['id']] = $comment;
}

foreach ($map as $id => $comment) {
  if ($comment['parent_id']) {
    $map[$comment['parent_id']]['replies'][] = &$map[$id];
  } else {
    $tree[] = &$map[$id];
  }
}

// Recursive function to render comments
function renderComments($comments, $user_id) {
  $html = '';
  foreach ($comments as $comment) {
    $isOwner = $user_id == $comment['user_id'];
    $html .= '
      <div class="mb-4 border-l-4 pl-4 border-blue-500">
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
          <strong>' . htmlspecialchars($comment['email']) . '</strong> at ' . date('F j, Y, g:i a', strtotime($comment['created_at'])) . '
        </p>
        <p class="mb-2 text-gray-800 dark:text-gray-100">' . nl2br(htmlspecialchars($comment['content'])) . '</p>
        <div class="flex items-center space-x-3 text-sm text-blue-500">
          <button onclick="toggleReply(' . $comment['id'] . ')">Reply</button>';

    if ($isOwner) {
      $html .= '
          <a href="edit_comment.php?id=' . $comment['id'] . '" class="text-yellow-500">Edit</a>
          <a href="delete_comment.php?id=' . $comment['id'] . '" class="text-red-500" onclick="return confirm(\'Delete this comment?\')">Delete</a>';
    }

    $html .= '
        </div>

        <form action="add_comment.php" method="POST" class="mt-2 hidden" id="reply-box-' . $comment['id'] . '">
          <input type="hidden" name="survey_id" value="' . htmlspecialchars($comment['survey_id']) . '">
          <input type="hidden" name="parent_id" value="' . $comment['id'] . '">
          <textarea name="content" rows="2" required placeholder="Write a reply..." class="w-full p-2 mt-2 rounded border dark:bg-gray-700 dark:text-white"></textarea>
          <button type="submit" class="mt-2 bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Reply</button>
        </form>';

    if (!empty($comment['replies'])) {
      $html .= '<div class="mt-4 ml-4">' . renderComments($comment['replies'], $user_id) . '</div>';
    }

    $html .= '</div>';
  }
  return $html;
}

echo renderComments($tree, $user_id);
