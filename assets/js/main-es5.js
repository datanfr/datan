"use strict";

/*
################
               FUNCTION IF LOCALHOST
################
*/
function get_base_url() {
  var localhost = 'http://localhost/datan';

  if (location.hostname === "localhost" || location.hostname === "127.0.0.1") {
    return localhost;
  } else {
    return 'https://datan.fr';
  }
}
/*
################
                A TRIER
################
*/


$(document).ready(function () {
  // popover
  $("[data-toggle=popover]").popover({
    placement: 'top',
    container: 'body',
    trigger: 'focus',
    html: true
  });
  $(".img-lazy").unveil(200, function () {
    this.onload = function () {
      $(this).removeClass("placeholder");
    };
  });
});
$(function () {
  // prevents jumping
  $("a.pop-me-over").on("click", function (e) {
    e.preventDefault();
    return true;
  });
  $(".pop-me-over").popover();
});
$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});
/*
################
               Button UP
################
*/

var btn = $('#btnup');
$(window).scroll(function () {
  if ($(window).scrollTop() > 300) {
    btn.addClass('show');
  } else {
    btn.removeClass('show');
  }
});
btn.on('click', function (e) {
  e.preventDefault();
  var distance = $(window).scrollTop();
  var speed = 2;
  var duration = $(window).scrollTop() / speed;
  $('html, body').animate({
    scrollTop: 0
  }, 2000, 'swing'); // for all browsers
});
/*
################
               Collapse button
################
*/

$(document).ready(function () {
  $("#collapseProximity").on("hide.bs.collapse", function () {
    $("#btn-ranking").html('Voir tout le classement');
  });
  $("#collapseProximity").on("show.bs.collapse", function () {
    $("#btn-ranking").html('Fermer');
  });
});
/*
################
               Flickity
################
*/
// init Flickity
// external js: flickity.pkgd.js

var carouselContainers = document.querySelectorAll('.carousel-container');

for (var i = 0; i < carouselContainers.length; i++) {
  var container = carouselContainers[i];
  initCarouselContainer(container);
}

function initCarouselContainer(container) {
  var carousel = container.querySelector('.carousel-cards');
  var flkty = new Flickity(carousel, {
    prevNextButtons: false,
    pageDots: false,
    freeScroll: true,
    contain: true
  });
  var previousButton = container.querySelector('.carousel--prev');
  previousButton.addEventListener('click', function () {
    flkty.previous();
  });
  var nextButton = container.querySelector('.carousel--next');
  nextButton.addEventListener('click', function () {
    flkty.next();
  });
}
/*
################
                no-img (votes controller)
################
*/


var cw = $('.no-img').width();
$('.no-img').css({
  'height': cw + 'px'
});
/*
################
                async_home
################
*/

(function () {
  'use strict'; // Page is loaded

  var objects = document.getElementsByClassName('async_home');
  Array.from(objects).map(function (item) {
    // Start loading image
    var img = new Image();

    if (window.matchMedia("(max-width: 575.98px)").matches) {
      /*img.src = item.dataset.mobile;
      img.onload = () => {
        item.classList.remove('async_background');
        return item.nodeName === 'IMG' ?
          item.src = item.dataset.src :
          item.style.backgroundImage = `linear-gradient(130deg, rgba(0, 183, 148, 0.7) 0.65%, rgba(36, 107, 150, 0.7) 112%), url(${item.dataset.mobile})`
      }; */
    } else if (window.matchMedia("(max-width: 970px)").matches) {
      // Once image is loaded replace the src of the HTML element
      img.src = item.dataset.tablet;

      img.onload = function () {
        item.classList.remove('async_home');
        return item.nodeName === 'IMG' ? item.src = item.dataset.src : item.style.backgroundImage = "linear-gradient(130deg, rgba(0, 183, 148, 0.7) 0.65%, rgba(36, 107, 150, 0.7) 112%), url(".concat(item.dataset.tablet, ")");
      };
    } else {
      // Once image is loaded replace the src of the HTML element
      img.src = item.dataset.src;

      img.onload = function () {
        item.classList.remove('async_home');
        return item.nodeName === 'IMG' ? item.src = item.dataset.src : item.style.backgroundImage = "linear-gradient(130deg, rgba(0, 183, 148, 0.7) 0.65%, rgba(36, 107, 150, 0.7) 112%), url(".concat(item.dataset.src, ")");
      };
    }
  });
})();
/*
################
                Read more
################
*/


$(function () {
  var $el, $ps, $up, totalHeight;
  $(".read-more-container .btn").click(function () {
    // IE 7 doesn't even get this far. I didn't feel like dicking with it.
    totalHeight = 0;
    var margin = 0;
    $el = $(this);
    var $p = $el.parent();
    $up = $p.parent();
    $ps = $up.find("p:not('.read-more-button')"); // measure how tall inside should be by adding together heights of all inside paragraphs (except read-more paragraph)

    $ps.each(function () {
      totalHeight += $(this).outerHeight();
      margin = parseInt($(this).css("margin-bottom"), 10);
      totalHeight = totalHeight + margin;
    });
    totalHeight = totalHeight - margin;
    $up.css({
      // Set height to prevent instant jumpdown when max height is removed
      "height": $up.height(),
      "max-height": 9999
    }).animate({
      "height": totalHeight
    }); // fade out read-more

    $p.fadeOut(); // prevent jump-down

    return false;
  });
});
/*
##########
NEWSLETTER (MODAL)
##########
*/
// Neweletter in header

$('#newsletterForm').on('submit', function (e) {
  e.preventDefault();
  $.ajax({
    url: get_base_url() + "/api/newsletter/create_newsletter",
    type: "POST",
    data: $('#newsletterForm').serialize(),
    dataType: 'json',
    success: function success(ac) {
      if (!ac) {
        $('#newsletterForm').hide();
        $('#modalFail').show();
      } else {
        $('#newsletterForm').hide();
        $('#modalSubscription').show();
      }
    },
    error: function error(err) {
      console.log('err', err);
      $('#newsletterForm').hide();
      $('#modalFail').show();
    }
  });
  return true;
}); // Newsletter on datan.fr/newsletter page

$('#newsletterPage').on('submit', function (e) {
  e.preventDefault();
  $.ajax({
    url: get_base_url() + "/api/newsletter/create_newsletter",
    type: "POST",
    data: $('#newsletterPage').serialize(),
    dataType: 'json',
    success: function success(ac) {
      if (!ac) {
        $('#newsletterFailed').removeClass("d-none");
      } else {
        $('#newsletterSuccess').removeClass("d-none");
      }
    },
    error: function error(err) {
      console.log('err', err);
      $('#newsletterFailed').removeClass("d-none");
    }
  });
  return true;
});
/*
##########
VOTE DATAN REQUESTED (MODAL)
##########
*/

$('#voteDatanRequestedForm').on('submit', function (e) {
  e.preventDefault();
  $.ajax({
    url: get_base_url() + "/api/votes/request_vote_datan",
    type: "POST",
    data: $('#voteDatanRequestedForm').serialize(),
    dataType: 'json',
    success: function success(ac) {
      if (!ac) {
        $('#voteDatanRequestedForm').hide();
        $('#fail').show();
      } else {
        $('#voteDatanRequestedForm').hide();
        $('#success').show();
      }
    },
    error: function error(err) {
      console.log('err', err);
      $('#voteDatanRequestedForm').hide();
      $('#fail').show();
    }
  });
  return true;
});
/*
##########
FAQ
##########
*/

$('#searchfaq').on('keyup', function (e) {
  $(".card-question").hide();
  var array = $(this).val().split(" ");
  var match = "(?:" + array.join(".*") + "|";
  array.reverse();
  match += array.join(".*") + ")";
  var regex = new RegExp(match, 'i');
  $(".card-question").filter(function () {
    return regex.test($(this).text());
  }).show();
});
/*
##########
Social media buttons
##########
*/
// Source: https://css-tricks.com/simple-social-sharing-links/

setShareLinks();

function socialWindow(url) {
  var left = (screen.width - 570) / 2;
  var top = (screen.height - 570) / 2;
  var params = "menubar=no,toolbar=no,status=no,width=570,height=570,top=" + top + ",left=" + left;
  window.open(url, "NewWindow", params);
}

function setShareLinks() {
  var pageUrl = encodeURIComponent(document.URL);
  var tweet = encodeURIComponent(jQuery("meta[property='og:description']").attr("content"));
  var whatsappMessage = "Je viens de découvrir un nouveau vote de l'Assemblée nationale sur Datan ! Découvre le aussi : " + document.URL;
  jQuery(".social-share.facebook").on("click", function () {
    url = "https://www.facebook.com/sharer.php?u=" + pageUrl;
    socialWindow(url);
  });
  jQuery(".social-share.twitter").on("click", function () {
    url = "https://twitter.com/intent/tweet?url=" + pageUrl + "&text=" + tweet;
    socialWindow(url);
  }); // REVOIR LINKEDIN

  jQuery(".social-share.linkedin").on("click", function () {
    url = "https://www.linkedin.com/sharing/share-offsite/?url=" + pageUrl;
    socialWindow(url);
  }); // Need to be tested prod

  jQuery(".social-share.whatsapp").on("click", function () {
    url = "whatsapp://send?text=" + whatsappMessage;
    socialWindow(url);
  });
}
//# sourceMappingURL=main-es5.js.map
