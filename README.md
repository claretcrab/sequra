
Requirements: https://symfony.com/doc/7.3/setup.html#technical-requirements

# Installation
```
docker-compose up -d
composer install
php bin/console doctrine:database:create
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate
php bin/console doctrine:migrations:migrate --env=test
```

# Run tests
```
php bin/phpunit
```

# Run application commands
```
php bin/console app:import-merchants
php bin/console app:import-orders --no-debug
php bin/console app:calculate-disbursements 1200 --no-debug (for first import)
php bin/console app:calculate-disbursements 1 (for the daily cron job)
php bin/console app:calculate-monthly-minimum-fees 40 (for the first import)
php bin/console app:calculate-monthly-minimum-fees 1 (for the monthly cron job)
php bin/console app:generate-reports
```

# Result
```
+------+-------------------------+-------------------------------+----------------------+
| Year | Number of disbursements | Amount disbursed to merchants | Amount of order fees |
+------+-------------------------+-------------------------------+----------------------+
| 2022 | 1547                    | 37.751.723,00 €               | 338.118,88 €         |
| 2023 | 10363                   | 189.137.034,00 €              | 1.699.264,40 €       |
+------+-------------------------+-------------------------------+----------------------+
+------+--------------------------------+--------------------------------+
| Year | Number of monthly fees charged | Amount of monthly fees charged |
+------+--------------------------------+--------------------------------+
| 2022 | 14                             | 375,26 €                       |
| 2023 | 148                            | 3.506,51 €                     |
+------+--------------------------------+--------------------------------+

```

# Explanation
Goal https://sequra.github.io/backend-challenge/

- The application is built with Symfony 7.3 and uses Doctrine Dbal for database interactions.
- The application imports merchants and orders from CSV files, calculates daily and weekly disbursements, monthly minimum fees and generates a report.
- The application is structured in a modular way
  - Domain layer: contains the business logic and domain entities.
  - Infrastructure layer: contains the database interactions and cli commands.
  - Application layer: contains the use cases and application services.
- The application includes some unit and integration tests to ensure correctness.
- The application is designed to be run as a set of CLI commands, which can be scheduled with cron jobs for regular execution.
- Orders are just disbursed once, so if you run the import command multiple times, it won't create duplicate disbursements. Once disbursed, the disbursed status is changed and referenced to a disbursement.
- Same with monthly minimum fees, they are charged once per merchant and month.
- Added .git folder, so you can see the commit history. (Keep in mind I didn't write meaningful commit messages, I usually squash commits before merging to main branch)

# TODOs
- Dockerize the main app
- Add more unit and integration tests
  - Create fixtures for tests
- Add more value objects
- Add validations (e.g. email format)
- Add more error handling and Domain exceptions
- Add indexes to the database tables
- CQRS with symfony messenger
  - Async calculations with retries
- CI/CD with GitHub Actions
- ...

# AI tools
Just used GitHub Copilot to auto-complete some parts of the code and GitHub Copilot Chat to help with some errors and optimizations.
