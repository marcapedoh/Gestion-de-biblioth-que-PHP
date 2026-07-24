/**
 * assets/js/theme.js
 * Gère le changement de thème visuel (Émeraude / Ivoire / Saphir).
 * L'application immédiate (anti-flash) se fait via un petit script inline
 * dans head_libs.php ; ce fichier gère uniquement les interactions une fois
 * le DOM prêt (bouton, menu, persistance).
 */
(function () {
    "use strict";

    var STORAGE_KEY = "bib-theme";
    var THEMES = [
        { id: "emeraude", label: "Émeraude Impériale", desc: "Sombre · or & émeraude", c1: "#0f2c20", c2: "#d4af37" },
        { id: "ivoire", label: "Ivoire & Bordeaux", desc: "Clair · parchemin & bordeaux", c1: "#f6f1e4", c2: "#7a1b2e" },
        { id: "saphir", label: "Nuit Saphir", desc: "Sombre · bleu nuit & or", c1: "#0f1b30", c2: "#d4af37" }
    ];

    function getTheme() {
        try {
            return localStorage.getItem(STORAGE_KEY) || "emeraude";
        } catch (e) {
            return "emeraude";
        }
    }

    function setTheme(id) {
        document.documentElement.setAttribute("data-theme", id);
        try {
            localStorage.setItem(STORAGE_KEY, id);
        } catch (e) { /* stockage indisponible : on continue sans persister */ }
        refreshUI(id);
        window.dispatchEvent(new CustomEvent("bib-theme-changed", { detail: { theme: id } }));
    }

    function refreshUI(activeId) {
        var options = document.querySelectorAll(".theme-option");
        options.forEach(function (opt) {
            var isActive = opt.getAttribute("data-theme-id") === activeId;
            opt.classList.toggle("is-active", isActive);
        });
    }

    function buildMenu(container) {
        THEMES.forEach(function (theme) {
            var btn = document.createElement("button");
            btn.type = "button";
            btn.className = "dropdown-item theme-option";
            btn.setAttribute("data-theme-id", theme.id);
            btn.innerHTML =
                '<span class="swatch-pair"><span style="background:' + theme.c1 + '"></span>' +
                '<span style="background:' + theme.c2 + '"></span></span>' +
                '<span><span class="d-block">' + theme.label + '</span>' +
                '<span class="d-block text-muted" style="font-size:.72rem;font-weight:400;">' + theme.desc + '</span></span>' +
                '<i class="fa-solid fa-circle-check theme-option-check"></i>';
            btn.addEventListener("click", function () {
                setTheme(theme.id);
            });
            container.appendChild(btn);
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        var menu = document.getElementById("themeMenu");
        if (menu) {
            buildMenu(menu);
        }
        refreshUI(getTheme());
    });

    // Expose une petite API globale au cas où d'autres scripts en auraient besoin.
    window.BibTheme = { get: getTheme, set: setTheme, list: THEMES };
})();
