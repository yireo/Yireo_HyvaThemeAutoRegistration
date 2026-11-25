# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 25 November 2026
### Added
- Support for PHP 8.5
- Documentation of the default prefixes (`Yireo_`, `YireoTraining_`), prefix matching and how to exclude modules or disable a default prefix

### Removed
- Support for PHP 8.1 (PHP 8.2 to 8.5 is now required)

### Fixed
- Security: module prefixes now only match at the start of a module name, so modules like `AcmeYireo_Foo` or an empty prefix no longer pull arbitrary modules (and their `tailwind.config.js`) into the Hyvä build
- Modules that are listed but not registered are skipped instead of producing broken entries in `app/etc/hyva-themes.json`
- Module paths are made relative to the Magento root only when they start with it
- Malformed event config data or extension entries no longer cause errors

## [1.0.7] - 22 October 2025
### Fixed
- Copy generic CI/CD files
- Add support for base-folder
- Add PHP 8.4

## [1.0.6]
- Initial releases
