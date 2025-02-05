# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [?.?.?](https://github.com/crowdsecurity/magento-cs-extension/releases/tag/v?.?.?) - 202?-??-??
[_Compare with previous release_](https://github.com/crowdsecurity/magento-cs-extension/compare/v1.1.1...HEAD)

**This release is not yet published.**

### Changed

- Uses the new `crowdsec/remediation-engine` `^4.1.0` dependency instead of `^3.3.0`

---

## [1.1.1](https://github.com/crowdsecurity/magento-cs-extension/releases/tag/v1.1.1) - 2024-04-12
[_Compare with previous release_](https://github.com/crowdsecurity/magento-cs-extension/compare/v1.1.0...v1.1.1)


### Changed

- No change: released on marketplace to confirm compatibility with Magento 2.4.7 and PHP 8.3

---

## [1.1.0](https://github.com/crowdsecurity/magento-cs-extension/releases/tag/v1.1.0) - 2024-01-05
[_Compare with previous release_](https://github.com/crowdsecurity/magento-cs-extension/compare/v1.0.0...v1.1.0)


### Changed

- Encrypt enrollment key in database

### Fixed

- Allow `crowdsec/symfony-cache:3.0.0` dependency to avoid composer conflict with some Magento 2.4.6 patch versions

---


## [1.0.0](https://github.com/crowdsecurity/magento-cs-extension/releases/tag/v1.0.0) - 2023-08-22
[_Compare with previous release_](https://github.com/crowdsecurity/magento-cs-extension/compare/v0.3.0...v1.0.0)


### Removed

- Remove fallback remediation setting and always use `bypass` as fallback

---

## [0.3.0](https://github.com/crowdsecurity/magento-cs-extension/releases/tag/v0.3.0) - 2023-08-21
[_Compare with previous release_](https://github.com/crowdsecurity/magento-cs-extension/compare/v0.2.0...v0.3.0)


### Add

- Add metrics in report page

---

## [0.2.0](https://github.com/crowdsecurity/magento-cs-extension/releases/tag/v0.2.0) - 2023-08-01
[_Compare with previous release_](https://github.com/crowdsecurity/magento-cs-extension/compare/v0.1.0...v0.2.0)


### Add

- Add observer to listen `crowdsec_engine_detected_alert` event and add alert to signal queue

---

## [0.1.0](https://github.com/crowdsecurity/magento-cs-extension/releases/tag/v0.1.0) - 2023-07-12
[_Compare with previous release_](https://github.com/crowdsecurity/magento-cs-extension/compare/v0.0.1...v0.1.0)


### Changed

- Use a default list for subscribed scenarios


---

## [0.0.1](https://github.com/crowdsecurity/magento-cs-extension/releases/tag/v0.0.1) - 2023-07-11

### Added
- Initial release
