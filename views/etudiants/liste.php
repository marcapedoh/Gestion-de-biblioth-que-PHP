<?php
// Protection du fichier contre un accès direct
if (count(get_included_files()) === 1) { http_response_code(403); exit; }
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Annuaire des Étudiants</h1>
        <p style="color: var(--text-secondary); margin-top: 5px;">Registre de la Promotion — Cycle Ingénieur</p>
    </div>
    <a href="<?php echo SITE_URL; ?>index.php?action=etudiants-ajouter" class="btn-premium">
        ➕ Inscrire un étudiant
    </a>
</div>

<?php if (isset($_GET['success'])): ?>
    <?php if ($_GET['success'] == 1): ?>
        <div class="alert alert-success">✨ Profil étudiant enregistré avec succès dans l'annuaire.</div>
    <?php elseif ($_GET['success'] == 2): ?>
        <div class="alert alert-success">🔄 Coordonnées de l'étudiant mises à jour avec succès.</div>
    <?php elseif ($_GET['success'] == 3): ?>
        <div class="alert alert-success">🗑️ L'étudiant a été retiré de l'annuaire de la bibliothèque.</div>
    <?php endif; ?>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger">⚠️ <?php echo htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>

<div class="glass-panel table-responsive">
    <table class="premium-table">
        <thead>
            <tr>
                <th>Nom & Prénom</th>
                <th>Filière / Spécialité</th>
                <th>Adresse Email</th>
                <th>Téléphone</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($etudiants) > 0): ?>
                <?php foreach ($etudiants as $e): ?>
                    <tr>
                        <td style="font-weight: 600; color: var(--text-primary);">
                            <?php echo htmlspecialchars($e['nom'] . ' ' . $e['prenom']); ?>
                        </td>
                        <td>
                            <span class="badge badge-success" style="background: rgba(79, 70, 229, 0.12); color: #a5b4fc; border: 1px solid rgba(79, 70, 229, 0.2);">
                                <?php echo htmlspecialchars($e['filiere']); ?>
                            </span>
                        </td>
                        <td style="color: var(--text-secondary);"><?php echo htmlspecialchars($e['email']); ?></td>
                        <td><?php echo htmlspecialchars($e['telephone']); ?></td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 10px;">
                                <a href="<?php echo SITE_URL; ?>index.php?action=etudiants-modifier&id=<?php echo $e['id']; ?>" 
                                   class="btn-premium btn-secondary" 
                                   style="padding: 6px 12px; font-size: 0.85rem; border-radius: 8px;">
                                   ✏️ Modifier
                                </a>
                                <a href="<?php echo SITE_URL; ?>index.php?action=etudiants-supprimer&id=<?php echo $e['id']; ?>" 
                                   class="btn-premium btn-secondary nav-logout" 
                                   style="padding: 6px 12px; font-size: 0.85rem; border-radius: 8px;"
                                   onclick="return confirm('Êtes-vous sûr de vouloir radier cet étudiant ? Cela n\'affectera pas l\'historique de ses emprunts retournés.');">
                                   🗑️ Radier
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                        Aucun étudiant n'est inscrit pour le moment.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="<?php echo SITE_URL; ?>index.php?action=etudiants&page=<?php echo $page - 1; ?>" class="page-btn">‹</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?php echo SITE_URL; ?>index.php?action=etudiants&page=<?php echo $i; ?>" 
               class="page-btn <?php echo ($page === $i) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="<?php echo SITE_URL; ?>index.php?action=etudiants&page=<?php echo $page + 1; ?>" class="page-btn">›</a>
        <?php endif; ?>
    </div>
<?php endif; ?>