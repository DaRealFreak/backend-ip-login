<?php

declare(strict_types=1);

namespace SKeuper\BackendIpLogin\Tests\Functional\Routing;

use SKeuper\BackendIpLogin\Controller\MfaController;
use SKeuper\BackendIpLogin\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3\CMS\Backend\Routing\Router;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Confirms the auth_mfa route override in Configuration/Backend/Routes.php
 * still wins over the core registration. If TYPO3 changes route loading order
 * or renames the auth_mfa route, this test fails.
 */
class MfaRouteOverrideTest extends AbstractFunctionalTestCase
{
    public function testAuthMfaRoutePointsToOurController(): void
    {
        $route = GeneralUtility::makeInstance(Router::class)->getRoute('auth_mfa');
        $target = $route->getOption('target');
        self::assertIsString($target);
        self::assertStringContainsString(MfaController::class, $target);
    }
}
