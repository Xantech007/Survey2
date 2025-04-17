<?php
require_once 'dbConnect.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php');
    exit;
}

// Validate survey_id
if (!isset($_POST['survey_id']) || !is_numeric($_POST['survey_id'])) {
    header('Location: home.php?error=invalid_survey');
    exit;
}

$surveyId = (int)$_POST['survey_id'];
$userId = $_SESSION['user']['id'];

try {
    $pdo->beginTransaction();

    // 1. Verify survey exists
    $stmt = $pdo->prepare("SELECT id FROM surveys WHERE id = ?");
    $stmt->execute([$surveyId]);
    if (!$stmt->fetch()) {
        throw new Exception("Survey not found");
    }

    // 2. Insert response record
    $stmt = $pdo->prepare("INSERT INTO responses (survey_id, user_id, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$surveyId, $userId]);
    $responseId = $pdo->lastInsertId();

    // 3. Insert answers
    if (!empty($_POST['answers']) && is_array($_POST['answers'])) {
        $stmt = $pdo->prepare("INSERT INTO response_answers (response_id, question_id, answer_text) VALUES (?, ?, ?)");
        
        $answersInserted = 0;
        foreach ($_POST['answers'] as $questionId => $answerText) {
            $questionId = (int)$questionId;
            $answerText = trim($answerText);
            
            if ($questionId > 0 && !empty($answerText)) {
                $stmt->execute([$responseId, $questionId, htmlspecialchars($answerText)]);
                $answersInserted++;
            }
        }
        
        if ($answersInserted === 0) {
            throw new Exception("No valid answers provided");
        }
    } else {
        throw new Exception("No answers received");
    }

    $pdo->commit();
    
    // Redirect with success message
    header('Location: home.php?success=survey_submitted');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Survey submission error: " . $e->getMessage());
    header('Location: take_survey.php?survey_id='.$surveyId.'&error=submission_failed');
    exit;
}
?>