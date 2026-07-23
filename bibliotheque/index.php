<?php
/**
 * index.php
 * Page d'accueil (tableau de bord). Page protégée : accessible uniquement
 * aux utilisateurs connectés.
 * Bonus : tableau de bord enrichi avec des graphiques (Chart.js).
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/Livre.php';
require_once __DIR__ . '/models/Etudiant.php';
require_once __DIR__ . '/models/Emprunt.php';
require_once __DIR__ . '/models/Categorie.php';

protegerPage();

// Statistiques générales
$nbLivres        = compterLivres('');
$nbCategories    = compterCategories();
$nbEtudiants     = compterEtudiants('');
$nbEmpruntsCours = compterLivresEmpruntesEnCours();
$nbEnRetard      = count(listerEmpruntsEnRetard());
$nbEmpruntsTotal = compterTousLesEmprunts();

// Données pour les graphiques
$statutEmprunts     = statistiquesEmpruntsParStatut();
$livresParCategorie = statistiquesLivresParCategorie();
$empruntsParMois    = statistiquesEmpruntsParMois();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Bibliothèque</title>
    <?php require __DIR__ . '/views/partials/head_libs.php'; ?>
</head>
<body>
<?php require __DIR__ . '/views/menu.php'; ?>

<div class="page-conteneur">
    <h1 class="page-titre h3">Bienvenue, <?= htmlspecialchars($_SESSION['utilisateur_nom']) ?></h1>
    <p class="text-muted mb-4">Voici un aperçu de l'activité de la bibliothèque universitaire.</p>

    <!-- Cartes de statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card stat-carte bg-bib-primary">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-valeur"><?= $nbLivres ?></div>
                        <div>Livres au catalogue</div>
                    </div>
                    <i class="fa-solid fa-book stat-icone"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-carte bg-bib-accent">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-valeur"><?= $nbEtudiants ?></div>
                        <div>Étudiants inscrits</div>
                    </div>
                    <i class="fa-solid fa-user-graduate stat-icone"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-carte bg-bib-warning">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-valeur"><?= $nbEmpruntsCours ?></div>
                        <div>Emprunts en cours</div>
                    </div>
                    <i class="fa-solid fa-right-left stat-icone"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-carte bg-bib-danger">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-valeur"><?= $nbEnRetard ?></div>
                        <div>Emprunts en retard</div>
                    </div>
                    <i class="fa-solid fa-triangle-exclamation stat-icone"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card carte h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-3"><i class="fa-solid fa-chart-pie me-2"></i>Répartition des emprunts</h2>
                    <canvas id="graphiqueStatutEmprunts" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card carte h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Livres par catégorie</h2>
                    <canvas id="graphiqueLivresCategorie" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card carte">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-3"><i class="fa-solid fa-chart-line me-2"></i>Emprunts enregistrés par mois (6 derniers mois)</h2>
                    <canvas id="graphiqueEmpruntsMois" height="90"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Accès rapide -->
    <div class="card carte">
        <div class="card-body">
            <h2 class="h6 text-muted mb-3">Accès rapide</h2>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-bib-accent btn-icone" href="views/livres/liste.php"><i class="fa-solid fa-book"></i> Gérer les livres</a>
                <a class="btn btn-outline-secondary btn-icone" href="views/categories/liste.php"><i class="fa-solid fa-tags"></i> Gérer les catégories</a>
                <a class="btn btn-outline-secondary btn-icone" href="views/etudiants/liste.php"><i class="fa-solid fa-user-graduate"></i> Gérer les étudiants</a>
                <a class="btn btn-outline-secondary btn-icone" href="views/emprunts/liste.php"><i class="fa-solid fa-right-left"></i> Gérer les emprunts</a>
                <a class="btn btn-outline-secondary btn-icone" href="views/journal/liste.php"><i class="fa-solid fa-clock-rotate-left"></i> Voir le journal</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/views/partials/scripts_communs.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    // Répartition des emprunts par statut (doughnut)
    new Chart(document.getElementById('graphiqueStatutEmprunts'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($statutEmprunts), JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{
                data: <?= json_encode(array_values($statutEmprunts)) ?>,
                backgroundColor: ['#ef6c00', '#c62828', '#2e7d32']
            }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    // Livres par catégorie (barres)
    new Chart(document.getElementById('graphiqueLivresCategorie'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($livresParCategorie, 'categorie'), JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{
                label: 'Nombre de livres',
                data: <?= json_encode(array_map('intval', array_column($livresParCategorie, 'total'))) ?>,
                backgroundColor: '#1f2d3d'
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    // Emprunts par mois (ligne)
    new Chart(document.getElementById('graphiqueEmpruntsMois'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($empruntsParMois, 'mois')) ?>,
            datasets: [{
                label: 'Emprunts',
                data: <?= json_encode(array_map('intval', array_column($empruntsParMois, 'total'))) ?>,
                borderColor: '#2e7d32',
                backgroundColor: 'rgba(46, 125, 50, 0.15)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
</script>
</body>
</html>
