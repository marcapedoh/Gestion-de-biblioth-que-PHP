<?php
// Protection du fichier contre un accès direct
if (count(get_included_files()) === 1) { http_response_code(403); exit; }
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Mise à jour du Profil</h1>
        <p style="color: var(--text-secondary); margin-top: 5px;">Modification de l'étudiant : <span style="color: var(--accent-gold); font-weight: 600;"><?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']); ?></span></p>
    </div>
    <a href="<?php echo SITE_URL; ?>index.php?action=etudiants" class="btn-premium btn-secondary">
        ⬅️ Annuler
    </a>
</div>

<div class="glass-panel" style="max-width: 700px; margin: 0 auto; padding: 40px;">

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">⚠️ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="<?php echo SITE_URL; ?>index.php?action=etudiants-modifier&id=<?php echo (int)$etudiant['id']; ?>" method="POST">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="nom">Nom</label>
                <input type="text" id="nom" name="nom" class="input-premium" value="<?php echo htmlspecialchars($etudiant['nom']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" class="input-premium" value="<?php echo htmlspecialchars($etudiant['prenom']); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="filiere">Filière / Cycle</label>
            <input type="text" id="filiere" name="filiere" class="input-premium" value="<?php echo htmlspecialchars($etudiant['filiere']); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Adresse Email</label>
            <input type="email" id="email" name="email" class="input-premium" value="<?php echo htmlspecialchars($etudiant['email']); ?>" required>
        </div>

        <div class="form-group" style="margin-bottom: 35px;">
            <label class="form-label" for="telephone">Téléphone</label>
            <input type="tel" id="telephone" name="telephone" class="input-premium" value="<?php echo htmlspecialchars($etudiant['telephone']); ?>" required>
        </div>

        <button type="submit" class="btn-premium" style="width: 100%; justify-content: center;">
            🔄 Enregistrer les modifications
        </button>

    </form>
</div>