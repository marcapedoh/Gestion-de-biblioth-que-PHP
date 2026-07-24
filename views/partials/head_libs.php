<?php
/**
 * views/partials/head_libs.php
 * Bonus : Interface responsive avec Bootstrap + icônes Font Awesome.
 * Inclus dans le <head> de chaque page. Attend que BASE_URL soit définie.
 */
?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0b1712">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
<script>
    /* Applique le thème mémorisé avant le premier rendu (évite le flash). */
    (function () {
        try {
            var t = localStorage.getItem('bib-theme') || 'emeraude';
            document.documentElement.setAttribute('data-theme', t);
        } catch (e) {
            document.documentElement.setAttribute('data-theme', 'emeraude');
        }
    })();
</script>
