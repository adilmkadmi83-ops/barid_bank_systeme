<?php
require 'auth.php';

// Chaque indicateur vient d'une requête indépendante : pas de jointure, donc pas de double comptage
$t_mand = $pdo->query("SELECT COUNT(*) AS c, COALESCE(SUM(montant), 0) AS s FROM mandats")->fetch();
$t_pay  = $pdo->query("SELECT COUNT(*) AS c, COALESCE(SUM(montant), 0) AS s FROM paiements")->fetch();
$t_rec  = $pdo->query("SELECT COUNT(*) FROM reclamations")->fetchColumn();
$rec_statuts = $pdo->query("SELECT statut, COUNT(*) AS c FROM reclamations GROUP BY statut")->fetchAll(PDO::FETCH_KEY_PAIR);
$in  = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM comptabilite WHERE type_operation = 'Entrée'")->fetchColumn();
$out = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM comptabilite WHERE type_operation = 'Sortie'")->fetchColumn();
$bilan = $in - $out;

include 'header.php';
?>
<section class="page active">
  <h1>Rapports Globaux</h1>
  <div class="form-box">
      <h3 style="color:#1a3c6d; border-bottom:2px solid #f9a01b; padding-bottom:10px;">Activité</h3>
      <ul style="line-height:2; list-style:none;">
          <li><strong>Mandats :</strong> <?= (int)$t_mand['c'] ?> (<?= montant($t_mand['s']) ?>)</li>
          <li><strong>Paiements :</strong> <?= (int)$t_pay['c'] ?> (<?= montant($t_pay['s']) ?>)</li>
          <li><strong>Réclamations :</strong> <?= (int)$t_rec ?>
              (En attente : <?= (int)($rec_statuts['En attente'] ?? 0) ?>,
               En cours : <?= (int)($rec_statuts['En cours'] ?? 0) ?>,
               Résolues : <?= (int)($rec_statuts['Résolue'] ?? 0) ?>)</li>
      </ul>
      <h3 style="color:#1a3c6d; border-bottom:2px solid #f9a01b; padding-bottom:10px; margin-top:20px;">Comptabilité</h3>
      <ul style="line-height:2; list-style:none;">
          <li class="text-green">Entrées : <?= montant($in) ?></li>
          <li class="text-red">Sorties : <?= montant($out) ?></li>
          <li class="<?= $bilan >= 0 ? 'text-blue' : 'text-red' ?>">Bilan (entrées − sorties) : <?= montant($bilan) ?></li>
      </ul>
      <p class="hint">Les montants des mandats et des paiements sont des opérations enregistrées dans l'application ; ils ne sont pas ajoutés automatiquement à la comptabilité.</p>
  </div>
</section>
</main></div><script src="script.js"></script></body></html>





































