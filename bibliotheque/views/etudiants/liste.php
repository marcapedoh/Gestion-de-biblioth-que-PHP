<?php
/**
 * views/etudiants/liste.php
 * Affiche la liste des étudiants avec recherche multicritère
 * (nom, prénom, email, filière), tri (Bonus) et pagination.
 * Exercice complémentaire 1 : recherche multicritère sur les étudiants.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Etudiant.php';

protegerPage();

$recherche = trim($_GET['recherche'] ?? '');
$page      = max(1, (int) ($_GET['page'] ?? 1));
$tri       = $_GET['tri'] ?? 'nom';
$ordre     = (strtoupper($_GET['ordre'] ?? 'ASC') === 'DESC') ? 'DESC' : 'ASC';

$etudiants      = listerEtudiants($recherche, $page, RESULTATS_PAR_PAGE, $tri, $ordre);
$totalEtudiants = compterEtudiants($recherche);
$totalPages     = max(1, (int) ceil($totalEtudiants / RESULTATS_PAR_PAGE));

$messageSucces = $_GET['succes'] ?? '';

function urlTriEtudiants(string $colonne, string $triActuel, string $ordreActuel, string $recherche): string
{
    $nouvelOrdre = ($triActuel === $colonne && $ordreActuel === 'ASC') ? 'DESC' : 'ASC';
    return 'liste.php?tri=' . $colonne . '&ordre=' . $nouvelOrdre . '&recherche=' . urlencode($recherche);
}

function icloneTriEtudiants(string $colonne, string $triActuel, string $ordreActuel): string
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
    <title>Étudiants - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../menu.php'; ?>

<div class="page-conteneur">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="page-titre h3 mb-0"><i class="fa-solid fa-user-graduate"></i> Gestion des étudiants</h1>
        <a class="btn btn-bib-accent btn-icone" href="ajouter.php">
            <i class="fa-solid fa-plus"></i> Ajouter un étudiant
        </a>
    </div>

    <?php if ($messageSucces === 'ajout'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i>Étudiant ajouté avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($messageSucces === 'modif'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i>Étudiant modifié avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($messageSucces === 'suppression'): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i>Étudiant supprimé avec succès.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <?php if (($_GET['erreur'] ?? '') === 'emprunts_lies'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-triangle-exclamation me-2"></i>Impossible de supprimer cet étudiant : des emprunts y sont liés.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="card carte">
        <div class="card-body">
            <form class="row g-2 align-items-center mb-3" method="get" action="liste.php">
                <input type="hidden" name="tri" value="<?= htmlspecialchars($tri) ?>">
                <input type="hidden" name="ordre" value="<?= htmlspecialchars($ordre) ?>">
                <div class="col-12 col-md">
                    <input type="text" class="form-control" name="recherche"
                           placeholder="Rechercher un étudiant (nom, prénom, email, filière)..."
                           value="<?= htmlspecialchars($recherche) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-bib-accent btn-icone"><i class="fa-solid fa-magnifying-glass"></i> Rechercher</button>
                </div>
                <div class="col-auto">
                    <a class="btn btn-outline-secondary btn-icone" href="liste.php"><i class="fa-solid fa-arrow-rotate-left"></i> Réinitialiser</a>
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
                                <a href="<?= urlTriEtudiants('nom', $tri, $ordre, $recherche) ?>">
                                    Nom <i class="<?= icloneTriEtudiants('nom', $tri, $ordre) ?>"></i>
                                </a>
                            </th>
                            <th class="entete-triable">
                                <a href="<?= urlTriEtudiants('prenom', $tri, $ordre, $recherche) ?>">
                                    Prénom <i class="<?= icloneTriEtudiants('prenom', $tri, $ordre) ?>"></i>
                                </a>
                            </th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th class="entete-triable">
                                <a href="<?= urlTriEtudiants('filiere', $tri, $ordre, $recherche) ?>">
                                    Filière <i class="<?= icloneTriEtudiants('filiere', $tri, $ordre) ?>"></i>
                                </a>
                            </th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($etudiants) === 0): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Aucun étudiant trouvé.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($etudiants as $etudiant): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($etudiant['nom']) ?></td>
                                <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($etudiant['email']) ?></td>
                                <td><?= htmlspecialchars($etudiant['telephone']) ?></td>
                                <td><span class="badge bg-bib-primary"><?= htmlspecialchars($etudiant['filiere']) ?></span></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary btn-icone" href="modifier.php?id=<?= (int) $etudiant['id'] ?>" title="Modifier">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a class="btn btn-sm btn-outline-danger btn-icone"
                                       href="supprimer.php?id=<?= (int) $etudiant['id'] ?>"
                                       onclick="return confirm('Confirmer la suppression de cet étudiant ?');" title="Supprimer">
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
