Show as: <a href="?mapview=true">Map</a> | <a href="/">Simple Table</a> | <a href="?advancedview=true">Advanced Table</a>
<div id="map" style="height:600px;"></div>
<?php
$country_code = $_SERVER["HTTP_CF_IPCOUNTRY"];
$csv = array_map('str_getcsv', file('db/country_latlon.csv'));
foreach ($csv as $cords) {
if ($cords[0] == $country_code) {
$lat = $cords[1];
$long = $cords[2];
}
}
?>
    <script src="http://www.openlayers.org/api/OpenLayers.js"></script>
    <script src="http://www.openstreetmap.org/openlayers/OpenStreetMap.js"></script>
<script>
  var map = new OpenLayers.Map('map');
  map.addLayer(new OpenLayers.Layer.OSM());
  map.addControl(new OpenLayers.Control.LayerSwitcher());
  var layer = new OpenLayers.Layer.GeoRSS("Diaspora Pods", "/api.php?key=4r45tg&format=georss");
  map.addLayer(layer);
  map.setCenter(new OpenLayers.LonLat(<?php echo $long;?>,<?php echo $lat?>).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject()),4);
</script>
