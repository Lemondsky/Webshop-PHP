<?php
session_start();

// Prüfen, ob der Benutzer schon eingeloggt ist
if (isset($_SESSION['benutzer_id'])) { // Wenn die Session-Variable gesetzt ist
    header("Location: ../index.php"); // Zur Startseite weiterleiten
    exit(); // Skript beenden
}

// Die Datenbankverbindung einbinden
// __ DIR__ gibt den Pfad zum aktuellen Verzeichnis zurück, in dem die Datei liegt
require_once __DIR__ . '/db_connect.php';
// Variable für Fehlermeldungen anlegen
$fehler = '';

// Prüfen, ob das Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verbindung zur Datenbank aufbauen
    $verbindung = erstelleDatenbankverbindung();

    // E-Mail aus dem Formular holen und "säubern"
    // säubern bedeutet, dass wir unerwünschte Zeichen entfernen
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    // Passwort aus dem Formular holen
    $passwort = $_POST['passwort'] ?? '';

    // Datenbankabfrage vorbereiten, um den Benutzer mit dieser E-Mail zu finden
    // prepare() bereitet eine SQL-Abfrage vor, die später ausgeführt wird
    // select id, username, password from users where email = ? sucht nach einem Benutzer mit der angegebenen E-Mail
    $abfrage = $verbindung->prepare("SELECT id, username, password FROM users WHERE email = ?");
    // Die E-Mail an die Abfrage binden
    // "s" steht für "string", da wir eine Zeichenkette (E-Mail) binden
    $abfrage->bind_param("s", $email);
    // Die Abfrage ausführen
    // execute() führt die vorbereitete Abfrage aus
    $abfrage->execute();
    // Das Ergebnis holen
    $ergebnis = $abfrage->get_result();

    // Prüfen, ob ein Benutzer gefunden wurde
    if ($ergebnis->num_rows === 1) { // Wenn genau ein Benutzer gefunden wurde
        // fetch_assoc() holt die Daten des Benutzers als assoziatives Array
        // Ein assoziatives Array ist ein Array, bei dem die Schlüssel (keys) die Spaltennamen der Datenbank sind
        $benutzer = $ergebnis->fetch_assoc(); // Die Benutzerdaten holen
        // Passwort überprüfen
        if (password_verify($passwort, $benutzer['password'])) { // Wenn das Passwort stimmt
            // session_regenerate_id(true) regeneriert die Session-ID, um Session-Hijacking zu verhindern
            // regenerieren bedeutet, dass eine neue, eindeutige ID für die Session erstellt wird
            session_regenerate_id(true); // Neue Session-ID generieren
            $_SESSION['benutzer_id'] = $benutzer['id']; // Benutzer-ID in der Session speichern
            $_SESSION['benutzername'] = $benutzer['username']; // Benutzernamen in der Session speichern
            header("Location: ../index.php"); // Zur Startseite weiterleiten
            exit(); // Skript beenden
        } else { // Wenn das Passwort nicht stimmt
            $fehler = "Falsches Passwort!"; // Fehlermeldung setzen (Tippfehler im Original korrigiert)
        }
    } else { // Wenn kein Benutzer gefunden wurde
        $fehler = "Kein Benutzer mit dieser E-Mail gefunden!"; // Fehlermeldung setzen
    }
    // Die Abfrage schließen
    $abfrage->close();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <title>Login</title>
    <?php include 'head.php';?> <!-- Head einbinden -->
    <link rel="stylesheet" href="../css/style_login.css"> <!-- Optional: Stylesheet für das Layout -->
</head>
<body>
<h2>Login</h2>
<!-- Fehlermeldung anzeigen, falls vorhanden -->
<?php if ($fehler): ?>
    <div style="color:red;"><?= htmlspecialchars($fehler) ?></div>
<?php endif; ?>
<!-- Das Login-Formular -->
<form method="POST">
    <!-- E-Mail-Eingabe -->
    <label>
        <input type="email" name="email" placeholder="E-Mail" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </label><br>
    <!-- Passwort-Eingabe -->
    <label>
        <input type="password" name="passwort" placeholder="Passwort" required>
    </label><br>
    <!-- Button zum Abschicken des Formulars -->
    <button type="submit">Einloggen</button>
</form>
<p>Noch kein Konto? <a href="register.php">Zur Registrierung</a></p>
</body>
<?php include 'footer.php';?> <!-- Footer einbinden -->
</html>
