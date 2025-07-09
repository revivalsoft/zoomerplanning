document.addEventListener('DOMContentLoaded', () => {
    const categorieSelector = document.querySelector('.js-categorie-selector');
    const plageSelector = document.querySelector('.js-plage-selector');
    const form = document.querySelector('form'); // Ajout de la déclaration explicite du formulaire

    if (!categorieSelector || !plageSelector || !form) {
        console.error('Un ou plusieurs éléments requis sont introuvables.');
        return;
    }

    // Mise à jour dynamique des options de la liste des plages
    categorieSelector.addEventListener('change', (event) => {
        const categorieId = event.target.value;

        // Réinitialiser les options de plage
        plageSelector.innerHTML = '<option value="">Sélectionnez une plage</option>';

        if (categorieId) {
            fetch(`/api/plages?categorieId=${categorieId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    data.forEach(plage => {
                        const option = document.createElement('option');
                        option.value = plage.id; // ID de la plage
                        option.textContent = plage.sigle; // Texte visible
                        plageSelector.appendChild(option);
                    });
                })
                .catch(error => console.error('Erreur lors du chargement des plages :', error));
        }
    });

    // Validation et récupération de l'ID de la plage sélectionnée lors de la soumission
    form.addEventListener('submit', (event) => {
        const selectedPlageId = plageSelector.value;

        if (!selectedPlageId) {
            alert('Veuillez sélectionner une plage avant de soumettre le formulaire.');
            event.preventDefault(); // Bloque la soumission
            return;
        }

        console.log('Plage sélectionnée avec ID :', selectedPlageId);


        // Création des données à envoyer
        const formData = new FormData();
        formData.append('plageId', selectedPlageId);

        // Envoi de la requête
        fetch('/auto/enregistrer', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors de l\'envoi des données');
                }
                return response.json();
            })
            .then(data => {
                console.log('Réponse du contrôleur :', data);
                alert('Envoi réussi !');
            })
            .catch(error => {
                console.error('Erreur :', error);
            });
    });
});
