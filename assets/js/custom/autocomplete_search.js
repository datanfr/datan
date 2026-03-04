// generic autocomplete helper, can attach to multiple inputs
function debounce(fn, delay) {
  var timer;
  return function() {
    var context = this;
    var args = arguments;
    clearTimeout(timer);
    timer = setTimeout(function() { fn.apply(context, args); }, delay);
  };
}

function initAutocomplete(options) {
  // options: {
  //   inputId, resultBlocId, resultListId,
  //   wrapperId (optional), moreLinkId (optional),
  //   type (optional), redirectPrefix (optional)
  // }
  var input = document.getElementById(options.inputId);
  if (!input) return;
  var bloc = document.getElementById(options.resultBlocId);
  var list = document.getElementById(options.resultListId);
  var wrapper = options.wrapperId ? document.getElementById(options.wrapperId) : null;
  var moreLink = options.moreLinkId ? document.getElementById(options.moreLinkId) : null;
  var currentFocus = -1;

  function close() {
    if (list) list.innerHTML = '';
    if (wrapper) wrapper.style.borderRadius = '8px';
    if (bloc) bloc.style.display = 'none';
    input.blur();
  }

  function addActive(x) {
    if (!x) return false;
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = x.length - 1;
    x[currentFocus].classList.add('active');
  }

  function removeActive(x) {
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove('active');
    }
  }

  function doSearch() {
    var query = input.value;
    if (query.length > 0) {
      // use origin-relative URL to avoid CORS issues
      var url = '/search_api?q=' + encodeURIComponent(query);
      if (options.type) url += '&type=' + encodeURIComponent(options.type);
      fetch(url)
        .then(function(resp) { return resp.json(); })
        .then(function(data) {
          if (bloc) bloc.style.display = 'block';
          if (list) list.innerHTML = '';
          currentFocus = -1;
          if (moreLink) moreLink.classList.remove('active');
          if (wrapper) {
            if (data.length > 0) {
              wrapper.style.borderBottomLeftRadius = '0px';
              wrapper.style.borderBottomRightRadius = '0px';
            } else {
              wrapper.style.borderRadius = '8px';
            }
          }
          data.forEach(function(result) {
            var a = document.createElement('a');
            a.innerHTML = result.text;
            a.className = result.source + ' no-decoration';
            a.href = get_base_url() + '/' + result.url;
            if (list) list.appendChild(a);
          });
        });
      if (moreLink && options.redirectPrefix) {
        moreLink.href = options.redirectPrefix + query;
      }
    } else {
      close();
    }
  }

  input.addEventListener('input', debounce(doSearch, 250));

  window.addEventListener('click', function(e) {
    if (e.target !== input) {
      close();
    }
  });

  window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      close();
    }
  });

  input.addEventListener('keydown', function(e) {
    var links = bloc ? bloc.getElementsByTagName('a') : [];
    var key = e.key;
    if (key === 'ArrowDown' || key === 'ArrowUp') {
      e.preventDefault();
      if (key === 'ArrowDown') {
        currentFocus++;
        addActive(links);
      } else {
        currentFocus--;
        addActive(links);
      }
    } else if (key === 'Enter') {
      e.preventDefault();
      if (currentFocus === -1) {
        if (options.redirectPrefix) {
          window.location.href = options.redirectPrefix + input.value;
        }
      } else if (links && links[currentFocus]) {
        links[currentFocus].click();
      }
    }
  });
}

// auto-init for the standard search box
document.addEventListener('DOMContentLoaded', function() {
  initAutocomplete({
    inputId: 'search',
    resultBlocId: 'search-results-bloc',
    resultListId: 'search-results-list',
    wrapperId: 'search-bloc',
    moreLinkId: 'more-results-link',
    redirectPrefix: 'recherche/'
  });
  // also initialise city search if present
  // determine appropriate result container IDs (some views reuse home markup)
  var cityBlocId = document.getElementById('city-search-results-bloc') ? 'city-search-results-bloc' : 'search-results-bloc';
  var cityListId = document.getElementById('city-search-results-list') ? 'city-search-results-list' : 'search-results-list';
  initAutocomplete({
    inputId: 'citySearch',
    resultBlocId: cityBlocId,
    resultListId: cityListId,
    wrapperId: 'city-search-bloc',
    type: 'ville'
  });
});