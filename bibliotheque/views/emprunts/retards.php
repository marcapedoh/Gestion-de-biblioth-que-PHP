<?php
/**
 * views/emprunts/retards.php
 * Exercice complémentaire 3 : affiche la liste des emprunts en retard
 * (en cours, dont la date de retour prévue est dépassée).
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Emprunt.php';

protegerPage();

$emprunts = listerEmpruntsEnRetard();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emprunts en retard - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../menu.php'; ?>

<div class="page-conteneur">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="page-titre h3 mb-0"><i class="fa-solid fa-triangle-exclamation text-danger"></i> Emprunts en retard</h1>
        <a class="btn btn-outline-secondary btn-icone" href="liste.php"><i class="fa-solid fa-arrow-left"></i> Retour à la liste complète</a>
    </div>

    <div class="card carte">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Livre</th>
                            <th>Étudiant</th>
                            <th>Date emprunt</th>
                            <th>Retour prévu</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($emprunts) === 0): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4"><i class="fa-solid fa-circle-check text-success me-1"></i>Aucun emprunt en retard.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($emprunts as $emprunt): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($emprunt['titre_livre']) ?></td>
                                <td><?= htmlspecialchars($emprunt['prenom_etudiant'] . ' ' . $emprunt['nom_etudiant']) ?></td>
                                <td><?= htmlspecialchars($emprunt['date_emprunt']) ?></td>
                                <td><span class="badge badge-retard"><?= htmlspecialchars($emprunt['date_retour_prevue']) ?></span></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-success btn-icone"
                                       href="retourner.php?id=<?= (int) $emprunt['id'] ?>"
                                       onclick="return confirm('Confirmer le retour de ce livre ?');" title="Retourner">
                                        <i class="fa-solid fa-rotate-left"></i> Retourner
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
