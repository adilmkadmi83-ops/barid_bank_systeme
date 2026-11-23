<?php
require 'auth.php';

$erreur = "";
$old = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verifier_csrf();
    $action = $_POST['action'] ?? '';

    if ($action == 'supprimer') {
        $stmt = $pdo->prepare("DELETE FROM paiements WHERE id = ?");
        $stmt->execute([(int)($_POST['id'] ?? 0)]);
        if ($stmt->rowCount() > 0) message_flash('succes', "Paiement supprimé avec succès.");
        else message_flash('erreur', "Paiement introuvable.");
        redirect('paiements.php');
    }

    if ($action == 'payer') {
        $old = $_POST;
        $client = trim($_POST['client'] ?? '');
        $ref    = trim($_POST['numero'] ?? '');
        $mont   = lire_montant($_POST['montant'] ?? '');

        if ($client === '' || $ref === '') $erreur = "Le client et la référence sont obligatoires.";
        elseif (longueur($client) > 100 || longueur($ref) > 50) $erreur = "Client (100 caractères max) ou référence (50 max) trop long.";
        elseif ($mont === null) $erreur = "Le montant doit être un nombre positif (exemple : 250.00).";

        if ($erreur === "") {
            $stmt = $pdo->prepare("INSERT INTO paiements (nom_client, numero_client, montant) VALUES (?, ?, ?)");
            $stmt->execute([$client, $ref, $mont]);
            message_flash('succes', "Paiement enregistré avec succès.");
            redirect('paiements.php');
        }
    }
}

// Recherche (client ou référence)
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM paiements WHERE nom_client LIKE ? OR numero_client LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$q%", "%$q%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM paiements ORDER BY id DESC");
}
$paiements = $stmt->fetchAll();

include 'header.php';
?>
<section class="page active">
  <h1>Barid Pay</h1>
  <?php afficher_message($erreur); ?>

  <button type="button" class="btn-action" id="btnFormulaire">Effectuer un paiement</button>

  <div class="form-box" id="formBox" data-ouvert="<?= $erreur !== '' ? '1' : '0' ?>">
      <form method="POST" action="paiements.php">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="payer">
          <h3>Nouveau paiement</h3>

          <label for="client">Client</label>
          <input type="text" id="client" name="client" class="input-text" maxlength="100" value="<?= e($old['client'] ?? '') ?>" required>

          <label for="numero">Référence</label>
          <input type="text" id="numero" name="numero" class="input-text" maxlength="50" value="<?= e($old['numero'] ?? '') ?>" required>

          <label for="montant">Montant (MAD)</label>
          <input type="number" id="montant" step="0.01" min="0.01" name="montant" class="input-text" value="<?= e($old['montant'] ?? '') ?>" required>

          <button type="submit" class="btn-action">Payer</button>
      </form>
  </div>

  <form method="GET" action="paiements.php" class="search-bar">
      <input type="text" name="q" class="input-text" placeholder="Rechercher (client, référence)" value="<?= e($q) ?>">
      <button type="submit" class="btn-action">Rechercher</button>
      <?php if ($q !== ''): ?><a href="paiements.php" class="btn-lien">Effacer</a><?php endif; ?>
  </form>

  <table class="table-simple">
      <thead>
      <tr><th>ID</th><th>Client</th><th>Ref</th><th>Montant</th><th>Date</th><th>Action</th></tr>
      </thead>
      <tbody>
      <?php if (!$paiements): ?>
      <tr><td colspan="6" class="text-center">Aucun paiement enregistré.</td></tr>
      <?php endif; ?>
      <?php foreach ($paiements as $p): ?>
      <tr>
          <td><?= (int)$p['id'] ?></td>
          <td><?= e($p['nom_client']) ?></td>
          <td><?= e($p['numero_client']) ?></td>
          <td class="text-right"><?= montant($p['montant']) ?></td>
          <td><?= e(substr($p['date_paiement'], 0, 10)) ?></td>
          <td>
              <form method="POST" action="paiements.php" class="form-suppr" data-confirm="Voulez-vous vraiment supprimer ce paiement ?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="supprimer">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button type="submit" class="btn-lien text-red">Supprimer</button>
              </form>
          </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
  </table>
</section>
</main></div><script src="script.js"></script></body></html>



















































