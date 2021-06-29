var qsRegex;
var buttonFilter_district;
var buttonFilter_state;

// init Isotope
var $grid = $('.sorting').isotope({
  itemSelector: '.sorting-item',
  layoutMode: 'fitRows',
  filter: function(){
    var $this = $(this);
    var searchResult = qsRegex ? $this.text().match( qsRegex ) : true;
    var buttonResult_district = buttonFilter_district ? $this.is( buttonFilter_district ) : true;
    var buttonResult_state = buttonFilter_state ? $this.is( buttonFilter_state ) : true;
    return searchResult && buttonResult_district && buttonResult_state;
  }
});

// bind filter on DISTRICT radio button click
$('.filtersDistrict').on( 'click', 'input', function() {
  // get filter value from input value
  //var filterValue = this.value;
  buttonFilter_district = this.value;
  $grid.isotope();
});

$('.filtersState').on( 'click', 'input', function() {
  // get filter value from input value
  //var filterValue = this.value;
  buttonFilter_state = this.value;
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
