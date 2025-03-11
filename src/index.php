<?php
$host = 'postgres';
$db = 'testdb';
$user = 'user';
$password = 'password';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db;";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo "Połączenie udane!<br>";

    $query = $pdo->query("SELECT 'Witaj w PostgreSQL!' AS message");
    $result = $query->fetch(PDO::FETCH_ASSOC);
    
    echo $result['message'];
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
}
?>
