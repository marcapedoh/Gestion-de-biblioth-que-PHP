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
    <div class="row g-3 mb-4 align-items-stretch">
        <div class="col-lg-5">
            <div class="card carte chart-card h-100">
                <div class="card-body">
                    <h2 class="chart-titre"><i class="fa-solid fa-chart-pie"></i>Répartition des emprunts</h2>
                    <div class="chart-zone chart-zone-md">
                        <canvas id="graphiqueStatutEmprunts"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card carte chart-card h-100">
                <div class="card-body">
                    <h2 class="chart-titre"><i class="fa-solid fa-chart-bar"></i>Livres par catégorie</h2>
                    <div class="chart-zone chart-zone-md">
                        <canvas id="graphiqueLivresCategorie"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card carte chart-card">
                <div class="card-body">
                    <h2 class="chart-titre"><i class="fa-solid fa-chart-line"></i>Emprunts enregistrés par mois (6 derniers mois)</h2>
                    <div class="chart-zone chart-zone-wide">
                        <canvas id="graphiqueEmpruntsMois"></canvas>
                    </div>
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
(function () {
    "use strict";

    var donneesStatutEmprunts = <?= json_encode($statutEmprunts, JSON_UNESCAPED_UNICODE) ?>;
    var donneesLivresCategorie = <?= json_encode($livresParCategorie, JSON_UNESCAPED_UNICODE) ?>;
    var donneesEmpruntsMois = <?= json_encode($empruntsParMois, JSON_UNESCAPED_UNICODE) ?>;

    /* Palettes assorties à chaque thème visuel (or / émeraude / bordeaux / saphir). */
    var PALETTES = {
        emeraude: { or: '#d4af37', orClair: '#ecd280', vert: '#2f8f5f', vertClair: '#4bb884', ambre: '#c8912b', rouge: '#b3273d', grille: 'rgba(243,239,228,0.10)', texte: 'rgba(243,239,228,0.72)', fond: '#0f2015', bordure: 'rgba(212,175,55,0.35)' },
        ivoire:   { or: '#a8801f', orClair: '#c9a227', vert: '#7a1b2e', vertClair: '#9c2c42', ambre: '#a8801f', rouge: '#7a1b2e', grille: 'rgba(43,28,20,0.10)', texte: 'rgba(43,28,20,0.72)', fond: '#fbf8f0', bordure: 'rgba(122,27,46,0.30)' },
        saphir:   { or: '#d4af37', orClair: '#ecd280', vert: '#3a61a8', vertClair: '#6489c9', ambre: '#c8912b', rouge: '#b3273d', grille: 'rgba(238,241,248,0.10)', texte: 'rgba(238,241,248,0.72)', fond: '#101d33', bordure: 'rgba(212,175,55,0.35)' }
    };

    var graphiques = [];

    function themeActif() {
        var t = document.documentElement.getAttribute('data-theme');
        return PALETTES[t] ? t : 'emeraude';
    }

    function optionsCommunes(p) {
        Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
        Chart.defaults.color = p.texte;
        return {
            plugins: {
                tooltip: {
                    backgroundColor: p.fond,
                    borderColor: p.bordure,
                    borderWidth: 1,
                    titleColor: p.orClair,
                    bodyColor: p.texte,
                    padding: 10,
                    cornerRadius: 8,
                    titleFont: { weight: '600' }
                }
            }
        };
    }

    function construireGraphiques() {
        graphiques.forEach(function (g) { g.destroy(); });
        graphiques = [];

        var p = PALETTES[themeActif()];
        var communes = optionsCommunes(p);

        // Répartition des emprunts par statut (doughnut)
        graphiques.push(new Chart(document.getElementById('graphiqueStatutEmprunts'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(donneesStatutEmprunts),
                datasets: [{
                    data: Object.values(donneesStatutEmprunts),
                    backgroundColor: [p.ambre, p.rouge, p.vert],
                    borderColor: p.fond,
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: Object.assign({}, communes, {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: Object.assign({}, communes.plugins, {
                    legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10, padding: 14, usePointStyle: true, pointStyle: 'circle' } }
                })
            })
        }));

        // Livres par catégorie (barres)
        graphiques.push(new Chart(document.getElementById('graphiqueLivresCategorie'), {
            type: 'bar',
            data: {
                labels: donneesLivresCategorie.map(function (l) { return l.categorie; }),
                datasets: [{
                    label: 'Nombre de livres',
                    data: donneesLivresCategorie.map(function (l) { return parseInt(l.total, 10); }),
                    backgroundColor: p.orClair,
                    hoverBackgroundColor: p.or,
                    borderRadius: 6,
                    maxBarThickness: 34
                }]
            },
            options: Object.assign({}, communes, {
                responsive: true,
                maintainAspectRatio: false,
                plugins: Object.assign({}, communes.plugins, { legend: { display: false } }),
                scales: {
                    x: { grid: { display: false }, ticks: { color: p.texte } },
                    y: { beginAtZero: true, ticks: { precision: 0, color: p.texte }, grid: { color: p.grille } }
                }
            })
        }));

        // Emprunts par mois (ligne)
        graphiques.push(new Chart(document.getElementById('graphiqueEmpruntsMois'), {
            type: 'line',
            data: {
                labels: donneesEmpruntsMois.map(function (l) { return l.mois; }),
                datasets: [{
                    label: 'Emprunts',
                    data: donneesEmpruntsMois.map(function (l) { return parseInt(l.total, 10); }),
                    borderColor: p.orClair,
                    backgroundColor: 'transparent',
                    pointBackgroundColor: p.or,
                    pointBorderColor: p.fond,
                    pointBorderWidth: 1.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5
                }]
            },
            options: Object.assign({}, communes, {
                responsive: true,
                maintainAspectRatio: false,
                plugins: Object.assign({}, communes.plugins, { legend: { display: false } }),
                scales: {
                    x: { grid: { display: false }, ticks: { color: p.texte } },
                    y: { beginAtZero: true, ticks: { precision: 0, color: p.texte }, grid: { color: p.grille } }
                }
            })
        }));

        // Léger dégradé sous la courbe, assorti au thème actif
        var ctxLigne = document.getElementById('graphiqueEmpruntsMois').getContext('2d');
        var degrade = ctxLigne.createLinearGradient(0, 0, 0, 130);
        degrade.addColorStop(0, p.or + '55');
        degrade.addColorStop(1, p.or + '00');
        graphiques[2].data.datasets[0].backgroundColor = degrade;
        graphiques[2].update();
    }

    construireGraphiques();
    window.addEventListener('bib-theme-changed', construireGraphiques);
})();
</script>
</body>
</html>
