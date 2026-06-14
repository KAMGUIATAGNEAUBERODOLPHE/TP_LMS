<?php
// installer.php - À lancer UNE SEULE FOIS sur Render pour créer la base de données
require_once 'config.php';

try {
    // Le code SQL de création des tables
    $sql = "
    CREATE TABLE IF NOT EXISTS utilisateurs (
        id_user INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        mot_de_passe VARCHAR(255) NOT NULL,
        role ENUM('promoteur', 'enseignant', 'etudiant') NOT NULL
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS modules (
        id_module INT AUTO_INCREMENT PRIMARY KEY,
        titre_module VARCHAR(150) NOT NULL,
        description TEXT
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS cours (
        id_cours INT AUTO_INCREMENT PRIMARY KEY,
        titre_cours VARCHAR(150) NOT NULL,
        id_module INT,
        id_enseignant INT,
        FOREIGN KEY (id_module) REFERENCES modules(id_module) ON DELETE CASCADE,
        FOREIGN KEY (id_enseignant) REFERENCES utilisateurs(id_user) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS lecons (
        id_lecon INT AUTO_INCREMENT PRIMARY KEY,
        titre_lecon VARCHAR(150) NOT NULL,
        type_contenu ENUM('pdf', 'video') NOT NULL,
        url_ressource TEXT NOT NULL,
        id_cours INT,
        questions_json LONGTEXT NOT NULL,
        FOREIGN KEY (id_cours) REFERENCES cours(id_cours) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS progressions (
        id_prog INT AUTO_INCREMENT PRIMARY KEY,
        id_etudiant INT,
        id_lecon INT,
        note_obtenue INT NOT NULL,
        statut ENUM('valide', 'echoue') NOT NULL,
        UNIQUE KEY unique_prog (id_etudiant, id_lecon),
        FOREIGN KEY (id_etudiant) REFERENCES utilisateurs(id_user) ON DELETE CASCADE,
        FOREIGN KEY (id_lecon) REFERENCES lecons(id_lecon) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    -- Insertion des comptes de test par défaut s'ils n'existent pas
    INSERT IGNORE INTO utilisateurs (id_user, nom, email, mot_de_passe, role) VALUES
    (1, 'JMAGUIA TAGNE (Étudiant)', 'etudiant@lms.cm', '5f4dcc3b5aa765d61d8327deb882cf99', 'etudiant'),
    (2, 'Dr. MESSI (Enseignant)', 'enseignant@lms.cm', '5f4dcc3b5aa765d61d8327deb882cf99', 'enseignant'),
    (3, 'Le Promoteur', 'promoteur@lms.cm', '5f4dcc3b5aa765d61d8327deb882cf99', 'promoteur');

    INSERT IGNORE INTO modules (id_module, titre_module, description) VALUES
    (1, 'Génie Logiciel & Applications Distantes', 'Grand module d administration et de validation.');

    INSERT IGNORE INTO cours (id_cours, titre_cours, id_module, id_enseignant) VALUES
    (1, 'Architecture des Systèmes Web Asynchrones', 1, 2);

    INSERT IGNORE INTO lecons (id_lecon, titre_lecon, type_contenu, url_ressource, id_cours, questions_json) VALUES
    (1, 'Introduction au protocole HTTP', 'pdf', 'https://www.w3.org/Protocols/Specs.html', 1, 
    '[{\"q\":\"Que signifie HTTP ?\",\"a\":[\"HyperText Transfer Protocol\",\"High Text Tech Protocol\",\"Hyperlink Terminal Text\"],\"r\":0},{\"q\":\"Quel est le port par défaut de HTTP ?\",\"a\":[\"443\",\"80\",\"21\"],\"r\":1},{\"q\":\"Quelle méthode lit une ressource ?\",\"a\":[\"POST\",\"GET\",\"DELETE\"],\"r\":1},{\"q\":\"Quel code statut signifie Succès ?\",\"a\":[\"404\",\"500\",\"200\"],\"r\":2},{\"q\":\"Quel code statut signifie Page non trouvée ?\",\"a\":[\"404\",\"403\",\"302\"],\"r\":0},{\"q\":\"HTTP est-il un protocole avec ou sans état ?\",\"a\":[\"Avec état\",\"Sans état\",\"Hybride\"],\"r\":1},{\"q\":\"Quelle méthode modifie totalement une ressource ?\",\"a\":[\"GET\",\"PUT\",\"HEAD\"],\"r\":1},{\"q\":\"Que signifie le code 500 ?\",\"a\":[\"Erreur Serveur\",\"Redirection\",\"Interdit\"],\"r\":0},{\"q\":\"Quelle version majeure a introduit le multiplexage ?\",\"a\":[\"HTTP/1.1\",\"HTTP/2\",\"HTTP/1.0\"],\"r\":1},{\"q\":\"Quel en-tête définit le type de contenu ?\",\"a\":[\"Content-Type\",\"Accept-Language\",\"User-Agent\"],\"r\":0}]');
    ";

    $db->exec($sql);
    echo "<h3>Base de données configurée avec succès sur Render ! 🎉</h3>";
    echo "<p>Vous pouvez maintenant vous connecter sur la page d'accueil.</p>";
} catch (PDOException $e) {
    die("Erreur lors de l'installation : " . $e->getMessage());
}
?>
