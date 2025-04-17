<?php
session_start();
require 'dbConnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$comment_id = $_GET['comment_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_text = $_POST['comment_text'];
    $stmt = $pdo->prepare("UPDATE comments SET comment_text = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$new_text, $comment_id, $_SESSION['user_id']]);
    header("Location: notifications.php");
    exit;
}

// Get current comment
$stmt = $pdo->prepare("SELECT comment_text FROM comments WHERE id = ? AND user_id = ?");
$stmt->execute([$comment_id, $_SESSION['user_id']]);
$comment = $stmt->fetch();

if (!$comment) {
    echo "Comment not found.";
    exit;
}
?>

<form method="POST" class="p-4 max-w-md mx-auto bg-white shadow rounded mt-10">
    <textarea name="comment_text" rows="4" class="w-full border p-2 rounded" required><?= htmlspecialchars($comment['comment_text']) ?></textarea>
    <button type="submit" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded">Update Comment</button>
</form>
