<?php
/**
 * views/etudiants/ajouter.php
 * Formulaire d'ajout d'un étudiant + traitement de la soumission.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/EtudiantController.php';
require_once __DIR__ . '/../../models/Journal.php';

protegerPage();

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$succes, $erreurs] = traiterAjoutEtudiant($_POST);

    if ($succes) {
        enregistrerAction('Ajout', 'Étudiants', 'Ajout de l\'étudiant ' . trim($_POST['prenom']) . ' ' . trim($_POST['nom']));
        header('Location: liste.php?succes=ajout');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un étudiant - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../menu.php'; ?>

<div class="page-conteneur">
    <h1 class="page-titre h3"><i class="fa-solid fa-user-graduate"></i> Ajouter un étudiant</h1>

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
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" required
                               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="prenom" class="form-label">Prénom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" required
                               value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="telephone" class="form-label">Téléphone</label>
                        <input type="text" class="form-control" id="telephone" name="telephone"
                               value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="filiere" class="form-label">Filière</label>
                        <input type="text" class="form-control" id="filiere" name="filiere"
                               value="<?= htmlspecialchars($_POST['filiere'] ?? '') ?>">
                    </div>
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
