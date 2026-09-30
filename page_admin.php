<?php
session_start();

/* -------------------------------
   SÉCURITÉ : ACCÈS RÉSERVÉ ADMIN
--------------------------------*/

if (!isset($_SESSION['user'])) {
    header("Location: page_connexion.php.php");
    exit;
}

$role = strtolower(trim($_SESSION['user']['role']));

if ($role !== 'admin') {
    header("Location: page_connexion.php.php");
    exit;
}

/* -------------------------------
   CONNEXION À LA BASE
--------------------------------*/
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mabanque;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✔ TABLE correcte : utilisateur (sans S)
    // ✔ SUPPRESSION de la colonne tel (elle n'existe pas)
    $stmt = $pdo->query("SELECT id_utilisateur, nom, prenom, email, role FROM utilisateur ORDER BY nom ASC");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Espace Administrateur - Gestion des clients</title>

<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6fb;
        margin: 0;
        padding: 0;
    }

    header {
        background: #003366;
        color: white;
        padding: 20px;
        text-align: center;
        font-size: 1.4rem;
        font-weight: bold;
    }

    .container {
        width: 90%;
        margin: 30px auto;
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    th, td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    th {
        background: #003366;
        color: white;
    }

    .btn {
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        color: white;
        font-size: 0.9rem;
    }

    .btn-view { background: #007bff; }
    .btn-edit { background: #28a745; }
    .btn-delete { background: #dc3545; }

    .btn:hover {
        opacity: 0.8;
    }
</style>
</head>

<body>

<header>
    👨‍💼 Espace Administrateur
</header>

<div class="container">

    <h2>Liste des utilisateurs</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($clients as $client): ?>
        <tr>
            <td><?= htmlspecialchars($client['id_utilisateur']) ?></td>
            <td><?= htmlspecialchars($client['nom']) ?></td>
            <td><?= htmlspecialchars($client['prenom']) ?></td>
            <td><?= htmlspecialchars($client['email']) ?></td>
            <td><?= htmlspecialchars($client['role']) ?></td>

            <td>
                <a class="btn btn-view" href="voir_compte.php?id=<?= $client['id_utilisateur'] ?>">Voir</a>
                <a class="btn btn-edit" href="modifier_compte.php?id=<?= $client['id_utilisateur'] ?>">Modifier</a>
                <a class="btn btn-delete" href="supprimer_compte.php?id=<?= $client['id_utilisateur'] ?>" onclick="return confirm('Supprimer ce client ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>

</div>

</body>
</html>
