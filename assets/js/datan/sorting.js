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

// bind filter on radio click
$('#filter').on( 'click', 'input', function() {
  // get filter value from input value
  //var filterValue = this.value;
  buttonFilter = this.value;
  $grid.isotope();
});

// bind filter on button click
$('#filter').on( 'click', 'button', function() {
  // get filter value from input value
  //var filterValue = this.value;
  buttonFilter = this.value;
  $grid.isotope();
});

/// --- Sorting multiple --- ///
$(".badge-field").click(function(){
  $(this).addClass("is-selected");
  $(".badge-field").not(this).removeClass("is-selected").addClass("not-selected");
})
$('#all-categories').click(function(){
  $(".badge-field").removeClass("not-selected").addClass("is-selected");
})

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
