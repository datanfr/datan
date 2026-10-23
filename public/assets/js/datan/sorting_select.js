var qsRegex;
var districtFilter = "*";
var groupFilter = "*";
var stateFilter;

// init Isotope
var $grid = $('.sorting').isotope({
  itemSelector: '.sorting-item',
  layoutMode: 'fitRows',
  filter: function(){
    var $this = $(this);
    var searchResult = qsRegex ? $this.text().match( qsRegex ) : true;
    var districtResult = districtFilter ? $this.is( districtFilter ) : true;
    var groupResult = groupFilter ? $this.is( groupFilter ) : true;
    var stateResult = stateFilter ? $this.is( stateFilter ) : true;
    return searchResult && groupResult && districtResult && stateResult;
  }
});

function districtChange() {
  var selectBox = document.getElementById("districtChange");
  districtFilter = selectBox.options[selectBox.selectedIndex].value;
  $grid.isotope();
}

function groupChange() {
  var selectBox = document.getElementById("groupChange");
  groupFilter = selectBox.options[selectBox.selectedIndex].value;
  $grid.isotope();
}

$('.stateChange').on( 'click', 'input', function() {
  stateFilter = this.value;
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
