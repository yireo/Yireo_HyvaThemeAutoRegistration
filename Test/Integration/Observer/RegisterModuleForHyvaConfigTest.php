<?php

declare(strict_types=1);

namespace Yireo\HyvaThemeAutoRegistration\Test\Integration\Observer;

use Magento\Framework\App\Cache;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ConfigInterface as EventManagerConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\Status;
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
        $this->enableModules(['Yireo_Test1']);

        $observer = $this->getObserver();
        $target = ObjectManager::getInstance()->get(RegisterModuleForHyvaConfig::class);

        $target->execute($observer);

        $this->assertNotEmpty($observer->getEvent()->getConfig()['extensions']);
        $this->assertObserverEventContainsModule($observer, 'Yireo_Test1');
    }

    #[ComponentsDir('../../../../vendor/yireo/magento2-hyva-theme-auto-registration/Test/Integration/_modules')]
    public function testIfObserverIncludesManualModuleNames()
    {
        $this->enableModules(['Foo_Bar1']);

        $observer = $this->getObserver();
        $target = ObjectManager::getInstance()->create(RegisterModuleForHyvaConfig::class, [
            'moduleNames' => ['Foo_Bar1']
        ]);

        $target->execute($observer);

        $this->assertNotEmpty($observer->getEvent()->getConfig()['extensions']);
        $this->assertObserverEventContainsModule($observer, 'Foo_Bar1');
    }

    private function getObserver(): Observer
    {
        $config = new DataObject(['extensions' => []]);
        $event = new DataObject(['config' => $config]);
        $observer = ObjectManager::getInstance()->create(Observer::class);
        $observer->setEvent($event);
        return $observer;
    }

    private function assertObserverEventContainsModule(Observer $observer, string $moduleName)
    {
        $componentRegistrar = ObjectManager::getInstance()->get(ComponentRegistrar::class);
        $modulePath = $componentRegistrar->getPath('module', $moduleName);

        $isModuleFound = false;
        $extensions = $observer->getEvent()->getConfig()['extensions'];
        foreach($extensions as $extension) {
            if ($extension['src'] === $modulePath) {
                $isModuleFound = true;
                break;
            }
        }

        $this->assertTrue($isModuleFound, var_export($extensions, true));
    }

    private function enableModules(array $moduleNames)
    {
        $moduleStatus = ObjectManager::getInstance()->get(Status::class);
        $moduleStatus->setIsEnabled(true, $moduleNames);

        $cache = ObjectManager::getInstance()->get(Cache::class);
        $cache->clean();

        $moduleList = ObjectManager::getInstance()->get(ModuleList::class);
        foreach ($moduleNames as $moduleName) {
            $moduleDefinition = $moduleList->getOne($moduleName);
            $this->assertNotEmpty($moduleDefinition, 'Module "'.$moduleName.'" was not found');
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
