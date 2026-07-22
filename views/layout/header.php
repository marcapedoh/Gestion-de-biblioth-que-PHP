<?php
// Protection du fichier contre un accès direct
if (count(get_included_files()) === 1) { http_response_code(403); exit; }

// On s'assure que la session est démarrée pour vérifier l'état de connexion
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHOENIX | Biblio — Gestion Patrimoniale</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body class="dashboard-page">

    <!-- Sprite SVG (dégradés partagés pour la fumée d'arrière-plan) -->
    <svg width="0" height="0" style="position:absolute;">
        <defs>
            <linearGradient id="threadGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" style="stop-color:var(--accent-gold); stop-opacity:0"/>
                <stop offset="50%" style="stop-color:var(--accent-gold-strong); stop-opacity:0.4"/>
                <stop offset="100%" style="stop-color:var(--accent-gold); stop-opacity:0"/>
            </linearGradient>
        </defs>
    </svg>

    <!-- Fumée violette — larges rubans doux, arrière-plan animé -->
    <svg class="smoke-layer" preserveAspectRatio="none" viewBox="0 0 1440 900" xmlns="http://www.w3.org/2000/svg">
        <g class="thread thread-1">
            <path d="M -150 640 C 120 540, 320 720, 540 600 C 760 480, 900 660, 1150 560 C 1350 480, 1500 580, 1650 500" stroke="url(#threadGradient)" stroke-width="70" fill="none" stroke-linecap="round"/>
        </g>
        <g class="thread thread-2">
            <path d="M -150 220 C 100 340, 300 140, 520 260 C 740 380, 900 160, 1120 280 C 1320 390, 1460 240, 1650 320" stroke="url(#threadGradient)" stroke-width="42" fill="none" stroke-linecap="round"/>
        </g>
        <g class="thread thread-3">
            <path d="M -150 830 C 180 740, 400 900, 660 780 C 920 660, 1080 860, 1650 740" stroke="url(#threadGradient)" stroke-width="95" fill="none" stroke-linecap="round"/>
        </g>
        <g class="thread thread-4">
            <path d="M -150 40 C 160 130, 360 -30, 600 80 C 840 190, 1000 10, 1220 100 C 1400 165, 1520 80, 1650 130" stroke="url(#threadGradient)" stroke-width="36" fill="none" stroke-linecap="round"/>
        </g>
        <g class="thread thread-5">
            <path d="M -150 460 C 140 380, 360 500, 600 420 C 860 335, 1020 470, 1280 400 C 1420 360, 1540 410, 1650 380" stroke="url(#threadGradient)" stroke-width="55" fill="none" stroke-linecap="round"/>
        </g>
    </svg>

    <?php if (isset($_SESSION['admin_id'])): ?>
    <nav class="premium-nav">
        <div style="display: flex; align-items: center; gap: 14px;">
            <img class="brand-logo" src="<?php echo SITE_URL; ?>public/images/phoenix-logo.png" alt="Phoenix">
            <div class="brand-wordmark">
                <span class="brand-name">PHOENIX<span class="brand-sep">|</span>Biblio</span>
                <span class="brand-tagline">Système Universitaire</span>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 25px;">
            <a href="<?php echo SITE_URL; ?>index.php?action=livres" 
               style="color: var(--text-primary); text-decoration: none; font-weight: 500; font-size: 0.95rem;" 
               onmouseover="this.style.color='var(--accent-color)'" onmouseout="this.style.color='var(--text-primary)'">
               📚 Catalogue
            </a>
            <a href="<?php echo SITE_URL; ?>index.php?action=etudiants" 
               style="color: var(--text-primary); text-decoration: none; font-weight: 500; font-size: 0.95rem;"
               onmouseover="this.style.color='var(--accent-color)'" onmouseout="this.style.color='var(--text-primary)'">
               🎓 Étudiants
            </a>
            <a href="<?php echo SITE_URL; ?>index.php?action=emprunts" 
               style="color: var(--text-primary); text-decoration: none; font-weight: 500; font-size: 0.95rem;"
               onmouseover="this.style.color='var(--accent-color)'" onmouseout="this.style.color='var(--text-primary)'">
               ⏳ Emprunts
            </a>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <button onclick="toggleTheme()" class="btn-premium btn-secondary" style="padding: 8px 14px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px;">
                🌓 Thème
            </button>
            <a href="<?php echo SITE_URL; ?>logout.php" class="btn-premium btn-secondary" 
               style="padding: 8px 14px; font-size: 0.85rem; border-color: rgba(239, 68, 68, 0.2); color: #f87171 !important; background: rgba(239, 68, 68, 0.05);"
               onclick="return confirm('Mettre fin à votre session administrative ?');">
               🚪 Quitter
            </a>
        </div>
    </nav>
    <?php endif; ?>

    <main>