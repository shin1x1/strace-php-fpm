.PHONY: init
init:
	docker compose up -d
	docker compose exec php-fpm cp -a .env.example .env
	docker compose exec php-fpm php artisan key:generate
	docker compose exec php-fpm composer install

.PHONY: clean
clean:
	-rm laravel/storage/logs/trace.txt
	docker compose down -v

