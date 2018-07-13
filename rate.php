<?php

/**
 * Popup modal for pod rating.
 */

declare(strict_types=1);

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
    <script>
        $(document).ready(function () {
            $('#addrating').click(function () {
                $('.ratings').hide('fast');
                $('#commentform').show('slow');
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
        });
    </script>
</head>
<body>
<div id="commentform" class="container" style="display:none">
    Would you like to add a comment?<br>
    <label>Your Name:<br><input id="username" name="username"></label><br>
    <label>Comment:<br><textarea id="comment" name="comment"></textarea></label><br>
    <label>Rating (1-10 scale, 10 high):<br><input id="rating" name="rating" type="number" min="1" max="10" step="1"></label><br>
    <input class="btn primary" id="submitrating" type="submit" value="Submit your Rating">
    <div class="alert-message warning" id="error" style="display:none">
        <span id="errortext">Some Error</span>
    </div>
</div>
<div>
    <?php

    try {
        $ratings = R::findAll('ratingcomments', 'domain LIKE ? ORDER BY date_created DESC LIMIT 8', [$_domain]);
    } catch (\RedBeanPHP\RedException $e) {
        die('Error in SQL query: ' . $e->getMessage());
    }

    echo '<div class="container ratings"><div class="row"><div class="col col-10"><b>Ratings for ' . $_domain . '</b></div></div>';
    if (empty($ratings)) {
        echo '<b>This pod has no rating yet!</b>';
    } else {
        foreach ($ratings as $rating) {
            echo '<div class="m-1 rounded"><div class="row  bg-secondary"><div class="col-10">Comment from: <b>' . $rating['username'] . '</b></div> <div class="col text-right">Rating: ' . $rating['rating'] . '</div></div>';
            echo '<div class="row"><div class="col-10"><i>' . $rating['comment'] . '</i></div><div class="col text-muted text-right" title="id: ' . $rating['id'] . '">' . date('Y-m-d', strtotime($rating['date_created'])) . '</div></div></div>';
        }
    }
    ?>
    <input id="addrating" class="btn primary" type="submit" value="Add a Rating">
</div>
</body>
</html>
