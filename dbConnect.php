<?php
$host = "sql100.infinityfree.com";
$port = 21; 
$dbname = "if0_39792521_survey2";
$username = "if0_39792521";
$password = "XUw97FL4b9V";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    header("Content-Type: application/json");
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed"]));
}
?>
