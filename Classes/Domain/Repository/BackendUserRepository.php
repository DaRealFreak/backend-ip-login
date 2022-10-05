<?php

namespace SKeuper\BackendIpLogin\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017-2022 Steffen Keuper <steffen.keuper@web.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Doctrine\DBAL\Driver\Exception;
use PDO;
use SKeuper\BackendIpLogin\Utility\ConfigurationUtility;
use SKeuper\BackendIpLogin\Utility\IpUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class BackendUserRepository extends Repository
{
    /**
     * check if there is an existing backend user with the current settings/ip information
     *
     * @param string $loginIpAddress
     * @param string $loginNetworkAddress
     * @param string $username
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws Exception
     */
    public static function getBackendUsers(string $loginIpAddress, string $loginNetworkAddress, string $username = ''): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_users');
        $query = $queryBuilder
            ->select('*')
            ->from('be_users')
            ->orderBy('admin', 'DESC')
            ->addOrderBy('lastlogin', 'DESC');

        $useNetworkAddress = boolval(ConfigurationUtility::getConfigurationKey("configuration.useNetworkAddress"));
        $allowLocalNetwork = boolval(ConfigurationUtility::getConfigurationKey("option.allowLocalNetwork"));
        $isLocalNetwork = $allowLocalNetwork && IpUtility::isLocalNetworkAddress();

        if (!$loginIpAddress && !$loginNetworkAddress && !$isLocalNetwork) {
            // only allow empty loginIpAddress and loginNetworkAddress on local network
            return [];
        }

        if (!$isLocalNetwork) {
            if ($useNetworkAddress) {
                $query->andWhere($queryBuilder->expr()->eq(
                    'tx_backendiplogin_last_login_ip_network',
                    $queryBuilder->createNamedParameter($loginNetworkAddress))
                );
            } else {
                $query->andWhere($queryBuilder->expr()->eq(
                    'tx_backendiplogin_last_login_ip',
                    $queryBuilder->createNamedParameter($loginIpAddress))
                );
            }
        }

        if ($username) {
            $query->andWhere($queryBuilder->expr()->eq(
                'username',
                $queryBuilder->createNamedParameter($username))
            );
        }

        $query->andWhere($queryBuilder->expr()->neq(
            'username',
            $queryBuilder->createNamedParameter('_cli_', PDO::PARAM_STR)
        ));

        return $query->execute()->fetchAllAssociative();
    }

    /**
     * refresh the saved ip information for the corresponding database user
     *
     * @param string|int $uid
     * @param string $ipAddress
     * @param string $ipNetworkAddress
     */
    public static function updateIpInformation(int $uid, string $ipAddress, string $ipNetworkAddress): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_users');
        $queryBuilder->update('be_users')
            ->set('tx_backendiplogin_last_login_ip', $ipAddress)
            ->set('tx_backendiplogin_last_login_ip_network', $ipNetworkAddress)
            ->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($uid, PDO::PARAM_INT)))
            ->execute();
    }
}