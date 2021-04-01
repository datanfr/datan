var qsRegex;
var selectedFilter;

// init Isotope
var $grid = $('.sorting').isotope({
  itemSelector: '.sorting-item',
  layoutMode: 'fitRows',
  filter: function(){
    var $this = $(this);
    var searchResult = qsRegex ? $this.text().match( qsRegex ) : true;
    var selectedResult = selectedFilter ? $this.is( selectedFilter ) : true;
    console.log(selectedFilter);
    return searchResult && selectedResult;
  }
});

function changeFilterFunc() {
  var selectBox = document.getElementById("selectFilter");
  selectedFilter = selectBox.options[selectBox.selectedIndex].value;
  //console.log(selectedFilter);
  $grid.isotope();
}

// use value of search field to filter
var $quicksearch = $('#quicksearch').keyup( debounce( function() {
  qsRegex = new RegExp( $quicksearch.val(), 'gi' );
  $grid.isotope();
}) );


// debounce so filtering doesn't happen every millisecond
function debounce( fn, threshold ) {
  var timeout;
  threshold = threshold || 100;
  return function debounced() {
    clearTimeout( timeout );
    var args = arguments;
    var _this = this;
    function delayed() {
      fn.apply( _this, args );
    }
    timeout = setTimeout( delayed, threshold );
  };
}
