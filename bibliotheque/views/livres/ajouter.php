<?php
/**
 * views/livres/ajouter.php
 * Formulaire d'ajout d'un livre (catégorie + couverture) + traitement.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/LivreController.php';
require_once __DIR__ . '/../../models/Categorie.php';
require_once __DIR__ . '/../../models/Journal.php';

protegerPage();

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$succes, $erreurs] = traiterAjoutLivre($_POST, $_FILES);

    if ($succes) {
        enregistrerAction('Ajout', 'Livres', 'Ajout du livre "' . trim($_POST['titre']) . '"');
        header('Location: liste.php?succes=ajout');
        exit;
    }
}

$categories = listerToutesLesCategories();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un livre - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../menu.php'; ?>

<div class="page-conteneur">
    <h1 class="page-titre h3"><i class="fa-solid fa-book"></i> Ajouter un livre</h1>

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

            <form method="post" action="ajouter.php" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="titre" class="form-label">Titre</label>
                    <input type="text" class="form-control" id="titre" name="titre" required
                           value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="auteur" class="form-label">Auteur</label>
                    <input type="text" class="form-control" id="auteur" name="auteur" required
                           value="<?= htmlspecialchars($_POST['auteur'] ?? '') ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="isbn" class="form-label">ISBN</label>
                        <input type="text" class="form-control" id="isbn" name="isbn"
                               value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="annee" class="form-label">Année</label>
                        <input type="number" class="form-control" id="annee" name="annee"
                               value="<?= htmlspecialchars($_POST['annee'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="quantite" class="form-label">Quantité disponible</label>
                        <input type="number" class="form-control" id="quantite" name="quantite" min="0"
                               value="<?= htmlspecialchars($_POST['quantite'] ?? '0') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="categorie_id" class="form-label">Catégorie</label>
                        <select class="form-select" id="categorie_id" name="categorie_id">
                            <option value="">-- Aucune catégorie --</option>
                            <?php foreach ($categories as $categorie): ?>
                                <option value="<?= (int) $categorie['id'] ?>"
                                    <?= (($_POST['categorie_id'] ?? '') == $categorie['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categorie['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Couverture (Bonus)</label>
                    <div class="zone-upload">
                        <i class="fa-solid fa-cloud-arrow-up fa-2x text-muted mb-2"></i>
                        <input type="file" class="form-control" name="couverture" accept="image/png, image/jpeg, image/webp">
                        <div class="form-text">Formats acceptés : JPG, PNG, WEBP — 2 Mo maximum.</div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-center mt-4">
                    <button type="submit" class="btn btn-bib-accent btn-icone"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
                    <a class="btn btn-secondary btn-icone" href="liste.php"><i class="fa-solid fa-xmark"></i> Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/scripts_communs.php'; ?>
</body>
</html>
