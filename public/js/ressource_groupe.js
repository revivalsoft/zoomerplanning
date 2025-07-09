document.addEventListener('DOMContentLoaded', () => {
    const groupesLeft = document.getElementById('groupes-left');
    const groupesRight = document.getElementById('groupes-right');
    const ressourcesLeft = document.getElementById('ressources-left');
    const ressourcesRight = document.getElementById('ressources-right');

    // Chargement des ressources selon le groupe sélectionné
    groupesLeft.addEventListener('change', () => loadRessources(groupesLeft, ressourcesLeft));
    groupesRight.addEventListener('change', () => loadRessources(groupesRight, ressourcesRight));

    function loadRessources(groupesSelect, ressourcesList) {
        const groupeId = groupesSelect.value;
        if (groupeId) {
            fetch(`/ressource-groupe/load-ressources/${groupeId}`)
                .then(response => response.json())
                .then(data => {
                    ressourcesList.innerHTML = '';
                    data.ressources.forEach(ressource => {
                        const item = document.createElement('li');
                        item.textContent = ressource.nom;
                        item.dataset.id = ressource.id;
                        item.classList.add('list-group-item');
                        ressourcesList.appendChild(item);
                    });
                });
        } else {
            ressourcesList.innerHTML = '';
        }
    }

    // Initialisation de Sortable.js
    const sortableLeft = new Sortable(ressourcesLeft, {
        group: 'shared', // Définit que les listes partagent des éléments
        animation: 150,
    });

    const sortableRight = new Sortable(ressourcesRight, {
        group: 'shared',
        animation: 150,
        onAdd: (event) => {
            // Empêche l'ajout d'éléments en double
            const existingItems = Array.from(ressourcesRight.children).map(item => item.dataset.id);
            const addedItemId = event.item.dataset.id;

            if (existingItems.filter(id => id === addedItemId).length > 1) {
                event.item.remove(); // Supprime le doublon
            } else {
                saveAssignment(addedItemId, groupesRight.value);
            }
        },
    });

    function saveAssignment(ressourceId, groupeId) {
        fetch('/ressource-groupe/assign', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ressource_id: ressourceId, groupe_id: groupeId })
        }).then(response => {
            if (!response.ok) {
                alert('Erreur lors de l\'enregistrement');
            }
        });
    }
});
