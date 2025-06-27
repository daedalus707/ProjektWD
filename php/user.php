<?php
session_start();
if ($_SESSION['role'] !== 'player') die("Dostęp zabroniony");

$pdo = new PDO("mysql:host=db;dbname=market", "user", "password");
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><link rel="stylesheet" href="style.css"><title>Panel</title></head><body>';

echo "<h1>Witaj, $username</h1>";
echo "<a href='offer_form.php'>Złóż/zmień ofertę</a> | ";
echo "<a href='logout.php'>Wyloguj się</a><br><br>";

// Pobierz i wyświetl oferty użytkownika z created_at
$stmtOffers = $pdo->prepare("SELECT quantity, price, created_at FROM offers WHERE user_id = ? ORDER BY created_at DESC");
$stmtOffers->execute([$user_id]);
$offers = $stmtOffers->fetchAll();

if ($offers) {
    echo "<h2>Twoje oferty:</h2>";
    echo "<table border='1'><tr><th>Ilość</th><th>Cena</th><th>Data złożenia</th></tr>";
    foreach ($offers as $o) {
        echo "<tr>";
        echo "<td>" . (int)$o['quantity'] . "</td>";
        echo "<td>" . number_format($o['price'], 2) . "</td>";
        echo "<td>" . htmlspecialchars($o['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "<p>Nie złożyłeś jeszcze żadnej oferty.</p><br>";
}

// Pobierz wyniki dla gracza
$stmtResults = $pdo->prepare("SELECT sold_quantity, price FROM results WHERE user_id = ?");
$stmtResults->execute([$user_id]);
$results = $stmtResults->fetchAll();

if ($results) {
    echo "<h2>Twoje wyniki:</h2><table border='1'><tr><th>Ilość sprzedana</th><th>Cena (PLN)</th><th>Zysk (PLN)</th></tr>";
foreach ($results as $r) {
    $profit = $r['sold_quantity'] * $r['price'];
    echo "<tr>
            <td>{$r['sold_quantity']}</td>
            <td>{$r['price']}</td>
            <td>" . number_format($profit, 2) . "</td>
          </tr>";
}
echo "</table>";

} else {
    echo "<p>Brak wyników rozliczenia.</p>";
}

echo '</body></html>';
exit;
