# 2x+

## Podmins
* Can no longer access db/pull.php to test their pod, they can however get to a debug screen from the edit pod area

## DB
* Add development and release dates to masterversions table https://github.com/diasporg/Poduptime/issues/143

## Cleanup
* Use the git API for release versions, check development releases on pods https://github.com/diasporg/Poduptime/issues/143
* Forbid access to files that should be cli only https://github.com/diasporg/Poduptime/issues/152
* Move from bower to yarn for packages

## End Users
* Show version and update in full view cleaner https://github.com/diasporg/Poduptime/issues/143
* Edit will send to email on file and be less delay, runner of site does not really have anyway to verify email address
* Filter and search on the columns of data
* Paginate the results so they fit per page

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
