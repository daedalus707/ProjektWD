<?php
$pdo = new PDO("mysql:host=db;dbname=market", "user", "password");

$pdo->exec("DROP TABLE IF EXISTS results, offers, users");

$pdo->exec("
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(10) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('player', 'admin') NOT NULL
)");

$pdo->exec("
CREATE TABLE offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    is_locked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

$pdo->exec("
CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sold_quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

$users = [
    ['A', 'A', 'player'],
    ['B', 'B', 'player'],
    ['C', 'C', 'player'],
    ['D', 'D', 'player'],
    ['X', 'X', 'admin'],
];

foreach ($users as [$u, $p, $r]) {
    $hashed = password_hash($p, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$u, $hashed, $r]);
}

echo "Baza danych i użytkownicy zostali utworzeni.";
