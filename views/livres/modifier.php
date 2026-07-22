<?php
// Protection du fichier contre un accès direct
if (count(get_included_files()) === 1) { http_response_code(403); exit; }
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Mise à jour des Métadonnées</h1>
        <p style="color: var(--text-secondary); margin-top: 5px;">Modification de l'ouvrage : <span style="color: var(--accent-gold); font-weight: 600;"><?php echo htmlspecialchars($livre['titre']); ?></span></p>
    </div>
    <a href="<?php echo SITE_URL; ?>index.php?action=livres" class="btn-premium btn-secondary">
        ⬅️ Annuler et retourner
    </a>
</div>

<div class="glass-panel" style="max-width: 700px; margin: 0 auto; padding: 40px;">

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">⚠️ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="<?php echo SITE_URL; ?>index.php?action=livres-modifier&id=<?php echo (int)$livre['id']; ?>" method="POST">
        
        <div class="form-group">
            <label class="form-label" for="titre">Titre de l'œuvre</label>
            <input type="text" id="titre" name="titre" class="input-premium" value="<?php echo htmlspecialchars($livre['titre']); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="auteur">Auteur</label>
            <input type="text" id="auteur" name="auteur" class="input-premium" value="<?php echo htmlspecialchars($livre['auteur']); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="isbn">Code International (ISBN)</label>
            <input type="text" id="isbn" name="isbn" class="input-premium" value="<?php echo htmlspecialchars($livre['isbn']); ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="annee">Année d'édition</label>
                <input type="number" id="annee" name="annee" class="input-premium" value="<?php echo htmlspecialchars($livre['annee']); ?>" min="1000" max="<?php echo date('Y'); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="quantite">Quantité globale en stock</label>
                <input type="number" id="quantite" name="quantite" class="input-premium" value="<?php echo (int)$livre['quantite']; ?>" min="0" required>
            </div>
        </div>

        <button type="submit" class="btn-premium" style="width: 100%; justify-content: center; margin-top: 20px;">
            🔄 Valider les modifications
        </button>

    </form>
</div>