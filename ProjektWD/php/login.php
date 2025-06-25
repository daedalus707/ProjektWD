<?php
session_start();
$pdo = new PDO("mysql:host=db;dbname=market", "user", "password");

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    header("Location: " . ($user['role'] === 'admin' ? 'admin.php' : 'user.php'));
} else {
    echo "Błędne dane logowania.";
}
