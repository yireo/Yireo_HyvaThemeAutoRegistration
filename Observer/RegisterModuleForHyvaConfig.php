<?php
declare(strict_types=1);

namespace Yireo\HyvaThemeAutoRegistration\Observer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Module\ModuleList;
use Yireo\HyvaThemeAutoRegistration\Utils\HyvaFiles;

class RegisterModuleForHyvaConfig implements ObserverInterface
{
    /**
     * @param ComponentRegistrar $componentRegistrar
     * @param DirectoryList $directoryList
     * @param ModuleList $moduleList
     * @param HyvaFiles $hyvaFiles
     * @param array $moduleNames
     * @param array $modulePrefixes
     * @param array $skipModules
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
     * @param Observer $observer
     * @return void
     * @throws FileSystemException
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $config = $event->getData('config');
        if (!$config instanceof DataObject) {
            return;
        }

        $extensions = $config->getData('extensions');
        if (!is_array($extensions)) {
            $extensions = [];
        }

        foreach ($this->moduleNames as $moduleName) {
            if (!is_string($moduleName)) {
                continue;
            }

            $this->addModule($moduleName, $extensions);
        }

        foreach ($this->moduleList->getAll() as $moduleData) {
            $moduleName = (string)($moduleData['name'] ?? '');
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
            if (!is_string($modulePrefix) || $modulePrefix === '') {
                continue;
            }

            if (str_starts_with($moduleName, $modulePrefix)) {
                return true;
            }
        }

        return false;
    }

    private function getModulePath(string $moduleName): string
    {
        $path = (string)$this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        if ($path === '') {
            return '';
        }

        $root = rtrim($this->directoryList->getRoot(), '/') . '/';
        if (str_starts_with($path, $root)) {
            return substr($path, strlen($root));
        }

        return $path;
    }

    private function isAlreadyDefined(string $path, array $extensions = []): bool
    {
        foreach ($extensions as $extension) {
            if (is_array($extension) && ($extension['src'] ?? null) === $path) {
                return true;
            }
        }

        return false;
    }

    private function addModule(string $moduleName, array &$extensions = [])
    {
        if ($moduleName === '' || in_array($moduleName, $this->skipModules, true)) {
            return;
        }

        $modulePath = $this->getModulePath($moduleName);
        if ($modulePath === '') {
            return;
        }

        if ($this->isAlreadyDefined($modulePath, $extensions)) {
            return;
        }

        $extensions[] = ['src' => $modulePath];
    }
}
