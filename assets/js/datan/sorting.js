var qsRegex;
var buttonFilter;

// init Isotope
var $grid = $('.sorting').isotope({
  itemSelector: '.sorting-item',
  layoutMode: 'fitRows',
  filter: function(){
    var $this = $(this);
    var searchResult = qsRegex ? $this.text().match( qsRegex ) : true;
    var buttonResult = buttonFilter ? $this.is( buttonFilter ) : true;
    return searchResult && buttonResult;
  }
});

// bind filter on radio button click
$('.filters').on( 'click', 'input', function() {
  // get filter value from input value
  //var filterValue = this.value;
  buttonFilter = this.value;
  $grid.isotope();
});

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
