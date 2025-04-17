<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'dbConnect.php'; 

$data = json_decode(file_get_contents("php://input"), true);

$survey_id = $data['survey_id'] ?? null;
$user_id = $data['user_id'] ?? null;
$answers = $data['answers'] ?? [];

if (!$survey_id || !$answers) {
    echo json_encode(["error" => "Survey ID and answers required"]);
    exit;
}

// Insert into responses table
$query = "INSERT INTO responses (survey_id, user_id) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $survey_id, $user_id);
$stmt->execute();
$response_id = $stmt->insert_id;

// Insert answers
$query = "INSERT INTO response_answers (response_id, question_id, answer_text) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
foreach ($answers as $answer) {
    $stmt->bind_param("iis", $response_id, $answer['question_id'], $answer['answer_text']);
    $stmt->execute();
}

echo json_encode(["message" => "Response submitted successfully"]);
?>