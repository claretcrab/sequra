
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
php bin/console app:import-orders --no-debug
php bin/console app:calculate-disbursements 1200 --no-debug (for first import)
php bin/console app:calculate-disbursements 1 (for the daily cron job)
php bin/console app:calculate-monthly-minimum-fees 40 (for the first import)
php bin/console app:calculate-monthly-minimum-fees 1 (for the monthly cron job)
php bin/console app:generate-report

# Result
```
+------+-------------------------+-------------------------------+----------------------+
| Year | Number of disbursements | Amount disbursed to merchants | Amount of order fees |
+------+-------------------------+-------------------------------+----------------------+
| 2022 | 1532                    | 37751723 €                    | 338118.88 €          |
| 2023 | 5853                    | 107913515 €                   | 969591.22 €          |
+------+-------------------------+-------------------------------+----------------------+
+------+--------------------------------+--------------------------------+
| Year | Number of monthly fees charged | Amount of monthly fees charged |
+------+--------------------------------+--------------------------------+
| 2022 | 13                             | 346.21 €                       |
| 2023 | 77                             | 1830.6 €                       |
+------+--------------------------------+--------------------------------+
```

# TODOs
- Dockerize the main app
- Add more unit and integration tests
- Add more value objects
- Add validations (e.g. email format)
- Add more error handling
- Add indexes to the database tables

# AI tools
Just used GitHub Copilot to auto-complete some parts of the code and GitHub Copilot Chat to help with some errors and optimizations.
