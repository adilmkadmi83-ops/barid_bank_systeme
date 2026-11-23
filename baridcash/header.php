<?php
// ===== header.php =====
// Protection de la page (via auth.php), puis en-tête et menu communs
require_once 'auth.php';

$user = $_SESSION["user"];
// Déterminer la page actuelle pour mettre le menu en "active"
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Banque Barid Bank</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ===== EN-TETE ===== -->
  <header class="topbar">
    <div class="logo">📮 Banque Barid Bank</div>
   <div class="user-info">Bienvenue, <strong><?php echo htmlspecialchars($user); ?></strong>
      <a href="logout.php" class="btn-logout">Déconnexion</a>
    </div>
  </header>

  <div class="container">
    <!-- ===== MENU LATERAL ===== -->
    <nav class="sidebar">
      <ul id="menu">
        <li class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php" style="text-decoration:none; color:inherit; display:block;">🏠 Dashboard</a>
        </li>
        <li class="menu-item <?php echo $current_page == 'mandats.php' ? 'active' : ''; ?>">
            <a href="mandats.php" style="text-decoration:none; color:inherit; display:block;">📄 Mandats</a>
        </li>
        <li class="menu-item <?php echo $current_page == 'paiements.php' ? 'active' : ''; ?>">
            <a href="paiements.php" style="text-decoration:none; color:inherit; display:block;">💳 Barid Pay</a>
        </li>
        <li class="menu-item <?php echo $current_page == 'comptabilite.php' ? 'active' : ''; ?>">
            <a href="comptabilite.php" style="text-decoration:none; color:inherit; display:block;">📊 Comptabilité</a>
        </li>
        <li class="menu-item <?php echo $current_page == 'reclamations.php' ? 'active' : ''; ?>">
            <a href="reclamations.php" style="text-decoration:none; color:inherit; display:block;">⚠️ Réclamations</a>
        </li>
        <li class="menu-item <?php echo $current_page == 'rapports.php' ? 'active' : ''; ?>">
            <a href="rapports.php" style="text-decoration:none; color:inherit; display:block;">📈 Rapports</a>
        </li>
      </ul>
    </nav>

    <!-- ===== CONTENU PRINCIPAL ===== -->
    <main class="content">











































