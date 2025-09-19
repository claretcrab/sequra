
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

# Result
```
+------+-------------------------+-------------------------------+----------------------+--------------------------------+--------------------------------+
| Year | Number of disbursements | Amount disbursed to merchants | Amount of order fees | Number of monthly fees charged | Amount of monthly fees charged |
+------+-------------------------+-------------------------------+----------------------+--------------------------------+--------------------------------+
| 2022 | 1532                    | 37751723 €                    | 338118.88 €          |                                |                                |
| 2023 | 5853                    | 107913515 €                   | 969591.22 €          |                                |                                |
+------+-------------------------+-------------------------------+----------------------+--------------------------------+--------------------------------+
```
