<?php
/**
 * views/auth/connexion.php
 * Formulaire de connexion. Cette vue est incluse par login.php,
 * qui a déjà défini $erreur si besoin. 
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Bibliothèque</title>
    <?php require __DIR__ . '/../partials/head_libs.php'; ?>
</head>
<body>
<div class="page-connexion">
    <div class="card carte-connexion">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="fa-solid fa-book-open-reader fa-2x text-success mb-2"></i>
                <h1 class="h4 fw-semibold mb-0">Bibliothèque Universitaire</h1>
                <p class="text-muted small">Connectez-vous pour accéder au dashboard</p>
            </div>

            <?php if (!empty($erreur)): ?>
                <div class="alert alert-danger py-2"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($erreur) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="mot_de_passe" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-bib-accent w-100 btn-icone">
                    <i class="fa-solid fa-right-to-bracket"></i> Se connecter
                </button>
            </form>

            <!-- <p class="text-center text-muted small mt-4 mb-0">
                Démo : admin@bibliotheque.com / admin123
            </p> -->
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/scripts_communs.php'; ?>
</body>
</html>
