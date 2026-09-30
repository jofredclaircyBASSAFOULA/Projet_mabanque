<?php
session_start();

/* -------------------------------
   SÉCURITÉ : ACCÈS RÉSERVÉ CLIENT
--------------------------------*/
if (!isset($_SESSION['user'])) {
    header("Location: page_connexion.php.php");
    exit;
}

$role = strtolower(trim($_SESSION['user']['role']));
if ($role !== 'client') {
    header("Location: page_connexion.php.php");
    exit;
}

/* -------------------------------
   DÉCONNEXION
--------------------------------*/
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: page_connexion.php.php");
    exit;
}

/* -------------------------------
   CONNEXION À LA BASE
--------------------------------*/
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mabanque;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id_client = $_SESSION['user']['id'];

    /* -------------------------------
       RÉCUPÉRATION DU COMPTE CLIENT
    --------------------------------*/
    $stmt = $pdo->prepare("SELECT * FROM comptes WHERE code_utilisateur = ?");
    $stmt->execute([$id_client]);
    $compte = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$compte) {
        die("Aucun compte trouvé pour ce client.");
    }

    $id_compte = $compte['id_compte'];

    /* -------------------------------
       RÉCUPÉRATION DES TRANSACTIONS
    --------------------------------*/
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE code_compte = ? ORDER BY id_transaction DESC");
    $stmt->execute([$id_compte]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* -------------------------------
       TRAITEMENT DU VIREMENT
    --------------------------------*/
    $message = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_GET['confidentialite'])) {
        $compte_dest = $_POST['compte_dest'];
        $montant = floatval($_POST['montant']);

        if ($montant <= 0 || $montant > $compte['solde']) {
            $message = "Montant invalide ou solde insuffisant.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM comptes WHERE num_compte = ?");
            $stmt->execute([$compte_dest]);
            $dest = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dest) {
                $message = "Compte destinataire introuvable.";
            } else {
                $pdo->prepare("UPDATE comptes SET solde = solde - ? WHERE id_compte = ?")
                    ->execute([$montant, $id_compte]);

                $pdo->prepare("UPDATE comptes SET solde = solde + ? WHERE id_compte = ?")
                    ->execute([$montant, $dest['id_compte']]);

                $pdo->prepare("INSERT INTO transactions(operation, montant, code_compte) VALUES('Virement envoyé', ?, ?)")
                    ->execute([$montant, $id_compte]);

                $pdo->prepare("INSERT INTO transactions(operation, montant, code_compte) VALUES('Virement reçu', ?, ?)")
                    ->execute([$montant, $dest['id_compte']]);

                $message = "Virement effectué avec succès !";
                header("Refresh:1");
            }
        }
    }

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Espace Client</title>

<style>
    body {
        font-family: Arial, sans-serif;
        background: #e9f2ff;
        margin: 0;
        padding: 0;
    }

    header {
        background: #0055a5;
        color: white;
        padding: 20px;
        font-size: 1.4rem;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    header a {
        color: white;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 6px;
        margin-left: 10px;
    }

    .btn-red { background: #c62828; }
    .btn-blue { background: #007bff; }

    .container {
        width: 90%;
        margin: 20px auto;
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    h2 { color: #0055a5; }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    th, td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
    }

    th {
        background: #0055a5;
        color: white;
    }

    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }

    input, button {
        padding: 10px;
        width: 100%;
        margin-top: 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    button {
        background: #0055a5;
        color: white;
        cursor: pointer;
        font-weight: bold;
    }
</style>
</head>

<body>

<header>
    💳 Espace Client — Bonjour <?= htmlspecialchars($_SESSION['user']['prenom']) ?>
    <div>
        <a href="?confidentialite=1" class="btn-blue">Confidentialité</a>
        <a href="?logout=1" class="btn-red">Déconnexion</a>
    </div>
</header>

<?php if (!isset($_GET['confidentialite'])): ?>

<!-- ============================
     SECTION COMPTE
============================= -->
<div class="container">
    <h2>Mon compte</h2>
    <p><strong>Numéro de compte :</strong> <?= $compte['num_compte'] ?></p>
    <p><strong>Solde :</strong> <?= number_format($compte['solde'], 2, ',', ' ') ?> €</p>
</div>

<!-- ============================
     SECTION VIREMENT
============================= -->
<div class="container">
    <h2>Faire un virement</h2>

    <?php if ($message): ?>
        <p class="<?= strpos($message, 'succès') !== false ? 'success' : 'error' ?>">
            <?= $message ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <label>Compte destinataire :</label>
        <input type="text" name="compte_dest" required>

        <label>Montant (€) :</label>
        <input type="number" step="0.01" name="montant" required>

        <button type="submit">Envoyer</button>
    </form>
</div>

<!-- ============================
     SECTION TRANSACTIONS
============================= -->
<div class="container">
    <h2>Mes transactions</h2>

    <table>
        <tr>
            <th>Opération</th>
            <th>Montant</th>
        </tr>

        <?php foreach ($transactions as $t): ?>
        <tr>
            <td><?= htmlspecialchars($t['operation']) ?></td>
            <td><?= htmlspecialchars($t['montant']) ?> €</td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php else: ?>

<!-- ============================
     SECTION CONFIDENTIALITÉ
============================= -->
<div class="container">
    <h2>Politique de Confidentialité</h2>

    <p><strong>M@Banque</strong> protège vos données personnelles conformément au RGPD.</p>

    <h3>1. Données collectées</h3>
    <ul>
        <li>Nom, prénom</li>
        <li>Email</li>
        <li>Téléphone</li>
        <li>Numéro de compte et solde</li>
        <li>Transactions</li>
    </ul>

    <h3>2. Utilisation des données</h3>
    <ul>
        <li>Connexion sécurisée</li>
        <li>Affichage du compte</li>
        <li>Virements</li>
        <li>Gestion des utilisateurs (admin)</li>
    </ul>

    <h3>3. Sécurité</h3>
    <p>Vos données sont protégées et ne sont jamais partagées avec des tiers.</p>

    <h3>4. Vos droits</h3>
    <ul>
        <li>Droit d’accès</li>
        <li>Droit de rectification</li>
        <li>Droit à l’effacement</li>
        <li>Droit d’opposition</li>
    </ul>

    <a href="page_admin.php.php" class="btn-blue">Fermer</a>
</div>

<?php endif; ?>

</body>
</html>
