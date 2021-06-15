Types
=====

[![Documentation Status][documentation shield]][documentation link]
[![License][license shield]][packagist page]
[![GitHub issues][github issues]][issues page]
[![Total Downloads][downloads shield]][packagist page]
[![Latest Stable Version][latest version shield]][packagist page]

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
