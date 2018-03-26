<?php

use RedBeanPHP\R;

defined('PODUPTIME') || die();

try {
  $pods = R::getAll("
    SELECT domain, signup, name, lat, long, softwarename, uptime_alltime, active_users_halfyear, service_facebook, service_twitter, service_tumblr, service_wordpress, service_xmpp
    FROM pods
    WHERE NOT hidden
      AND lat != ''
      AND long != ''
      AND status < ?
  ", [PodStatus::Recheck]);
} catch (\RedBeanPHP\RedException $e) {
  die('Error in SQL query: ' . $e->getMessage());
}

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
<script type="text/javascript">
  var geoJsonData = {
    'type': 'FeatureCollection',
    'features': [
      <?php

      $i = 0;
      foreach ($pods as $pod) {
        // If this isn't the first entry, put a comma to separate the entries.
        $i++ > 0 && print ',';

        $feat = '';
        $pod['service_facebook'] && $feat .= '<div class="smlogo smlogo-facebook" title="Publish to Facebook"></div>';
        $pod['service_twitter'] && $feat .= '<div class="smlogo smlogo-twitter" title="Publish to Twitter"></div>';
        $pod['service_tumblr'] && $feat .= '<div class="smlogo smlogo-tumblr" title="Publish to Tumblr"></div>';
        $pod['service_wordpress'] && $feat .= '<div class="smlogo smlogo-wordpress" title="Publish to WordPress"></div>';
        $pod['service_xmpp'] && $feat .= '<div class="smlogo smlogo-xmpp"><img src="/images/icon-xmpp.png" width="16" height="16" title="XMPP chat server" alt="XMPP chat server"></div>';

        $pod_name = htmlentities($pod['name'], ENT_QUOTES);
        $signup = $pod['signup'] ? 'yes' : 'no';
        echo <<<EOF
{
  'type': 'Feature',
  'id': '1',
  'properties' : {
    'html': '<a href="/go.php?domain={$pod['domain']}">{$pod_name}</a><br>Software: {$pod['softwarename']}<br> Open Signup: {$signup}<br> Users: {$pod['active_users_halfyear']}<br> Uptime: {$pod['uptime_alltime']}%<br> Services:{$feat}'
  },
  'geometry': {
    'type': 'Point',
    'coordinates': [{$pod['long']},{$pod['lat']}]
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
