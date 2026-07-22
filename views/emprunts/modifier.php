<?php
// Protection du fichier contre un accès direct
if (count(get_included_files()) === 1) { http_response_code(403); exit; }
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Prolongation de l'Emprunt</h1>
        <p style="color: var(--text-secondary); margin-top: 5px;">Ajuster la date limite de restitution</p>
    </div>
    <a href="<?php echo SITE_URL; ?>index.php?action=emprunts" class="btn-premium btn-secondary">
        ⬅️ Annuler
    </a>
</div>

<div class="glass-panel" style="max-width: 700px; margin: 0 auto; padding: 40px;">
    <form action="<?php echo SITE_URL; ?>index.php?action=emprunts-modifier&id=<?php echo (int)$emprunt['id']; ?>" method="POST">
        
        <div class="form-group">
            <label class="form-label">Ouvrage concerné</label>
            <input type="text" class="input-premium" value="<?php echo htmlspecialchars($emprunt['livre_titre']); ?>" disabled style="opacity: 0.6; background: rgba(0,0,0,0.4);">
        </div>

        <div class="form-group">
            <label class="form-label">Étudiant bénéficiaire</label>
            <input type="text" class="input-premium" value="<?php echo htmlspecialchars($emprunt['etudiant_nom'] . ' ' . $emprunt['etudiant_prenom']); ?>" disabled style="opacity: 0.6; background: rgba(0,0,0,0.4);">
        </div>

        <div class="form-group" style="margin-bottom: 35px;">
            <label class="form-label" for="date_retour_prevue">Nouvelle date de restitution limite</label>
            <input type="date" 
                   id="date_retour_prevue" 
                   name="date_retour_prevue" 
                   class="input-premium" 
                   value="<?php echo htmlspecialchars($emprunt['date_retour_prevue']); ?>" 
                   required>
        </div>

        <button type="submit" class="btn-premium" style="width: 100%; justify-content: center;">
            🔄 Accorder la prolongation
        </button>

    </form>
</div>