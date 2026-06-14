<?php
require_once 'config.php';
session_start();
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'enseignant') { header('Location: index.php'); exit; }

$id_prof = $_SESSION['user_id'];
$msg = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = htmlspecialchars($_POST['titre']);
    $type = $_POST['type'];
    $url = htmlspecialchars($_POST['url']);
    $id_cours = intval($_POST['id_cours']);
    
    // Génération automatique d'un template de 10 questions pour l'évaluation exigée
    $questions = [];
    for($i=1; $i<=10; $i++) {
        $questions[] = [
            "q" => htmlspecialchars($_POST["q_$i"]),
            "a" => [htmlspecialchars($_POST["q_{$i}_o1"]), htmlspecialchars($_POST["q_{$i}_o2"]), htmlspecialchars($_POST["q_{$i}_o3"])],
            "r" => intval($_POST["q_{$i}_r"])
        ];
    }
    $json = json_encode($questions, JSON_UNESCAPED_UNICODE);

    $ins = $db->prepare("INSERT INTO lecons (titre_lecon, type_contenu, url_ressource, id_cours, questions_json) VALUES (?, ?, ?, ?, ?)");
    $ins->execute([$titre, $type, $url, $id_cours, $json]);
    $msg = "Leçon et évaluation de 10 questions ajoutées avec succès !";
}

$cours = $db->query("SELECT * FROM cours WHERE id_enseignant = $id_prof")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><title>Studio Enseignant</title>
    <style>
        body { font-family: sans-serif; background: #0f172a; color: white; padding: 30px; }
        .form-box { background: #1e293b; padding: 25px; border-radius: 8px; max-width: 700px; margin: auto; }
        input, select, textarea { width: 100%; padding: 8px; margin: 5px 0 15px; background: #334155; border:1px solid #475569; color:white; border-radius:4px; box-sizing:border-box;}
        button { background: #8b5cf6; padding: 12px 20px; border:none; color:white; font-weight:bold; border-radius:4px; cursor:pointer;}
        .q-block { background: #334155; padding: 10px; margin-bottom: 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Espace Enseignant - Création de Leçon</h2>
        <p>Enseignant actif : <strong><?= $_SESSION['user_nom'] ?></strong> | <a href="logout.php" style="color:#f87171">Déconnexion</a></p>
        <?php if($msg): ?><p style="color:#10b981"><?= $msg ?></p><?php endif; ?>
        
        <form method="POST">
            <label>Associer au Cours :</label>
            <select name="id_cours" required>
                <?php foreach($cours as $c): ?><option value="<?= $c['id_cours'] ?>"><?= $c['titre_cours'] ?></option><?php endforeach; ?>
            </select>

            <label>Titre de la leçon :</label>
            <input type="text" name="titre" required placeholder="Ex: Structuration d'une requête AJAX">

            <label>Type de support :</label>
            <select name="type">
                <option value="pdf">Document (PDF)</option>
                <option value="video">Vidéo (Lien Externe)</option>
            </select>

            <label>URL du support (Lien Cloud/Drive/YouTube) :</label>
            <input type="url" name="url" required placeholder="https://...">

            <h3>Configuration de l'Évaluation Obligatoire (10 Questions)</h3 >
            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 25px; border: 1px solid #475569; padding: 10px;">
                <?php for($i=1; $i<=10; $i++): ?>
                    <div class="q-block">
                        <strong>Question <?= $i ?> :</strong>
                        <input type="text" name="q_<?= $i ?>" placeholder="Énoncé de la question" required>
                        <input type="text" name="q_<?= $i ?>_o1" placeholder="Option 1" required>
                        <input type="text" name="q_<?= $i ?>_o2" placeholder="Option 2" required>
                        <input type="text" name="q_<?= $i ?>_o3" placeholder="Option 3" required>
                        <select name="q_<?= $i ?>_r">
                            <option value="0">La bonne réponse est l'Option 1</option>
                            <option value="1">La bonne réponse est l'Option 2</option>
                            <option value="2">La bonne réponse est l'Option 3</option>
                        </select>
                    </div>
                <?php endfor; ?>
            </div>

            <button type="submit">Publier la leçon et son évaluation</button>
        </form>
    </div>
</body>
</html>
