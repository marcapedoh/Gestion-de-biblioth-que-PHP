<?php
// Protection du fichier contre un accès direct
if (count(get_included_files()) === 1) { http_response_code(403); exit; }
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Gestion des Emprunts</h1>
        <p style="color: var(--text-secondary); margin-top: 5px;">Suivi des flux et des restitutions de livres</p>
    </div>
    <a href="<?php echo SITE_URL; ?>index.php?action=emprunts-ajouter" class="btn-premium">
        ⏳ Allouer un emprunt
    </a>
</div>

<?php if (isset($_GET['success'])): ?>
    <?php if ($_GET['success'] == 1): ?>
        <div class="alert alert-success">✨ L'emprunt a été validé. Le stock du livre a été décrémenté de 1.</div>
    <?php elseif ($_GET['success'] == 2): ?>
        <div class="alert alert-success">🔄 Le livre a été restitué avec succès. Le stock a été réincrémenté de 1.</div>
    <?php endif; ?>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">⚠️ Impossible de traiter cette action ou l'emprunt est introuvable.</div>
<?php endif; ?>

<div class="glass-panel table-responsive">
    <table class="premium-table">
        <thead>
            <tr>
                <th>Livre Emprunté</th>
                <th>Étudiant</th>
                <th>Date Sortie</th>
                <th>Retour Prévu</th>
                <th>Statut</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($emprunts) > 0): ?>
                <?php foreach ($emprunts as $em): ?>
                    <tr>
                        <td style="font-weight: 600; color: var(--text-primary); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?php echo htmlspecialchars($em['livre_titre']); ?>
                        </td>
                        <td style="color: var(--text-primary);">
                            <?php echo htmlspecialchars($em['etudiant_nom'] . ' ' . $em['etudiant_prenom']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($em['date_emprunt']); ?></td>
                        <td><?php echo htmlspecialchars($em['date_retour_prevue']); ?></td>
                        <td>
                            <?php if ($em['statut'] === 'En cours'): ?>
                                <span class="badge badge-warning">En cours</span>
                            <?php else: ?>
                                <span class="badge badge-success">Restitué le <?php echo htmlspecialchars($em['date_retour']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <?php if ($em['statut'] === 'En cours'): ?>
                                <a href="<?php echo SITE_URL; ?>index.php?action=emprunts-retour&id=<?php echo $em['id']; ?>" 
                                   class="btn-premium btn-secondary" 
                                   style="padding: 6px 14px; font-size: 0.85rem; border-radius: 8px; border-color: rgba(52, 211, 153, 0.3);"
                                   onclick="return confirm('Confirmer la restitution de cet ouvrage et réincrémenter le stock ?');">
                                   ✔ Enregistrer le retour
                                </a>
                            <?php else: ?>
                                <span style="color: var(--text-secondary); font-size: 0.9rem; padding-right: 10px;">Archivé</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                        Aucun mouvement de livre enregistré pour le moment.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="<?php echo SITE_URL; ?>index.php?action=emprunts&page=<?php echo $page - 1; ?>" class="page-btn">‹</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?php echo SITE_URL; ?>index.php?action=emprunts&page=<?php echo $i; ?>" 
               class="page-btn <?php echo ($page === $i) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="<?php echo SITE_URL; ?>index.php?action=emprunts&page=<?php echo $page + 1; ?>" class="page-btn">›</a>
        <?php endif; ?>
    </div>
<?php endif; ?>