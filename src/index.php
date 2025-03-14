<?php
session_start();

$host = 'postgres';
$db = 'testdb';
$user = 'user';
$password = 'password';

try {
    $pdo = new PDO("pgsql:host=$host;port=5432;dbname=$db;", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Tworzenie tabeli, jeśli nie istnieje
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            login VARCHAR(50) UNIQUE NOT NULL,
            password TEXT NOT NULL,
            is_admin BOOLEAN NOT NULL
        );
    ");

    // Sprawdzenie, czy tabela jest pusta
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Lista użytkowników
        $users = [
            ['A', 'passA', 0], // false = 0
            ['B', 'passB', 0],
            ['C', 'passC', 0],
            ['D', 'passD', 0],
            ['Administrator', 'passAdmin', 1] // true = 1
        ];

        $stmt = $pdo->prepare("INSERT INTO users (login, password, is_admin) VALUES (:login, :password, :is_admin)");

        foreach ($users as $user) {
            $stmt->execute([
                'login' => $user[0],
                'password' => password_hash($user[1], PASSWORD_BCRYPT),
                'is_admin' => $user[2] // 0 lub 1 zamiast true/false
            ]);
        }
    }

} catch (PDOException $e) {
    die("Błąd połączenia: " . $e->getMessage());
}

// Obsługa logowania
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = :login");
    $stmt->execute(['login' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['login'];
        $_SESSION['is_admin'] = (bool) $user['is_admin'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Niepoprawne dane logowania!";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
</head>
<body>
    <h2>Panel logowania</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        Login: <input type="text" name="login" required><br>
        Hasło: <input type="password" name="password" required><br>
        <button type="submit">Zaloguj</button>
    </form>
</body>
</html>
