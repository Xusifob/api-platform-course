composer-install: ## [Development] Runs composer install in the container to install backend dependencies
	docker-compose run --rm php composer install

composer-update: ## [Development] Runs composer install in the container to install backend dependencies
	docker-compose run --rm php composer update

update-schema: ## [Development] Updates the SQL schema
	docker-compose run --rm php php bin/console doctrine:schema:update --dump-sql --force --env=test

database-create: ## [Development] Updates the SQL schema
	docker-compose run --rm php php bin/console doctrine:database:create

load-fixtures: ## [Development] Loads fixtures into the database
	docker-compose run --rm php php bin/console doctrine:fixtures:load

run-command: ## [Development] Run a command inside docker
	docker-compose run --rm php php bin/console ${command}

phpunit: ## [Development] Run a command inside docker
	docker-compose run --rm php bin/phpunit --testdox

phpunit-now: ## [Development] Run a command inside docker
	docker-compose exec php bin/phpunit --testdox --group=now

create-migration: ## [Development] Generate a doctrine migration
	make run-command command='doctrine:migrations:diff'

run-migration: ## [Development] Run a doctrine migration
	make run-command command='doctrine:migrations:migrate --verbose --no-interaction'

migration-sync:
	make run-command command='doctrine:migrations:version --add --all'

clear-cache: ## [Development] Clears cache
	make run-command command='cache:clear'

composer-require: ## [Development] Install a new composer package
	docker-compose run --rm php composer require ${package}

composer-require-dev: ## [Development] Install a new composer package
	docker-compose run --rm php composer require --dev ${package}

shell: ## [Development] Gets a shell in the apache container
	docker-compose run --rm php sh


routes-dev: ## [Development] Lists all routes of the application
	DOCKER_COMPOSE_ENV=dev docker-compose run --rm php php bin/console debug:router | grep doc

