<?php

declare(strict_types=1);

namespace Yireo\HyvaThemeAutoRegistration\Test\Integration\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ConfigInterface as EventManagerConfig;
use Magento\Framework\Event\Observer;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\ComponentsDir;
use PHPUnit\Framework\TestCase;
use Yireo\HyvaThemeAutoRegistration\Observer\RegisterModuleForHyvaConfig;

class RegisterModuleForHyvaConfigTest extends TestCase
{
    #[AppArea('frontend')]
    public function testIfObserverIsRegistered()
    {
        // @todo: Copy this to integration test helper
        $eventManagerConfig = ObjectManager::getInstance()->get(EventManagerConfig::class);
        $observers = $eventManagerConfig->getObservers('hyva_config_generate_before');

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

    #[AppIsolation(true)]
    #[ComponentsDir('../../../../vendor/yireo/magento2-hyva-theme-auto-registration/Test/Integration/_modules')]
    public function testIfObserverExecutionIncludesConstructorBasedModules()
    {
        $moduleStatus = ObjectManager::getInstance()->get(\Magento\Framework\Module\Status::class);
        $moduleStatus->setIsEnabled(true, ['Yireo_Test1']);

        $cache = ObjectManager::getInstance()->get(\Magento\Framework\App\Cache::class);
        $cache->clean();

        $observer = $this->getObserver();
        $target = ObjectManager::getInstance()->get(RegisterModuleForHyvaConfig::class);
        $target->execute($observer);

        $this->assertNotEmpty($observer->getEvent()->getConfig()['extensions']);
        $this->assertObserverEventContainsModule($observer, 'Yireo_Test1');
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
            }
        }


        $this->assertTrue($isModuleFound, var_export($extensions, true));
    }
}
