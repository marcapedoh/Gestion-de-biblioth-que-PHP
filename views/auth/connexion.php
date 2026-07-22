<?php
// Protection d'accès
if (count(get_included_files()) === 1) { http_response_code(403); exit; }

// Variables attendues (fournies par login.php) :
// $activePanel  -> 'login' | 'forgot' | 'reset'
// $error        -> string|null
// $success      -> string|null
// $resetToken   -> string|null (pour le panneau 'reset')
// $debugResetLink -> string|null (uniquement si APP_DEBUG, cf. login.php)

$activePanel = $activePanel ?? 'login';
?>

<div class="login-frame">
    <div class="login-card">
        <div class="auth-form-inner">
            <img class="brand-logo brand-logo-lg" src="<?php echo SITE_URL; ?>public/images/phoenix-logo.png" alt="Phoenix">

            <!-- ================= PANNEAU CONNEXION ================= -->
            <div class="auth-panel <?php echo $activePanel === 'login' ? 'active' : ''; ?>" id="panel-login">
                <h2>Bienvenue !</h2>
                <p class="auth-subtitle">Espace Administration — PHOENIX | Biblio</p>

                <?php if ($activePanel === 'login' && !empty($error)): ?>
                    <div class="auth-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($activePanel === 'login' && !empty($success)): ?>
                    <div class="auth-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form action="<?php echo SITE_URL; ?>login.php" method="POST" autocomplete="off">
                    <div class="input-pill-wrap">
                        <svg class="input-pill-icon" viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 6l10 7 10-7"/></svg>
                        <input type="email" name="email" class="input-pill" placeholder="Email" required>
                    </div>
                    <div class="input-pill-wrap">
                        <svg class="input-pill-icon" viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
                        <input type="password" name="mot_de_passe" id="login-password" class="input-pill input-pill-has-toggle" placeholder="Mot de passe" required>
                        <button type="button" class="pill-toggle-eye" data-target="login-password" aria-label="Afficher le mot de passe">
                            <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.94 10.94 0 0112 19c-7 0-11-7-11-7a21.27 21.27 0 015.06-6.06M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 7 11 7a21.27 21.27 0 01-3.22 4.35M14.12 14.12a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>

                    <div class="auth-links">
                        <a href="#" class="auth-link" id="link-show-forgot">Mot de passe oublié ?</a>
                    </div>

                    <input type="hidden" name="do_login" value="1">
                    <button type="submit" class="btn-login">Se connecter</button>
                </form>
            </div>

            <!-- ================= PANNEAU MOT DE PASSE OUBLIÉ ================= -->
            <div class="auth-panel <?php echo $activePanel === 'forgot' ? 'active' : ''; ?>" id="panel-forgot">
                <h2>Mot de passe oublié</h2>
                <p class="auth-subtitle">Indiquez votre email, un lien de réinitialisation vous sera envoyé.</p>

                <?php if ($activePanel === 'forgot' && !empty($error)): ?>
                    <div class="auth-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($activePanel === 'forgot' && !empty($success)): ?>
                    <div class="auth-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <?php if ($activePanel === 'forgot' && !empty($debugResetLink)): ?>
                    <div class="auth-debug">
                        <strong>Mode démo (pas de serveur mail configuré) :</strong>
                        <a href="<?php echo htmlspecialchars($debugResetLink); ?>"><?php echo htmlspecialchars($debugResetLink); ?></a>
                    </div>
                <?php endif; ?>

                <form action="<?php echo SITE_URL; ?>login.php" method="POST" autocomplete="off">
                    <div class="input-pill-wrap">
                        <svg class="input-pill-icon" viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 6l10 7 10-7"/></svg>
                        <input type="email" name="forgot_email" class="input-pill" placeholder="Email" required>
                    </div>

                    <div class="auth-links">
                        <a href="#" class="auth-link" id="link-show-login-1">&larr; Retour à la connexion</a>
                    </div>

                    <input type="hidden" name="do_forgot" value="1">
                    <button type="submit" class="btn-login">Envoyer le lien</button>
                </form>
            </div>

            <!-- ================= PANNEAU RÉINITIALISATION ================= -->
            <div class="auth-panel <?php echo $activePanel === 'reset' ? 'active' : ''; ?>" id="panel-reset">
                <h2>Nouveau mot de passe</h2>
                <p class="auth-subtitle">Choisissez un nouveau mot de passe pour votre compte.</p>

                <?php if ($activePanel === 'reset' && !empty($error)): ?>
                    <div class="auth-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form action="<?php echo SITE_URL; ?>login.php" method="POST" autocomplete="off">
                    <div class="input-pill-wrap">
                        <svg class="input-pill-icon" viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
                        <input type="password" name="new_password" id="new-password" class="input-pill input-pill-has-toggle" placeholder="Nouveau mot de passe" minlength="8" required>
                        <button type="button" class="pill-toggle-eye" data-target="new-password" aria-label="Afficher le mot de passe">
                            <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.94 10.94 0 0112 19c-7 0-11-7-11-7a21.27 21.27 0 015.06-6.06M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 7 11 7a21.27 21.27 0 01-3.22 4.35M14.12 14.12a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    <div class="input-pill-wrap">
                        <svg class="input-pill-icon" viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
                        <input type="password" name="confirm_password" id="confirm-password" class="input-pill input-pill-has-toggle" placeholder="Confirmer le mot de passe" minlength="8" required>
                        <button type="button" class="pill-toggle-eye" data-target="confirm-password" aria-label="Afficher le mot de passe">
                            <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.94 10.94 0 0112 19c-7 0-11-7-11-7a21.27 21.27 0 015.06-6.06M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 7 11 7a21.27 21.27 0 01-3.22 4.35M14.12 14.12a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>

                    <input type="hidden" name="do_reset" value="1">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($resetToken ?? ''); ?>">
                    <button type="submit" class="btn-login">Réinitialiser</button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    // --- Bascule d'affichage du mot de passe (icône œil) ---
    document.querySelectorAll('.pill-toggle-eye').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.target);
            if (!input) return;
            var showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.querySelector('.icon-eye').style.display = showing ? '' : 'none';
            btn.querySelector('.icon-eye-off').style.display = showing ? 'none' : '';
            btn.setAttribute('aria-label', showing ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
        });
    });

    // --- Bascule entre le panneau "connexion" et "mot de passe oublié" ---
    // (Le panneau "reset" n'est jamais atteint en JS : il vient toujours du lien reçu par email/mode démo)
    function showPanel(name) {
        document.querySelectorAll('.auth-panel').forEach(function (p) { p.classList.remove('active'); });
        var target = document.getElementById('panel-' + name);
        if (target) target.classList.add('active');
    }

    var showForgot = document.getElementById('link-show-forgot');
    var showLogin1 = document.getElementById('link-show-login-1');

    if (showForgot) showForgot.addEventListener('click', function (e) { e.preventDefault(); showPanel('forgot'); });
    if (showLogin1) showLogin1.addEventListener('click', function (e) { e.preventDefault(); showPanel('login'); });
})();
</script>
