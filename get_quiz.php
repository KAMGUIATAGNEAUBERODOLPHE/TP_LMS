<?php
require_once 'config.php';
header('Content-Type: application/json');

if(isset($_GET['id_lecon'])) {
    $stmt = $db->prepare("SELECT questions_json FROM lecons WHERE id_lecon = ?");
    $stmt->execute([$_GET['id_lecon']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $res['questions_json'];
}
?>
