# configparser

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-codecov]][link-codecov]
[![StyleCI][ico-styleci]][link-styleci]
[![Total Downloads][ico-downloads]][link-downloads]

A simple YAML config file loader.

## Install

Via Composer

```bash
$ composer require kyos/configparser
```

## Usage

Given the following `config.yaml` configuration file:
```yaml
application:
  releaseStage: Production
  debugMode: true
```
you can parse any of its properties using:

```php
$parser = new Kyos\ConfigParser('config.yaml');
echo $parser->get('application.releaseStage');
```

You can use the following notations:

1. **Dot notation**:
```php
echo $parser->get('application.releaseStage');
```

2. **Array notation**:
```php
echo $parser->get(['application', 'releaseStage']);
```

3. **String notation** (for top level keys):
```php
var_dump($parser->get('application'));
```

## Assertions

Using ConfigParser, you can require specific keys to be defined and their types.
Note: All commands should be proceeded by an evaluate function call.

### Required Key

```php
echo $parser->evaluate('application.releaseStage')->isRequired();
```

### String

```php
echo $parser->evaluate('application.releaseStage')->isString();
```

### Numeric

```php
echo $parser->evaluate('application.releaseStage')->isNumeric();
```

### Boolean

```php
echo $parser->evaluate('application.releaseStage')->isBoolean();
```

### Allowed Values

```php
echo $parser->evaluate('application.releaseStage')->isOneOf(['Production', 'Staging', 'Test']);
```

### Chain

Assertion functions can be also chained.

```php
echo $parser->evaluate('application.releaseStage')
            ->isRequired()->isString()
            ->isOneOf(['Production', 'Staging', 'Test']);
```

## Change log

Please see [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email pagoulatos@kyos.com instead of using the issue tracker.

## Credits

- [Zois Pagoulatos][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/kyos/configparser.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-travis]: https://travis-ci.com/kyosenergy/configparser.svg?branch=master
[ico-codecov]: https://codecov.io/gh/kyosenergy/configparser/branch/master/graph/badge.svg
[ico-styleci]: https://github.styleci.io/repos/159172475/shield?branch=master
[ico-downloads]: https://img.shields.io/packagist/dt/kyos/configparser.svg

[link-packagist]: https://packagist.org/packages/kyos/configparser
[link-travis]: https://travis-ci.com/kyosenergy/configparser
[link-codecov]: https://codecov.io/gh/kyosenergy/configparser
[link-styleci]: https://github.styleci.io/repos/159172475
[link-downloads]: https://packagist.org/packages/kyos/configparser
[link-author]: https://github.com/zoispag
[link-contributors]: ../../contributors
