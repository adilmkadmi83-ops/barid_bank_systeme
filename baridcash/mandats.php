<?php
require 'auth.php';

$types = ['Mandat national', 'Mandat international', 'Organisme'];
$erreur = "";
$old = [];          // valeurs affichées dans le formulaire
$edit_id = 0;       // > 0 si on modifie un mandat

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verifier_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action == 'supprimer') {
        $stmt = $pdo->prepare("DELETE FROM mandats WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) message_flash('succes', "Mandat supprimé avec succès.");
        else message_flash('erreur', "Mandat introuvable.");
        redirect('mandats.php');
    }

    if ($action == 'ajouter' || $action == 'modifier') {
        $old = $_POST;
        $type = $_POST['type_mandat'] ?? '';
        $exp  = trim($_POST['expediteur'] ?? '');
        $ben  = trim($_POST['beneficiaire'] ?? '');
        $mont = lire_montant($_POST['montant'] ?? '');

        if (!in_array($type, $types)) $erreur = "Veuillez choisir un type de mandat valide.";
        elseif ($exp === '' || $ben === '') $erreur = "L'expéditeur et le bénéficiaire sont obligatoires.";
        elseif (longueur($exp) > 100 || longueur($ben) > 100) $erreur = "Les noms ne doivent pas dépasser 100 caractères.";
        elseif ($mont === null) $erreur = "Le montant doit être un nombre positif (exemple : 1500.50).";

        if ($erreur === "") {
            if ($action == 'ajouter') {
                $stmt = $pdo->prepare("INSERT INTO mandats (type_mandat, nom_expediteur, nom_beneficiaire, montant) VALUES (?, ?, ?, ?)");
                $stmt->execute([$type, $exp, $ben, $mont]);
                message_flash('succes', "Mandat ajouté avec succès.");
            } else {
                $stmt = $pdo->prepare("UPDATE mandats SET type_mandat = ?, nom_expediteur = ?, nom_beneficiaire = ?, montant = ? WHERE id = ?");
                $stmt->execute([$type, $exp, $ben, $mont, $id]);
                message_flash('succes', "Mandat modifié avec succès.");
            }
            redirect('mandats.php');
        }
        if ($action == 'modifier') $edit_id = $id;
    }
}

// Clic sur « Modifier » : on charge le mandat dans le formulaire
if (isset($_GET['edit']) && $edit_id == 0) {
    $stmt = $pdo->prepare("SELECT * FROM mandats WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $m = $stmt->fetch();
    if ($m) {
        $edit_id = $m['id'];
        $old = ['type_mandat' => $m['type_mandat'], 'expediteur' => $m['nom_expediteur'],
                'beneficiaire' => $m['nom_beneficiaire'], 'montant' => $m['montant']];
    }
}

// Recherche (type, expéditeur ou bénéficiaire)
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM mandats WHERE type_mandat LIKE ? OR nom_expediteur LIKE ? OR nom_beneficiaire LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$q%", "%$q%", "%$q%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM mandats ORDER BY id DESC");
}
$mandats = $stmt->fetchAll();
$form_ouvert = ($edit_id > 0 || $erreur !== "");

include 'header.php';
?>
<section class="page active">
  <h1>Mandats</h1>
  <?php afficher_message($erreur); ?>

  <button type="button" class="btn-action" id="btnFormulaire">+ Nouveau mandat</button>

  <div class="form-box" id="formBox" data-ouvert="<?= $form_ouvert ? '1' : '0' ?>">
      <form method="POST" action="mandats.php">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= $edit_id ? 'modifier' : 'ajouter' ?>">
          <input type="hidden" name="id" value="<?= (int)$edit_id ?>">
          <h3><?= $edit_id ? "Modifier le mandat n° " . (int)$edit_id : "Ajouter un mandat" ?></h3>

          <label for="type_mandat">Type</label>
          <select id="type_mandat" name="type_mandat" class="input-select" required>
              <option value="">-- Choisir --</option>
              <?php foreach ($types as $t): ?>
              <option value="<?= e($t) ?>" <?= ($old['type_mandat'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
              <?php endforeach; ?>
          </select>

          <label for="expediteur">Expéditeur</label>
          <input type="text" id="expediteur" name="expediteur" class="input-text" maxlength="100" value="<?= e($old['expediteur'] ?? '') ?>" required>

          <label for="beneficiaire">Bénéficiaire</label>
          <input type="text" id="beneficiaire" name="beneficiaire" class="input-text" maxlength="100" value="<?= e($old['beneficiaire'] ?? '') ?>" required>

          <label for="montant">Montant (MAD)</label>
          <input type="number" id="montant" step="0.01" min="0.01" name="montant" class="input-text" value="<?= e($old['montant'] ?? '') ?>" required>

          <button type="submit" class="btn-action"><?= $edit_id ? 'Enregistrer les modifications' : 'Ajouter' ?></button>
          <?php if ($edit_id): ?><a href="mandats.php" class="btn-lien">Annuler</a><?php endif; ?>
      </form>
  </div>

  <form method="GET" action="mandats.php" class="search-bar">
      <input type="text" name="q" class="input-text" placeholder="Rechercher (type, expéditeur, bénéficiaire)" value="<?= e($q) ?>">
      <button type="submit" class="btn-action">Rechercher</button>
      <?php if ($q !== ''): ?><a href="mandats.php" class="btn-lien">Effacer</a><?php endif; ?>
  </form>

  <table class="table-simple">
      <thead>
      <tr><th>ID</th><th>Type</th><th>Exp.</th><th>Bén.</th><th>Montant</th><th>Date</th><th>Action</th></tr>
      </thead>
      <tbody>
      <?php if (!$mandats): ?>
      <tr><td colspan="7" class="text-center">Aucun mandat enregistré.</td></tr>
      <?php endif; ?>
      <?php foreach ($mandats as $m): ?>
      <tr>
          <td><?= (int)$m['id'] ?></td>
          <td><?= e($m['type_mandat']) ?></td>
          <td><?= e($m['nom_expediteur']) ?></td>
          <td><?= e($m['nom_beneficiaire']) ?></td>
          <td class="text-right"><?= montant($m['montant']) ?></td>
          <td><?= e(substr($m['date_ajout'], 0, 10)) ?></td>
          <td>
              <a href="?edit=<?= (int)$m['id'] ?>" class="text-blue">Modifier</a> |
              <form method="POST" action="mandats.php" class="form-suppr" data-confirm="Voulez-vous vraiment supprimer ce mandat ?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="supprimer">
                  <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                  <button type="submit" class="btn-lien text-red">Supprimer</button>
              </form>
          </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
  </table>
</section>
</main></div><script src="script.js"></script></body></html>































