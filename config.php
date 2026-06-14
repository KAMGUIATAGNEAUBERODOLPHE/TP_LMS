<?php
// config.php - Configuration compatible Local (XAMPP) et Production (Render)

// Si on est sur Render, il prend les variables du serveur, sinon il prend "localhost" (XAMPP)
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'lms_master';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: ''; 
$port = getenv('DB_PORT') ?: '3306';

try {
    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
