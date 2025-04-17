<?php
header("Content-Type: application/json");
require_once 'dbConnect.php';
session_start();

// Get and validate input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid JSON data']));
}

// Validate required fields
if (empty($data['title']) || !is_string($data['title'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Survey title is required and must be a string']));
}

if (empty($data['questions']) || !is_array($data['questions'])) {
    http_response_code(400);
    die(json_encode(['error' => 'At least one question is required']));
}

// Filter out empty questions
$validQuestions = array_filter(array_map('trim', $data['questions']), 'strlen');
if (empty($validQuestions)) {
    http_response_code(400);
    die(json_encode(['error' => 'No valid questions provided']));
}

try {
    $pdo->beginTransaction();
    
    // Insert survey
    $stmt = $pdo->prepare("INSERT INTO surveys (title, description, user_id) VALUES (?, ?, ?)");
    $stmt->execute([
        htmlspecialchars($data['title']),
        htmlspecialchars($data['description'] ?? ''),
        $_SESSION['user']['id']
    ]);
    $surveyId = $pdo->lastInsertId();
    
    // Insert questions
    $stmt = $pdo->prepare("INSERT INTO questions (survey_id, question_text) VALUES (?, ?)");
    foreach ($validQuestions as $question) {
        $stmt->execute([$surveyId, htmlspecialchars($question)]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'survey_id' => $surveyId,
        'questions_inserted' => count($validQuestions)
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    if ($pdo->inTransaction()) $pdo->rollBack();
}
?>