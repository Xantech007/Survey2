<?php
session_start();
require 'dbConnect.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['survey_id']) || empty($_POST['content'])) {
  header("Location: index.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$survey_id = $_POST['survey_id'];
$content = trim($_POST['content']);
$parent_id = $_POST['parent_id'] ?? null;

if ($parent_id === '') {
  $parent_id = null;
}

// Insert comment or reply
$stmt = $pdo->prepare("INSERT INTO comments (survey_id, user_id, content, parent_id) VALUES (?, ?, ?, ?)");
$stmt->execute([$survey_id, $user_id, $content, $parent_id]);

// Redirect back to the survey page
header("Location: survey_view.php?id=" . $survey_id);
exit;
