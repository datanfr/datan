(function ($) {
 "use strict";

 const french = {
     processing:     "Traitement en cours...",
     search:         "Recherche :",
     lengthMenu:    "Afficher _MENU_ votes",
     info:           "Affichage des votes _START_ &agrave; _END_ sur _TOTAL_ votes",
     infoEmpty:      "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
     infoFiltered:   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
     infoPostFix:    "",
     loadingRecords: "Chargement en cours...",
     zeroRecords:    "Aucun &eacute;l&eacute;ment &agrave; afficher",
     emptyTable:     "Aucune donnée disponible dans le tableau",
     paginate: {
         first:      "Premier",
         previous:   "Pr&eacute;c&eacute;dent",
         next:       "Suivant",
         last:       "Dernier"
     },
     aria: {
         sortAscending:  ": activer pour trier la colonne par ordre croissant",
         sortDescending: ": activer pour trier la colonne par ordre décroissant"
     }
 };

	$(document).ready(function() {
    $.fn.dataTable.moment( 'DD-MM-YYYY' );
    //d-m-Y

    // TABLE PAGE votes
    $('#table-vote-all').DataTable({
      language: french,
      order: [[1, 'desc'], [0, 'desc']]
    });

    // TABLE PAGE votes/x -> groupes
    $('#table-vote-individual-groupes').DataTable({
      "paging" : false,
      "info": false,
      "bFilter": false,
      responsive: {
        details: false
      },
      columnDefs: [{
        className: 'control',
        orderable: false,
        targets: -1
      }],
      language: french
    });

    // TABLE PAGE parrainages
    $('#table-parrainages-deputes').DataTable( {
      "paging" : true,
      "ordering": false,
      "info": false,
      responsive: {
        details: false
      },
      initComplete: function () {
          this.api().columns().every( function (i) {
            var column = this;
            if (screen.width > 767) {
              if (i == 1 | i == 2 | i == 3) {
                $(column.header()).append("</br>");
                var select = $('<select style="margin-top: 12px; margin-bottom: 5px" class="form-control"><option value=""></option></select>')
                    .appendTo( $(column.header()) )
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );

                        column
                            .search( val ? '^'+val+'$' : '', true, false )
                            .draw();
                    } );

                    column.data().unique().sort().each( function ( d, j ) {
                        select.append( '<option value="'+d+'">'+d+'</option>' )
                    } );
              }
            }
        });
      },
      language: french
    } );

    // TABLE PAGE votes/x -> deputes
    $('#table-vote-individual-deputes').DataTable( {
      "paging" : false,
      "ordering": false,
      "info": false,
      responsive: {
        details: false
      },
      initComplete: function () {
          this.api().columns().every( function (i) {
            var column = this;
            if (screen.width > 767) {
              if (i == 1 | i == 2 | i == 3) {
                $(column.header()).append("</br>");
                var select = $('<select style="margin-top: 12px; margin-bottom: 5px" class="form-control"><option value=""></option></select>')
                    .appendTo( $(column.header()) )
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );

                        column
                            .search( val ? '^'+val+'$' : '', true, false )
                            .draw();
                    } );

                    column.data().unique().sort().each( function ( d, j ) {
                        select.append( '<option value="'+d+'">'+d+'</option>' )
                    } );
              }
            }
        });
      },
      language: french
    } );

    // TABLE PAGE député/votes/all & groupes/votes/all
    $('#table-deputes-groupes-votes-all').DataTable({
      responsive: {
        details: false
      },
      language: french,
      order: [[2, 'desc'], [1, 'desc']]
    });

    // TABLE PAGE stats
    $('#table-stats').DataTable({
      "paging" : false,
      "info": false,
      responsive: {
        details: false
      },
      language: french
    });

    $('#table-stats2').DataTable({
      "paging" : false,
      "info": false,
      responsive: {
        details: false
      },
      language: french
    });

    $('#table-stats3').DataTable({
      "paging" : false,
      "info": false,
      responsive: {
        details: false
      },
      language: french
    });

    $('#table-stats-origine-sociale').DataTable( {
      "ordering": false,
      "info": false,
      responsive: {
        details: false
      },
      initComplete: function () {
          this.api().columns().every( function (i) {
            const column = this;
            if (screen.width > 767) {
              if (i == 1 || i == 2 || i == 3) {
                $(column.header()).append("<br>");
                const select = $('<select aria-label="Filtrer par '+$(column.header()).text()+'" style="margin-top: 12px; margin-bottom: 5px" class="form-control"><option value=""></option></select>')
                    .appendTo( $(column.header()) )
                    .on( 'change', function () {
                        const val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );

                        column
                            .search( val ? '^'+val+'$' : '', true, false )
                            .draw();
                    } );

                    column.data().unique().sort().each( function ( d, j ) {
                        select.append( '<option value="'+d+'">'+d+'</option>' )
                    } );
              }
            }
        });
      },
      language: french
    } );

	});

})(jQuery);
