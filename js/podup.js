function get_date(timestamp) {
  var date = new Date(timestamp * 1000);
  return [date.getMonth()+1,date.getDate(), date.getFullYear()].join('/');
}
function map() {
  $('#map').empty().show();
  $('#results').hide();
  $('#add').hide();
  var map = new OpenLayers.Map('map');
  map.addLayer(new OpenLayers.Layer.OSM());
  map.addControl(new OpenLayers.Control.LayerSwitcher());
  var layer = new OpenLayers.Layer.GeoRSS("Diaspora Pods", "/api.php?key=4r45tg&format=georss");
  //map.popupSize(2,2);
  map.addLayer(layer);
  map.zoomTo(2);
}
function nomap() {
  $('#results').show();
  $('#map').empty().hide();
  $('#add').show();
}
$(document).ready(function(){
$.facebox.settings.closeImage = 'http://c807316.r16.cf2.rackcdn.com/closelabel.png'
$.facebox.settings.loadingImage = 'http://c807316.r16.cf2.rackcdn.com/loading.gif'
  $('a[rel*=facebox]').facebox()
  $('.tipsy').tipsy();
  $('#add').click(function() {
    $('#howto').show('slow'); $('#add').hide('slow');$('#results').hide('slow');
  });
  $("#myTable").tablesorter( {sortList: [[1,1], [2,1]]} );
  $('#add').delay(8000).fadeIn(2000);
  $('#others').delay(8000).fadeIn(2000);
  //$('#buttonsy').delay(5550).slideDown(3330);
  //$('#title').delay(5000).slideUp(2333);
  $( ".utc-timestamp" ).each(function() {
    $( this ).text( get_date( $( this ).text() ) );
  });
});

