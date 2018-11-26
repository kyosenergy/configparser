# configparser

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what
PSRs you support to avoid any confusion with users and contributors.

## Install

Via Composer

```bash
$ composer require kyos/configparser
```

## Usage

```php
$parser = new Kyos\ConfigParser('config.yaml');
echo $parser->get('application.releaseStage');
```

You can use the following notations:

1. String notation (for top level keys):
```php
echo $parser->get('application');
```

2. Dot notation:
```php
echo $parser->get('application.releaseStage');
```

3. Array notation:
```php
echo $parser->get(['application', 'releaseStage']);
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

Assertion function can be also chained.

```php
echo $parser->evaluate('application.releaseStage')->isRequired()->isString()->isOneOf(['Production', 'Staging', 'Test']);
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

[ico-version]: https://img.shields.io/packagist/v/kyos/configparser.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/kyos/configparser/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/kyos/configparser.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/kyos/configparser.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/kyos/configparser.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/kyos/configparser
[link-travis]: https://travis-ci.org/kyos/configparser
[link-scrutinizer]: https://scrutinizer-ci.com/g/kyos/configparser/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/kyos/configparser
[link-downloads]: https://packagist.org/packages/kyos/configparser
[link-author]: https://github.com/zoispag
[link-contributors]: ../../contributors
