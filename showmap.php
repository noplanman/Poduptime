Show as: <a href="?mapview=true">Map</a> <a href="/">Simple Table</a>  <a href="?advancedview=true">Advanced Table</a>
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
<link rel="stylesheet" href="css/leaflet.css" />
<script src="js/leaflet.js"></script>
<script type="text/javascript" src="js/leaflet.markercluster.js"></script>
<div id="map"></div>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<script type="text/javascript">
var geoJsonData = {
"type": "FeatureCollection",
"features": [
<?php
include('db/config.php');
$dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
if (!$dbh) {
  die("Error in connection: " . pg_last_error());
}
$sql = "SELECT * FROM pods WHERE hidden <> 'yes'";
$result = pg_query($dbh, $sql);
if (!$result) {
  die("Error in SQL query: " . pg_last_error());
}
$numrows = pg_num_rows($result);
while ($row = pg_fetch_array($result)) {
  unset($feat);
  if ($row["service_facebook"] == "t") {$feat.= "<div id=\'facebook\' class=\'smlogo\'></div>";}
  if ($row["service_twitter"] == "t") {$feat.= "<div id=\'twitter\' class=\'smlogo\'></div>";}
  if ($row["service_tumblr"] == "t") {$feat.= "<div id=\'tumblr\' class=\'smlogo\'></div>";}
  if ($row["service_wordpress"] == "t") {$feat.= "<div id=\'wordpress\' class=\'smlogo\'></div>";}
  unset($signup);if ($row["signup"] == 1) {$signup = "yes";} else {$signup= "no";}
  $pod_name = htmlentities($row["name"], ENT_QUOTES);
echo <<<EOF
{ "type": "Feature", "id":"1", "properties":
{ "html":"{$pod_name}<br><a href=\'http://{$row["domain"]}\'>Visit</a>{$row["domain"]}<br> Open Signup: {$signup}<br> Users: {$row["active_users_halfyear"]}<br> Uptime: {$row["uptimelast7"]}%<br> Services:{$feat}" }, "geometry": { "type": "Point", "coordinates": [{$row["long"]},{$row["lat"]} ] } },
EOF;
}
?>
{ "type": "Feature", "id":"1", "properties": { "html":"" }, "geometry": { "type": "Point", "coordinates": [0,0 ] } }
]
};
var tiles = L.tileLayer('https://{s}.tiles.mapbox.com/v4/diasporg.l615e519/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoiZGlhc3BvcmciLCJhIjoibTVBaldtayJ9.HdGPBIFeZyNKKQqCmU11nA', {
maxZoom: 18,
attribution: "<a href='https://www.mapbox.com/about/maps/' target='_blank'>&copy; Mapbox &copy; OpenStreetMap</a> <a class='mapbox-improve-map' href='https://www.mapbox.com/map-feedback/' target='_blank'>Improve this map</a>"
});
var map = L.map('map', { zoom: 5, center: [<?php echo $lat; ?>, <?php echo $long; ?>] }).addLayer(tiles);
var markers = L.markerClusterGroup({maxClusterRadius: 2, animateAddingMarkers: true, iconCreateFunction: function(cluster) {return new L.DivIcon({ html: '<b class="icon">' + cluster.getChildCount() + '</b>', className: 'mycluster', iconSize: L.point(35, 35) });}});
var geoJsonLayer = L.geoJson(geoJsonData, {
onEachFeature: function (feature, layer) {
layer.bindPopup(feature.properties.html);
}
});
markers.addLayer(geoJsonLayer);
map.addLayer(markers);
</script>
