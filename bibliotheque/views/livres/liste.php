<?php
/**
 * views/livres/liste.php
 * Affiche la liste des livres avec recherche multicritère, tri (Bonus)
 * et pagination.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Livre.php';

protegerPage();

$recherche = trim($_GET['recherche'] ?? '');
$page      = max(1, (int) ($_GET['page'] ?? 1));
$tri       = $_GET['tri'] ?? 'titre';
$ordre     = (strtoupper($_GET['ordre'] ?? 'ASC') === 'DESC') ? 'DESC' : 'ASC';

$livres      = listerLivres($recherche, $page, RESULTATS_PAR_PAGE, $tri, $ordre);
$totalLivres = compterLivres($recherche);
$totalPages  = max(1, (int) ceil($totalLivres / RESULTATS_PAR_PAGE));

$messageSucces = $_GET['succes'] ?? '';

/**
 * Construit l'URL de tri pour un en-tête de colonne, en inversant le sens
 * si la colonne est déjà celle utilisée pour trier (Bonus : tri des listes).
 */
function urlTriLivres(string $colonne, string $triActuel, string $ordreActuel, string $recherche): string
{
    $nouvelOrdre = ($triActuel === $colonne && $ordreActuel === 'ASC') ? 'DESC' : 'ASC';
    return 'liste.php?tri=' . $colonne . '&ordre=' . $nouvelOrdre . '&recherche=' . urlencode($recherche);
}

function icloneTri(string $colonne, string $triActuel, string $ordreActuel): string
{
    if ($triActuel !== $colonne) {
        return 'fa-solid fa-sort';
    }
    return $ordreActuel === 'ASC' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Livres - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../menu.php'; ?>

<div class="page-conteneur">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="page-titre h3 mb-0"><i class="fa-solid fa-book"></i> Gestion des livres</h1>
        <a class="btn btn-bib-accent btn-icone" href="ajouter.php">
            <i class="fa-solid fa-plus"></i> Ajouter un livre
        </a>
    </div>

    <?php if ($messageSucces === 'ajout'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i>Livre ajouté avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($messageSucces === 'modif'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i>Livre modifié avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($messageSucces === 'suppression'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i>Livre supprimé avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <?php if (($_GET['erreur'] ?? '') === 'emprunts_lies'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-triangle-exclamation me-2"></i>Impossible de supprimer ce livre : des emprunts y sont liés.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="card carte">
        <div class="card-body">
            <form class="row g-2 align-items-center mb-3" method="get" action="liste.php">
                <input type="hidden" name="tri" value="<?= htmlspecialchars($tri) ?>">
                <input type="hidden" name="ordre" value="<?= htmlspecialchars($ordre) ?>">
                <div class="col-12 col-md">
                    <input type="text" class="form-control" name="recherche" placeholder="Rechercher un livre (titre, auteur, ISBN)..."
                           value="<?= htmlspecialchars($recherche) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-bib-accent btn-icone"><i class="fa-solid fa-magnifying-glass"></i> Rechercher</button>
                </div>
                <div class="col-auto">
                    <a class="btn btn-outline-secondary btn-icone" href="liste.php"><i class="fa-solid fa-arrow-rotate-left"></i> Réinitialiser</a>
                </div>
                <div class="col-auto">
                    <a class="btn btn-outline-secondary btn-icone" href="disponibles.php"><i class="fa-solid fa-check"></i> Disponibles</a>
                </div>
                <div class="col-auto ms-md-auto">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary btn-icone dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-file-export"></i> Exporter
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="export_excel.php?recherche=<?= urlencode($recherche) ?>&tri=<?= $tri ?>&ordre=<?= $ordre ?>"><i class="fa-solid fa-file-excel text-success me-2"></i>Excel (.xls)</a></li>
                            <li><a class="dropdown-item" target="_blank" href="export_pdf.php?recherche=<?= urlencode($recherche) ?>&tri=<?= $tri ?>&ordre=<?= $ordre ?>"><i class="fa-solid fa-file-pdf text-danger me-2"></i>PDF</a></li>
                        </ul>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Couverture</th>
                            <th class="entete-triable">
                                <a href="<?= urlTriLivres('titre', $tri, $ordre, $recherche) ?>">
                                    Titre <i class="<?= icloneTri('titre', $tri, $ordre) ?>"></i>
                                </a>
                            </th>
                            <th class="entete-triable">
                                <a href="<?= urlTriLivres('auteur', $tri, $ordre, $recherche) ?>">
                                    Auteur <i class="<?= icloneTri('auteur', $tri, $ordre) ?>"></i>
                                </a>
                            </th>
                            <th>Catégorie</th>
                            <th>ISBN</th>
                            <th class="entete-triable">
                                <a href="<?= urlTriLivres('date', $tri, $ordre, $recherche) ?>">
                                    Année <i class="<?= icloneTri('date', $tri, $ordre) ?>"></i>
                                </a>
                            </th>
                            <th class="entete-triable">
                                <a href="<?= urlTriLivres('quantite', $tri, $ordre, $recherche) ?>">
                                    Quantité <i class="<?= icloneTri('quantite', $tri, $ordre) ?>"></i>
                                </a>
                            </th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($livres) === 0): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">Aucun livre trouvé.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($livres as $livre): ?>
                            <tr>
                                <td>
                                    <img class="couverture-miniature"
                                         src="<?= $livre['couverture'] ? URL_COUVERTURES . htmlspecialchars($livre['couverture']) : BASE_URL . 'assets/img/couverture-defaut.svg' ?>"
                                         alt="Couverture de <?= htmlspecialchars($livre['titre']) ?>">
                                </td>
                                <td class="fw-semibold"><?= htmlspecialchars($livre['titre']) ?></td>
                                <td><?= htmlspecialchars($livre['auteur']) ?></td>
                                <td>
                                    <?php if ($livre['nom_categorie']): ?>
                                        <span class="badge bg-bib-primary"><?= htmlspecialchars($livre['nom_categorie']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars($livre['isbn']) ?></td>
                                <td><?= htmlspecialchars((string) $livre['annee']) ?></td>
                                <td>
                                    <?php if ((int) $livre['quantite'] > 0): ?>
                                        <span class="badge badge-retourne"><?= (int) $livre['quantite'] ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-retard">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary btn-icone" href="modifier.php?id=<?= (int) $livre['id'] ?>" title="Modifier">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a class="btn btn-sm btn-outline-danger btn-icone"
                                       href="supprimer.php?id=<?= (int) $livre['id'] ?>"
                                       onclick="return confirm('Confirmer la suppression de ce livre ?');" title="Supprimer">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <nav class="mt-3">
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                            <a class="page-link" href="liste.php?page=<?= $p ?>&recherche=<?= urlencode($recherche) ?>&tri=<?= $tri ?>&ordre=<?= $ordre ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/scripts_communs.php'; ?>
</body>
</html>
