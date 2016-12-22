<?php
//focus map to the users side of the globe
//Cloudflare country code pull
$country_code = $_SERVER['HTTP_CF_IPCOUNTRY'];

$csv = array_map('str_getcsv', file('db/country_latlon.csv'));
foreach ($csv as $cords) {
  if ($cords[0] === $country_code) {
    $lat  = $cords[1];
    $long = $cords[2];
  }
}
?>
<link rel="stylesheet" href="bower_components/leaflet/dist/leaflet.css"/>
<script src="bower_components/leaflet/dist/leaflet.js"></script>
<script type="text/javascript" src="bower_components/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
<div id="map"></div>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<script type="text/javascript">
  var geoJsonData = {
    'type': 'FeatureCollection',
    'features': [
      <?php
      require_once __DIR__ . '/config.php';

      $dbh = pg_connect("dbname=$pgdb user=$pguser password=$pgpass");
      $dbh || die('Error in connection: ' . pg_last_error());

      $sql = "SELECT * FROM pods WHERE hidden <> 'yes'";
      $result = pg_query($dbh, $sql);
      $result || die('Error in SQL query: ' . pg_last_error());

      $numrows = pg_num_rows($result);
      $i = 0;
      while ($row = pg_fetch_array($result)) {
        // If this isn't the first entry, put a comma to separate the entries.
        $i++ > 0 && print ',';

        $feat = '';
        $row['service_facebook'] === 't' && $feat .= '<div class="smlogo smlogo-facebook"></div>';
        $row['service_twitter'] === 't' && $feat .= '<div class="smlogo smlogo-twitter"></div>';
        $row['service_tumblr'] === 't' && $feat .= '<div class="smlogo smlogo-tumblr"></div>';
        $row['service_wordpress'] === 't' && $feat .= '<div class="smlogo smlogo-wordpress"></div>';
        $row['xmpp'] === 't' && $feat .= '<div class="smlogo smlogo-xmpp"><img src="/images/icon-xmpp.png" width="16" height="16" title="XMPP chat server" alt="XMPP chat server"></div>';

        $pod_name = htmlentities($row['name'], ENT_QUOTES);
        $scheme   = $row['secure'] === 'true' ? 'https://' : 'http://';
        $signup   = $row['signup'] === '1' ? 'yes' : 'no';
        echo <<<EOF
{
  'type': 'Feature',
  'id': '1',
  'properties' : {
    'html': '{$pod_name}<br><a href="{$scheme}{$row['domain']}">Visit</a><br> Open Signup: {$signup}<br> Users: {$row['active_users_halfyear']}<br> Uptime: {$row['uptimelast7']}%<br> Services:{$feat}'
  },
  'geometry': {
    'type': 'Point',
    'coordinates': [{$row['long']},{$row['lat']}]
  }
}
EOF;
      }
      ?>
    ]
  };
  var tiles = L.tileLayer('https://{s}.tiles.mapbox.com/v4/diasporg.l615e519/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoiZGlhc3BvcmciLCJhIjoibTVBaldtayJ9.HdGPBIFeZyNKKQqCmU11nA', {
    maxZoom: 18,
    attribution: '<a href="https://www.mapbox.com/about/maps/" target="_blank">&copy; Mapbox &copy; OpenStreetMap</a> <a class="mapbox-improve-map" href="https://www.mapbox.com/map-feedback/" target="_blank">Improve this map</a>'
  });
  var map = L.map('map', {zoom: 5, center: [<?php echo $lat; ?>, <?php echo $long; ?>]}).addLayer(tiles);
  var markers = L.markerClusterGroup({
    maxClusterRadius: 2, animateAddingMarkers: true, iconCreateFunction: function (cluster) {
      return new L.DivIcon({html: '<b class="icon">' + cluster.getChildCount() + '</b>', className: 'mycluster', iconSize: L.point(35, 35)});
    }
  });
  var geoJsonLayer = L.geoJson(geoJsonData, {
    onEachFeature: function (feature, layer) {
      layer.bindPopup(feature.properties.html);
    }
  });
  markers.addLayer(geoJsonLayer);
  map.addLayer(markers);
</script>
