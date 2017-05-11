SHELL := /bin/bash

###> test scripts ###
tests:
	testing/run_test_suite.sh
.PHONY: tests

cs-check:
	testing/run_cs_check.sh
.PHONY: cs-check

dependency-check:
	testing/run_dependency_check.sh
.PHONY: dependency-check

dependency-update:
	# Loop through all directories containing "composer.json".
	# Update composer if a sample has a new major version that matches our requirements.
	find * -name "phpunit.xml*" -not -path '*/vendor/*' -exec dirname {} \; | while read DIR; do \
		pushd $$DIR; \
		composer install; \
		if composer outdated --direct -m | grep -q 'google/' ; then \
		    composer update; \
		fi; \
		popd; \
	done;
.PHONY: update-dependencies
