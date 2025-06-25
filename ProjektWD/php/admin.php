<?php
session_start();
if ($_SESSION['role'] !== 'admin') die("Dostęp zabroniony");

$pdo = new PDO("mysql:host=db;dbname=market", "user", "password");
echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
<link rel="stylesheet" href="style.css">
<title>Panel</title>
</head><body>';

echo "<h1>Panel administratora</h1>";
echo "<a href='logout.php'>Wyloguj się</a><br><br>";

// Oferty
$offers = $pdo->query("
    SELECT users.username, offers.quantity, offers.price 
    FROM offers JOIN users ON users.id = offers.user_id
    ORDER BY offers.price ASC")->fetchAll();

echo "<h2>Oferty</h2><table border='1'><tr><th>Gracz</th><th>Ilość</th><th>Cena</th></tr>";
foreach ($offers as $o) {
    echo "<tr><td>{$o['username']}</td><td>{$o['quantity']}</td><td>{$o['price']}</td></tr>";
}
echo "</table><br>";

echo "<form method='POST' action='market_logic.php'>
        <input type='submit' name='close' value='Rozlicz rynek'>
      </form><br>";

// Wyniki
$results = $pdo->query("
    SELECT users.username, results.sold_quantity, results.price 
    FROM results JOIN users ON users.id = results.user_id
    ORDER BY results.price ASC")->fetchAll();

if ($results) {
    echo "<h2>Wyniki rozliczenia</h2><table border='1'><tr><th>Gracz</th><th>Ilość sprzedana</th><th>Cena</th></tr>";
    foreach ($results as $r) {
        echo "<tr><td>{$r['username']}</td><td>{$r['sold_quantity']}</td><td>{$r['price']}</td></tr>";
    }
    echo "</table><br>";
} else {
    echo "<p>Brak wyników do wyświetlenia.</p>";
}

// Przygotowanie danych do wykresu
$sold = $pdo->query("
    SELECT users.username, SUM(results.sold_quantity) AS total_sold
    FROM results JOIN users ON users.id = results.user_id
    GROUP BY users.username")->fetchAll();

$labels = [];
$data = [];

foreach ($sold as $row) {
    $labels[] = $row['username'];
    $data[] = (int)$row['total_sold'];
}

// Formularz resetu rynku
echo "<form method='POST' action='reset_market.php' onsubmit=\"return confirm('Na pewno chcesz zresetować rynek?');\">
        <input type='submit' value='Resetuj rynek'>
      </form><br>";

// Canvas wykresu
if ($data) {
    echo "<h2>Podsumowanie sprzedaży (wykres)</h2>
    <canvas id='salesChart' width='600' height='300'></canvas>";
}
?>

<!-- Wczytaj Chart.js i wygeneruj wykres -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart')?.getContext('2d');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Sprzedane sztuki',
                data: <?= json_encode($data) ?>,
                backgroundColor: '#3498db'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    stepSize: 1,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}
</script>

</body></html>
<?php exit;
