<?php
declare(strict_types=1);

namespace Yireo\HyvaCheckoutUtils\Observer;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Module\ModuleList;
use Yireo\HyvaCheckoutUtils\Utils\HyvaFiles;

class RegisterModuleForHyvaConfig implements ObserverInterface
{
    /**
     * @param ComponentRegistrar $componentRegistrar
     * @param array $moduleNames
     */
    public function __construct(
        private ComponentRegistrar $componentRegistrar,
        private FileDriver $fileDriver,
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
    public function execute(Observer $event)
    {
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

            if (false === $this->hasHyvaFiles($moduleName)) {
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

    /**
     * @param string $moduleName
     * @return bool
     * @throws FileSystemException
     */
    private function hasHyvaFiles(string $moduleName): bool
    {
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        foreach ($this->hyvaFiles->getFiles() as $file) {
            if ($this->fileDriver->isExists($path . '/' . $file)) {
                return true;
            }
        }

        return false;
    }
}
