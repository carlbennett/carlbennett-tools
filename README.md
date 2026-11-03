# carlbennett-tools
[![ci-cd](https://github.com/carlbennett/carlbennett-tools/actions/workflows/ci-cd.yml/badge.svg)](https://github.com/carlbennett/carlbennett-tools/actions/workflows/ci-cd.yml)

Carl's Tools (carlbennett-tools) is a collection of web tools/utilities authored by [@carlbennett](https://github.com/carlbennett).

## Requirements

- PHP 8.4 or newer (64-bit), with the `gmp`, `json`, `mbstring`, `pdo` and `pdo_mysql` extensions.
- [Composer](https://getcomposer.org/).
- MariaDB, for users, pastes and Plex users.

## Development

```sh
composer install
cp etc/config.sample.json etc/config.json
```

Run the checks that CI runs:

```sh
lib/bin/phpunit           # tests; fails on PHP deprecations, warnings and notices
lib/bin/phpstan analyse   # static analysis at level 5, against PHP 8.4
```

The ci-cd workflow lints every PHP file (failing on compile-time deprecations), runs PHPStan, and
runs the tests on PHP 8.4 and 8.5. It also lints and tests against nightly PHP 8.6 and 8.7 as an
early warning; failures there are reported but don't fail the run.
