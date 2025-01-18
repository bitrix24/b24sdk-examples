# Long-running ETL-tasks with batch api-calls

1. Open shell and go to current folder
2. Install dependencies via composer

```shell
composer install
```

3. Call in shell

```shell
php etl-cli-example.php --webhook=INSERT_YOUR_WEBHOOK_HERE
```

4. Have fun!

## Additional dependencies in example

- [monolog](https://github.com/Seldaek/monolog) - PSR-3 compatible logger
- [symfony/console](https://symfony.com/doc/current/components/console.html) - component for create beautiful and
  testable command line interfaces
- [symfony/filesystem](https://symfony.com/doc/current/components/filesystem.html) - platform-independent utilities for
  filesystem operations and for file/directory paths manipulation
- [league/csv](https://csv.thephpleague.com/9.0/) - simple library to ease CSV document loading as well as writing,
  selecting and converting CSV records.
- [fakerphp/faker](https://fakerphp.org/) - PHP library that generates fake data for you