# Testing

## Requirements

Dagger : >= v0.18.10

```shell
$ dagger develop
```

## Running the tests

```shell
$ dagger call test-phpunit-unit --symfony-version '6.4.*' --php-version '8.2' stdout
$ dagger call test-validate-dependencies --symfony-version '6.4.*' --php-version '8.2' stdout
$ dagger call generate-docs --symfony-version '6.4.*' --php-version '8.2' export --path ./docs
```
