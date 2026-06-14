<?php
require_once 'config.php';
session_start();
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'etudiant') { header('Location: index.php'); exit; }

$id_etudiant = $_SESSION['user_id'];

// Calcul de la progression globale basée sur la moyenne des taux de réussite
$prog_stmt = $db->prepare("SELECT AVG(note_obtenue * 10) as moy FROM progressions WHERE id_etudiant = ? AND statut = 'valide'");
$prog_stmt->execute([$id_etudiant]);
$progression = round($prog_stmt->fetch(PDO::FETCH_ASSOC)['moy'] ?? 0);

// Récupération des leçons
$lecons = $db->query("SELECT l.*, p.note_obtenue, p.statut FROM lecons l LEFT JOIN progressions p ON l.id_lecon = p.id_lecon AND p.id_etudiant = $id_etudiant")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><title>Espace Étudiant</title>
    <style>
        body { font-family: sans-serif; background: #0f172a; color: #f8fafc; margin: 0; display: flex; height: 100vh; }
        .sidebar { width: 300px; background: #1e293b; padding: 20px; border-right: 1px solid #334155; }
        .content { flex: 1; padding: 30px; overflow-y: auto; }
        .progress-bar { background: #334155; border-radius: 10px; height: 20px; width: 100%; margin: 15px 0; overflow: hidden; }
        .progress-fill { background: #10b981; height: 100%; width: <?= $progression ?>%; transition: width 0.5s; }
        .lecon-item { background: #334155; padding: 15px; margin-bottom: 10px; border-radius: 6px; cursor: pointer; }
        .btn { background: #8b5cf6; padding: 10px 15px; border: none; color: white; border-radius: 4px; cursor: pointer; margin-top: 15px; }
        .quiz-container { background: #1e293b; padding: 20px; border-radius: 8px; margin-top: 20px; display: none; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Mon Profil</h3>
        <p>👤 <?= $_SESSION['user_nom'] ?></p>
        <hr style="border-color:#334155">
        <h4>Progression Globale</h4>
        <div class="progress-bar"><div class="progress-fill"></div></div>
        <p align="right"><?= $progression ?>% Réussite</p>
        <hr style="border-color:#334155">
        <h3>Mes Leçons</h3>
        <?php foreach($lecons as $l): ?>
            <div class="lecon-item" onclick="chargerLecon('<?= $l['titre_lecon'] ?>', '<?= $l['type_contents'] ?>', '<?= $l['url_ressource'] ?>', <?= $l['id_lecon'] ?>)">
                <strong><?= $l['titre_lecon'] ?></strong><br>
                <small>Statut : <?= $l['statut'] ? ($l['statut'] == 'valide' ? '✅ Validé ('.$l['note_obtenue'].'/10)' : '❌ Échoué') : '⏳ Non fait' ?></small>
            </div>
        <?php endforeach; ?>
        <br><a href="logout.php" style="color:#f87171">Déconnexion</a>
    </div>

    <div class="content">
        <h2 id="lecon-titre">Sélectionnez une leçon à gauche pour commencer</h2>
        <div id="lecon-media" style="margin-bottom: 20px;"></div>
        <button id="btn-quiz" class="btn" style="display:none;" onclick="ouvrirQuiz()">Passer à l'évaluation (10 Questions)</button>

        <div id="quiz" class="quiz-container">
            <form id="quiz-form">
                <input type="hidden" id="quiz-lecon-id" name="id_lecon">
                <div id="questions-liste"></div>
                <button type="submit" class="btn" style="background:#10b981;">Soumettre mon évaluation</button>
            </form>
        </div>
    </div>

    <script>
        let currentLeconId = null;

        function chargerLecon(titre, type, url, id) {
            currentLeconId = id;
            document.getElementById('lecon-titre').innerText = titre;
            document.getElementById('quiz').style.display = 'none';
            
            let mediaDiv = document.getElementById('lecon-media');
            if(type === 'pdf') {
                mediaDiv.innerHTML = `<iframe src="${url}" width="100%" height="450px" style="border:none; border-radius:6px;"></iframe>`;
            } else {
                mediaDiv.innerHTML = `<p>Regardez la vidéo via ce lien externe : <a href="${url}" target="_blank" style="color:#8b5cf6;">Ouvrir la leçon vidéo 📺</a></p>`;
            }
            document.getElementById('btn-quiz').style.display = 'block';
        }

        function ouvrirQuiz() {
            document.getElementById('quiz-lecon-id').value = currentLeconId;
            
            // Récupération asynchrone (AJAX Fetch) des questions de l'évaluation
            fetch(`get_quiz.php?id_lecon=${currentLeconId}`)
            .then(res => res.json())
            .then(questions => {
                let html = '';
                questions.forEach((q, i) => {
                    html += `<div style="margin-bottom:15px;"><strong>Question ${i+1} : ${q.q}</strong><br>`;
                    q.a.forEach((opt, idx) => {
                        html += `<label><input type="radio" name="q_${i}" value="${idx}" required> ${opt}</label><br>`;
                    });
                    html += `</div>`;
                });
                document.getElementById('questions-liste').innerHTML = html;
                document.getElementById('quiz').style.display = 'block';
            });
        }

        document.getElementById('quiz-form').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            // Envoi asynchrone (AJAX POST) des réponses pour calcul et mise à jour de la progression
            fetch('evaluer.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                alert(`Résultat : ${res.note}/10. Statut : ${res.statut === 'valide' ? 'VALIDÉ (>= 60%) 🏆' : 'ÉCHOUÉ (< 60%). Réessayez !'}`);
                location.reload();
            });
        });
    </script>
</body>
</html>
