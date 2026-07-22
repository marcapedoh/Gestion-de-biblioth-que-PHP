<?php
// Protection du fichier contre un accès direct
if (count(get_included_files()) === 1) { http_response_code(403); exit; }
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Catalogue des Ouvrages</h1>
        <p style="color: var(--text-secondary); margin-top: 5px;">Informatique & Littérature Africaine</p>
    </div>
    <a href="<?php echo SITE_URL; ?>index.php?action=livres-ajouter" class="btn-premium">
        ➕ Ajouter un ouvrage
    </a>
</div>

<?php if (isset($_GET['success'])): ?>
    <?php if ($_GET['success'] == 1): ?>
        <div class="alert alert-success">✨ Nouvel ouvrage enregistré avec succès dans le patrimoine.</div>
    <?php elseif ($_GET['success'] == 2): ?>
        <div class="alert alert-success">🔄 Les métadonnées de l'ouvrage ont été mises à jour avec rigueur.</div>
    <?php elseif ($_GET['success'] == 3): ?>
        <div class="alert alert-success">🗑️ L'ouvrage a été retiré du catalogue avec succès.</div>
    <?php endif; ?>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger">⚠️ <?php echo htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>

<div class="glass-panel" style="padding: 20px; margin-bottom: 30px;">
    <form action="<?php echo SITE_URL; ?>index.php" method="GET" style="display: flex; gap: 15px;">
        <input type="hidden" name="action" value="livres">
        <input type="text" 
               name="search" 
               class="input-premium" 
               placeholder="Rechercher par Titre, Auteur ou code ISBN..." 
               value="<?php echo htmlspecialchars($search); ?>" 
               style="flex: 1;">
        <button type="submit" class="btn-premium btn-secondary" style="padding: 0 30px;">
            🔍 Filtrer
        </button>
        <?php if (!empty($search)): ?>
            <a href="<?php echo SITE_URL; ?>index.php?action=livres" class="btn-premium btn-secondary" style="justify-content: center; align-items: center;">
                Réinitialiser
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="glass-panel table-responsive">
    <table class="premium-table">
        <thead>
            <tr>
                <th>Titre de l'Ouvrage</th>
                <th>Auteur</th>
                <th>ISBN</th>
                <th>Année</th>
                <th>Stock disponible</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($livres) > 0): ?>
                <?php foreach ($livres as $l): ?>
                    <tr>
                        <td style="font-weight: 600; color: var(--text-primary); max-width: 350px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?php echo htmlspecialchars($l['titre']); ?>
                        </td>
                        <td style="color: var(--text-secondary);"><?php echo htmlspecialchars($l['auteur']); ?></td>
                        <td><code style="color: var(--accent-gold); font-size: 0.9rem;"><?php echo htmlspecialchars($l['isbn']); ?></code></td>
                        <td><?php echo htmlspecialchars($l['annee']); ?></td>
                        <td>
                            <?php if ($l['quantite'] > 0): ?>
                                <span class="badge badge-success"><?php echo (int)$l['quantite']; ?> disponibles</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Rupture de stock</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 10px;">
                                <a href="<?php echo SITE_URL; ?>index.php?action=livres-modifier&id=<?php echo $l['id']; ?>" 
                                   class="btn-premium btn-secondary" 
                                   style="padding: 6px 12px; font-size: 0.85rem; border-radius: 8px;">
                                   ✏️ Modifier
                                </a>
                                <a href="<?php echo SITE_URL; ?>index.php?action=livres-supprimer&id=<?php echo $l['id']; ?>" 
                                   class="btn-premium btn-secondary nav-logout" 
                                   style="padding: 6px 12px; font-size: 0.85rem; border-radius: 8px;"
                                   onclick="return confirm('Êtes-vous absolument sûr de vouloir retirer cet ouvrage de manière définitive ?');">
                                   🗑️ Retirer
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                        Aucun ouvrage ne correspond aux critères de recherche actuels.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="<?php echo SITE_URL; ?>index.php?action=livres&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="page-btn">‹</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?php echo SITE_URL; ?>index.php?action=livres&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
               class="page-btn <?php echo ($page === $i) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="<?php echo SITE_URL; ?>index.php?action=livres&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="page-btn">›</a>
        <?php endif; ?>
    </div>
<?php endif; ?>