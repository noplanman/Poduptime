<?php
//focus map to the users side of the globe
// Cloudflare country code pull.
$country_code = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';

$csv = array_map('str_getcsv', file('db/country_latlon.csv'));
foreach ($csv as $cords) {
  if ($cords[0] === $country_code) {
    $lat  = $cords[1];
    $long = $cords[2];
  } else {
    $lat = 31;
    $long = -99;
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

      $sql = "SELECT domain,signup,name,lat,long,uptime_alltime,active_users_halfyear,service_facebook,service_twitter,service_tumblr,service_wordpress,service_xmpp FROM pods WHERE NOT hidden";
      $result = pg_query($dbh, $sql);
      $result || die('Error in SQL query: ' . pg_last_error());

      $numrows = pg_num_rows($result);
      $i = 0;
      while ($row = pg_fetch_array($result)) {
        // If this isn't the first entry, put a comma to separate the entries.
        $i++ > 0 && print ',';

        $feat = '';
        $row['service_facebook'] === 't' && $feat .= '<div class="smlogo smlogo-facebook" title="Publish to Facebook"></div>';
        $row['service_twitter'] === 't' && $feat .= '<div class="smlogo smlogo-twitter" title="Publish to Twitter"></div>';
        $row['service_tumblr'] === 't' && $feat .= '<div class="smlogo smlogo-tumblr" title="Publish to Tumblr"></div>';
        $row['service_wordpress'] === 't' && $feat .= '<div class="smlogo smlogo-wordpress" title="Publish to WordPress"></div>';
        $row['service_xmpp'] === 't' && $feat .= '<div class="smlogo smlogo-xmpp"><img src="/images/icon-xmpp.png" width="16" height="16" title="XMPP chat server" alt="XMPP chat server"></div>';

        $pod_name = htmlentities($row['name'], ENT_QUOTES);
        $signup   = $row['signup'] === 't' ? 'yes' : 'no';
        echo <<<EOF
{
  'type': 'Feature',
  'id': '1',
  'properties' : {
    'html': '{$pod_name}<br><a href="/go.php?domain={$row['domain']}">Visit</a><br> Open Signup: {$signup}<br> Users: {$row['active_users_halfyear']}<br> Uptime: {$row['uptime_alltime']}%<br> Services:{$feat}'
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
  var map = L.map('map', {zoom: 3, center: [<?php echo $lat; ?>, <?php echo $long; ?>]}).addLayer(tiles);
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
