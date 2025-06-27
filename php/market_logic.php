<?php
require_once 'utils.php';
$pdo = new PDO("mysql:host=db;dbname=market", "user", "password");

// Czyścimy poprzednie wyniki
$pdo->exec("TRUNCATE TABLE results");

// Pobieramy oferty
$offers = $pdo->query("SELECT * FROM offers ORDER BY price ASC")->fetchAll(PDO::FETCH_ASSOC);

// Grupujemy oferty po cenie
$groups = [];
foreach ($offers as $offer) {
    $groups[$offer['price']][] = $offer;
}

$totalSold = 0;
$sales = []; // [user_id => sold]

foreach ($groups as $price => $groupOffers) {
    $demandAtPrice = demand($price) - $totalSold;
    if ($demandAtPrice <= 0) continue;

    $totalQuantity = array_sum(array_column($groupOffers, 'quantity'));

    if ($totalQuantity <= $demandAtPrice) {
        foreach ($groupOffers as $offer) {
            $sold = $offer['quantity'];
            $pdo->prepare("INSERT INTO results (user_id, sold_quantity, price) VALUES (?, ?, ?)")
                ->execute([$offer['user_id'], $sold, $price]);
            $sales[$offer['user_id']] = ($sales[$offer['user_id']] ?? 0) + $sold;
            $totalSold += $sold;
        }
    } else {
        $soldSum = 0;
        $soldArray = [];

        foreach ($groupOffers as $offer) {
            $share = $offer['quantity'] / $totalQuantity;
            $soldQty = floor($demandAtPrice * $share);
            $soldArray[$offer['user_id']] = $soldQty;
            $soldSum += $soldQty;
        }

        $residual = $demandAtPrice - $soldSum;
        if ($residual > 0) {
            $maxOffer = null;
            $maxQuantity = 0;
            foreach ($groupOffers as $offer) {
                if ($offer['quantity'] > $maxQuantity) {
                    $maxQuantity = $offer['quantity'];
                    $maxOffer = $offer['user_id'];
                }
            }
            $soldArray[$maxOffer] += $residual;
        }

        foreach ($groupOffers as $offer) {
            $sold = $soldArray[$offer['user_id']];
            $pdo->prepare("INSERT INTO results (user_id, sold_quantity, price) VALUES (?, ?, ?)")
                ->execute([$offer['user_id'], $sold, $price]);
            $sales[$offer['user_id']] = ($sales[$offer['user_id']] ?? 0) + $sold;
            $totalSold += $sold;
        }
    }
}

// Zapewnij, że każdy gracz ma wartość w $sales (nawet 0)
$stmt = $pdo->query("SELECT id FROM users WHERE role = 'player'");
while ($row = $stmt->fetch()) {
    if (!isset($sales[$row['id']])) {
        $sales[$row['id']] = 0;
    }
}

// Przygotowanie danych do wykresu
$labels = [];
$values = [];

$stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'player' ORDER BY username ASC");
while ($row = $stmt->fetch()) {
    $labels[] = $row['username'];
    $values[] = $sales[$row['id']] ?? 0;
}

// Generowanie wykresu
$width = 600;
$height = 400;
$image = imagecreatetruecolor($width, $height);
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$blue  = imagecolorallocate($image, 52, 152, 219);

imagefilledrectangle($image, 0, 0, $width, $height, $white);

// Skala i marginesy
$margin = 40;
$barSpacing = count($values) > 1 ? ($width - 2 * $margin) / (count($values) - 1) : 0;
$maxValue = max($values) ?: 1;

// Minimalna skala, by wartości poniżej 10 były też widoczne
$minScaleValue = 10;
$scale = ($height - 2 * $margin) / max($maxValue, $minScaleValue);

// Oś X i Y
imageline($image, $margin, $margin, $margin, $height - $margin, $black); // Y
imageline($image, $margin, $height - $margin, $width - $margin, $height - $margin, $black); // X

// Punkty wykresu
$points = [];
foreach ($values as $i => $val) {
    $x = (int)($margin + $i * $barSpacing);
    $y = (int)($height - $margin - $val * $scale);
    $points[] = [$x, $y];
    imagefilledellipse($image, $x, $y, 6, 6, $blue);
}

// Linie między punktami
for ($i = 0; $i < count($points) - 1; $i++) {
    imageline($image, $points[$i][0], $points[$i][1], $points[$i + 1][0], $points[$i + 1][1], $blue);
}

// Etykiety graczy na osi X
foreach ($labels as $i => $label) {
    $x = (int)($margin + $i * $barSpacing - 10);
    $y = $height - $margin + 10;
    imagestring($image, 3, $x, $y, $label, $black);
}

// Konwersja wykresu do base64
ob_start();
imagepng($image);
$imgData = ob_get_clean();
imagedestroy($image);
$base64 = base64_encode($imgData);

// HTML
echo <<<HTML
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Rynek rozliczony</title>
  <link rel="stylesheet" href="style.css">
</head>
<body style="text-align:center;padding:40px;background:#f4f4f4;">
  <h2>✅ Rynek rozliczony</h2>
  <p>Sprzedaż graczy (sztuki):</p>
  <img src="data:image/png;base64,$base64" alt="Wykres sprzedaży" style="border:1px solid #ccc;border-radius:8px;">
  <br><br>
  <form action="admin.php">
    <button type="submit" style="padding:10px 20px;background:#2980b9;color:white;border:none;border-radius:5px;cursor:pointer;">⬅️ Powrót do panelu administratora</button>
  </form>
</body>
</html>
HTML;
