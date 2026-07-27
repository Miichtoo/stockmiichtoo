<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock RP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="#" aria-label="Accueil Stock RP">
            <span class="brand-cube">⬡</span>
            <span>STOCK RP</span>
        </a>

        <div class="toolbar">
            <label class="search-wrap">
                <span>🔎</span>
                <input id="searchInput" type="search" placeholder="Rechercher une ressource..." autocomplete="off">
            </label>

            <button class="btn btn-primary" id="addButton" type="button">＋ Ressource</button>
            <button class="btn btn-primary" id="adminButton" type="button">🔐 Admin</button>
        </div>
    </header>

    <main>
        <section id="resourceGrid" class="resource-grid" aria-live="polite"></section>
        <div id="emptyState" class="empty-state" hidden>
            <div class="empty-icon">📦</div>
            <h2>Aucune ressource</h2>
            <p>Ajoute ta première ressource avec le bouton en haut.</p>
        </div>
    </main>

    <div class="modal-backdrop" id="resourceModal" hidden>
        <section class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
            <div class="modal-header">
                <h2 id="modalTitle">Ajouter une ressource</h2>
                <button class="icon-button" id="closeModal" type="button" aria-label="Fermer">✕</button>
            </div>

            <form id="resourceForm">
                <input id="resourceId" type="hidden">

                <label>
                    Nom
                    <input id="resourceName" maxlength="100" required placeholder="Ex. Lingot de fer">
                </label>

                <div class="form-row">
                    <label>
                        Quantité
                        <input id="resourceQuantity" type="number" min="0" value="0" required>
                    </label>
                    <label>
                        Icône
                        <input id="resourceIcon" maxlength="16" value="📦" placeholder="📦">
                    </label>
                </div>

                <div class="modal-actions">
                    <button class="btn btn-secondary" id="cancelModal" type="button">Annuler</button>
                    <button class="btn btn-primary" type="submit">Enregistrer</button>
                </div>
            </form>
        </section>
    </div>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    <script src="app.js" defer></script>
</body>
</html>
