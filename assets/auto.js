document.addEventListener('DOMContentLoaded', () => {
    const categorieSelector = document.querySelector('.js-categorie-selector');
    const plageSelector = document.querySelector('.js-plage-selector');

    if (!categorieSelector || !plageSelector) {
        console.error('Un ou plusieurs éléments requis sont introuvables.');
        return;
    }

    categorieSelector.addEventListener('change', (event) => {
        const categorieId = event.target.value;

        // Réinitialiser les options du champ des plages
        plageSelector.innerHTML = '<option value="">Sélectionnez une plage</option>';

        if (categorieId) {
            fetch(`/api/plages?categorieId=${categorieId}`).then(response => response.json()).then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                data.forEach(plage => {
                    const option = document.createElement('option');
                    option.value = plage.id;
                    option.textContent = plage.sigle;
                    plageSelector.appendChild(option);
                });
            }).catch(error => console.error('Erreur lors du chargement des plages:', error));
        }
    });

    // Écouteur sur la soumission du formulaire
    form.addEventListener('submit', (event) => {
        const selectedPlageId = plageSelector.value;

        // Validation simple pour s'assurer qu'une plage est sélectionnée
        if (!selectedPlageId) {
            alert('Veuillez sélectionner une plage avant de soumettre le formulaire.');
            event.preventDefault(); // Empêche la soumission si aucune plage n'est sélectionnée
            return;
        }

        console.log('Plage sélectionnée avec ID :', selectedPlageId);

        // Optionnel : Vérifier d'autres champs ici si nécessaire
    });



});