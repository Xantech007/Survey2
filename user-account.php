<?php
require_once 'dbConnect.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['signup'])) {
        // Registration logic
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $name = trim($_POST['name']);
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirm_password']);

        // Validations
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        if (empty($name)) {
            $errors['name'] = 'Name is required';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        // Check if email exists
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $errors['user_exist'] = 'Email already registered';
                }
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $errors['general'] = 'Registration failed. Please try again.';
            }
        }

        if (empty($errors)) {
            try {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
                $stmt->execute([$name, $email, $hashedPassword]);
                
                $_SESSION['success'] = 'Registration successful! Please login.';
                header('Location: index.php');
                exit();
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $errors['general'] = 'Registration failed. Please try again.';
            }
        }

        $_SESSION['errors'] = $errors;
        header('Location: register.php');
        exit();

    } elseif (isset($_POST['signin'])) {
        // Login logic
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT id, name, email, password, role, last_login FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Set session variables
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'last_login' => $user['last_login']
                    ];

                    // Update last login time
                    $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")
                        ->execute([$user['id']]);
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: admin_dashboard.php');
                    } else {
                        header('Location: home.php');
                    }
                    exit();
                } else {
                    $errors['login'] = 'Invalid email or password';
                }
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $errors['login'] = 'Login failed. Please try again.';
            }
        }

        $_SESSION['errors'] = $errors;
        header('Location: index.php');
        exit();
    }
}

header('Location: index.php');
exit();
?>