<?php

declare(strict_types=1);

namespace Yireo\HyvaThemeAutoRegistration\Test\Integration;

use PHPUnit\Framework\TestCase;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsEnabled;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegistered;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegisteredForReal;

class ModuleTest extends TestCase
{
    use AssertModuleIsEnabled;
    use AssertModuleIsRegistered;
    use AssertModuleIsRegisteredForReal;
    
    public function testModuleIsEnabled()
    {
        $moduleName = 'Yireo_HyvaThemeAutoRegistration';
        
        $this->assertModuleIsRegistered($moduleName);
        $this->assertModuleIsRegisteredForReal($moduleName);
        $this->assertModuleIsEnabled($moduleName);
    }
}