<?php
/**
 * views/categories/liste.php
 * Bonus : Gestion des catégories de livres.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Categorie.php';

protegerPage();

$categories = listerCategoriesAvecNbLivres();
$messageSucces = $_GET['succes'] ?? '';
$messageErreur = $_GET['erreur'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catégories - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../menu.php'; ?>

<div class="page-conteneur">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="page-titre h3 mb-0"><i class="fa-solid fa-tags"></i> Gestion des catégories</h1>
        <a class="btn btn-bib-accent btn-icone" href="ajouter.php">
            <i class="fa-solid fa-plus"></i> Ajouter une catégorie
        </a>
    </div>

    <?php if ($messageSucces === 'ajout'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i>Catégorie ajoutée avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($messageSucces === 'modif'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i>Catégorie modifiée avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($messageSucces === 'suppression'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i>Catégorie supprimée avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <?php if ($messageErreur === 'existe'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-triangle-exclamation me-2"></i>Cette catégorie existe déjà.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="card carte">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Description</th>
                            <th class="text-center">Nombre de livres</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($categories) === 0): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Aucune catégorie pour le moment.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($categories as $categorie): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($categorie['nom']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($categorie['description'] ?? '') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-bib-primary"><?= (int) $categorie['nb_livres'] ?></span>
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary btn-icone" href="modifier.php?id=<?= (int) $categorie['id'] ?>" title="Modifier">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a class="btn btn-sm btn-outline-danger btn-icone" href="supprimer.php?id=<?= (int) $categorie['id'] ?>"
                                       onclick="return confirm('Confirmer la suppression de cette catégorie ? Les livres associés ne seront pas supprimés.');" title="Supprimer">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/scripts_communs.php'; ?>
</body>
</html>
