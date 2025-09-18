docker-compose up -d
bin/console doctrine:database:create
bin/console doctrine:database:create --env=test
bin/console doctrine:migrations:migrate
bin/console doctrine:migrations:migrate --env=test

bin/phpunit

bin/console app:import-merchants
bin/console app:import-orders (change APP_DEBUG to 0 in .env.dev)
bin/console app:calculate-disbursements
