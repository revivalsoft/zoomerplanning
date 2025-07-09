$(function () {

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

            var sigle = celltext;

            document.getElementById('nomressource').innerHTML = nomressource;
            document.getElementById('numligne').innerHTML = numligne;
            document.getElementById('date').innerHTML = numjour + '-' + nummois + '-' + numan;

            document.getElementById('sigle').style.backgroundColor = cellbackcolor;
            document.getElementById('sigle').style.color = cellcolor;
            //document.getElementById('sigle').innerHTML = celltext;
            document.getElementById('note').innerHTML = cellnote;

            // condition pour click si case blanche
            if (idplan > 0) {
                fetchFieldValueByName(sigle);
            }

            var modal = document.getElementById("myModal");
            modal.style.display = "block";

            var btn = document.getElementById("fermer");
            btn.addEventListener("click", closeDialog);

            function closeDialog() {
                idplan = 0;
                modal.style.display = "none";
            }

            // ajax requete pour trouver la légende correspondant au nom du sigle sélectionné
            //combiné avec DataController et routes.yaml
            function fetchFieldValueByName(name) {
                fetch(`/get-field-value-by-name/${encodeURIComponent(name)}`)
                    .then(response => {

                        return response.json();
                    })
                    .then(data => {
                        document.getElementById('sigle').innerHTML = celltext + ' - ' + data.value;
                    });
            }
            // fin ajax
        }

    }));
});


