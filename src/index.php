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
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .login-container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        width: 320px;
    }

    h2 {
        margin-bottom: 15px;
        color: #333;
    }

    .input-group {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    input {
        width: calc(100% - 20px);
        padding: 12px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        text-align: left; /* Tekst wyrównany do lewej */
        padding-left: 10px; /* Odstęp od lewej krawędzi */
        outline: none;
        transition: border-color 0.3s;
    }

    input:focus {
        border-color: #007bff;
    }

    button {
        width: 100%;
        background: #007bff;
        color: white;
        border: none;
        padding: 12px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s;
    }

    button:hover {
        background: #0056b3;
    }

    .error {
        color: red;
        margin-bottom: 10px;
    }

    /* Poprawka: placeholdery również wyrównane do lewej */
    input::placeholder {
        text-align: left;
        color: #aaa;
    }
</style>



</head>
<body>
<div class="login-container">
    <h2>Panel logowania</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <div class="input-group">
            <input type="text" name="login" placeholder="Login" required>
            <input type="password" name="password" placeholder="Hasło" required>
        </div>
        <button type="submit">Zaloguj</button>
    </form>
</div>

</body>
</html>
