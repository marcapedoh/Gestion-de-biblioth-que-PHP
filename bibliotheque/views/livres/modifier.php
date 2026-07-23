<?php
/**
 * views/livres/modifier.php
 * Formulaire de modification d'un livre existant (catégorie + couverture) + traitement.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/LivreController.php';
require_once __DIR__ . '/../../models/Categorie.php';
require_once __DIR__ . '/../../models/Journal.php';

protegerPage();

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: liste.php');
    exit;
}

$livre = trouverLivreParId($id);

if (!$livre) {
    header('Location: liste.php');
    exit;
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$succes, $erreurs] = traiterModificationLivre($id, $_POST, $_FILES, $livre['couverture']);

    if ($succes) {
        enregistrerAction('Modification', 'Livres', 'Modification du livre "' . trim($_POST['titre']) . '"');
        header('Location: liste.php?succes=modif');
        exit;
    }

    // On garde les valeurs saisies pour ré-affichage en cas d'erreur
    $livre = array_merge($livre, $_POST);
}

$categories = listerToutesLesCategories();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un livre - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../menu.php'; ?>

<div class="page-conteneur">
    <h1 class="page-titre h3"><i class="fa-solid fa-book"></i> Modifier un livre</h1>

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

            <form method="post" action="modifier.php?id=<?= (int) $id ?>" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="titre" class="form-label">Titre</label>
                    <input type="text" class="form-control" id="titre" name="titre" required
                           value="<?= htmlspecialchars($livre['titre']) ?>">
                </div>

                <div class="mb-3">
                    <label for="auteur" class="form-label">Auteur</label>
                    <input type="text" class="form-control" id="auteur" name="auteur" required
                           value="<?= htmlspecialchars($livre['auteur']) ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="isbn" class="form-label">ISBN</label>
                        <input type="text" class="form-control" id="isbn" name="isbn"
                               value="<?= htmlspecialchars($livre['isbn']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="annee" class="form-label">Année</label>
                        <input type="number" class="form-control" id="annee" name="annee"
                               value="<?= htmlspecialchars((string) $livre['annee']) ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="quantite" class="form-label">Quantité disponible</label>
                        <input type="number" class="form-control" id="quantite" name="quantite" min="0"
                               value="<?= htmlspecialchars((string) $livre['quantite']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="categorie_id" class="form-label">Catégorie</label>
                        <select class="form-select" id="categorie_id" name="categorie_id">
                            <option value="">-- Aucune catégorie --</option>
                            <?php foreach ($categories as $categorie): ?>
                                <option value="<?= (int) $categorie['id'] ?>"
                                    <?= ((int) ($livre['categorie_id'] ?? 0) === (int) $categorie['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categorie['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Couverture (Bonus)</label>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <img class="couverture-apercu"
                             src="<?= $livre['couverture'] ? URL_COUVERTURES . htmlspecialchars($livre['couverture']) : BASE_URL . 'assets/img/couverture-defaut.svg' ?>"
                             alt="Couverture actuelle" style="width:80px;height:110px;">
                        <?php if ($livre['couverture']): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="supprimer_couverture" id="supprimer_couverture">
                                <label class="form-check-label small" for="supprimer_couverture">Supprimer la couverture actuelle</label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="zone-upload">
                        <i class="fa-solid fa-cloud-arrow-up text-muted mb-1"></i>
                        <input type="file" class="form-control" name="couverture" accept="image/png, image/jpeg, image/webp">
                        <div class="form-text">Laissez vide pour conserver la couverture actuelle.</div>
                    </div>
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
