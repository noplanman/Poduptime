# 2.3.0

## Podmins
* Can no longer access db/pull.php to test their pod, they can however get to a debug screen from the edit pod area
* Language is detected based on your homepage, edit your homepage to non-en if that is what you use
* Edit will send to email on file and be less delay, runner of site does not really have anyway to verify email address

## DB
* Add development and release dates to masterversions table https://github.com/diasporg/Poduptime/issues/143
* Store full country name, store days monitored each pod https://github.com/diasporg/Poduptime/issues/150
* Store detectedlanguage https://github.com/diasporg/Poduptime/issues/144
* DB migrations see db/version.md
* rename table rating_comments to ratingcomments for redbean support https://github.com/diasporg/Poduptime/issues/146
* Default new pods to UP to be checked
* Remove unused hidden and secure columns https://github.com/diasporg/Poduptime/issues/141 https://github.com/diasporg/Poduptime/issues/140

## Cleanup
* Use the git API for release versions, check development releases on pods https://github.com/diasporg/Poduptime/issues/143
* Forbid access to files that should be cli only https://github.com/diasporg/Poduptime/issues/152
* Move from bower to yarn for packages https://bower.io/blog/2017/how-to-migrate-away-from-bower/
* Move to PHP 7.2 with declare(strict_types=1);
* Move to Eslint compliance https://eslint.org/docs/rules/
* Move to PSR-2 compliance https://www.php-fig.org/psr/psr-2/
* NOTE config.php.example change to full paths for 2 items!

## End Users
* Show version and update in full view cleaner https://github.com/diasporg/Poduptime/issues/143
* Filter and search on the columns of data https://github.com/diasporg/Poduptime/issues/147
* Paginate the results so they fit per page https://github.com/diasporg/Poduptime/issues/147
* Show time as human readable everywhere https://github.com/diasporg/Poduptime/issues/150

# 2.2.0

## Podmins
* Can now pause/unpause or delete from podmin area

## End Users
* go.php auto select picks a more stable pod than before
* Graph on user growth on the network
* Make map prettier
* Use lines on tables to make them more readable

## Cleanup
* Don't delete dead pods, keep them and data for history hide them for users
* Put daily tasks in the pull.sh and run each day
* Fix ipv6

## DB
* Add monthly stats table
* Update status to 1-5 rather than text
* Two migrations for this version update see db/version.md
