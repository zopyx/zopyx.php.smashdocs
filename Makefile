install:
	php composer.phar install


test:
	vendor/bin/phpunit --bootstrap api.php testsuite.php
