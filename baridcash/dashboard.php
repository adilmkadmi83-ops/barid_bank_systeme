<?php
require 'auth.php';

// Indicateurs calculés à partir de MySQL (COALESCE : 0 si la table est vide)
$nb_mandats   = $pdo->query("SELECT COUNT(*) FROM mandats")->fetchColumn();
$nb_paiements = $pdo->query("SELECT COUNT(*) FROM paiements")->fetchColumn();
$nb_reclam    = $pdo->query("SELECT COUNT(*) FROM reclamations WHERE statut != 'Résolue'")->fetchColumn();
$tot_paiements = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM paiements")->fetchColumn();
$entrees = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM comptabilite WHERE type_operation = 'Entrée'")->fetchColumn();
$sorties = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM comptabilite WHERE type_operation = 'Sortie'")->fetchColumn();
$solde = $entrees - $sorties;

include 'header.php';
?>
<section class="page active">
  <h1>Dashboard</h1>
  <p>Vue générale de l'activité Banque Barid Bank.</p>
  <div class="cards">
    <div class="card"><h3>Mandats</h3><span class="text-blue"><?= (int)$nb_mandats ?></span></div>
    <div class="card"><h3>Paiements Barid Pay</h3><span class="text-blue"><?= (int)$nb_paiements ?></span></div>
    <div class="card"><h3>Réclamations en cours</h3><span class="text-red"><?= (int)$nb_reclam ?></span></div>
    <div class="card"><h3>Total Paiements</h3><span class="text-green"><?= montant($tot_paiements) ?></span></div>
  </div>
  <div class="cards">
    <div class="card"><h3>Total des entrées</h3><span class="text-green"><?= montant($entrees) ?></span></div>
    <div class="card"><h3>Total des sorties</h3><span class="text-red"><?= montant($sorties) ?></span></div>
    <div class="card"><h3>Solde comptable</h3><span class="<?= $solde >= 0 ? 'text-green' : 'text-red' ?>"><?= montant($solde) ?></span></div>
  </div>
</section>
</main></div><script src="script.js"></script></body></html>












































