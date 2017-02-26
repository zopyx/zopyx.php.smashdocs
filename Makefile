install:
	php composer.phar install
	composer update

test:
	vendor/bin/phpunit --bootstrap api.php testsuite.php

test-debug:
	SMASHDOCS_DEBUG=1 vendor/bin/phpunit --bootstrap api.php testsuite.php
