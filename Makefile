vendor:
	composer update

check: vendor
	vendor/bin/phpunit

.PHONY: check
