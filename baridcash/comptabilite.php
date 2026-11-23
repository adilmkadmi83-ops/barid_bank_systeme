<?php
require 'auth.php';

$types = ['Entrée', 'Sortie'];
$erreur = "";
$old = [];
$edit_id = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verifier_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action == 'supprimer') {
        $stmt = $pdo->prepare("DELETE FROM comptabilite WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) message_flash('succes', "Opération comptable supprimée avec succès.");
        else message_flash('erreur', "Opération introuvable.");
        redirect('comptabilite.php');
    }

    if ($action == 'add' || $action == 'modifier') {
        $old = $_POST;
        $type = $_POST['type'] ?? '';
        $desc = trim($_POST['desc'] ?? '');
        $mont = lire_montant($_POST['montant'] ?? '');
        $date = lire_date($_POST['date'] ?? '');

        if (!in_array($type, $types)) $erreur = "Le type doit être « Entrée » ou « Sortie ».";
        elseif ($desc === '') $erreur = "La description est obligatoire.";
        elseif (longueur($desc) > 255) $erreur = "La description ne doit pas dépasser 255 caractères.";
        elseif ($mont === null) $erreur = "Le montant doit être un nombre positif (exemple : 1200.00).";
        elseif ($date === null) $erreur = "La date est invalide.";

        if ($erreur === "") {
            if ($action == 'add') {
                $stmt = $pdo->prepare("INSERT INTO comptabilite (type_operation, description, montant, date_operation) VALUES (?, ?, ?, ?)");
                $stmt->execute([$type, $desc, $mont, $date]);
                message_flash('succes', "Opération comptable enregistrée avec succès.");
            } else {
                $stmt = $pdo->prepare("UPDATE comptabilite SET type_operation = ?, description = ?, montant = ?, date_operation = ? WHERE id = ?");
                $stmt->execute([$type, $desc, $mont, $date, $id]);
                message_flash('succes', "Opération comptable modifiée avec succès.");
            }
            redirect('comptabilite.php');
        }
        if ($action == 'modifier') $edit_id = $id;
    }
}

// Clic sur « Modifier »
if (isset($_GET['edit']) && $edit_id == 0) {
    $stmt = $pdo->prepare("SELECT * FROM comptabilite WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $o = $stmt->fetch();
    if ($o) {
        $edit_id = $o['id'];
        $old = ['type' => $o['type_operation'], 'desc' => $o['description'],
                'montant' => $o['montant'], 'date' => substr($o['date_operation'], 0, 10)];
    }
}

// Totaux calculés depuis MySQL : Solde = Entrées - Sorties
$entrees = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM comptabilite WHERE type_operation = 'Entrée'")->fetchColumn();
$sorties = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM comptabilite WHERE type_operation = 'Sortie'")->fetchColumn();
$solde = $entrees - $sorties;

$ops = $pdo->query("SELECT * FROM comptabilite ORDER BY date_operation DESC, id DESC")->fetchAll();
$form_ouvert = ($edit_id > 0 || $erreur !== "");

include 'header.php';
?>
<section class="page active">
  <h1>Comptabilité</h1>
  <?php afficher_message($erreur); ?>

  <div class="cards">
    <div class="card"><h3>Total des entrées</h3><span class="text-green"><?= montant($entrees) ?></span></div>
    <div class="card"><h3>Total des sorties</h3><span class="text-red"><?= montant($sorties) ?></span></div>
    <div class="card"><h3>Solde</h3><span class="<?= $solde >= 0 ? 'text-green' : 'text-red' ?>"><?= montant($solde) ?></span></div>
  </div>

  <button type="button" class="btn-action" id="btnFormulaire">+ Nouvelle opération</button>

  <div class="form-box" id="formBox" data-ouvert="<?= $form_ouvert ? '1' : '0' ?>">
      <form method="POST" action="comptabilite.php">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= $edit_id ? 'modifier' : 'add' ?>">
          <input type="hidden" name="id" value="<?= (int)$edit_id ?>">
          <h3><?= $edit_id ? "Modifier l'opération n° " . (int)$edit_id : "Ajouter une opération" ?></h3>

          <label for="type">Type</label>
          <select id="type" name="type" class="input-select">
              <?php foreach ($types as $t): ?>
              <option value="<?= e($t) ?>" <?= ($old['type'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
              <?php endforeach; ?>
          </select>

          <label for="desc">Description</label>
          <input type="text" id="desc" name="desc" class="input-text" maxlength="255" value="<?= e($old['desc'] ?? '') ?>" required>

          <label for="montant">Montant (MAD)</label>
          <input type="number" id="montant" step="0.01" min="0.01" name="montant" class="input-text" value="<?= e($old['montant'] ?? '') ?>" required>

          <label for="date">Date</label>
          <input type="date" id="date" name="date" class="input-text" value="<?= e($old['date'] ?? date('Y-m-d')) ?>" required>

          <button type="submit" class="btn-action"><?= $edit_id ? 'Enregistrer les modifications' : 'Ajouter' ?></button>
          <?php if ($edit_id): ?><a href="comptabilite.php" class="btn-lien">Annuler</a><?php endif; ?>
      </form>
  </div>

  <table class="table-simple">
      <thead>
      <tr><th>ID</th><th>Date</th><th>Type</th><th>Desc</th><th>Montant</th><th>Action</th></tr>
      </thead>
      <tbody>
      <?php if (!$ops): ?>
      <tr><td colspan="6" class="text-center">Aucune opération enregistrée.</td></tr>
      <?php endif; ?>
      <?php foreach ($ops as $op): ?>
      <tr>
          <td><?= (int)$op['id'] ?></td>
          <td><?= e(substr($op['date_operation'], 0, 10)) ?></td>
          <td class="<?= $op['type_operation'] == 'Entrée' ? 'text-green' : 'text-red' ?>"><?= e($op['type_operation']) ?></td>
          <td><?= e($op['description']) ?></td>
          <td class="text-right"><?= montant($op['montant']) ?></td>
          <td>
              <a href="?edit=<?= (int)$op['id'] ?>" class="text-blue">Modifier</a> |
              <form method="POST" action="comptabilite.php" class="form-suppr" data-confirm="Voulez-vous vraiment supprimer cette opération ?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="supprimer">
                  <input type="hidden" name="id" value="<?= (int)$op['id'] ?>">
                  <button type="submit" class="btn-lien text-red">Supprimer</button>
              </form>
          </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
  </table>
</section>
</main></div><script src="script.js"></script></body></html>











































