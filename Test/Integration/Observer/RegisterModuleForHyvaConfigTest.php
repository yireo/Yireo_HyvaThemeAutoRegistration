<?php

declare(strict_types=1);

namespace Yireo\HyvaThemeAutoRegistration\Test\Integration\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ConfigInterface as EventManagerConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Module\ModuleList;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\ComponentsDir;
use Magento\TestFramework\Fixture\DbIsolation;
use PHPUnit\Framework\TestCase;
use Yireo\HyvaThemeAutoRegistration\Observer\RegisterModuleForHyvaConfig;

class RegisterModuleForHyvaConfigTest extends TestCase
{
    #[AppArea('frontend')]
    public function testIfObserverIsRegistered()
    {
        $this->assertObserverDefinion('hyva_config_generate_before');
    }

    #[ComponentsDir('../../../../vendor/yireo/magento2-hyva-theme-auto-registration/Test/Integration/_modules')]
    public function testIfObserverIncludesYireoBasedModules()
    {
        $this->assertModulesAreRegistered(['Yireo_Test1']);

        $moduleList = $this->createMock(ModuleList::class);
        $moduleList->method('getAll')->willReturn(['Yireo_Test1' => ['name' => 'Yireo_Test1']]);

        $observer = $this->getObserver();
        $target = ObjectManager::getInstance()->create(RegisterModuleForHyvaConfig::class, [
            'moduleList' => $moduleList
        ]);

        $target->execute($observer);

        $this->assertNotEmpty($observer->getEvent()->getConfig()['extensions']);
        $this->assertObserverEventContainsModule($observer, 'Yireo_Test1');
    }

    #[ComponentsDir('../../../../vendor/yireo/magento2-hyva-theme-auto-registration/Test/Integration/_modules')]
    public function testIfObserverIncludesManualModuleNames()
    {
        $this->assertModulesAreRegistered(['Foo_Bar1']);

        $observer = $this->getObserver();
        $target = ObjectManager::getInstance()->create(RegisterModuleForHyvaConfig::class, [
            'moduleNames' => ['Foo_Bar1']
        ]);

        $target->execute($observer);

        $this->assertNotEmpty($observer->getEvent()->getConfig()['extensions']);
        $this->assertObserverEventContainsModule($observer, 'Foo_Bar1');
    }

    #[ComponentsDir('../../../../vendor/yireo/magento2-hyva-theme-auto-registration/Test/Integration/_modules')]
    public function testIfObserverOnlyMatchesPrefixAtStartOfModuleName()
    {
        $this->assertModulesAreRegistered(['Yireo_Test1', 'AcmeYireo_Test2']);

        $moduleList = $this->createMock(ModuleList::class);
        $moduleList->method('getAll')->willReturn([
            'Yireo_Test1' => ['name' => 'Yireo_Test1'],
            'AcmeYireo_Test2' => ['name' => 'AcmeYireo_Test2'],
        ]);

        $observer = $this->getObserver();
        $target = ObjectManager::getInstance()->create(RegisterModuleForHyvaConfig::class, [
            'moduleList' => $moduleList,
            'modulePrefixes' => ['Yireo_'],
        ]);

        $target->execute($observer);

        $this->assertObserverEventContainsModule($observer, 'Yireo_Test1');
        $this->assertObserverEventNotContainsModule($observer, 'AcmeYireo_Test2');
    }

    #[ComponentsDir('../../../../vendor/yireo/magento2-hyva-theme-auto-registration/Test/Integration/_modules')]
    public function testIfObserverIgnoresEmptyModulePrefix()
    {
        $this->assertModulesAreRegistered(['Yireo_Test1']);

        $moduleList = $this->createMock(ModuleList::class);
        $moduleList->method('getAll')->willReturn(['Yireo_Test1' => ['name' => 'Yireo_Test1']]);

        $observer = $this->getObserver();
        $target = ObjectManager::getInstance()->create(RegisterModuleForHyvaConfig::class, [
            'moduleList' => $moduleList,
            'modulePrefixes' => [''],
        ]);

        $target->execute($observer);

        $this->assertEmpty($observer->getEvent()->getConfig()['extensions']);
    }

    public function testIfObserverSkipsUnregisteredModuleNames()
    {
        $moduleList = $this->createMock(ModuleList::class);
        $moduleList->method('getAll')->willReturn([]);

        $observer = $this->getObserver();
        $target = ObjectManager::getInstance()->create(RegisterModuleForHyvaConfig::class, [
            'moduleList' => $moduleList,
            'moduleNames' => ['Missing_Module'],
        ]);

        $target->execute($observer);

        $this->assertEmpty($observer->getEvent()->getConfig()['extensions']);
    }

    #[ComponentsDir('../../../../vendor/yireo/magento2-hyva-theme-auto-registration/Test/Integration/_modules')]
    public function testIfObserverHandlesExtensionsWithoutSrc()
    {
        $this->assertModulesAreRegistered(['Foo_Bar1']);

        $moduleList = $this->createMock(ModuleList::class);
        $moduleList->method('getAll')->willReturn([]);

        $observer = $this->getObserver([['foo' => 'bar'], 'invalid']);
        $target = ObjectManager::getInstance()->create(RegisterModuleForHyvaConfig::class, [
            'moduleList' => $moduleList,
            'moduleNames' => ['Foo_Bar1'],
        ]);

        $target->execute($observer);

        $this->assertObserverEventContainsModule($observer, 'Foo_Bar1');
    }

    public function testIfObserverIgnoresInvalidConfig()
    {
        $observer = ObjectManager::getInstance()->create(Observer::class);
        $observer->setEvent(new DataObject(['config' => 'invalid']));

        $target = ObjectManager::getInstance()->create(RegisterModuleForHyvaConfig::class, [
            'moduleNames' => ['Foo_Bar1'],
        ]);

        $target->execute($observer);

        $this->assertSame('invalid', $observer->getEvent()->getData('config'));
    }

    private function assertObserverEventNotContainsModule(Observer $observer, string $moduleName)
    {
        $componentRegistrar = ObjectManager::getInstance()->get(ComponentRegistrar::class);
        $modulePath = (string)$componentRegistrar->getPath('module', $moduleName);
        foreach ($observer->getEvent()->getConfig()['extensions'] as $extension) {
            $this->assertNotSame($modulePath, $extension['src']);
            $this->assertFalse(str_ends_with($modulePath, '/' . $extension['src']), $moduleName . ' was included');
        }
    }

    private function getObserver(array $extensions = []): Observer
    {
        $config = new DataObject(['extensions' => $extensions]);
        $event = new DataObject(['config' => $config]);
        $observer = ObjectManager::getInstance()->create(Observer::class);
        $observer->setEvent($event);
        return $observer;
    }

    private function assertObserverEventContainsModule(Observer $observer, string $moduleName)
    {
        $componentRegistrar = ObjectManager::getInstance()->get(ComponentRegistrar::class);
        $modulePath = $componentRegistrar->getPath('module', $moduleName);
        $rootPath = ObjectManager::getInstance()->get(DirectoryList::class)->getRoot();
        if (str_starts_with($modulePath, $rootPath)) {
            $modulePath = trim(substr($modulePath, strlen($rootPath)), '/');
        }

        $isModuleFound = false;
        $extensions = $observer->getEvent()->getConfig()['extensions'];
        foreach($extensions as $extension) {
            if (is_array($extension) && ($extension['src'] ?? null) === $modulePath) {
                $isModuleFound = true;
                break;
            }
        }

        $this->assertTrue($isModuleFound, var_export($extensions, true));
    }

    private function assertModulesAreRegistered(array $moduleNames)
    {
        $componentRegistrar = ObjectManager::getInstance()->get(ComponentRegistrar::class);
        foreach ($moduleNames as $moduleName) {
            $modulePath = $componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
            $this->assertNotEmpty($modulePath, 'Module "'.$moduleName.'" was not registered');
        }
    }

    private function assertObserverDefinion(string $eventName)
    {
        $eventManagerConfig = ObjectManager::getInstance()->get(EventManagerConfig::class);
        $observers = $eventManagerConfig->getObservers($eventName);

        $foundObserver = false;
        $debugInfo = [];
        foreach($observers as $observer) {
            $debugInfo[] = $observer;
            if ($observer['instance'] === RegisterModuleForHyvaConfig::class) {
                $foundObserver = true;
            }
        }

        $this->assertTrue($foundObserver, var_export($debugInfo, true));
    }
}
