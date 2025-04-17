<?php
require_once 'dbConnect.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['survey_id']) || !is_numeric($_GET['survey_id'])) {
    header('Location: home.php?error=invalid_survey');
    exit;
}

$surveyId = (int)$_GET['survey_id'];

// Display success/error messages
$alert = '';
if (isset($_GET['success'])) {
    $alert = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Survey submitted successfully!</div>';
}
if (isset($_GET['error'])) {
    $alert = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Error: '.htmlspecialchars($_GET['error']).'</div>';
}

try {
    // Get survey details
    $stmt = $pdo->prepare("SELECT id, title, description FROM surveys WHERE id = ?");
    $stmt->execute([$surveyId]);
    $survey = $stmt->fetch();

    if (!$survey) {
        header('Location: home.php?error=survey_not_found');
        exit;
    }

    // Get questions
    $stmt = $pdo->prepare("SELECT id, question_text FROM questions WHERE survey_id = ? ORDER BY id");
    $stmt->execute([$surveyId]);
    $questions = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Error loading survey. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($survey['title']) ?> - Survey</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-gray-700 p-4 text-white flex justify-between items-center">
        <span class="font-bold text-lg"><?= htmlspecialchars($survey['title']) ?></span>
        <a href="home.php" class="text-white hover:underline">← Back to Home</a>
    </nav>

    <div class="max-w-3xl mx-auto p-6">
        <?= $alert ?>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <?php if (!empty($survey['description'])): ?>
                <p class="text-gray-600 mb-6"><?= htmlspecialchars($survey['description']) ?></p>
            <?php endif; ?>

            <?php if (!empty($questions)): ?>
                <form id="surveyResponseForm" method="POST" action="submit_survey.php" class="space-y-6">
                    <input type="hidden" name="survey_id" value="<?= $survey['id'] ?>">

                    <?php foreach ($questions as $index => $question): ?>
                        <div class="mb-4">
                            <label class="block text-lg font-medium text-gray-800 mb-2">
                                <?= ($index + 1) ?>. <?= htmlspecialchars($question['question_text']) ?>
                            </label>
                            <input type="text" name="answers[<?= $question['id'] ?>]" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    <?php endforeach; ?>

                    <div class="flex justify-between pt-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                            Submit Survey
                        </button>
                        <a href="home.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                            Cancel
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                    <p>This survey doesn't have any questions yet.</p>
                </div>
                <a href="home.php" class="inline-block mt-4 text-blue-600 hover:underline">← Return to home</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>