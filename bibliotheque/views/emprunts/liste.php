<?php
/**
 * views/emprunts/liste.php
 * Affiche la liste des emprunts avec recherche, tri (Bonus) et pagination.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Emprunt.php';

protegerPage();

$recherche = trim($_GET['recherche'] ?? '');
$page      = max(1, (int) ($_GET['page'] ?? 1));
$tri       = $_GET['tri'] ?? 'date';
$ordre     = (strtoupper($_GET['ordre'] ?? 'DESC') === 'ASC') ? 'ASC' : 'DESC';

$emprunts      = listerEmprunts($recherche, $page, RESULTATS_PAR_PAGE, $tri, $ordre);
$totalEmprunts = compterEmprunts($recherche);
$totalPages    = max(1, (int) ceil($totalEmprunts / RESULTATS_PAR_PAGE));

$messageSucces = $_GET['succes'] ?? '';

/**
 * Détermine si un emprunt en cours est en retard (date de retour prévue dépassée).
 */
function estEnRetard(array $emprunt): bool
{
    return $emprunt['statut'] === 'En cours' && strtotime($emprunt['date_retour_prevue']) < strtotime(date('Y-m-d'));
}

function urlTriEmprunts(string $colonne, string $triActuel, string $ordreActuel, string $recherche): string
{
    $nouvelOrdre = ($triActuel === $colonne && $ordreActuel === 'DESC') ? 'ASC' : 'DESC';
    return 'liste.php?tri=' . $colonne . '&ordre=' . $nouvelOrdre . '&recherche=' . urlencode($recherche);
}

function icloneTriEmprunts(string $colonne, string $triActuel, string $ordreActuel): string
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
    <title>Emprunts - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../menu.php'; ?>

<div class="page-conteneur">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="page-titre h3 mb-0"><i class="fa-solid fa-right-left"></i> Gestion des emprunts</h1>
        <a class="btn btn-bib-accent btn-icone" href="ajouter.php">
            <i class="fa-solid fa-plus"></i> Nouvel emprunt
        </a>
    </div>

    <?php if ($messageSucces === 'ajout'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i>Emprunt enregistré avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($messageSucces === 'retour'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i>Retour enregistré avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($messageSucces === 'suppression'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i>Emprunt supprimé avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="card carte">
        <div class="card-body">
            <form class="row g-2 align-items-center mb-3" method="get" action="liste.php">
                <input type="hidden" name="tri" value="<?= htmlspecialchars($tri) ?>">
                <input type="hidden" name="ordre" value="<?= htmlspecialchars($ordre) ?>">
                <div class="col-12 col-md">
                    <input type="text" class="form-control" name="recherche" placeholder="Rechercher (livre, nom ou prénom de l'étudiant)..."
                           value="<?= htmlspecialchars($recherche) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-bib-accent btn-icone"><i class="fa-solid fa-magnifying-glass"></i> Rechercher</button>
                </div>
                <div class="col-auto">
                    <a class="btn btn-outline-secondary btn-icone" href="liste.php"><i class="fa-solid fa-arrow-rotate-left"></i> Réinitialiser</a>
                </div>
                <div class="col-auto">
                    <a class="btn btn-outline-danger btn-icone" href="retards.php"><i class="fa-solid fa-triangle-exclamation"></i> En retard</a>
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
                            <th class="entete-triable">
                                <a href="<?= urlTriEmprunts('titre', $tri, $ordre, $recherche) ?>">
                                    Livre <i class="<?= icloneTriEmprunts('titre', $tri, $ordre) ?>"></i>
                                </a>
                            </th>
                            <th>Étudiant</th>
                            <th class="entete-triable">
                                <a href="<?= urlTriEmprunts('date', $tri, $ordre, $recherche) ?>">
                                    Date emprunt <i class="<?= icloneTriEmprunts('date', $tri, $ordre) ?>"></i>
                                </a>
                            </th>
                            <th class="entete-triable">
                                <a href="<?= urlTriEmprunts('retour', $tri, $ordre, $recherche) ?>">
                                    Retour prévu <i class="<?= icloneTriEmprunts('retour', $tri, $ordre) ?>"></i>
                                </a>
                            </th>
                            <th>Retour effectif</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($emprunts) === 0): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">Aucun emprunt trouvé.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($emprunts as $emprunt): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($emprunt['titre_livre']) ?></td>
                                <td><?= htmlspecialchars($emprunt['prenom_etudiant'] . ' ' . $emprunt['nom_etudiant']) ?></td>
                                <td><?= htmlspecialchars($emprunt['date_emprunt']) ?></td>
                                <td><?= htmlspecialchars($emprunt['date_retour_prevue']) ?></td>
                                <td><?= htmlspecialchars($emprunt['date_retour'] ?? '-') ?></td>
                                <td>
                                    <?php if ($emprunt['statut'] === 'Retourné'): ?>
                                        <span class="badge badge-retourne"><i class="fa-solid fa-check me-1"></i>Retourné</span>
                                    <?php elseif (estEnRetard($emprunt)): ?>
                                        <span class="badge badge-retard"><i class="fa-solid fa-triangle-exclamation me-1"></i>En retard</span>
                                    <?php else: ?>
                                        <span class="badge badge-en-cours"><i class="fa-solid fa-clock me-1"></i>En cours</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($emprunt['statut'] === 'En cours'): ?>
                                        <a class="btn btn-sm btn-outline-success btn-icone"
                                           href="retourner.php?id=<?= (int) $emprunt['id'] ?>"
                                           onclick="return confirm('Confirmer le retour de ce livre ?');" title="Retourner">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </a>
                                        <a class="btn btn-sm btn-outline-primary btn-icone" href="modifier.php?id=<?= (int) $emprunt['id'] ?>" title="Modifier">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a class="btn btn-sm btn-outline-danger btn-icone"
                                       href="supprimer.php?id=<?= (int) $emprunt['id'] ?>"
                                       onclick="return confirm('Confirmer la suppression de cet emprunt ?');" title="Supprimer">
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
