<?php
session_start();
if ($_SESSION['role'] !== 'player') die("Dostęp zabroniony");

$pdo = new PDO("mysql:host=db;dbname=market", "user", "password");
$id = $_SESSION['user_id'];

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Oferta</title>
<link rel="stylesheet" href="style.css">
<style>
  .form-container {
    max-width: 400px;
    margin: 80px auto;
    padding: 30px;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
  }

  .form-container h2 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 24px;
  }

  .form-container input[type="number"] {
    width: 100%;
    padding: 12px;
    margin-bottom: 16px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
  }

  .form-container button {
    width: 100%;
    padding: 12px;
    font-weight: bold;
    font-size: 15px;
    color: white;
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
  }

  .form-container button:hover {
    background: linear-gradient(135deg, #219150, #27c76a);
  }

  .spacer {
    height: 10px;
  }

</style>
</head><body>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q = (int)$_POST['quantity'];
    $p = (float)$_POST['price'];
    $pdo->prepare("DELETE FROM offers WHERE user_id = ?")->execute([$id]);
    $pdo->prepare("INSERT INTO offers (user_id, quantity, price) VALUES (?, ?, ?)")
        ->execute([$id, $q, $p]);
    
    echo '<div class="form-container">';
    echo "<h2>Oferta została złożona.</h2>";
    echo "<div class='spacer'></div>";
    echo "<form action='user.php'><button type='submit'>⬅️ Powrót do panelu</button></form>";
    echo '</div>';
} else {
    echo '<div class="form-container">
            <h2>Złóż swoją ofertę</h2>
            <form method="POST">
              <input name="quantity" type="number" placeholder="Ilość sztuk" required><br>
              <input name="price" type="number" step="0.01" placeholder="Cena za sztukę (PLN)" required><br>
              <button type="submit">💼 Złóż ofertę</button>
            </form>
            <div class="spacer"></div>
            <form action="user.php">
              <button type="submit">⬅️ Powrót do panelu</button>
            </form>
          </div>';
}

echo '</body></html>';
