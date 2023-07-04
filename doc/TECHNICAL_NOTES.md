![CrowdSec Logo](images/logo_crowdsec.png)

# CrowdSec Engine extension for Magento 2

## Technical notes

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->


<!-- END doctoc generated TOC please keep comment here to allow auto update -->


## CrowdSec Remediation Engine

This extension is mainly based on the CrowdSec Remediation Engine PHP SDK library. It is an open source library whose 
code you can find [here](https://github.com/crowdsecurity/php-remediation-engine).


## Magento 2.2

As Magento 2.2 is not compatible with PHP 7.2 until [2.2.9](https://github.com/magento/magento2/blob/2.2.9/composer.json#L11) and has reached end of support in [2019](https://devdocs.magento.com/release/released-versions.html#22), we did not try to make this extension compatible with it.


## Why `crowdsec/magento-symfony-cache` dependency?

This `CrowdSec_Engine` module depends on the [CrowdSec Remediation Engine library `crowdsec/remediation-engine`](https://github.com/crowdsecurity/php-remediation-engine) that comes with`symfony/cache` as dependency (`v5` or `v6`).

Since Magento `2.4.4`, a fresh installation on PHP 8 will lock a `3.0.0` version of `psr/cache`. And it also installs a `v2.2.11` version of `web-token/jwt-framework` that locks a `v4.4.45` version of`symfony/http-kernel`.


As a `v5` version of `symfony/cache` required `^1.0|^2.0` version of `psr/cache`, and a `v6` version of `symfony/cache` conflicts with `symfony/http-kernel` <5.4, it is impossible to require any version of the`symfony/cache` package.

That's why we needed to create a fork of `symfony/cache` that we called `crowdsec/magento-symfony-cache`.

The `v1` version of `crowdsec/magento-symfony-cache` only requires some specific `5.x.y` version of `symfony/cache`and is only available for PHP < `8.0.2`.

For PHP >= `8.0.2`, we provide a compatible `v2` version of `crowdsec/magento-symfony-cache`.
This `v2` version replaces the specified `5.x.y` version of `symfony/cache` : we use a copy of `5.x.y` files and allow `psr/cache` `3.0`. We also copy some `6.0.z` files to have compatible PHP 8 method signatures.


_Update_: Since Magento `2.4.6`, it is possible to install `symfony/cache` because the required version of 
`web-token/jwt-framework` is `3.1`. But, in order to keep compatibility with `2.4.4` and `2.4.5`, we have to 
keep this `crowdsec/magento-symfony-cache` dependency.



@TODO: how to use helper to send signals
