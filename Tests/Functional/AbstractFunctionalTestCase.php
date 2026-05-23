<?php

declare(strict_types=1);

namespace SKeuper\BackendIpLogin\Tests\Functional;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Base for functional tests that need the extension loaded.
 */
abstract class AbstractFunctionalTestCase extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'skeuper/backend-ip-login',
    ];
}
