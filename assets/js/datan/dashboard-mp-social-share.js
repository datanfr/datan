function socialWindow(url) {
  var left = (screen.width - 570) / 2;
  var top = (screen.height - 570) / 2;
  var params = "menubar=no,toolbar=no,status=no,width=570,height=570,top=" + top + ",left=" + left;
  window.open(url,"NewWindow",params);
}

var pageUrl = encodeURIComponent(document.URL);


jQuery(".social-share.twitter").on("click", function() {
  // Get values from the vote
  let legislature = $(this).attr("data-legislature");
  let voteNumero = $(this).attr("data-voteNumero");
  let position = $(this).attr("data-position");
  let title = $(this).attr("data-title");
  let tweet = "À l'Assemblée, " + position + " lors du scrutin « " + title + " ». Découvrez mon explication de vote sur Datan ! [hashtagDirectAN]";

  let url = "https://twitter.com/intent/tweet?url=" + pageUrl + "&text=" + tweet;
  socialWindow(url);
});
