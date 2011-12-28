
sh -e /etc/init.d/xvfb start
export DISPLAY=:99.0 && firefox -CreateProfile ./phpunit/ff.profile
wget http://selenium.googlecode.com/files/selenium-server-standalone-2.15.0.jar
export DISPLAY=:99.0 && sudo java -jar selenium-server-standalone-2.15.0.jar -firefoxProfileTemplate "./phpunit/ff.profile/"&
pyrus install phpunit/PHPUnit_Selenium
phpenv rehash
