<?php
// ===== auth.php =====
// Protège les pages internes + petites fonctions utilitaires partagées

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Utilisateur non connecté : retour à la page de connexion
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once 'database.php';

// Échappe un texte avant de l'afficher (protection contre l'injection HTML / XSS)
function e($texte) {
    return htmlspecialchars((string)$texte, ENT_QUOTES, 'UTF-8');
}

// Redirection simple (évite de renvoyer un formulaire quand on actualise la page)
function redirect($page) {
    header("Location: $page");
    exit();
}

// Mémorise un message pour l'afficher après la redirection
function message_flash($type, $texte) {
    $_SESSION['flash'] = ['type' => $type, 'texte' => $texte];
}

// Affiche le message mémorisé (succès ou erreur) et/ou une erreur de formulaire
function afficher_message($erreur = "") {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        if ($f['type'] === 'succes') {
            echo "<div id='successMessage' class='success-msg'>" . e($f['texte']) . "</div>";
        } else {
            echo "<div class='error-box'>" . e($f['texte']) . "</div>";
        }
    }
    if ($erreur !== "") {
        echo "<div class='error-box'>" . e($erreur) . "</div>";
    }
}

// Jeton CSRF : empêche l'envoi d'un formulaire depuis un autre site
function csrf_field() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return "<input type='hidden' name='csrf' value='" . $_SESSION['csrf'] . "'>";
}
function verifier_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        die("Requête refusée (jeton de sécurité invalide). Retournez à la page précédente et réessayez.");
    }
}

// Valide un montant : nombre positif, 2 décimales maximum. Retourne null si invalide.
function lire_montant($valeur) {
    $valeur = str_replace([' ', ','], ['', '.'], trim((string)$valeur));
    if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $valeur) || (float)$valeur <= 0) {
        return null;
    }
    return $valeur;
}

// Valide une date au format AAAA-MM-JJ. Retourne null si invalide.
function lire_date($valeur) {
    $d = DateTime::createFromFormat('Y-m-d', (string)$valeur);
    return ($d && $d->format('Y-m-d') === $valeur) ? $valeur : null;
}

// Nombre de caractères d'un texte UTF-8 (accents comptés pour 1)
function longueur($texte) {
    return preg_match_all('/./us', (string)$texte);
}

// Affiche un montant au format français
function montant($nombre) {
    return number_format((float)$nombre, 2, ',', ' ') . ' MAD';
}
?>


































































