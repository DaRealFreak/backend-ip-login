<?php

declare(strict_types=1);

namespace SKeuper\BackendIpLogin\Tests\Functional\Hook;

use SKeuper\BackendIpLogin\Hook\PageRendererHook;
use SKeuper\BackendIpLogin\Tests\Functional\AbstractFunctionalTestCase;

/**
 * Verifies the SC_OPTIONS render-preProcess hook registration in ext_localconf.php
 * still works — TYPO3 has historically threatened to remove this hook in favor of
 * PSR-14 events; this test will scream when that happens.
 */
class PageRendererHookRegistrationTest extends AbstractFunctionalTestCase
{
    public function testRenderPreProcessHookIsRegistered(): void
    {
        $hooks = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'] ?? [];
        $expected = PageRendererHook::class . '->pageRendererPreProcessHook';
        self::assertContains($expected, $hooks);
    }
}
