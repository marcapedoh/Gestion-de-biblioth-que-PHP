<?php
// Protection du fichier contre un accès direct
if (count(get_included_files()) === 1) { http_response_code(403); exit; }
?>
    </main>

    <footer style="background: var(--glass-bg); backdrop-filter: blur(10px); border-top: 1px solid var(--glass-border); padding: 20px; text-align: center; font-size: 0.85rem; color: var(--text-secondary); margin-top: auto;">
        <p>&copy; <?php echo date('Y'); ?> — <strong>PHOENIX <span style="color: var(--accent-gold);">|</span> Biblio</strong>. Conçu pour le projet de fin de cycle d'ingénierie.</p>
        <p style="font-size: 0.75rem; margin-top: 4px; color: var(--accent-gold);">Interface Luxury Glass v3.0</p>
    </footer>

    <script>
        // Vérification et application du thème stocké au chargement initial
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', savedTheme);

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const targetTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            // Applique le nouveau thème sur la balise <html>
            document.documentElement.setAttribute('data-theme', targetTheme);
            // Sauvegarde le choix de l'utilisateur pour les prochaines pages
            localStorage.setItem('theme', targetTheme);
        }
    </script>
    <script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>
</body>
</html>