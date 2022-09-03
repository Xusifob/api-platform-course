include Makefile.dev
include Makefile.test

shell: ## [Development] Gets a shell in the apache container
	docker-compose exec php bash

routes-dev: ## [Development] Lists all routes of the application
	DOCKER_COMPOSE_ENV=dev docker-compose run --rm php php bin/console debug:router

