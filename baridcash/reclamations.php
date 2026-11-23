<?php
require 'auth.php';

$statuts = ['En attente', 'En cours', 'Résolue'];
$erreur = "";
$old = [];
$edit_id = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verifier_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action == 'supprimer') {
        $stmt = $pdo->prepare("DELETE FROM reclamations WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) message_flash('succes', "Réclamation supprimée avec succès.");
        else message_flash('erreur', "Réclamation introuvable.");
        redirect('reclamations.php');
    }

    // Changement rapide de statut depuis le tableau
    if ($action == 'statut') {
        $statut = $_POST['statut'] ?? '';
        if (in_array($statut, $statuts)) {
            $stmt = $pdo->prepare("UPDATE reclamations SET statut = ? WHERE id = ?");
            $stmt->execute([$statut, $id]);
            message_flash('succes', "Statut de la réclamation modifié avec succès.");
        } else {
            message_flash('erreur', "Statut invalide.");
        }
        redirect('reclamations.php');
    }

    if ($action == 'add' || $action == 'modifier') {
        $old = $_POST;
        $client = trim($_POST['client'] ?? '');
        $sujet  = trim($_POST['sujet'] ?? '');
        $desc   = trim($_POST['desc'] ?? '');
        $statut = $_POST['statut'] ?? 'En attente';

        if ($client === '' || $sujet === '' || $desc === '') $erreur = "Le client, le sujet et la description sont obligatoires.";
        elseif (longueur($client) > 100 || longueur($sujet) > 200) $erreur = "Client (100 caractères max) ou sujet (200 max) trop long.";
        elseif (!in_array($statut, $statuts)) $erreur = "Statut invalide.";

        if ($erreur === "") {
            if ($action == 'add') {
                $stmt = $pdo->prepare("INSERT INTO reclamations (nom_client, sujet, description) VALUES (?, ?, ?)");
                $stmt->execute([$client, $sujet, $desc]);
                message_flash('succes', "Réclamation ajoutée avec succès.");
            } else {
                $stmt = $pdo->prepare("UPDATE reclamations SET nom_client = ?, sujet = ?, description = ?, statut = ? WHERE id = ?");
                $stmt->execute([$client, $sujet, $desc, $statut, $id]);
                message_flash('succes', "Réclamation modifiée avec succès.");
            }
            redirect('reclamations.php');
        }
        if ($action == 'modifier') $edit_id = $id;
    }
}

// Clic sur « Modifier »
if (isset($_GET['edit']) && $edit_id == 0) {
    $stmt = $pdo->prepare("SELECT * FROM reclamations WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $r = $stmt->fetch();
    if ($r) {
        $edit_id = $r['id'];
        $old = ['client' => $r['nom_client'], 'sujet' => $r['sujet'], 'desc' => $r['description'], 'statut' => $r['statut']];
    }
}

// Recherche (client ou sujet)
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM reclamations WHERE nom_client LIKE ? OR sujet LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$q%", "%$q%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM reclamations ORDER BY id DESC");
}
$reclamations = $stmt->fetchAll();
$form_ouvert = ($edit_id > 0 || $erreur !== "");

// Couleur du badge selon le statut
function classe_badge($statut) {
    if ($statut == 'Résolue') return 'badge-green';
    if ($statut == 'En cours') return 'badge-blue';
    return 'badge-orange';
}

include 'header.php';
?>
<section class="page active">
  <h1>Réclamations</h1>
  <?php afficher_message($erreur); ?>

  <button type="button" class="btn-action" id="btnFormulaire">+ Nouvelle réclamation</button>

  <div class="form-box" id="formBox" data-ouvert="<?= $form_ouvert ? '1' : '0' ?>">
      <form method="POST" action="reclamations.php">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= $edit_id ? 'modifier' : 'add' ?>">
          <input type="hidden" name="id" value="<?= (int)$edit_id ?>">
          <h3><?= $edit_id ? "Modifier la réclamation n° " . (int)$edit_id : "Ajouter une réclamation" ?></h3>

          <label for="client">Client</label>
          <input type="text" id="client" name="client" class="input-text" maxlength="100" value="<?= e($old['client'] ?? '') ?>" required>

          <label for="sujet">Sujet</label>
          <input type="text" id="sujet" name="sujet" class="input-text" maxlength="200" value="<?= e($old['sujet'] ?? '') ?>" required>

          <label for="desc">Description</label>
          <textarea id="desc" name="desc" class="input-textarea" required><?= e($old['desc'] ?? '') ?></textarea>

          <?php if ($edit_id): ?>
          <label for="statut">Statut</label>
          <select id="statut" name="statut" class="input-select">
              <?php foreach ($statuts as $s): ?>
              <option value="<?= e($s) ?>" <?= ($old['statut'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
              <?php endforeach; ?>
          </select>
          <?php endif; ?>

          <button type="submit" class="btn-action"><?= $edit_id ? 'Enregistrer les modifications' : 'Ajouter' ?></button>
          <?php if ($edit_id): ?><a href="reclamations.php" class="btn-lien">Annuler</a><?php endif; ?>
      </form>
  </div>

  <form method="GET" action="reclamations.php" class="search-bar">
      <input type="text" name="q" class="input-text" placeholder="Rechercher (client, sujet)" value="<?= e($q) ?>">
      <button type="submit" class="btn-action">Rechercher</button>
      <?php if ($q !== ''): ?><a href="reclamations.php" class="btn-lien">Effacer</a><?php endif; ?>
  </form>

  <table class="table-simple">
      <thead>
      <tr><th>ID</th><th>Client</th><th>Sujet</th><th>Date</th><th>Statut</th><th>Action</th></tr>
      </thead>
      <tbody>
      <?php if (!$reclamations): ?>
      <tr><td colspan="6" class="text-center">Aucune réclamation enregistrée.</td></tr>
      <?php endif; ?>
      <?php foreach ($reclamations as $r): ?>
      <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= e($r['nom_client']) ?></td>
          <td><?= e($r['sujet']) ?></td>
          <td><?= e(substr($r['date_reclamation'], 0, 10)) ?></td>
          <td><span class="badge <?= classe_badge($r['statut']) ?>"><?= e($r['statut']) ?></span></td>
          <td>
              <form method="POST" action="reclamations.php" class="form-inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="statut">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <select name="statut" class="input-select-petit" aria-label="Nouveau statut">
                      <?php foreach ($statuts as $s): ?>
                      <option value="<?= e($s) ?>" <?= $r['statut'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                      <?php endforeach; ?>
                  </select>
                  <button type="submit" class="btn-lien text-green">OK</button>
              </form> |
              <a href="?edit=<?= (int)$r['id'] ?>" class="text-blue">Modifier</a> |
              <form method="POST" action="reclamations.php" class="form-suppr" data-confirm="Voulez-vous vraiment supprimer cette réclamation ?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="supprimer">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button type="submit" class="btn-lien text-red">Supprimer</button>
              </form>
          </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
  </table>
</section>
</main></div><script src="script.js"></script></body></html>
































