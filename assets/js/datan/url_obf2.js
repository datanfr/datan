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
  $(".url_obf").click(function() {
    var source1 = $(this).attr("url_obf");
    var source2 = source1.substring(6);
    var url = str_rot13(source2);
    window.open(url, '_blank');
    return false;
  });
});
