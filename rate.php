<?php

use RedBeanPHP\R;

($_domain = $_GET['domain'] ?? null) || die('domain not specified');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

define('PODUPTIME', microtime(true));

// Set up global DB connection.
R::setup("pgsql:host={$pghost};dbname={$pgdb}", $pguser, $pgpass, true);
R::testConnection() || die('Error in DB connection');
R::usePartialBeans(true);
?>
  <html>
  <head>
    <style type="text/css">
      #slider {
        margin: 10px;
        width: 250px;
        display: inline-block;
      }

      #rating {
        height: 35px;
        width: 35px;
      }
    </style>
    <script>
      $(document).ready(function () {
        $('#addrating').click(function () {
          $('#commentform').show('slow');
          $('.ratings').hide('slow');
        });
        $('#submitrating').click(function () {
          var domain = '<?php echo $_domain; ?>';
          $.ajax({
            type: 'POST',
            url: 'db/saverating.php',
            data: 'username=' + $('#username').val() + '&userurl=' + $('#userurl').val() + '&comment=' + $('#comment').val() + '&rating=' + $('#rating').val() + '&domain=' + domain,
            success: function (msg) {
              if (msg == 1) {
                $('#commentform').replaceWith('<h3>Your comment was saved, Thank You!</h3>');
                $('#submitrating').unbind('click');
              } else {
                $('#errortext').html(msg);
                $('#error').slideDown(633).delay(2500).slideUp(633);
              }
            }
          });
        });

        $('#slider').slider({
          animate: true, max: 10, min: 1, step: 1, value: 5, stop: function (event, ui) {
            var value = $('#slider').slider('option', 'value');
            $('#rating').prop('value', value)
          }
        });
      });
    </script>
  </head>

<div>
  <?php

  try {
    $ratings = R::findAll('ratingcomments', 'domain LIKE ? ORDER BY date_created DESC LIMIT 8', [$_domain]);
  } catch (\RedBeanPHP\RedException $e) {
    die('Error in SQL query: ' . $e->getMessage());
  }

  echo '<div class="container ratings"><div class="row"><div class="col col-10"><h3>Podupti.me ratings for ' . $_domain . ' pod</h3></div></div>';
  if (empty($ratings)) {
    echo '<b>This pod has no rating yet!</b>';
  } else {
    foreach ($ratings as $rating) {
      if ($rating['admin'] === '1') {
        echo 'Poduptime Approved Comment - User: <b>' . $rating['username'] . '</b> Url: <a href="' . $rating['userurl'] . '">' . $rating['userurl'] . '</a> Rating: <b>' . $rating['rating'] . '</b> <br>';
        echo '<i>' . $rating['comment'] . '</i><span class="label" title="id: ' . $rating['id'] . '" style="float:right;margin-right:115px;">' . $rating['date_created'] . '</span><hr>';
      } else {
        echo '<div class="m-1 rounded bg-light"><div class="row"><div class="col-10">Comment from: <b>' . $rating['username'] . '</b></div> <div class="col">Rating: ' . $rating['rating'] . '</div></div>';
        echo '<div class="row"><div class="col-10"><i>' . $rating['comment'] . '</i></div><div="col" title="id: ' . $rating['id'] . '">' . date('Y-m-d', strtotime($rating['date_created'])) . '</div></div>';
      }
    }
  }
  ?>
    <input id="addrating" class="btn primary" type="submit" value="Add a Rating">
    </div>
<div id="commentform" style="display:none">
  Would you like to add a comment?<br>
  <label>Your Name:<br><input id="username" name="username"></label><br>
  <label>Comment:<br><textarea id="comment" name="comment"></textarea></label><br>
  Rating (1-10 scale, 10 high)<br>
  <div id="slider"></div>
  <input class="disabled" disabled="" id="rating" name="rating" value=""><br>
  <input class="btn primary" id="submitrating" type="submit" value="Submit your Rating">
  <div class="alert-message warning" id="error" style="display:none">
    <span id="errortext">Some Error</span>
  </div>
</div>
<?php
