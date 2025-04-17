<?php
session_start();
require 'dbConnect.php';

$user_id = $_SESSION['user_id'];
$survey_id = $_POST['survey_id'];
$comment = htmlspecialchars($_POST['comment']);

$stmt = $pdo->prepare("INSERT INTO comments (user_id, survey_id, comment_text) VALUES (?, ?, ?)");
$stmt->execute([$user_id, $survey_id, $comment]);

// after saving the response/comment
$userEmail = ...; // survey owner’s email
$subject = "New response on your survey";
$message = "Someone just responded to your survey.";
mail($userEmail, $subject, $message); // requires mail server or SMTP setup


header("Location: index.php");
exit;
