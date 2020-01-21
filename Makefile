start:
	php -S localhost:8080 -t public public/index.php

bootstrap-update:
	cp vendor/twbs/bootstrap/dist/css/bootstrap.min.css assets/css/
