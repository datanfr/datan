(function ($) {
 "use strict";

	$(document).ready(function() {
    $.fn.dataTable.moment( 'DD-MM-YYYY' );
    //d-m-Y

    // TABLE PAGE votes
    $('#table-vote-all').DataTable({
      "order": [[0, "desc"]],
      language: {
          processing:     "Traitement en cours...",
          search:         "Rechercher&nbsp;:",
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
      }
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
      language: {
          processing:     "Traitement en cours...",
          search:         "Rechercher&nbsp;:",
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
      }
    });

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
      language: {
          processing:     "Traitement en cours...",
          search:         "Rechercher un député&nbsp;:",
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
      }
    } );

    // TABLE PAGE député/votes/all & groupes/votes/all
    $('#table-deputes-groupes-votes-all').DataTable({
      "order": [[0, "desc"]],
      responsive: {
        details: false
      },
      language: {
          processing:     "Traitement en cours...",
          search:         "Rechercher&nbsp;:",
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
      }
    });

    // TABLE PAGE stats
    $('#table-stats').DataTable({
      "paging" : false,
      "info": false,
      responsive: {
        details: false
      },
      language: {
          processing:     "Traitement en cours...",
          search:         "Rechercher votre député&nbsp;:",
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
      }
    });

    $('#table-stats2').DataTable({
      "paging" : false,
      "info": false,
      responsive: {
        details: false
      },
      language: {
          processing:     "Traitement en cours...",
          search:         "Rechercher votre député&nbsp;:",
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
      }
    });

    $('#table-stats-origine-sociale').DataTable( {
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
      language: {
          processing:     "Traitement en cours...",
          search:         "Rechercher un député&nbsp;:",
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
      }
    } );

	});

})(jQuery);
