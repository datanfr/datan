$(document).ready(function () {
  $('.map_france path').on('click', function(){
    var url = $(this).attr("data-slug");
    //alert(url);
    location.href = 'https://datan.fr/deputes/' + url;
  });

  $('.map_outre_mer g').on('click', function(){
    var url = $(this).attr("data-slug");
    //alert(url);
    location.href = 'https://datan.fr/deputes/' + url;
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
