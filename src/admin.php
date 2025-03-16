<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

echo "<h2>Witaj, " . $_SESSION['user'] . "!</h2>";

echo "<p>Jesteś Administratorem.</p>";

echo '<a href="logout.php">Wyloguj</a>';
?>