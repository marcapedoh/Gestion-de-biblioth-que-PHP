<?php
/**
 * views/emprunts/modifier.php
 * Permet de corriger les dates d'un emprunt existant.
 * (Le livre, l'étudiant et le stock ne sont pas modifiables ici pour éviter
 * les incohérences ; utiliser Retourner/Supprimer pour ces cas.)
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Emprunt.php';
require_once __DIR__ . '/../../models/Journal.php';

protegerPage();

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: liste.php');
    exit;
}

$emprunt = trouverEmpruntParId($id);

if (!$emprunt) {
    header('Location: liste.php');
    exit;
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dateEmprunt      = trim($_POST['date_emprunt'] ?? '');
    $dateRetourPrevue = trim($_POST['date_retour_prevue'] ?? '');

    if ($dateEmprunt === '' || !strtotime($dateEmprunt)) {
        $erreurs[] = "La date d'emprunt est invalide.";
    }
    if ($dateRetourPrevue === '' || !strtotime($dateRetourPrevue)) {
        $erreurs[] = "La date de retour prévue est invalide.";
    }

    if (count($erreurs) === 0) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("UPDATE emprunts SET date_emprunt = ?, date_retour_prevue = ? WHERE id = ?");
        $stmt->execute([$dateEmprunt, $dateRetourPrevue, $id]);

        enregistrerAction('Modification', 'Emprunts', 'Modification des dates de l\'emprunt du livre "' . $emprunt['titre_livre'] . '"');

        header('Location: liste.php?succes=modif');
        exit;
    }

    $emprunt = array_merge($emprunt, $_POST);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un emprunt - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../menu.php'; ?>

<div class="page-conteneur">
    <h1 class="page-titre h3"><i class="fa-solid fa-right-left"></i> Modifier un emprunt</h1>

    <div class="card carte formulaire-centre">
        <div class="card-body p-4">
            <?php if (count($erreurs) > 0): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($erreurs as $erreur): ?>
                            <li><?= htmlspecialchars($erreur) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="alert alert-light border mb-4">
                <strong><i class="fa-solid fa-book me-1"></i> Livre :</strong> <?= htmlspecialchars($emprunt['titre_livre']) ?><br>
                <strong><i class="fa-solid fa-user-graduate me-1"></i> Étudiant :</strong> <?= htmlspecialchars($emprunt['prenom_etudiant'] . ' ' . $emprunt['nom_etudiant']) ?>
            </div>

            <form method="post" action="modifier.php?id=<?= (int) $id ?>">
                <div class="mb-3">
                    <label for="date_emprunt" class="form-label">Date d'emprunt</label>
                    <input type="date" class="form-control" id="date_emprunt" name="date_emprunt" required
                           value="<?= htmlspecialchars($emprunt['date_emprunt']) ?>">
                </div>

                <div class="mb-3">
                    <label for="date_retour_prevue" class="form-label">Date de retour prévue</label>
                    <input type="date" class="form-control" id="date_retour_prevue" name="date_retour_prevue" required
                           value="<?= htmlspecialchars($emprunt['date_retour_prevue']) ?>">
                </div>

                <div class="d-flex gap-2 justify-content-center mt-4">
                    <button type="submit" class="btn btn-bib-accent btn-icone"><i class="fa-solid fa-floppy-disk"></i> Mettre à jour</button>
                    <a class="btn btn-secondary btn-icone" href="liste.php"><i class="fa-solid fa-xmark"></i> Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/scripts_communs.php'; ?>
</body>
</html>
