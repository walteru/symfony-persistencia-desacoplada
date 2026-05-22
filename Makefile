DOCKER_APP = persistencia-app

.DEFAULT_GOAL := help

help: ## Muestra esta ayuda
	@echo 'uso: make [target]'
	@echo
	@egrep '^(.+)\:\ ##\ (.+)' ${MAKEFILE_LIST} | column -t -c 2 -s ':#'

start: ## Levanta los contenedores
	docker compose up -d --remove-orphans
	@echo "App: http://localhost:8090"

stop: ## Detiene los contenedores
	docker compose stop

down: ## Baja contenedores y red
	docker compose down --remove-orphans

build: ## Construye las imágenes
	docker compose build

rebuild: ## Reconstruye desde cero y levanta
	docker compose down --remove-orphans && docker compose build --no-cache && docker compose up -d

logs: ## Logs en vivo
	docker compose logs -f

sh: ## Shell dentro del contenedor de la app
	docker compose exec app bash

composer: ## Ejecuta composer dentro del contenedor (ej: make composer c="require foo")
	docker compose exec app composer $(c)

console: ## Ejecuta bin/console (ej: make console c="cache:clear")
	docker compose exec app php bin/console $(c)

migrate: ## Aplica las migraciones de Doctrine
	docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

test: ## Corre la suite de tests (PHPUnit)
	docker compose exec app php bin/phpunit
