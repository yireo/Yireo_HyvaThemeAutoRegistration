<?php
declare(strict_types=1);

namespace Yireo\HyvaThemeAutoRegistration\Observer;

use Magento\Framework\App\Filesystem\DirectoryList;
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
        private DirectoryList $directoryList,
        private ModuleList $moduleList,
        private HyvaFiles $hyvaFiles,
        private array $moduleNames = [],
        private array $modulePrefixes = [],
        private array $skipModules = [],
    ) {
    }

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
            $this->addModule($moduleName, $extensions);
        }

        foreach ($this->moduleList->getAll() as $moduleData) {
            $moduleName = $moduleData['name'];
            if (false === $this->allowModuleName($moduleName)) {
                continue;
            }

            if (false === $this->hyvaFiles->hasHyvaFiles($moduleName)) {
                continue;
            }

            $this->addModule($moduleName, $extensions);
        }

        $config->setData('extensions', $extensions);
    }

    private function allowModuleName(string $moduleName): bool
    {
        foreach ($this->modulePrefixes as $modulePrefix) {
            if (str_contains($moduleName, $modulePrefix)) {
                return true;
            }
        }

        return false;
    }

    private function getModulePath(string $moduleName): string
    {
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        if (false === strstr($path, $this->directoryList->getRoot())) {
            return $path;
        }

        if (defined('BP')) {
            $path = str_replace(BP, '', $path);
        }

        return trim($path, '/');
    }

    private function isAlreadyDefined(string $path, array $extensions = []): bool
    {
        foreach ($extensions as $extension) {
            if ($extension['src'] === $path) {
                return true;
            }
        }

        return false;
    }

    private function addModule(string $moduleName, array &$extensions = [])
    {
        if (in_array($moduleName, $this->skipModules)) {
            return;
        }

        $modulePath = $this->getModulePath($moduleName);
        if ($this->isAlreadyDefined($modulePath, $extensions)) {
            return;
        }

        $extensions[] = ['src' => $modulePath];
    }
}
