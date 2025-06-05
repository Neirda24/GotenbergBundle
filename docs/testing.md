# Testing

## Requirements

Dagger : >= v0.18.9

```shell
$ dagger develop
```

## Running the tests

```shell
$ dagger call test-phpunit-unit --symfony-version '6.4.*' --php-version '8.2' stdout
$ dagger call generate-docs --symfony-version '6.4.*' --php-version '8.2' export --path ./docs
```
