<?php

declare(strict_types=1);

namespace SKeuper\BackendIpLogin\Tests\Functional\Domain\Repository;

use SKeuper\BackendIpLogin\Domain\Repository\BackendUserRepository;
use SKeuper\BackendIpLogin\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Verifies ext_tables.sql added the two tx_backendiplogin columns to be_users
 * and that BackendUserRepository queries them via the QueryBuilder API the
 * installed TYPO3 version exposes.
 */
class BackendUserRepositoryTest extends AbstractFunctionalTestCase
{
    public function testIpColumnsExistOnBeUsers(): void
    {
        $columns = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('be_users')
            ->createSchemaManager()
            ->listTableColumns('be_users');

        $names = array_map(static fn($c) => $c->getName(), $columns);
        self::assertContains('tx_backendiplogin_last_login_ip', $names);
        self::assertContains('tx_backendiplogin_last_login_ip_network', $names);
    }

    public function testGetBackendUsersQueryExecutes(): void
    {
        // Smoke test: the QueryBuilder chain in getBackendUsers() must not throw
        // on the installed TYPO3 version (catches expr()->eq / createNamedParameter drift).
        // Empty test DB → no users match → empty result.
        self::assertSame([], BackendUserRepository::getBackendUsers('127.0.0.1', '127.0.0.0'));
    }
}
