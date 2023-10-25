<?php
declare(strict_types=1);

namespace Yireo\HyvaCheckoutUtils\Utils;

use Magento\Framework\Component\ComponentRegistrar;

class RegisterHyvaModule
{
    public function __construct(
        private ComponentRegistrar $componentRegistrar
    ) {}


}
