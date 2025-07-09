document.addEventListener('DOMContentLoaded', function () {
    const sortableList = document.getElementById('sortable-list');
    //const groupe_id = document.getElementById('sortable-list').dataset.idgroupe;
    const groupe_id = sortableList.dataset.idGroupe;
    if (sortableList) {
        Sortable.create(sortableList);
    }

    const saveOrderButton = document.getElementById('save-order');
    if (saveOrderButton) {
        saveOrderButton.addEventListener('click', function () {
            // Récupérer les IDs dans l'ordre trié
            const order = Array.from(document.querySelectorAll('#sortable-list li')).map(el => el.getAttribute('data-id'));

            // Afficher l'ordre dans la console
            //console.log('Nouvel ordre des IDs :', order);


            fetch('/hierarchie/saveorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ order, groupe_id }),
            })
                .then((response) => {
                    if (response.ok) {
                        return response.json();
                    }
                    throw new Error('Erreur lors de l\'enregistrement de l\'ordre.');
                })
                .then(data => {
                    // Afficher le message de la réponse JSON dans une alerte
                    if (data.message) {
                        alert(data.message);
                    }
                })
                .catch((error) => {
                    console.error('Une erreur est survenue :', error);
                });
        });
    } else {
        console.error('Le bouton save-order est introuvable.');
    }
});
