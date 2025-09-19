
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
APP_DEBUG=0 php bin/console app:import-orders
APP_DEBUG=0 php bin/console app:calculate-disbursements 1200 (for first import)
php bin/console app:calculate-disbursements 1 (for the daily cron job)
php bin/console app:generate-report
