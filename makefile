start:
	docker compose up -d
	docker exec php symfony serve --no-tls
stop:
	docker compose down
console:
	docker exec -it php bash
init:
	docker compose up -d --build
	docker exec php php bin/console doctrine:database:create
	docker exec php php bin/console make:migration
	docker exec php php bin/console doctrine:migrations:migrate
	docker exec php php bin/console app:create-admin

