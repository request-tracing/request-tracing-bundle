.DEFAULT_GOAL:=help

.PHONY: dependencies
dependencies: ## Install the Composer dependencies
	composer install --no-interaction --ansi

.PHONY: test
test: ## Run the test suite
	vendor/bin/phpunit --testdox

.PHONY: coverage
coverage: ## Report the test coverage
	vendor/bin/phpunit --coverage-text

.PHONY: php-cs-fixer
php-cs-fixer: ## Fix coding standards violations
	vendor/bin/php-cs-fixer fix --no-interaction --allow-risky=yes --diff --verbose

.PHONY: php-cs-fixer-ci
php-cs-fixer-ci: ## Check coding standards without fixing them
	vendor/bin/php-cs-fixer fix --dry-run --no-interaction --allow-risky=yes --diff --verbose --stop-on-violation

.PHONY: phpstan
phpstan: ## Run static analysis
	vendor/bin/phpstan analyse --no-progress --memory-limit=512M

.PHONY: phpstan-ci
phpstan-ci: ## Run static analysis, annotating the diff with any findings
	vendor/bin/phpstan analyse --no-progress --memory-limit=512M --error-format=github

.PHONY: audit
audit: ## Check the dependencies for known security vulnerabilities
	composer audit --no-interaction --ansi

.PHONY: qa
qa: php-cs-fixer-ci phpstan test audit ## Run all quality assurance checks

# Based on https://suva.sh/posts/well-documented-makefiles/
.PHONY: help
help: ## Display this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n\nTargets:\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)
