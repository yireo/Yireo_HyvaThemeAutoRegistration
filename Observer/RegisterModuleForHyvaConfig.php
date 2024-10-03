<?php
declare(strict_types=1);

namespace Yireo\HyvaThemeAutoRegistration\Observer;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Module\ModuleList;
use Yireo\HyvaThemeAutoRegistration\Utils\HyvaFiles;

class RegisterModuleForHyvaConfig implements ObserverInterface
{
    /**
     * @param ComponentRegistrar $componentRegistrar
     * @param array $moduleNames
     */
    public function __construct(
        private ComponentRegistrar $componentRegistrar,
        private ModuleList $moduleList,
        private HyvaFiles $hyvaFiles,
        private array $moduleNames = []
    )
    {}

    /**
     * @param Observer $event
     * @return void
     * @throws FileSystemException
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $config = $event->getData('config');
        $extensions = $config->hasData('extensions') ? $config->getData('extensions') : [];

        foreach ($this->moduleNames as $moduleName) {
            $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
            $extensions[] = ['src' => str_replace(BP, '', $path)];
        }

        foreach ($this->moduleList->getAll() as $moduleData) {
            $moduleName = $moduleData['name'];
            if (false === $this->allowModuleName($moduleName)) {
                continue;
            }

            if (false === $this->hyvaFiles->hasHyvaFiles($moduleName)) {
                continue;
            }

            $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
            $extensions[] = ['src' => str_replace(BP, '', $path)];
        }

        $config->setData('extensions', $extensions);
    }

    private function allowModuleName(string $moduleName): bool
    {
        return (bool) preg_match('/^(Yireo|YireoTraining)_/', $moduleName);
    }
}
