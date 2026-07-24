<?php
/**
 * views/livres/disponibles.php
 * Exercice complémentaire 2 : affiche uniquement les livres disponibles
 * (quantité disponible > 0).
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Livre.php';

protegerPage();

$livres = listerLivresDisponibles();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Livres disponibles - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../menu.php'; ?>

<div class="page-conteneur">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="page-titre h3 mb-0"><i class="fa-solid fa-check-circle"></i> Livres disponibles</h1>
        <a class="btn btn-outline-secondary btn-icone" href="liste.php"><i class="fa-solid fa-arrow-left"></i> Retour à la liste complète</a>
    </div>

    <div class="card carte">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Couverture</th>
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>ISBN</th>
                            <th>Année</th>
                            <th>Quantité</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($livres) === 0): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Aucun livre disponible pour le moment.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($livres as $livre): ?>
                            <tr>
                                <td>
                                    <img class="couverture-miniature"
                                         src="<?= $livre['couverture'] ? URL_COUVERTURES . htmlspecialchars($livre['couverture']) : BASE_URL . 'assets/img/couverture-defaut.svg' ?>"
                                         alt="Couverture">
                                </td>
                                <td class="fw-semibold"><?= htmlspecialchars($livre['titre']) ?></td>
                                <td><?= htmlspecialchars($livre['auteur']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($livre['isbn']) ?></td>
                                <td><?= htmlspecialchars((string) $livre['annee']) ?></td>
                                <td><span class="badge badge-retourne"><?= (int) $livre['quantite'] ?></span></td>
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
