<?php
/**
 * includes/ExportHelper.php
 * Bonus : Export des listes au format PDF ou Excel.
 *
 * - Excel : génère un vrai fichier .xls (table HTML avec les en-têtes MIME
 *   Excel) téléchargé directement par le navigateur. Aucune librairie externe
 *   n'est nécessaire, Excel/LibreOffice ouvrent nativement ce format.
 * - PDF : génère une page HTML "prête à imprimer" (mise en page A4, sans
 *   menu ni boutons) qui déclenche automatiquement la boîte de dialogue
 *   d'impression du navigateur ; l'utilisateur choisit "Enregistrer au
 *   format PDF" comme imprimante. C'est l'approche standard, fiable et
 *   sans dépendance, pour produire un export PDF en PHP pur.
 */

/**
 * Génère et envoie un fichier Excel (.xls) au navigateur, puis arrête
 * l'exécution du script.
 *
 * @param string $nomFichier Nom du fichier téléchargé (sans extension)
 * @param array  $entetes    Libellés des colonnes, ex: ['Titre', 'Auteur']
 * @param array  $lignes     Tableau de lignes, chaque ligne étant un tableau
 *                           de valeurs dans le même ordre que $entetes
 */
function exporterVersExcel(string $nomFichier, array $entetes, array $lignes): void
{
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nomFichier . '.xls"');
    header('Pragma: public');
    header('Cache-Control: max-age=0');

    // BOM UTF-8 pour que les caractères accentués s'affichent correctement dans Excel
    echo "\xEF\xBB\xBF";

    echo '<table border="1">';
    echo '<tr>';
    foreach ($entetes as $entete) {
        echo '<th style="background-color:#1f2d3d;color:#ffffff;padding:6px;">' . htmlspecialchars($entete) . '</th>';
    }
    echo '</tr>';

    foreach ($lignes as $ligne) {
        echo '<tr>';
        foreach ($ligne as $valeur) {
            echo '<td style="padding:6px;">' . htmlspecialchars((string) $valeur) . '</td>';
        }
        echo '</tr>';
    }

    echo '</table>';
    exit;
}

/**
 * Génère une page HTML imprimable (export PDF via la boîte de dialogue
 * d'impression du navigateur : "Enregistrer au format PDF"), puis arrête
 * l'exécution du script.
 *
 * @param string $titre      Titre affiché en haut du document
 * @param array  $entetes    Libellés des colonnes
 * @param array  $lignes     Tableau de lignes (mêmes règles que exporterVersExcel)
 */
function exporterVersPDF(string $titre, array $entetes, array $lignes): void
{
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars($titre) ?></title>
        <style>
            @page { size: A4; margin: 18mm; }
            * { box-sizing: border-box; }
            body { font-family: Arial, Helvetica, sans-serif; color: #1f2d3d; margin: 0; padding: 20px; }
            h1 { font-size: 1.4em; margin-bottom: 4px; }
            .sous-titre { color: #607d8b; font-size: 0.85em; margin-bottom: 18px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #cfd8dc; padding: 8px 10px; font-size: 0.85em; text-align: left; }
            th { background-color: #1f2d3d; color: #fff; }
            tr:nth-child(even) td { background-color: #f4f6f8; }
            .barre-outils { margin-bottom: 20px; }
            .barre-outils button {
                background-color: #2e7d32; color: #fff; border: none; padding: 8px 18px;
                border-radius: 4px; cursor: pointer; font-size: 0.9em;
            }
            @media print {
                .barre-outils { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="barre-outils">
            <button onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>
        </div>

        <h1><?= htmlspecialchars($titre) ?></h1>
        <p class="sous-titre">Bibliothèque Universitaire — document généré le <?= date('d/m/Y à H:i') ?></p>

        <table>
            <thead>
                <tr>
                    <?php foreach ($entetes as $entete): ?>
                        <th><?= htmlspecialchars($entete) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignes as $ligne): ?>
                    <tr>
                        <?php foreach ($ligne as $valeur): ?>
                            <td><?= htmlspecialchars((string) $valeur) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <script>
            // Ouvre automatiquement la boîte de dialogue d'impression
            window.onload = function () { window.print(); };
        </script>
    </body>
    </html>
    <?php
    exit;
}
