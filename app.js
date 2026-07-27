const grid = document.querySelector('#resourceGrid');
const emptyState = document.querySelector('#emptyState');
const searchInput = document.querySelector('#searchInput');
const addButton = document.querySelector('#addButton');
const adminButton = document.querySelector('#adminButton');
const modal = document.querySelector('#resourceModal');
const closeModalButton = document.querySelector('#closeModal');
const cancelModalButton = document.querySelector('#cancelModal');
const form = document.querySelector('#resourceForm');
const modalTitle = document.querySelector('#modalTitle');
const idInput = document.querySelector('#resourceId');
const nameInput = document.querySelector('#resourceName');
const quantityInput = document.querySelector('#resourceQuantity');
const iconInput = document.querySelector('#resourceIcon');
const toast = document.querySelector('#toast');

let resources = [];
let searchTimer;

async function request(url, options = {}) {
    const response = await fetch(url, {
        headers: { 'Content-Type': 'application/json' },
        ...options,
    });

    const data = await response.json().catch(() => ({
        ok: false,
        error: 'Réponse serveur invalide.',
    }));

    if (!response.ok || !data.ok) {
        throw new Error(data.error || 'Une erreur est survenue.');
    }

    return data;
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function render() {
    grid.innerHTML = resources.map(resource => `
        <article class="resource-card" data-id="${resource.id}">
            <button class="delete-button" data-action="delete" title="Supprimer" aria-label="Supprimer ${escapeHtml(resource.name)}">×</button>
            <h2 class="card-title">${escapeHtml(resource.name)}</h2>
            <div class="quantity">
                <span class="quantity-icon">${escapeHtml(resource.icon || '📦')}</span>
                <span>${Number(resource.quantity).toLocaleString('fr-FR')}</span>
            </div>
            <div class="card-actions">
                <button class="action-button action-add" data-action="add" title="Ajouter 1">＋</button>
                <button class="action-button action-remove" data-action="remove" title="Retirer 1">−</button>
                <button class="action-button action-edit" data-action="edit" title="Modifier">✏️</button>
            </div>
        </article>
    `).join('');

    emptyState.hidden = resources.length !== 0;
}

async function loadResources() {
    try {
        const query = encodeURIComponent(searchInput.value.trim());
        const data = await request(`api.php?action=list&q=${query}`);
        resources = data.resources;
        render();
    } catch (error) {
        showToast(error.message, true);
    }
}

function openModal(resource = null) {
    idInput.value = resource?.id ?? '';
    nameInput.value = resource?.name ?? '';
    quantityInput.value = resource?.quantity ?? 0;
    iconInput.value = resource?.icon ?? '📦';
    modalTitle.textContent = resource ? 'Modifier la ressource' : 'Ajouter une ressource';
    modal.hidden = false;
    setTimeout(() => nameInput.focus(), 0);
}

function closeModal() {
    modal.hidden = true;
    form.reset();
    idInput.value = '';
    quantityInput.value = 0;
    iconInput.value = '📦';
}

function showToast(message, isError = false) {
    toast.textContent = message;
    toast.classList.toggle('error', isError);
    toast.classList.add('visible');
    window.clearTimeout(showToast.timer);
    showToast.timer = window.setTimeout(() => toast.classList.remove('visible'), 2800);
}

async function changeQuantity(id, delta) {
    try {
        await request('api.php?action=change', {
            method: 'POST',
            body: JSON.stringify({ id, delta }),
        });
        await loadResources();
    } catch (error) {
        showToast(error.message, true);
    }
}

async function deleteResource(resource) {
    const confirmed = window.confirm(`Supprimer « ${resource.name} » ?`);
    if (!confirmed) return;

    try {
        await request('api.php?action=delete', {
            method: 'POST',
            body: JSON.stringify({ id: resource.id }),
        });
        showToast('Ressource supprimée.');
        await loadResources();
    } catch (error) {
        showToast(error.message, true);
    }
}

grid.addEventListener('click', event => {
    const button = event.target.closest('button[data-action]');
    if (!button) return;

    const card = button.closest('.resource-card');
    const id = Number(card.dataset.id);
    const resource = resources.find(item => Number(item.id) === id);
    if (!resource) return;

    switch (button.dataset.action) {
        case 'add':
            changeQuantity(id, 1);
            break;
        case 'remove':
            changeQuantity(id, -1);
            break;
        case 'edit':
            openModal(resource);
            break;
        case 'delete':
            deleteResource(resource);
            break;
    }
});

form.addEventListener('submit', async event => {
    event.preventDefault();

    const id = Number(idInput.value || 0);
    const payload = {
        id,
        name: nameInput.value,
        quantity: Number(quantityInput.value),
        icon: iconInput.value,
    };

    try {
        await request(`api.php?action=${id ? 'update' : 'create'}`, {
            method: 'POST',
            body: JSON.stringify(payload),
        });
        showToast(id ? 'Ressource modifiée.' : 'Ressource ajoutée.');
        closeModal();
        await loadResources();
    } catch (error) {
        showToast(error.message, true);
    }
});

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadResources, 200);
});

addButton.addEventListener('click', () => openModal());
adminButton.addEventListener('click', () => showToast('Le bouton Admin est prêt à être relié à ton système de connexion.'));
closeModalButton.addEventListener('click', closeModal);
cancelModalButton.addEventListener('click', closeModal);

modal.addEventListener('click', event => {
    if (event.target === modal) closeModal();
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && !modal.hidden) closeModal();
});

loadResources();
