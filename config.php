<?php
// config.php - Version finale sécurisée avec SSL pour PostgreSQL Render
$host = getenv('DB_HOST') ?: 'dpg-d8n7v1rtqb8s73cs94ig-a.frankfurt-postgres.render.com';
$dbname = getenv('DB_NAME') ?: 'lms_master';
$user = getenv('DB_USER') ?: 'kamguia';
$pass = getenv('DB_PASS') ?: 'ITP6RURHpjf8tavT4TiNkm9G7DRSqt3w';
$port = getenv('DB_PORT') ?: '5432';

try {
    // AJOUT DE "sslmode=require" à la fin de la chaîne de connexion
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
