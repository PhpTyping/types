Types
=====
[![Build Job][stable-build-badge]][stable-build]
[![Integration Job][integration-build-badge]][integration-build]
[![Documentation][documentation shield]][documentation link]
[![License][license shield]][packagist page]
[![Issues][github issues]][issues page]
[![Downloads][downloads shield]][packagist page]
[![Latest][latest version shield]][packagist page]

[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=PhpTyping-types)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=alert_status)](https://sonarcloud.io/dashboard?id=PhpTyping-types)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=PhpTyping-types)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=security_rating)](https://sonarcloud.io/dashboard?id=PhpTyping-types)
[![Bugs][sonar-bugs]](https://sonarcloud.io/dashboard?id=PhpTyping-types)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=coverage)](https://sonarcloud.io/dashboard?id=PhpTyping-types)
[![Duplicated Lines (%)](https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=duplicated_lines_density)](https://sonarcloud.io/dashboard?id=PhpTyping-types)
[![Lines of Code](https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=ncloc)](https://sonarcloud.io/dashboard?id=PhpTyping-types)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=sqale_index)](https://sonarcloud.io/dashboard?id=PhpTyping-types)
[![Vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=vulnerabilities)](https://sonarcloud.io/dashboard?id=PhpTyping-types)

<img src="https://raw.githubusercontent.com/TheDevNetwork/Aux/master/images/php-types.png" alt="PhpTyping" width="150px"/>

## PHP Primitive wrappers.

### Description

What SPL_Types should have, and could have been.

> This library aggregates multiple PHP libraries and wraps them in a single repo, providing decorator
classes / sub-types with some extra features.

[SPL_Types](https://pecl.php.net/package/spl_types) has been broken since...forever. The last release
was in [2012](https://pecl.php.net/package/spl_types).  No library has tried to do this since, and SPL_Types
providing an empty interface for each  primitive object was not terribly useful.

Libraries such as `Doctrine/Collections`, `Stringy`, `PHPMoney/Money`, `MyClabs/Enum` and others like them, 
are infinitely more useful. They provide rich interfaces  that allow you to breeze through coding as opposed 
to trying to remember the name of specific functions for particular primitives.

Documentation
-------------
##### Full docs

[Please see the online documentation](https://php-types.readthedocs.io/en/latest/?)

##### Requirements

PHP 8.0 or above.

###### Optional Requirements:

* [PHP bcmath]
* [PHP gmp]

##### Installation

Using CLI:

```bash
composer require typing/types:*@stable
```

Or directly on the `composer.json` file:
```json
{
    "require": {
        "typing/types": "*@stable"
    }
}
```

See https://getcomposer.org/ for more information and documentation.

Contributing
------------

If you want to contribute, please read the [CONTRIBUTING](CONTRIBUTING.md).

License
-------

This library is released under the MIT license. See the complete license in the [LICENSE](LICENSE) file.

[github issues]: https://img.shields.io/github/issues/PhpTyping/Types.svg?style=flat-square
[issues page]: https://github.com/PhpTyping/Types/issues
[downloads shield]: https://img.shields.io/packagist/dt/typing/types.svg?style=flat-square
[latest version shield]: https://img.shields.io/packagist/v/typing/types.svg?style=flat-square
[packagist page]: https://packagist.org/packages/typing/types
[PHP bcmath]: https://secure.php.net/manual/en/book.bc.php
[PHP gmp]: https://secure.php.net/manual/en/book.gmp.php
[license shield]: https://img.shields.io/github/license/PhpTyping/types?style=flat-square
[packagist page]: https://packagist.org/packages/typing/types
[documentation shield]: https://readthedocs.org/projects/php-types/badge/?version=latest&style=flat-square
[documentation link]: https://php-types.readthedocs.io/en/latest/?badge=latest
[stable-build-badge]: https://img.shields.io/endpoint?style=flat-square&url=https://gist.githubusercontent.com/vpassapera/027dcddb6a1dc1995a2a47e528aaf020/raw/build-stable.json
[stable-build]: https://github.com/PhpTyping/types/actions/workflows/build-stable.yaml
[integration-build-badge]: https://img.shields.io/endpoint?style=flat-square&url=https://gist.githubusercontent.com/vpassapera/73b13bfc6a004696c00552deb44b9e40/raw/build-integration.json
[integration-build]: https://github.com/PhpTyping/types/actions/workflows/build-integration.yaml
[sonar-bugs]: https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=bugs&style=flat-square
