<?php
declare(strict_types=1);

namespace Yireo\HyvaCheckoutUtils\Observer;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RegisterModuleForHyvaConfig implements ObserverInterface
{
    /**
     * @param ComponentRegistrar $componentRegistrar
     * @param array $moduleNames
     */
    public function __construct(
        private ComponentRegistrar $componentRegistrar,
        private array $moduleNames = []
    )
    {}

    public function execute(Observer $event)
    {
        $config = $event->getData('config');
        $extensions = $config->hasData('extensions') ? $config->getData('extensions') : [];

        foreach ($this->moduleNames as $moduleName) {
            $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
            $extensions[] = ['src' => str_replace(BP, '', $path)];
        }

        $config->setData('extensions', $extensions);
    }
}
