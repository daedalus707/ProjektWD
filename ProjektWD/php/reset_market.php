<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    die("Dostęp zabroniony");
}

$pdo = new PDO("mysql:host=db;dbname=market", "user", "password");

// Usuwamy oferty i wyniki
$pdo->exec("DELETE FROM offers");
$pdo->exec("DELETE FROM results");

header("Location: admin.php");
exit;
