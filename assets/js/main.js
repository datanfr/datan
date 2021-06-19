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
      $(this).removeClass("placeholder")
    }
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
  $('[data-toggle="tooltip"]').tooltip()
})

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
if ($('.carousel-cards').length > 0) {
  var $carousel = $('.carousel-cards').flickity({
    prevNextButtons: false,
    pageDots: false,
    freeScroll: true,
    contain: true
  });
  // Flickity instance
  var flkty = $carousel.data('flickity');
  // previous
  $('.button--previous').on('click', function () {
    $carousel.flickity('previous');
  });
  // next
  $('.button--next').on('click', function () {
    $carousel.flickity('next');
  });
}

/*
################
                no-img (votes controller)
################
*/

var cw = $('.no-img').width();
$('.no-img').css({ 'height': cw + 'px' });

/*
################
                async_home
################
*/

(() => {
  'use strict';
  // Page is loaded
  const objects = document.getElementsByClassName('async_home');
  Array.from(objects).map((item) => {
    // Start loading image
    const img = new Image();
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
      img.onload = () => {
        item.classList.remove('async_home');
        return item.nodeName === 'IMG' ?
          item.src = item.dataset.src :
          item.style.backgroundImage = `linear-gradient(130deg, rgba(0, 183, 148, 0.7) 0.65%, rgba(36, 107, 150, 0.7) 112%), url(${item.dataset.tablet})`
      };
    } else {
      // Once image is loaded replace the src of the HTML element
      img.src = item.dataset.src;
      img.onload = () => {
        item.classList.remove('async_home');
        return item.nodeName === 'IMG' ?
          item.src = item.dataset.src :
          item.style.backgroundImage = `linear-gradient(130deg, rgba(0, 183, 148, 0.7) 0.65%, rgba(36, 107, 150, 0.7) 112%), url(${item.dataset.src})`
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
    let $p = $el.parent();
    $up = $p.parent();
    $ps = $up.find("p:not('.read-more-button')");

    // measure how tall inside should be by adding together heights of all inside paragraphs (except read-more paragraph)
    $ps.each(function () {
      totalHeight += $(this).outerHeight();
      margin = parseInt($(this).css("margin-bottom"), 10);
      totalHeight = totalHeight + margin;
    });

    totalHeight = totalHeight - margin;

    $up
      .css({
        // Set height to prevent instant jumpdown when max height is removed
        "height": $up.height(),
        "max-height": 9999
      })
      .animate({
        "height": totalHeight
      });

    // fade out read-more
    $p.fadeOut();

    // prevent jump-down
    return false;

  });

});


/*
##########
NEWSLETTER
##########
*/
$('#newsletterForm').on('submit', (e) => {
  e.preventDefault();
  $.ajax({
    url: "api/newsletter/create_newsletter",
    type: "POST",
    data: $('#newsletterForm').serialize(),
    dataType: 'json',
    success: function (ac) {
      if (!ac) {
        $('#newsletterForm').hide();
        $('#modalFail').show();
      } else {
        $('#newsletterForm').hide();
        $('#modalSubscription').show();
      }
    },
    error: function (err) {
      console.log('err', err)
      $('#newsletterForm').hide();
      $('#modalFail').show();
    }
  });
  return true;
})


/*
##########
FAQ
##########
*/
$.expr[':'].icontains = function(a, i, m) {
  .indexOf(m[3].toUpperCase()) >= 0);
  return $(a).text().toUpperCase()
      .indexOf(m[3].toUpperCase()) >= 0;
};
$('#searchfaq').on('keyup', function(e){
  $(".card-question").hide();
  $(".card-question:icontains('" + $(this).val() + "')").show();
})