<?php
require_once 'config.php';
session_start();
header('Content-Type: application/json');

$id_etudiant = $_SESSION['user_id'];
$id_lecon = intval($_POST['id_lecon']);

$stmt = $db->prepare("SELECT questions_json FROM lecons WHERE id_lecon = ?");
$stmt->execute([$id_lecon]);
$lecon = $stmt->fetch(PDO::FETCH_ASSOC);
$questions = json_encode(json_decode($lecon['questions_json']), true); // Décodage propre du JSON
$questions = json_decode($lecon['questions_json'], true);

$note = 0;
foreach($questions as $i => $q) {
    if(isset($_POST["q_$i"]) && intval($_POST["q_$i"]) === $q['r']) {
        $note++;
    }
}

// Condition stricte : 60% requis (donc 6 bonnes réponses sur 10)
$statut = ($note >= 6) ? 'valide' : 'echoue';

// Sauvegarde ou écrasement de la progression
$save = $db->prepare("INSERT INTO progressions (id_etudiant, id_lecon, note_obtenue, statut) 
                      VALUES (?, ?, ?, ?) 
                      ON DUPLICATE KEY UPDATE note_obtenue = ?, statut = ?");
$save->execute([$id_etudiant, $id_lecon, $note, $statut, $note, $statut]);

echo json_encode(['note' => $note, 'statut' => $statut]);
?>
