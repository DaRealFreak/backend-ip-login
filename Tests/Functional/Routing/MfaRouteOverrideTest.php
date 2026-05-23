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
        // Router::getRoute() was added in TYPO3 12; iterate getRoutes() so the
        // test runs on 11.5 too. Keys are route identifiers, values are Symfony
        // Route objects on every supported version.
        $routes = iterator_to_array(GeneralUtility::makeInstance(Router::class)->getRoutes());
        self::assertArrayHasKey('auth_mfa', $routes);
        $target = $routes['auth_mfa']->getOption('target');
        self::assertIsString($target);
        self::assertStringContainsString(MfaController::class, $target);
    }
}
