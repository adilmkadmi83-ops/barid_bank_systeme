<?php
// ===== database.php =====
// Connexion à la base de données via PDO

$host = 'localhost';
$dbname = 'baridcash';
$username = 'root'; // XAMPP : "root" par défaut
$password = '';     // XAMPP : mot de passe vide par défaut

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // afficher les erreurs SQL sous forme d'exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // résultats sous forme de tableaux associatifs
    ]);
} catch (PDOException $e) {
    // Le détail technique est écrit dans le journal d'Apache, pas affiché à l'utilisateur
    error_log("Connexion BD : " . $e->getMessage());
    die("Erreur : connexion à la base de données impossible. Vérifiez que MySQL est démarré et que la base « baridcash » est importée.");
}
?>
























































