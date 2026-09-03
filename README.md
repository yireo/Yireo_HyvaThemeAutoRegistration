# Yireo HyvaThemeAutoRegistration

<!-- badges.specs.start -->
![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-hyva-theme-auto-registration)
<!-- badges.specs.end -->

**Magento 2 module to make it easier to register a custom `tailwind.config.js` file of your own module in the global Hyvä Themes Tailwind configuration**

### Background
Hyvä Themes offers a Magento 2 CLI command `hyva:config:generate` to allow building a file `app/etc/hyva-themes.json` that again is used in modern-day Tailwind configuration of Hyvä-based themes. However, to extend this, one must add a custom observer to the module, which leads to a lot of code duplication across your modules. This module aims to simplify this. It offers an observer following the official Hyvä documentation. 

However, this observer automatically registers any module that has a prefix `Yireo_` or `YireoTraining_` (it works for me). And it allows for extending things with a DI plugin.

### Usage
Add this module as a dependency to your `composer.json` file and `etc/module.xml` file.

Next, add the following DI configuration to your module its `etc/di.xml` file (assuming here that `Foo_Bar` is the name of your own module):
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Yireo\HyvaThemeAutoRegistration\Observer\RegisterModuleForHyvaConfig">
        <arguments>
            <argument name="moduleNames" xsi:type="array">
                <item name="Foo_Bar" xsi:type="string">Foo_Bar</item>
            </argument>
        </arguments>
    </type>
</config>
```

Alternatively, you can include all your modules by configuring a module prefix:

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Yireo\HyvaThemeAutoRegistration\Observer\RegisterModuleForHyvaConfig">
        <arguments>
            <argument name="modulePrefixes" xsi:type="array">
                <item name="Foo_" xsi:type="string">Foo_</item>
            </argument>
        </arguments>
    </type>
</config>
```

## Current status

<!-- badges.test.start -->
![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_HyvaThemeAutoRegistration/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_HyvaThemeAutoRegistration/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_HyvaThemeAutoRegistration/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_HyvaThemeAutoRegistration/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_HyvaThemeAutoRegistration/compile.yml?label=compile)
<!-- badges.test.end -->
