<?php
ob_start();
header("Content-Type: application/json");
require_once 'dbConnect.php';

if (!isset($_GET['survey_id']) || !is_numeric($_GET['survey_id'])) {
    http_response_code(400);
    die(json_encode(["error" => "Valid survey ID required"]));
}

$surveyId = (int)$_GET['survey_id'];

try {
    // Get survey
    $stmt = $pdo->prepare("SELECT id, title, description FROM surveys WHERE id = ?");
    $stmt->execute([$surveyId]);
    $survey = $stmt->fetch();

    if (!$survey) {
        http_response_code(404);
        die(json_encode(["error" => "Survey not found"]));
    }

    // Get questions
    $stmt = $pdo->prepare("SELECT id, question_text FROM questions WHERE survey_id = ?");
    $stmt->execute([$surveyId]);
    $questions = $stmt->fetchAll();

    echo json_encode([
        "survey" => $survey,
        "questions" => $questions
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Database error",
        "message" => $e->getMessage()
    ]);
}

ob_end_flush();
?>