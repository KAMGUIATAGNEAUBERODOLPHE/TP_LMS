<?php
// installer.php - Version corrigée pour PostgreSQL sur Render
require_once 'config.php';

try {
    // 1. Création des tables avec la syntaxe PostgreSQL
    $db->exec("
        CREATE TABLE IF NOT EXISTS utilisateurs (
            id_user SERIAL PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            email VARCHAR(150) UNIQUE NOT NULL,
            mot_de_passe VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL CHECK (role IN ('promoteur', 'enseignant', 'etudiant'))
        );

        CREATE TABLE IF NOT EXISTS modules (
            id_module SERIAL PRIMARY KEY,
            titre_module VARCHAR(150) NOT NULL,
            description TEXT
        );

        CREATE TABLE IF NOT EXISTS cours (
            id_cours SERIAL PRIMARY KEY,
            titre_cours VARCHAR(150) NOT NULL,
            id_module INT REFERENCES modules(id_module) ON DELETE CASCADE,
            id_enseignant INT REFERENCES utilisateurs(id_user) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS lecons (
            id_lecon SERIAL PRIMARY KEY,
            titre_lecon VARCHAR(150) NOT NULL,
            type_contenu VARCHAR(10) NOT NULL CHECK (type_contenu IN ('pdf', 'video')),
            url_ressource TEXT NOT NULL,
            id_cours INT REFERENCES cours(id_cours) ON DELETE CASCADE,
            questions_json TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS progressions (
            id_prog SERIAL PRIMARY KEY,
            id_etudiant INT REFERENCES utilisateurs(id_user) ON DELETE CASCADE,
            id_lecon INT REFERENCES lecons(id_lecon) ON DELETE CASCADE,
            note_obtenue INT NOT NULL,
            statut VARCHAR(10) NOT NULL CHECK (statut IN ('valide', 'echoue')),
            CONSTRAINT unique_prog UNIQUE (id_etudiant, id_lecon)
        );
    ");

    // 2. Vérification et Insertion des données de test
    $check = $db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
    if ($check == 0) {
        $db->exec("
            INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES
            ('JMAGUIA TAGNE (Étudiant)', 'etudiant@lms.cm', '5f4dcc3b5aa765d61d8327deb882cf99', 'etudiant'),
            ('Dr. MESSI (Enseignant)', 'enseignant@lms.cm', '5f4dcc3b5aa765d61d8327deb882cf99', 'enseignant'),
            ('Le Promoteur', 'promoteur@lms.cm', '5f4dcc3b5aa765d61d8327deb882cf99', 'promoteur');

            INSERT INTO modules (titre_module, description) VALUES
            ('Génie Logiciel & Applications Distantes', 'Grand module d administration et de validation.');

            INSERT INTO cours (titre_cours, id_module, id_enseignant) VALUES
            ('Architecture des Systèmes Web Asynchrones', 1, 2);

            INSERT INTO lecons (titre_lecon, type_contenu, url_ressource, id_cours, questions_json) VALUES
            ('Introduction au protocole HTTP', 'pdf', 'https://www.w3.org/Protocols/Specs.html', 1, 
            '[{\"q\":\"Que signifie HTTP ?\",\"a\":[\"HyperText Transfer Protocol\",\"High Text Tech Protocol\",\"Hyperlink Terminal Text\"],\"r\":0},{\"q\":\"Quel est le port par défaut de HTTP ?\",\"a\":[\"443\",\"80\",\"21\"],\"r\":1},{\"q\":\"Quelle méthode lit une ressource ?\",\"a\":[\"POST\",\"GET\",\"DELETE\"],\"r\":1},{\"q\":\"Quel code statut signifie Succès ?\",\"a\":[\"404\",\"500\",\"200\"],\"r\":2},{\"q\":\"Quel code statut signifie Page non trouvée ?\",\"a\":[\"404\",\"403\",\"302\"],\"r\":0},{\"q\":\"HTTP est-il un protocole avec ou sans état ?\",\"a\":[\"Avec état\",\"Sans état\",\"Hybride\"],\"r\":1},{\"q\":\"Quelle méthode modifie totalement une ressource ?\",\"a\":[\"GET\",\"PUT\",\"HEAD\"],\"r\":1},{\"q\":\"Que signifie le code 500 ?\",\"a\":[\"Erreur Serveur\",\"Redirection\",\"Interdit\"],\"r\":0},{\"q\":\"Quelle version majeure a introduit le multiplexage ?\",\"a\":[\"HTTP/1.1\",\"HTTP/2\",\"HTTP/1.0\"],\"r\":1},{\"q\":\"Quel en-tête définit le type de contenu ?\",\"a\":[\"Content-Type\",\"Accept-Language\",\"User-Agent\"],\"r\":0}]');
        ");
    }

    echo "<h3>Base de données PostgreSQL configurée avec succès ! 🎉</h3>";
} catch (PDOException $e) {
    die("Erreur lors de l'installation : " . $e->getMessage());
}
?>
