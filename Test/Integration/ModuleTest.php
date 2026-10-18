<?php

declare(strict_types=1);

namespace Yireo\HyvaThemeAutoRegistration\Test\Integration;

use PHPUnit\Framework\TestCase;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsEnabled;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegistered;

class ModuleTest extends TestCase
{
    use AssertModuleIsEnabled;
    use AssertModuleIsRegistered;
    
    public function testModuleIsEnabled()
    {
        $moduleName = 'Yireo_HyvaThemeAutoRegistration';
        
        $this->assertModuleIsRegistered($moduleName);
        $this->assertModuleIsEnabled($moduleName);
    }
}