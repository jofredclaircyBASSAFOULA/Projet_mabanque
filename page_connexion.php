<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Connexion à la base
$pdo = new PDO("mysql:host=localhost;dbname=mabanque;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // ✔ Table correcte : utilisateur
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✔ Mot de passe en clair
    if ($user && $mot_de_passe === $user['mot_de_passe']) {

        $_SESSION['user'] = [
            "id" => $user["id_utilisateur"],
            "email" => $user["email"],
            "role" => strtolower(trim($user["role"])),
            "nom" => $user["nom"],
            "prenom" => $user["prenom"],
            "tel" => $user["tel"]
        ];

        if ($_SESSION['user']['role'] === 'admin') {
            header("Location: page_admin.php.php");
        } else {
            header("Location: page_client.php.php");
        }
        exit();

    } else {
        $message = "Email ou mot de passe incorrect";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 320px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #4facfe;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #007bff;
        }

        a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>

<body>

<div class="container">
    <h2>Connexion</h2>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>

        <button type="submit">Se connecter</button>
    </form>

    <p class="error"><?php echo htmlspecialchars($message); ?></p>

    <p>
        Pas de compte ?
        <a href="page_inscription.php.php">S'inscrire</a>
    </p>
</div>

</body>
</html>
