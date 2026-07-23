<?php
/**
 * views/categories/ajouter.php
 * Formulaire d'ajout d'une catégorie + traitement de la soumission.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/CategorieController.php';
require_once __DIR__ . '/../../models/Journal.php';

protegerPage();

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$succes, $erreurs] = traiterAjoutCategorie($_POST);

    if ($succes) {
        enregistrerAction('Ajout', 'Catégories', 'Ajout de la catégorie "' . trim($_POST['nom']) . '"');
        header('Location: liste.php?succes=ajout');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une catégorie - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../menu.php'; ?>

<div class="page-conteneur">
    <h1 class="page-titre h3"><i class="fa-solid fa-tag"></i> Ajouter une catégorie</h1>

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

            <form method="post" action="ajouter.php">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom de la catégorie</label>
                    <input type="text" class="form-control" id="nom" name="nom" required
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
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
