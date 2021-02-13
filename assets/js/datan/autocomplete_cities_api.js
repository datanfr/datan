$(function() {
  $('#cities').autocomplete({
    source: function(request, response) {
      $.ajax({
        url: "https://geo.api.gouv.fr/communes?nom=" + $("input[name='cities']").val() + "&fields=departement&boost=population&limit=10",
        data: { q: request.term },
        dataType: "json",
        success: function(data) {
          console.log(data);
          response(data);
        }
      })
    },
    select: function(event, ui) {
      var code = ui.item.code;
      var departement = ui.item.departement.code;
      //alert(ui.item ? ("You picked " + code) : "Nothing selected, input was " + this.value);
      document.location.href = "search/" + code + "/" + departement;
    }
  }).autocomplete("instance")._renderItem = function(ul, item) {
    console.log('test');

    var item = $('<div class="list_item_container">' + item.nom + ' (' + item.departement.code + ')</div>')
    return $("<li>").append(item).appendTo(ul);
  };
});
