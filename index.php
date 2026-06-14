<?php
require_once 'config.php';
session_start();
$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = md5($_POST['password']); // Système d'empreinte basique concordant avec le SQL

    $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = ? AND mot_de_passe = ?");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'promoteur') header('Location: promoteur.php');
        if ($user['role'] === 'enseignant') header('Location: enseignant.php');
        if ($user['role'] === 'etudiant') header('Location: etudiant.php');
        exit;
    } else {
        $erreur = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><title>Connexion | LMS Master</title>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; margin:0; }
        .box { background-color: #1e293b; padding: 40px; border-radius: 8px; width: 350px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        input { width: 100%; padding: 10px; margin: 10px 0; background: #334155; border: 1px solid #475569; color: white; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #8b5cf6; border: none; color: white; font-weight: bold; border-radius: 4px; cursor: pointer; }
        button:hover { background: #7c3aed; }
        .err { color: #f87171; font-size: 0.9rem; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>LMS Master 🎓</h2>
        <?php if($erreur): ?><div class="err"><?= $erreur ?></div><?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
