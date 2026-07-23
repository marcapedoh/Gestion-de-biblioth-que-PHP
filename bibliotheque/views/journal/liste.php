<?php
/**
 * views/journal/liste.php
 * Bonus : Journalisation des actions (ajout, modification, suppression...).
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Journal.php';

protegerPage();

$page = max(1, (int) ($_GET['page'] ?? 1));

$entrees    = listerJournal($page, RESULTATS_PAR_PAGE);
$total      = compterJournal();
$totalPages = max(1, (int) ceil($total / RESULTATS_PAR_PAGE));

/**
 * Associe une icône et une couleur Bootstrap à chaque type d'action.
 */
function styleAction(string $action): array
{
    switch ($action) {
        case 'Ajout':
            return ['fa-solid fa-circle-plus', 'text-success'];
        case 'Modification':
            return ['fa-solid fa-pen', 'text-warning'];
        case 'Suppression':
            return ['fa-solid fa-trash', 'text-danger'];
        case 'Connexion':
            return ['fa-solid fa-right-to-bracket', 'text-primary'];
        case 'Déconnexion':
            return ['fa-solid fa-right-from-bracket', 'text-secondary'];
        case 'Emprunt':
            return ['fa-solid fa-hand-holding', 'text-info'];
        case 'Retour':
            return ['fa-solid fa-rotate-left', 'text-success'];
        default:
            return ['fa-solid fa-circle-info', 'text-muted'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Journal des actions - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../menu.php'; ?>

<div class="page-conteneur">
    <h1 class="page-titre h3"><i class="fa-solid fa-clock-rotate-left"></i> Journal des actions</h1>
    <p class="text-muted">Historique des ajouts, modifications et suppressions effectués dans l'application.</p>

    <div class="card carte">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Utilisateur</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($entrees) === 0): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Aucune action enregistrée pour le moment.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($entrees as $entree): ?>
                            <?php [$icone, $couleur] = styleAction($entree['action']); ?>
                            <tr>
                                <td class="text-nowrap"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($entree['date_action']))) ?></td>
                                <td><?= htmlspecialchars($entree['utilisateur_nom'] ?? 'Système') ?></td>
                                <td class="<?= $couleur ?>"><i class="<?= $icone ?> me-1"></i><?= htmlspecialchars($entree['action']) ?></td>
                                <td><span class="badge bg-bib-primary"><?= htmlspecialchars($entree['module']) ?></span></td>
                                <td class="text-muted"><?= htmlspecialchars($entree['description']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <nav class="mt-3">
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                            <a class="page-link" href="liste.php?page=<?= $p ?>"><?= $p ?></a>
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
