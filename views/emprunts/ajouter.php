<?php
/**
 * views/emprunts/ajouter.php
 * Formulaire d'enregistrement d'un emprunt + traitement.
 * Exercice complémentaire 5 : refuse l'emprunt si quantité disponible = 0
 * (vérifié côté modèle, dans enregistrerEmprunt()).
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/EmpruntController.php';
require_once __DIR__ . '/../../models/Livre.php';
require_once __DIR__ . '/../../models/Etudiant.php';
require_once __DIR__ . '/../../models/Journal.php';

protegerPage();

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$succes, $erreurs] = traiterAjoutEmprunt($_POST);

    if ($succes) {
        $livre    = trouverLivreParId((int) $_POST['id_livre']);
        $etudiant = trouverEtudiantParId((int) $_POST['id_etudiant']);
        enregistrerAction('Emprunt', 'Emprunts', 'Emprunt du livre "' . ($livre['titre'] ?? '') . '" par ' . ($etudiant['prenom'] ?? '') . ' ' . ($etudiant['nom'] ?? ''));
        header('Location: liste.php?succes=ajout');
        exit;
    }
}

// Seuls les livres disponibles peuvent être proposés au prêt
$livresDisponibles = listerLivresDisponibles();
$etudiants         = listerTousLesEtudiants();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvel emprunt - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../menu.php'; ?>

<div class="page-conteneur">
    <h1 class="page-titre h3"><i class="fa-solid fa-right-left"></i> Enregistrer un emprunt</h1>

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

            <?php if (count($livresDisponibles) === 0): ?>
                <div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-2"></i>Aucun livre disponible actuellement pour un emprunt.</div>
            <?php endif; ?>

            <form method="post" action="ajouter.php">
                <div class="mb-3">
                    <label for="id_livre" class="form-label">Livre</label>
                    <select class="form-select" id="id_livre" name="id_livre" required>
                        <option value="">-- Choisir un livre --</option>
                        <?php foreach ($livresDisponibles as $livre): ?>
                            <option value="<?= (int) $livre['id'] ?>">
                                <?= htmlspecialchars($livre['titre']) ?> (<?= (int) $livre['quantite'] ?> dispo.)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="id_etudiant" class="form-label">Étudiant</label>
                    <select class="form-select" id="id_etudiant" name="id_etudiant" required>
                        <option value="">-- Choisir un étudiant --</option>
                        <?php foreach ($etudiants as $etudiant): ?>
                            <option value="<?= (int) $etudiant['id'] ?>">
                                <?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date_emprunt" class="form-label">Date d'emprunt</label>
                        <input type="date" class="form-control" id="date_emprunt" name="date_emprunt" required
                               value="<?= htmlspecialchars($_POST['date_emprunt'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="date_retour_prevue" class="form-label">Date de retour prévue</label>
                        <input type="date" class="form-control" id="date_retour_prevue" name="date_retour_prevue" required
                               value="<?= htmlspecialchars($_POST['date_retour_prevue'] ?? date('Y-m-d', strtotime('+14 days'))) ?>">
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-center mt-4">
                    <button type="submit" class="btn btn-bib-accent btn-icone"><i class="fa-solid fa-floppy-disk"></i> Enregistrer l'emprunt</button>
                    <a class="btn btn-secondary btn-icone" href="liste.php"><i class="fa-solid fa-xmark"></i> Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/scripts_communs.php'; ?>
</body>
</html>
