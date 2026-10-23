/*
################
                link obfuscation
################
*/

function str_rot13(str) {
  return (str + '').replace(/[a-z]/gi, function(s) {
    return String.fromCharCode(s.charCodeAt(0)
    + (s.toLowerCase() < 'n' ? 13 : -13));
  });
}

$(document).ready(function(){
  // Par attribut et non par classe : `url_obf` sert aussi de style à de vrais
  // liens (classements), qui n'ont rien à décoder.
  var masques = $("[url_obf]");

  // L'application d'origine laissait ces éléments hors du parcours clavier ;
  // les rendre focusables et actionnables ne change rien à l'écran.
  masques.attr("tabindex", 0);

  function ouvrir() {
    var source1 = $(this).attr("url_obf");
    var source2 = source1.substring(6);
    var url = str_rot13(source2);
    window.location.href = url;
    return false;
  }

  masques.click(ouvrir);
  masques.keydown(function(e) {
    if (e.key === "Enter" || e.key === " ") {
      e.preventDefault();
      return ouvrir.call(this);
    }
  });
});
