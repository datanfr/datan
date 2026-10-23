$(document).ready(function () {
  // Le legacy écrit ici « https://datan.fr/deputes/ » en dur : la carte y
  // quitte tout autre hôte (préproduction, poste local). L'adresse relative
  // sert la même route partout — c'est une correction, pas un écart.
  $('.map_france path').on('click', function(){
    var url = $(this).attr("data-slug");
    location.href = '/deputes/' + url;
  });

  $('.map_outre_mer g').on('click', function(){
    var url = $(this).attr("data-slug");
    location.href = '/deputes/' + url;
  });

  $(".map_france path").tooltip({
      'container': 'body',
      'placement': 'right'
  });

  $(".map_outre_mer g").tooltip({
      'container': 'body',
      'placement': 'right'
  });
});
