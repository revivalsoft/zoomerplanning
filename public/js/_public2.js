$(function () {
    $('table').on('click', 'td.tdplanning', function (e) {
        e.stopImmediatePropagation();
        $('#sigle, #note, #taskinfo').empty();

        const td = this;
        const parts = td.id.split('-');
        if (parts.length !== 6) return;
        const [idRes, jour, ligne, , mois, annee] = parts;

        const resEl = document.getElementById('r' + idRes) || {};
        const nomRes = resEl.innerText || '';
        const funcRes = resEl.title || '';
        const bg = td.style.backgroundColor;
        const fg = td.style.color;
        const txt = td.innerText.trim();
        const noteT = td.title || '';

        const taskName = td.getAttribute('data-task-name') || '';
        const taskStart = td.getAttribute('data-task-start') || '';
        const taskEnd = td.getAttribute('data-task-end') || '';

        // Ressource / fonction / ligne / date
        $('#nomressource').text('Ressource : ' + nomRes);
        $('#fonctionressource').text('Fonction : ' + funcRes);
        $('#numligne').text('Ligne : ' + ligne);
        $('#date').text(`Date : ${jour}-${mois}-${annee}`);

        // Style et contenu principale
        $('#sigle')
            .css({ 'background-color': bg, 'color': fg })
            .text(taskName ? `Tâche : ${taskName}` : txt);

        // Affichage dans Note
        if (taskName) {
            $('#note').text(`Période : ${taskStart} → ${taskEnd}`);
        } else {
            $('#note').text(noteT);
        }


        // On ne se sert plus de taskinfo pour Gantt
        $('#taskinfo').empty();

        // Affiche la modale
        $('#myModal').show();

    });

    $('#fermer').on('click', function () {
        $('#myModal').hide();
    });



});
