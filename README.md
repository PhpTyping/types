Types
=====

[![Build Job][github-stable-build-shield]][github stable build]
[![Patch Job][github-patch-shield]][github patch build]
[![Documentation][rtd-doc-shield]][github doc repo link]
[![License][github-license-shield]][packagist page]
[![Issues][github-issues-shield]][github issues page]
[![Downloads][pkgist-dls-shield]][packagist page]
[![Latest][pkgist-version-shield]][packagist page]

[![Quality Gate Status][sonar-gate-shield]][sonar page]
[![Maintainability Rating][sonar-maint-shield]][sonar page]
[![Reliability Rating][sonar-rel-shield]][sonar page]
[![Security Rating][sonar-sec-shield]][sonar page]
[![Bugs][sonar-bugs-shield]][sonar page]
[![Coverage][sonar-cov-shield]][sonar page]
[![Duplicated Lines (%)][sonar-cpd-shield]][sonar page]
[![Lines of Code][sonar-loc-shield]][sonar page]
[![Technical Debt][sonar-debt-shield]][sonar page]
[![Vulnerabilities][sonar-vul-shield]][sonar page]

<img src="https://raw.githubusercontent.com/TheDevNetwork/Aux/master/images/php-types.png" alt="PhpTyping" width="150px"/>

## PHP Primitive wrappers.

### Description

Types is a library that provides a collection of useful primitive wrappers, 
similar to what other languages can do ( Java, etc). It fixes a few issues 
some internal functions have, and limits coercion around native PHP functions.

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

[Please see the online documentation][doc link]

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

For locally testing workflows, use [Act](https://github.com/nektos/act) workflow tester.

If you want to contribute, please read the [CONTRIBUTING](CONTRIBUTING.md).

License
-------

This library is released under the MIT license. See the complete license in the [LICENSE](LICENSE) file.

[PHP bcmath]: https://secure.php.net/manual/en/book.bc.php
[PHP gmp]: https://secure.php.net/manual/en/book.gmp.php
[doc link]: https://phptyping.github.io/types-documentation/
[sonar page]: https://sonarcloud.io/dashboard?id=PhpTyping-types
[packagist page]: https://packagist.org/packages/typing/types
[github issues page]: https://github.com/PhpTyping/Types/issues
[github stable build]: https://github.com/PhpTyping/types/actions/workflows/build-stable.yaml
[github patch build]: https://github.com/PhpTyping/types/actions/workflows/continous-patching.yaml
[github doc repo link]: https://github.com/PhpTyping/types-documentation
[github-issues-shield]: https://img.shields.io/github/issues/PhpTyping/Types.svg?style=flat-square
[github-license-shield]: https://img.shields.io/github/license/PhpTyping/types?style=flat-square
[github-stable-build-shield]: https://img.shields.io/endpoint?style=flat-square&url=https://gist.githubusercontent.com/vpassapera/027dcddb6a1dc1995a2a47e528aaf020/raw/build-stable.json
[github-patch-shield]: https://img.shields.io/endpoint?style=flat-square&url=https://gist.githubusercontent.com/vpassapera/d18a6a553c2308b59df916c29ce64eb6/raw/patching.json
[pkgist-dls-shield]: https://img.shields.io/packagist/dt/typing/types.svg?style=flat-square
[pkgist-version-shield]: https://img.shields.io/packagist/v/typing/types.svg?style=flat-square
[rtd-doc-shield]: https://readthedocs.org/projects/php-types/badge/?version=latest&style=flat-square
[sonar-bugs-shield]: https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=bugs&style=flat-square
[sonar-maint-shield]: https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=sqale_rating
[sonar-gate-shield]: https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=alert_status
[sonar-rel-shield]: https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=reliability_rating
[sonar-sec-shield]: https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=security_rating
[sonar-cov-shield]: https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=coverage
[sonar-cpd-shield]: https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=duplicated_lines_density
[sonar-loc-shield]: https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=ncloc
[sonar-debt-shield]: https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=sqale_index
[sonar-vul-shield]: https://sonarcloud.io/api/project_badges/measure?project=PhpTyping-types&metric=vulnerabilities
