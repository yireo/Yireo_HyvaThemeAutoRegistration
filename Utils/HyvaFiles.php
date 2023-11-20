<?php
declare(strict_types=1);

namespace Yireo\HyvaCheckoutUtils\Utils;

use Magento\Framework\Component\ComponentRegistrar;

class HyvaFiles
{
    public function getFiles(): array
    {
        return [
            'view/frontend/tailwind/tailwind.config.js',
            'view/frontend/tailwind/tailwind-source.css'
        ];
    }
}
