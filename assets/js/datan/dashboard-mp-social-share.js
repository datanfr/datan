function socialWindow(url) {
  var left = (screen.width - 570) / 2;
  var top = (screen.height - 570) / 2;
  var params = "menubar=no,toolbar=no,status=no,width=570,height=570,top=" + top + ",left=" + left;
  window.open(url,"NewWindow",params);
}
var pageUrl = encodeURIComponent(document.URL);
//var tweet = encodeURIComponent(jQuery("meta[property='og:description']").attr("content"));
var tweet = "A l'Assemblée, j'ai voté [pour/contre] le scrutin : « [title_scrutin] ». Découvrez mon explication de vote sur ma page Datan ! #DirectAN [URL]";
var whatsappMessage = "Je viens de découvrir un nouveau vote de l'Assemblée nationale sur Datan ! Découvre le aussi : " + document.URL;

jQuery(".social-share.facebook").on("click", function() {
    var url = "https://www.facebook.com/sharer.php?u=" + pageUrl;
    socialWindow(url);
});

jQuery(".social-share.twitter").on("click", function() {
    var url = "https://twitter.com/intent/tweet?url=" + pageUrl + "&text=" + tweet;
    socialWindow(url);
});

jQuery(".social-share.linkedin").on("click", function() {
    var url = "https://www.linkedin.com/sharing/share-offsite/?url=" + pageUrl;
    socialWindow(url);
})

jQuery(".social-share.whatsapp").on("click", function() {
    var url = "whatsapp://send?text=" + whatsappMessage;
    socialWindow(url);
})
