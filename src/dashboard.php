<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

echo "<h2>Witaj, " . $_SESSION['user'] . "!</h2>";

if ($_SESSION['is_admin']) {
    echo "<p>Jesteś administratorem!</p>";
} else {
    echo "<p>Jesteś zwykłym użytkownikiem.</p>";
}

echo '<a href="logout.php">Wyloguj</a>';
?>