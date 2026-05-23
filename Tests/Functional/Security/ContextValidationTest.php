<?php

declare(strict_types=1);

namespace SKeuper\BackendIpLogin\Tests\Functional\Security;

use SKeuper\BackendIpLogin\Security\ContextValidation;
use SKeuper\BackendIpLogin\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3\CMS\Core\Core\Environment;

/**
 * Boots a real TYPO3 so the Environment / ApplicationContext stack is exercised.
 * Pinpoints whether Environment::getContext()->isProduction()/isDevelopment() exists
 * in the TYPO3 version under test.
 */
class ContextValidationTest extends AbstractFunctionalTestCase
{
    public function testValidateContextDisablesInProductionByDefault(): void
    {
        // Exercises Environment::getContext()->isProduction() via ContextValidation.
        // If isProduction() doesn't exist on the installed TYPO3 version, this fatals.
        self::assertFalse(Environment::getContext()->isProduction());
        self::assertTrue(ContextValidation::validateContext());
    }
}
