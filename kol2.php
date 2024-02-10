CREATE TABLE IF NOT EXISTS Osoby (
    id INTEGER PRIMARY KEY,
    imię TEXT,
    nazwisko TEXT,
    wiek INTEGER);

//rysuj.php
<?php

try {
    // Utwórz połączenie z bazą danych SQLite3
    $db_file = 'moja_baza.db'; // Ścieżka do pliku bazy danych SQLite3
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Przygotuj zapytanie SQL do pobrania danych o wieku
    $sql = "SELECT wiek FROM Osoby";
    $stmt = $pdo->query($sql);
    $ages = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Inicjalizacja liczników dla przedziałów wiekowych
    $range_0_20 = 0;
    $range_20_40 = 0;
    $range_40_60 = 0;
    $range_60_plus = 0;

    // Oblicz liczbę osób w każdym przedziale wiekowym
    foreach ($ages as $age) {
        if ($age >= 0 && $age <= 20) {
            $range_0_20++;
        } elseif ($age > 20 && $age <= 40) {
            $range_20_40++;
        } elseif ($age > 40 && $age <= 60) {
            $range_40_60++;
        } else {
            $range_60_plus++;
        }
    }

    // Stwórz histogram przy użyciu biblioteki GD
    $width = 600;
    $height = 700;
    $bar_width = 50;
    $image = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    imagefilledrectangle($image, 0, 0, $width, $height, $white);

    // Oblicz wysokość słupków dla każdego przedziału wiekowego
    $max_count = max($range_0_20, $range_20_40, $range_40_60, $range_60_plus);
    $scale = $max_count > 0 ? $height / $max_count : 1;

    // Rysuj słupki histogramu
    $x = 30;
    $bar_colors = [$black, $black, $black, $black]; // Kolor słupków
    $bar_labels = ['0-20', '20-40', '40-60', '60+']; // Etykiety na osi X
    $bar_counts = [$range_0_20, $range_20_40, $range_40_60, $range_60_plus]; // Liczba osób w każdym przedziale wiekowym

    for ($i = 0; $i < count($bar_counts); $i++) {
        $x += 80;
        $bar_height = $bar_counts[$i] * ($scale - 20);
        $y = $height - $bar_height - 50;
        imagefilledrectangle($image, $x, $y, $x + $bar_width, $height - 50, $bar_colors[$i]);
        imagestring($image, 5, $x, $height - 40, $bar_labels[$i], $black);
        imagestring($image, 5, $x, $y - 20, $bar_counts[$i], $black);
    }

    // Wyświetl histogram jako obraz PNG
    header('Content-type: image/png');
    imagepng($image);
    imagedestroy($image);
} catch (PDOException $e) {
    // Obsłuż błąd
    echo "Błąd: " . $e->getMessage();
}

?>


//dodaj.php

<?php

try {
    // Utwórz połączenie z bazą danych SQLite3
    $db_file = 'moja_baza.db'; // Ścieżka do pliku bazy danych SQLite3
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pobierz dane z formularza
    $imie = $_POST['imie'] ?? '';
    $nazwisko = $_POST['nazwisko'] ?? '';
    $wiek = $_POST['wiek'] ?? '';
    
    // Przygotuj zapytanie SQL do wstawienia danych do tabeli Osoby
    $sql = "INSERT INTO Osoby (imię, nazwisko, wiek) VALUES (:imie, :nazwisko, :wiek)";
    $stmt = $pdo->prepare($sql);

    // Wykonaj zapytanie z przekazaniem wartości bezpośrednio do metody execute()
    $stmt->execute(array(':imie' => $imie, ':nazwisko' => $nazwisko, ':wiek' => $wiek));

    echo "Dane zostały dodane do bazy danych.";
} catch(PDOException $e) {
    // Obsłuż błąd
    echo "Błąd: " . $e->getMessage();
}

?>


//index.php

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formularz</title>
</head>
<body>
    <h2>Dodaj osobę</h2>
    <form action="dodaj.php" method="post">
        <label for="imie">Imię:</label><br>
        <input type="text" id="imie" name="imie"><br>
        <label for="nazwisko">Nazwisko:</label><br>
        <input type="text" id="nazwisko" name="nazwisko"><br>
        <label for="wiek">Wiek:</label><br>
        <input type="number" id="wiek" name="wiek" max="100" min="1" required><br><br>
        <button type="submit" name="dodaj">Dodaj</button>
    </form>
    
    <h2>Rysuj histogram</h2>
    <form action="rysuj.php" method="post">
        <button type="submit" name="rysuj">Rysuj</button>
    </form>
</body>
</html>
