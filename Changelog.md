#2+

## Podmins
* Language is detected based on your homepage, edit your homepage to non-en if that is what you use

# DB
* Store detectedlanguage 
* Migration needed see db/version.md

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
