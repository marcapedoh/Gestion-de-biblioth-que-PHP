<?php
/**
 * views/menu.php
 * Menu de navigation commun, inclus dans toutes les pages protégées.
 * Bonus : navbar Bootstrap responsive avec icônes Font Awesome.
 * Attend que BASE_URL soit déjà définie (via config.php).
 */

$scriptCourant = $_SERVER['SCRIPT_NAME'] ?? '';

function lienActif(string $scriptCourant, string $motif): string
{
    return (strpos($scriptCourant, $motif) !== false) ? 'active' : '';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand fw-semibold" href="<?= BASE_URL ?>index.php">
            <i class="fa-solid fa-book-open-reader"></i>Bibliothèque Universitaire
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navPrincipal">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= lienActif($scriptCourant, '/index.php') ?>" href="<?= BASE_URL ?>index.php">
                        <i class="fa-solid fa-gauge-high"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= lienActif($scriptCourant, '/views/livres/') ?>" href="<?= BASE_URL ?>views/livres/liste.php">
                        <i class="fa-solid fa-book"></i> Livres
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= lienActif($scriptCourant, '/views/categories/') ?>" href="<?= BASE_URL ?>views/categories/liste.php">
                        <i class="fa-solid fa-tags"></i> Catégories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= lienActif($scriptCourant, '/views/etudiants/') ?>" href="<?= BASE_URL ?>views/etudiants/liste.php">
                        <i class="fa-solid fa-user-graduate"></i> Étudiants
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= lienActif($scriptCourant, '/views/emprunts/') ?>" href="<?= BASE_URL ?>views/emprunts/liste.php">
                        <i class="fa-solid fa-right-left"></i> Emprunts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= lienActif($scriptCourant, '/views/journal/') ?>" href="<?= BASE_URL ?>views/journal/liste.php">
                        <i class="fa-solid fa-clock-rotate-left"></i> Journal
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav align-items-lg-center gap-lg-2">
                <li class="nav-item theme-switch dropdown">
                    <button class="theme-switch-btn dropdown-toggle" type="button" id="themeSwitchBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Changer de thème">
                        <span class="swatch"></span>
                        <span class="d-none d-md-inline">Thème</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end theme-menu" id="themeMenu" aria-labelledby="themeSwitchBtn">
                        <!-- Options injectées dynamiquement par assets/js/theme.js -->
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-circle-user me-1"></i>
                        <?= htmlspecialchars($_SESSION['utilisateur_nom'] ?? '') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>Déconnexion
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
