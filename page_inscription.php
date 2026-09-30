<?php
session_start();

//  Connexion à la base de données
$host = "localhost";
$dbname = "mabanque";
$user = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur connexion BDD : " . $e->getMessage());
}

// Variables messages
$erreur = "";
$success = "";

//  Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $tel = htmlspecialchars($_POST['telephone']);
    $email = htmlspecialchars($_POST['email']);
    $role = $_POST['role'];

    // Hash du mot de passe
    $mdp = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO utilisateur (nom, prenom, tel, email, role, mot_de_passe)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $prenom, $tel, $email, $role, $mdp]);

        $success = "Inscription réussie dans M@Banque !";

    } catch (PDOException $e) {
        $erreur = "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Inscription - M@Banque</title>

<style>
    body {
        margin: 0;
        padding: 0;
        font-family: "Segoe UI", Arial, sans-serif;
        background: linear-gradient(135deg, #0a2a43, #0e4d92);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .container {
        background: white;
        padding: 35px;
        width: 450px;
        border-radius: 18px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        animation: fadeIn 0.6s ease-in-out;
        border-top: 6px solid #0e4d92;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    h2 {
        text-align: center;
        color: #0e4d92;
        font-size: 26px;
        margin-bottom: 25px;
    }

    label {
        font-weight: bold;
        color: #0a2a43;
        font-size: 15px;
    }

    input, select {
        width: 100%;
        padding: 12px;
        margin-top: 6px;
        margin-bottom: 18px;
        border-radius: 8px;
        border: 1px solid #b5c7d3;
        font-size: 15px;
        transition: 0.2s;
    }

    input:focus, select:focus {
        border-color: #0e4d92;
        outline: none;
        box-shadow: 0 0 6px rgba(14,77,146,0.4);
    }

    input[type="submit"] {
        background: #0e4d92;
        color: white;
        cursor: pointer;
        font-weight: bold;
        border: none;
        padding: 14px;
        border-radius: 8px;
        font-size: 16px;
        transition: 0.3s;
    }

    input[type="submit"]:hover {
        background: #0a2a43;
        transform: scale(1.03);
    }

    .message {
        text-align: center;
        font-weight: bold;
        margin-bottom: 15px;
        font-size: 15px;
    }

    .success { color: #1e7e34; }
    .error { color: #d9534f; }

</style>
</head>

<body>

<div class="container">
    <h2>Formulaire d'inscription</h2>

    <!-- Messages -->
    <?php if (!empty($erreur)) echo "<p class='message error'>$erreur</p>"; ?>
    <?php if (!empty($success)) echo "<p class='message success'>$success</p>"; ?>

    <form method="POST">

        <label>Nom :</label>
        <input type="text" name="nom" required>

        <label>Prénom :</label>
        <input type="text" name="prenom" required>

        <label>Téléphone :</label>
        <input type="text" name="telephone" required>

        <label>Email :</label>
        <input type="email" name="email" required>

        <label>Mot de passe :</label>
        <input type="password" name="mot_de_passe" required>

        <label>Rôle :</label>
        <select name="role">
            <option value="client">Client</option>
            <option value="admin">Admin</option>
        </select>

        <input type="submit" value="S'inscrire">
    </form>
</div>

</body>
</html>
