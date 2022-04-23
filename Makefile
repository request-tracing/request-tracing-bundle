.DEFAULT_GOAL:=help

.PHONY: dependencies
dependencies:
	composer install --no-interaction --no-suggest --no-scripts --ansi

.PHONY: test
test:
	vendor/bin/phpunit --testdox --exclude-group=none --colors=always

.PHONY: qa
qa: php-cs-fixer-ci phpstan

.PHONY: php-cs-fixer
php-cs-fixer:
	vendor/bin/php-cs-fixer fix --no-interaction --allow-risky=yes --diff --verbose

.PHONY: php-cs-fixer-ci
php-cs-fixer-ci:
	vendor/bin/php-cs-fixer fix --dry-run --no-interaction --allow-risky=yes --diff --verbose --stop-on-violation

PHONY: phpstan
phpstan:
	vendor/bin/phpstan analyse --level=max src/

# Based on https://suva.sh/posts/well-documented-makefiles/
help:  ## Display this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n\nTargets:\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)
