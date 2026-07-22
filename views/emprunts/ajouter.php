<?php
// Protection du fichier contre un accès direct
if (count(get_included_files()) === 1) { http_response_code(403); exit; }
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Nouvelle Allocation</h1>
        <p style="color: var(--text-secondary); margin-top: 5px;">Associer un ouvrage disponible à un étudiant</p>
    </div>
    <a href="<?php echo SITE_URL; ?>index.php?action=emprunts" class="btn-premium btn-secondary">
        ⬅️ Retour aux emprunts
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        ❌ <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="glass-panel" style="max-width: 750px; margin: 0 auto; padding: 40px;">
    <form action="<?php echo SITE_URL; ?>index.php?action=emprunts-ajouter" method="POST">
        
        <div class="form-group">
            <label class="form-label" for="id_livre">Sélectionner l'ouvrage patrimonial</label>
            <select id="id_livre" name="id_livre" class="input-premium" required>
                <option value="" disabled selected hidden>Choisir un livre...</option>
                <?php foreach ($livres as $l): ?>
                    <option value="<?php echo $l['id']; ?>">
                        <?php echo htmlspecialchars($l['titre']); ?> — par <?php echo htmlspecialchars($l['auteur']); ?> 
                        (<?php echo (int)$l['quantite']; ?> en stock)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="id_etudiant">Étudiant bénéficiaire</label>
            <select id="id_etudiant" name="id_etudiant" class="input-premium" required>
                <option value="" disabled selected hidden>Choisir un étudiant...</option>
                <?php foreach ($etudiants as $e): ?>
                    <option value="<?php echo $e['id']; ?>">
                        <?php echo htmlspecialchars($e['nom'] . ' ' . $e['prenom']); ?> [<?php echo htmlspecialchars($e['filiere']); ?>]
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 35px;">
            <div class="form-group">
                <label class="form-label" for="date_emprunt">Date de sortie</label>
                <input type="date" 
                       id="date_emprunt" 
                       name="date_emprunt" 
                       class="input-premium" 
                       value="<?php echo date('Y-m-d'); ?>" 
                       required>
            </div>

            <div class="form-group">
                <label class="form-label" for="date_retour_prevue">Date de restitution limite</label>
                <input type="date" 
                       id="date_retour_prevue" 
                       name="date_retour_prevue" 
                       class="input-premium" 
                       value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" 
                       required>
            </div>
        </div>

        <button type="submit" class="btn-premium" style="width: 100%; justify-content: center;">
            ⏳ Valider et décrémenter le stock
        </button>

    </form>
</div>