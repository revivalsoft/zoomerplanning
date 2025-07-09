$(function () {

    var id_sigle = 0;
    var tabsigle = [];
    var tabct = [];
    var tabcf = [];

    // Sélection des plages désactivée au chargement de la page
    $('#selection_plage').prop('disabled', true);

    // gérer ici les listes déroulantes de choix de categorie de plage
    $('#selection').on('change', (function () {
        var categoryId = $(this).val();
        id_sigle = 0;

        tabsigle = [];
        tabct = [];
        tabcf = [];

        // Vider la liste des plages
        $('#selection_plage').empty().append('<option value="">Sélectionnez une plage</option>').prop('disabled', true);

        if (categoryId) { // Requête AJAX pour récupérer les plages de la catégorie sélectionnée

            fetch('/get-data/?id=' + categoryId, {
                method: 'GET',
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        // Ajouter les options des villes dans la liste déroulante

                        data.forEach(plage => {

                            $('#selection_plage').append('<option style="background-color:' + plage.cf + ';color:' + plage.ct + '" value="' + plage.id + '">' + plage.sigle + '-' + plage.legende + '</option>');
                            // les données suivantes sont utilisées pour l'insertion 
                            // dans les plannings mensuels de la plage sélectionnée
                            tabsigle[plage.id] = plage.sigle;
                            tabcf[plage.id] = plage.cf; // couleur fond
                            tabct[plage.id] = plage.ct;  // couleur texte

                        });
                        $('#selection_plage').prop('disabled', false);
                    }
                })
                .catch(error => {
                    console.log('Erreur:', error);
                });

        }
    }));


    // ----------------------------------------------------------------
    // selection d'une plage
    $('#selection_plage').on('change', (function () {
        id_sigle = $(this).val();

    }));

    // ------------------------------

    $('table td').on('click', (function () {

        var idplan = 0;

        var cell = $(this).attr('id');

        if (cell != null) { // sinon génère des erreurs dans la console javascript
            var tabcell = cell.split('-');


            var idressource = tabcell[0];
            var numjour = tabcell[1];
            var numligne = tabcell[2];
            idplan = tabcell[3];
            var nummois = tabcell[4];
            var numan = tabcell[5];

            var nomressource = document.getElementById('r' + idressource).innerHTML;
            var cellbackcolor = document.getElementById(cell).style.backgroundColor;
            var cellcolor = document.getElementById(cell).style.color;
            var celltext = document.getElementById(cell).innerHTML;
            var cellnote = document.getElementById(cell).title;

            document.getElementById('nomressource').innerHTML = "Ressource : " + nomressource;
            document.getElementById('numligne').innerHTML = "Ligne : " + numligne;
            document.getElementById('numjour').innerHTML = "Jour : " + numjour;
            document.getElementById('nummois').innerHTML = "Mois : " + nummois;
            document.getElementById('numan').innerHTML = "An: " + numan;

            document.getElementById('sigle').style.backgroundColor = cellbackcolor;
            document.getElementById('sigle').style.color = cellcolor;
            document.getElementById('sigle').innerHTML = celltext;
            document.getElementById('idplan').innerHTML = idplan;
            document.getElementById('note').value = cellnote;
        }
        // ----MODALE POUR NOTES
        if (document.getElementById('insert_note').checked) {
            if (idplan > 0) {

                var modal = document.getElementById("myModal");
                modal.style.display = "block";

                var btn = document.getElementById("fermer");
                btn.addEventListener("click", closeDialogNote);


                function closeDialogNote() {
                    idplan = 0;
                    modal.style.display = "none";
                }

                //NOTE
                $('#update-note-btn').on('click', function () {
                    var newNote = $('#note').val();

                    newNote = newNote.trim();

                    if (idplan > 0) {

                        fetch('/update-gestion/' + idplan, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest' // Indique que c'est une requête AJAX
                            },
                            body: JSON.stringify({
                                note: newNote
                            })
                        })
                            .then(response => {
                                if (response.ok) {
                                    // return response.json(); // Lire la réponse JSON si le serveur renvoie du JSON

                                    document.getElementById(cell).title = newNote;
                                    document.getElementById(cell).style.backgroundColor = cellbackcolor;
                                    document.getElementById(cell).style.color = cellcolor;
                                    if (newNote === "") {
                                        document.getElementById(cell).style.textDecoration = "none";
                                    }
                                    else {
                                        document.getElementById(cell).style.textDecoration = "underline";
                                    }
                                    idplan = 0;
                                    modal.style.display = "none";
                                }
                                else {
                                    throw new Error('Une erreur est survenue lors de la mise à jour.');
                                }
                            });
                    }
                });
                //fin note
            }
        }

        // DEBUT INSERTION PLAGE
        if (document.getElementById('insert_plage').checked) { // ----------------

            if (idplan < 1 && id_sigle > 0) {

                var newid = 0;
                var newidplan = 0;
                fetch('/new', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(
                        {
                            idressource: idressource,
                            idplage: id_sigle,
                            jour: numjour,
                            mois: nummois,
                            an: numan,
                            ligne: numligne
                        }
                    )
                }).then(response => response.json()).then(data => {
                    if (data.success) {

                        document.getElementById(cell).style.backgroundColor = tabcf[id_sigle];
                        document.getElementById(cell).style.color = tabct[id_sigle];
                        document.getElementById(cell).innerHTML = tabsigle[id_sigle];
                        newidplan = data.id;
                        var balisetd = document.getElementById(cell);
                        newid = idressource + '-' + numjour + '-' + numligne + '-' + newidplan + '-' + nummois + '-' + numan;
                        balisetd.id = newid; // on assigne le nouvel id de l'enregistrement dans la table gestion
                    }
                });

                idplan = 0;

            }
            else {
                alert('Veuillez sélectionner une plage (ou supprimer la plage actuelle).')
            }
        }
        // ----------------- FIN INSERTION PLAGE

        // ------ SUPPRESSION PLAGE
        if (document.getElementById('delete_plage').checked) {
            if (idplan > 0) {

                fetch('/delete-gestion/' + idplan, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(response => response.json()).then(data => {
                    if (data.status === 'Entity deleted') {
                        document.getElementById(cell).innerHTML = "-";
                        document.getElementById(cell).style.backgroundColor = '#FFFFFF';
                        document.getElementById(cell).style.color = '#FFFFFF';
                        var balisetd = document.getElementById(cell);
                        newidplan = 0; //indique que la cellule est à nouveau vide
                        newid = idressource + '-' + numjour + '-' + numligne + '-' + newidplan + '-' + nummois + '-' + numan;
                        balisetd.id = newid;

                        idplan = 0;
                    }
                });
            }
        }
    }));
});
