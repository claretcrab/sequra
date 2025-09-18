docker-compose up -d
bin/console doctrine:database:create
bin/console doctrine:database:create --env=test
bin/console doctrine:migrations:migrate
bin/console doctrine:migrations:migrate --env=test

bin/phpunit

bin/console app:import-merchants
bin/console app:import-orders (as pending)
