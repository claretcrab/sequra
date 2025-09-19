
Requirements: https://symfony.com/doc/current/setup.html#technical-requirements

# Installation
docker-compose up -d
php bin/console doctrine:database:create
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate
php bin/console doctrine:migrations:migrate --env=test

# Run tests
php bin/phpunit

# Run application commands
php bin/console app:import-merchants
php APP_DEBUG=0 bin/console app:import-orders
php bin/console app:calculate-disbursements 1200 (for first import)
