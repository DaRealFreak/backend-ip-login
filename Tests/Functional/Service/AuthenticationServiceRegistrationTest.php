<?php

declare(strict_types=1);

namespace SKeuper\BackendIpLogin\Tests\Functional\Service;

use SKeuper\BackendIpLogin\Service\AuthenticationService;
use SKeuper\BackendIpLogin\Tests\Functional\AbstractFunctionalTestCase;

/**
 * Confirms ExtensionManagementUtility::addService() in ext_localconf.php still
 * lands our entry in $GLOBALS['T3_SERVICES'] after boot, on every supported
 * TYPO3 version.
 */
class AuthenticationServiceRegistrationTest extends AbstractFunctionalTestCase
{
    public function testAuthServiceIsRegistered(): void
    {
        self::assertArrayHasKey('auth', $GLOBALS['T3_SERVICES']);
        // ext_localconf.php passes the class FQN as $serviceKey (3rd arg of addService).
        self::assertArrayHasKey(AuthenticationService::class, $GLOBALS['T3_SERVICES']['auth']);
        $entry = $GLOBALS['T3_SERVICES']['auth'][AuthenticationService::class];
        self::assertSame(AuthenticationService::class, $entry['className']);
        self::assertSame('backend_ip_login', $entry['extKey']);
        self::assertArrayHasKey('getUserBE', $entry['serviceSubTypes']);
        self::assertArrayHasKey('authUserBE', $entry['serviceSubTypes']);
    }
}
