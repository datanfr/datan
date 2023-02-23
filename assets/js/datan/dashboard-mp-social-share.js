function socialWindow(url) {
  var left = (screen.width - 570) / 2;
  var top = (screen.height - 570) / 2;
  var params = "menubar=no,toolbar=no,status=no,width=570,height=570,top=" + top + ",left=" + left;
  window.open(url,"NewWindow",params);
}

jQuery(".social-share.twitter").on("click", function() {
  let position = $(this).attr("data-position");
  let title = $(this).attr("data-title");
  let url = $(this).attr("data-url");
  let encodeUrl = encodeURIComponent(url);
  let tweet = "À l'Assemblée, " + position + " lors du scrutin « " + title + " ». Découvrez mon explication de vote sur Datan ! [hashtagDirectAN]";
  let output = "https://twitter.com/intent/tweet?url=" + encodeUrl + "&text=" + tweet;
  socialWindow(output);
});

jQuery(".social-share.facebook").on("click", function() {
    let url = $(this).attr("data-url");
    let encodeUrl = encodeURIComponent(url);
    var output = "https://www.facebook.com/sharer.php?u=" + url;
    socialWindow(output);
});
