<?php
// ===== login.php =====
session_start();
require 'database.php';

// Déjà connecté : direction le dashboard
if (isset($_SESSION["user"])) {
    header("Location: dashboard.php");
    exit();
}

$erreur = "";

// Si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    // Requête préparée : on cherche l'utilisateur par son identifiant uniquement
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    $ok = false;
    if ($user) {
        if (password_verify($password, $user['password'])) {
            $ok = true;
        } elseif (password_get_info($user['password'])['algo'] === null && hash_equals($user['password'], $password)) {
            // Ancien compte avec mot de passe en clair : on le vérifie puis on le convertit en hachage
            $ok = true;
            $maj = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $maj->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
        }
    }

    if ($ok) {
        // Succès : nouvelle session sécurisée
        session_regenerate_id(true);
        $_SESSION["user"] = $user['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        // Échec : message volontairement général
        $erreur = "Identifiant ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banque Barid Bank - Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-box">
        <div class="logo">📮 Banque Barid Bank</div>
        <h2>Connexion</h2>

        <?php if (!empty($erreur)): ?>
            <div class="error-msg" style="color: red; margin-bottom: 15px; text-align: center; font-weight: bold;">
                <?php echo htmlspecialchars($erreur); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn-login">Se connecter</button>
        </form>
        <p class="hint">Compte de démonstration (local) : admin / 1234</p>
    </div>
</body>
</html>






























