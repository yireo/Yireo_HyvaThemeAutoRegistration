<?php
declare(strict_types=1);

namespace Yireo\HyvaThemeAutoRegistration\Utils;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File as FileDriver;

class HyvaFiles
{
    public function __construct(
        private ComponentRegistrar $componentRegistrar,
        private FileDriver $fileDriver,
    ) {
    }
    public function getFiles(): array
    {
        return [
            'view/frontend/tailwind/tailwind.config.js',
            'view/frontend/tailwind/tailwind-source.css'
        ];
    }
    
    /**
     * @param string $moduleName
     * @return bool
     * @throws FileSystemException
     */
    public function hasHyvaFiles(string $moduleName): bool
    {
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        foreach ($this->getFiles() as $file) {
            if ($this->fileDriver->isExists($path . '/' . $file)) {
                return true;
            }
        }
        
        return false;
    }
}
