![CrowdSec Logo](images/logo_crowdsec.png)
# CrowdSec Engine extension for Magento 2

## Developer guide


<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**

- [Local development](#local-development)
  - [DDEV setup](#ddev-setup)
    - [DDEV installation](#ddev-installation)
    - [Create a Magento 2 DDEV project with some DDEV add-ons](#create-a-magento-2-ddev-project-with-some-ddev-add-ons)
    - [Magento 2 installation](#magento-2-installation)
      - [Set up Magento 2](#set-up-magento-2)
    - [Configure Magento 2 for local development](#configure-magento-2-for-local-development)
    - [Crowdsec Engine extension installation](#crowdsec-engine-extension-installation)
  - [Extension quality](#extension-quality)
  - [End-to-end tests](#end-to-end-tests)
  - [Cron](#cron)
  - [Varnish](#varnish)
    - [Varnish debug](#varnish-debug)
- [Commit message](#commit-message)
  - [Allowed message `type` values](#allowed-message-type-values)
- [Release process](#release-process)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->



## Local development

There are many ways to install this extension on a local Magento 2 environment.

We are using [DDEV](https://ddev.readthedocs.io/en/stable/) because it is quite simple to use and customize.

You may use your own local stack, but we provide here some useful tools that depends on DDEV.


### DDEV setup

For a quick start, follow the below steps.

The final structure of the project will look like below.

```
m2-sources (choose the name you want for this folder)
│   
│ (Magento 2 sources installed with composer)    
│
└───.ddev
│   │   
│   │ (DDEV files)
│   
└───my-own-modules (do not change this folder name)
    │   
    │
    └───crowdsec-engine (do not change this folder name)
       │   
       │ (Cloned sources of this repo)
         
```

**N.B.:** you can use whatever name you like for the folder `m2-sources` but, in order to use our pre-configured ddev
commands, you must respect the sub folders naming: `my-own-modules` and `crowdsec-engine`.

#### DDEV installation

This project is fully compatible with DDEV 1.21.6, and it is recommended to use this specific version. For the DDEV 
installation, please follow the [official instructions](https://ddev.readthedocs.io/en/stable/#installation).

#### Create a Magento 2 DDEV project with some DDEV add-ons

``` bash
mkdir m2-sources && cd m2-sources
ddev config --project-type=magento2 --project-name=your-project-name --php-version=8.1 --docroot=pub --create-docroot --disable-settings-management
ddev get ddev/ddev-redis
ddev get ddev/ddev-memcached
ddev get ddev/ddev-elasticsearch
ddev get julienloizelet/ddev-tools
ddev get julienloizelet/ddev-playwright
ddev start
```

#### Magento 2 installation
You will need your Magento 2 credentials to install the source code.

```bash
 ddev composer create --repository=https://repo.magento.com/ magento/project-community-edition -y
```


##### Set up Magento 2

```bash
 ddev magento setup:install \
                       --base-url=https://your-project-name.ddev.site/ \
                       --db-host=db \
                       --db-name=db \
                       --db-user=db \
                       --db-password=db \
                       --backend-frontname=admin \
                       --admin-firstname=admin \
                       --admin-lastname=admin \
                       --admin-email=admin@admin.com \
                       --admin-user=admin \
                       --admin-password=admin123 \
                       --language=en_US \
                       --currency=USD \
                       --timezone=America/Chicago \
                       --use-rewrites=1 \
                       --elasticsearch-host=elasticsearch --search-engine=elasticsearch7
```


#### Configure Magento 2 for local development

```bash
ddev magento deploy:mode:set developer
ddev magento config:set admin/security/password_is_forced 0
ddev magento config:set admin/security/password_lifetime 0
ddev magento module:disable Magento_AdminAdobeImsTwoFactorAuth (Magento >= 2.4.6 only)
ddev magento module:disable Magento_TwoFactorAuth
ddev magento setup:performance:generate-fixtures setup/performance-toolkit/profiles/ce/small.xml
ddev magento c:c
```

#### Crowdsec Engine extension installation

```bash
 cd m2-sources
 mkdir -p my-own-modules/crowdsec-engine
 cd my-own-modules/crowdsec-engine
 git clone git@github.com:crowdsecurity/magento-cs-extension.git ./
 ddev composer config repositories.crowdsec-engine-module path my-own-modules/crowdsec-engine/
 ddev composer require crowdsec/magento2-module-engine:@dev
 ddev magento module:enable CrowdSec_Engine
 ddev magento setup:upgrade
 ddev magento cache:flush
```

### Extension quality

During development, you can run some static php tools to ensure quality code:  

- PHP Code Sniffer: `ddev phpcs my-own-modules/crowdsec-engine --ignore="*/node_modules/*"`
- PHP Mess Detector: `ddev phpmd --exclude "node_modules"  my-own-modules/crowdsec-engine`
- PHP Stan: `ddev phpstan my-own-modules/crowdsec-engine`

You can also check unit tests: `ddev phpunit my-own-modules/crowdsec-engine/Test/Unit`

### End-to-end tests

We are using a Jest/Playwright Node.js stack to launch a suite of end-to-end tests.

**Please note** that those tests modify local configurations and log content on the fly.

Tests code is in the `Test/EndToEnd` folder.

Tests must be run sequentially (`fullyParallel: false` in the `playwright.config.ts` file)

```bash
ddev get julienloizelet/ddev-crowdsec-php
cp .ddev/okaeli-add-on/magento2/custom_files/crowdsec/engine/docker-compose.override.yaml .ddev/docker-compose.override.yaml
ddev magento config:set crowdsec_engine/general/enrollment_key <YOUR_ENROLL_KEY>
cp .ddev/okaeli-add-on/magento2/custom_scripts/cronLaunch.php ${{ github.workspace }}/pub/cronLaunch.php
cp .ddev/okaeli-add-on/magento2/custom_scripts/crowdsec/engine/runActions.php ${{ github.workspace }}/pub/runActions.php
ddev restart
ddev playwright-install
```
 
Modify data in `Test/EndToEnd/.env` file then:

```
ddev playwright test config
ddev playwright test config --headed
ddev playwright test user-enum --headed 
```

To see the browser in headed mode, you can find the playwright url with `ddev describe`. 

To see the report: 

```
ddev playwright show-report --host 0.0.0.0
```

**N.B**: For some test, you'll need to empty the `captcha_log` table in the database.

and browse to `https://your-project-name.ddev.site:9323/`


### Cron

You can simulate Magento 2 cron with the following command in 
a new terminal: 

```bash
 ddev cron
```

You should find a `var/log/magento.cron.log` for debug.


### Varnish

If you want to test with Varnish, please follow these instructions:

First, you should configure your Magento 2 instance to use Varnish as full page cache:

```bash
ddev magento config:set system/full_page_cache/caching_application 2
```

Then, you can add specific files for Varnish and restart:

```bash
ddev get ddev/ddev-varnish
cp .ddev/okaeli-add-on/magento2/custom_files/default.vcl .ddev/varnish/default.vcl
ddev restart
```

Finally, we need to change the ACL part for purge process:

```bash
ddev replace-acl $(ddev find-ip ddev-router)
ddev reload-vcl
```


For information, here are the differences between the back office generated `default.vcl` and the `default.vcl` we use:

- We changed the probe url from `"/pub/health_check.php"` to `"/health_check.php"` as explained in the [official documentation](https://devdocs.magento.com/guides/v2.4/config-guide/varnish/config-varnish-advanced.html):

```
 .probe = {
    .url = "/health_check.php";
    .timeout = 2s;
    .interval = 5s;
    .window = 10;
    .threshold = 5;
    }
```


- We added this part for Marketplace EQP Varnish test simulation as explained in the [official documentation](https://devdocs.magento.com/marketplace/sellers/installation-and-varnish-tests.html#additional-magento-configuration):

```
if (resp.http.x-varnish ~ " ") {
           set resp.http.X-EQP-Cache = "HIT";
       } else {
           set resp.http.X-EQP-Cache = "MISS";
}
```


#### Varnish debug

To see if purge works, you can do :

```bash
ddev exec -s varnish varnishlog -g request -q \'ReqMethod eq "PURGE"\'
```

And then, from another terminal, flush the cache :

```bash
ddev magento cache:flush
```

You should see in the log the following content:

```
VCL_call       RECV
VCL_acl        MATCH purge "your-ddev-router-ip"
VCL_return     synth
VCL_call       HASH
VCL_return     lookup
RespProtocol   HTTP/1.1
RespStatus     200
RespReason     Purged
```

## Commit message

In order to have an explicit commit history, we are using some commits message convention with the following format:

    <type>(<scope>): <subject>

Allowed `type` are defined below.
`scope` value intends to clarify which part of the code has been modified. It can be empty or `*` if the change is a
global or difficult to assign to a specific part.
`subject` describes what has been done using the imperative, present tense.

Example:

    feat(admin): Add css for admin actions


You can use the `commit-msg` git hook that you will find in the `.githooks` folder : 

```bash
cp .githooks/commit-msg .git/hooks/commit-msg
chmod +x .git/hooks/commit-msg
```

### Allowed message `type` values

- chore (automatic tasks; no production code change)
- ci (updating continuous integration process; no production code change)
- comment (commenting;no production code change)
- docs (changes to the documentation)
- feat (new feature for the user)
- fix (bug fix for the user)
- refactor (refactoring production code)
- style (formatting; no production code change)
- test (adding missing tests, refactoring tests; no production code change)

## Release process

We are using [semantic versioning](https://semver.org/) to determine a version number.

Before publishing a new release, there are some manual steps to take:

- Change the version number in the `composer.json` file
- Change the version number in the `Constants.php` file
- Update the `CHANGELOG.md` file


Then, using the [GitHub CLI](https://github.com/cli/cli), you can: 
- create a draft release: `gh workflow run release.yml -f tag_name=vx.y.z -f draft=true`
- publish a prerelease:  `gh workflow run release.yml -f tag_name=vx.y.z -f prerelease=true`
- publish a release: `gh workflow run release.yml -f tag_name=vx.y.z`

Note that the GitHub action will fail if the tag `tag_name` already exits.

At the end of the GitHub action process, you will find a `crowdsec-magento2-module-engine-x.y.z.zip` file in the 
GitHub release assets.

 
