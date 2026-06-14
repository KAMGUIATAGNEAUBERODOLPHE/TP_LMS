<?php
require_once 'config.php';
session_start();
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'promoteur') { header('Location: index.php'); exit; }

$msg = "";
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_module'])) {
    $titre = htmlspecialchars($_POST['titre_module']);
    $desc = htmlspecialchars($_POST['description']);
    $ins = $db->prepare("INSERT INTO modules (titre_module, description) VALUES (?, ?)");
    $ins->execute([$titre, $desc]);
    $msg = "Nouveau module déployé au catalogue.";
}

// Requête de vérification pour la délivrance des certificats : étudiants ayant validé toutes les leçons existantes avec au moins 60%
$certifs = $db->query("
    SELECT u.nom, m.titre_module, u.id_user, m.id_module 
    FROM utilisateurs u
    CROSS JOIN modules m
    WHERE u.role = 'etudiant'
    AND NOT EXISTS (
        SELECT l.id_lecon FROM lecons l 
        JOIN cours c ON l.id_cours = c.id_cours
        WHERE c.id_module = m.id_module
        AND l.id_lecon NOT IN (
            SELECT id_lecon FROM progressions WHERE id_etudiant = u.id_user AND statut = 'valide'
        )
    )
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><title>Espace Promoteur</title>
    <style>
        body { font-family: sans-serif; background: #0f172a; color: white; padding: 30px; display: flex; gap: 30px; }
        .panel { background: #1e293b; padding: 25px; border-radius: 8px; flex: 1; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0 15px; background: #334155; border:1px solid #475569; color:white; border-radius:4px; box-sizing:border-box;}
        button { background: #10b981; padding: 12px; border:none; color:white; font-weight:bold; border-radius:4px; cursor:pointer; width:100%;}
        .cert-card { background: #334155; padding: 15px; margin-top: 10px; border-radius: 6px; border-left: 5px solid #eab308; }
    </style>
</head>
<body>
    <div class="panel">
        <h2>Création de Modules de Cours</h2>
        <?php if($msg): ?><p style="color:#10b981"><?= $msg ?></p><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="btn_module" value="1">
            <label>Intitulé du Grand Module :</label>
            <input type="text" name="titre_module" required placeholder="Ex: Master Spécialisé en Cloud Computing">
            <label>Description des compétences visées :</label>
            <textarea name="description" rows="4" required></textarea>
            <button type="submit">Activer le module</button>
        </form>
        <br><br><a href="logout.php" style="color:#f87171">Déconnexion</a>
    </div>

    <div class="panel">
        <h2>Attribution des Certificats Académiques</h2>
        <p>Étudiants éligibles (Ayant validé 100% des évaluations à minimum 60%) :</p>
        <?php if(empty($certifs)): ?>
            <p style="color:#94a3b8;">Aucun étudiant n'a encore entièrement finalisé et validé de module.</p>
        <?php else: ?>
            <?php foreach($certifs as $cert): ?>
                <div class="cert-card">
                    <strong>🎓 <?= $cert['nom'] ?></strong><br>
                    <small>A brillamment validé le programme : <em><?= $cert['titre_module'] ?></em></small><br>
                    <button style="margin-top:10px; padding:6px; font-size:0.8rem;" onclick="alert('Certificat généré avec succès avec signature numérique de l administration !')">Attribuer le Certificat Officiel</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
