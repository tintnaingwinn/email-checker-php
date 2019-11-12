# Validate Email for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tintnaingwin/email-checker-php.svg?style=flat-square)](https://packagist.org/packages/tintnaingwin/email-checker-php)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)

**Notice** -  That extracts the MX records from the email address and connect with the mail server to make sure the mail address accurately exist. So it may be slow loading time in local and some co-operate MX records take a long time.

## Installation

You can install the package via composer:

```bash
composer require tintnaingwin/email-checker-php
```

## Usage

``` php
$email_checker = new Tintnaingwin\EmailCheckerPHP\EmailChecker();
echo $email_checker->check('amigo.k8@gmail.com');
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security
If you discover any security-related issues, please email amigo.k8@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
