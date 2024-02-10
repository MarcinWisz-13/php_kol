<?php
session_start();

try {
    $db = new PDO('sqlite:baza.db');
} catch (PDOException $e) {
    die('Błąd połączenia z bazą danych: ' . $e->getMessage());
}


$query = 'CREATE TABLE IF NOT EXISTS rejestracja (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT NOT NULL,
            imie TEXT NOT NULL,
            wiek INTEGER NOT NULL,
            email TEXT NOT NULL
        )';
$db->exec($query);


function czyJuzZarejestrowano($ip) {
    global $db;
    $stmt = $db->prepare('SELECT COUNT(*) FROM rejestracja WHERE ip_address = :ip');
    $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}


function dodajRejestracje($ip, $imie, $wiek, $email) {
    global $db;
    $stmt = $db->prepare('INSERT INTO rejestracja (ip_address, imie, wiek, email) VALUES (:ip, :imie, :wiek, :email)');
    $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindParam(':imie', $imie, PDO::PARAM_STR);
    $stmt->bindParam(':wiek', $wiek, PDO::PARAM_INT);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $imie = $_POST['imie'];
    $wiek = $_POST['wiek'];
    $email = $_POST['email'];

    if (czyJuzZarejestrowano($ip)) {

        $stmt = $db->query('SELECT * FROM rejestracja');
        $listaOsob = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo 'Lista zarejestrowanych osób:';
        foreach ($listaOsob as $osoba) {
            echo '<br>' . $osoba['imie'] . ', ' . $osoba['wiek'] . ' lat, ' . $osoba['email'];
        }
    } else {

        if (empty($imie) || empty($wiek) || empty($email)) {
            echo 'Wypełnij wszystkie pola formularza.';
        } else {

            $stmt = $db->prepare('SELECT COUNT(*) FROM rejestracja WHERE email = :email');
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                echo 'Podany e-mail już istnieje w bazie danych.';
            } else {
     
                dodajRejestracje($ip, $imie, $wiek, $email);

              
                $stmt = $db->query('SELECT * FROM rejestracja');
                $listaOsob = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo 'Lista zarejestrowanych osób:';
                foreach ($listaOsob as $osoba) {
                    echo '<br>' . $osoba['imie'] . ', ' . $osoba['wiek'] . ' lat, ' . $osoba['email'];
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Formularz rejestracji</title>
</head>
<body>

<?php

if (czyJuzZarejestrowano($_SERVER['REMOTE_ADDR']) > 0 && !($_SERVER['REQUEST_METHOD'] == 'POST'))
{
    $stmt = $db->query('SELECT * FROM rejestracja');
    $listaOsob = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo 'Lista zarejestrowanych osób:';
    foreach ($listaOsob as $osoba) {
        echo '<br>' . $osoba['imie'] . ', ' . $osoba['wiek'] . ' lat, ' . $osoba['email'];
    }
    
}
else {

?>

    <form method="post" action="">
        <label for="imie">Imię:</label>
        <input type="text" name="imie" required><br>
        
        <label for="wiek">Wiek:</label>
        <input type="number" name="wiek" required><br>
        
        <label for="email">E-mail:</label>
        <input type="email" name="email" required><br>
        
        <button type="submit">Zarejestruj się</button>
    </form>
    <?php
    }
    ?>
</body>
</html>
