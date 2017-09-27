<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Steffen Keuper <steffen.keuper@web.de>
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

namespace SKeuper\BackendIpLogin\Domain\Repository;

use SKeuper\BackendIpLogin\Utility\ConfigurationUtility;
use SKeuper\BackendIpLogin\Utility\IpUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Class BackendUserRepository
 * @package SKeuper\BackendIpLogin\Domain\Repository
 */
class BackendUserRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * check if there is an existing backend user with the current settings/ip informations
     *
     * @param string $loginIpAddress
     * @param string $loginNetworkAddress
     * @param string $username
     * @return array
     */
    public static function getBackendUsers($loginIpAddress="", $loginNetworkAddress="", $username="")
    {
        /** @var DatabaseConnection $t3db */
        $t3db = $GLOBALS['TYPO3_DB'];
        $whereCondition = '(`be_users`.`deleted` = 0) 
            AND (`be_users`.`disable` = 0) 
            AND (`be_users`.`starttime` <= ' . (string)time() . ') 
            AND ((`be_users`.`endtime` = 0) OR (`be_users`.`endtime` > ' . (string)time() . ')) 
            AND (`be_users`.`pid` = 0)';

        $useNetworkAddress = boolval(ConfigurationUtility::getConfigurationKey("configuration.useNetworkAddress"));
        $allowLocalNetwork = boolval(ConfigurationUtility::getConfigurationKey("option.allowLocalNetwork"));
        $isLocalNetwork = $allowLocalNetwork && IpUtility::isLocalNetworkAddress();

        if (!$loginIpAddress && !$loginNetworkAddress && !$isLocalNetwork) {
            // only allow empty loginIpAddress and loginNetworkAddress on local network
            return [];
        }

        if (!$isLocalNetwork) {
            if ($useNetworkAddress) {
                $whereCondition .= ' AND `be_users`.`tx_backendiplogin_last_login_ip_network` = ' .
                    $t3db->fullQuoteStr($loginNetworkAddress, "be_users");
            } elseif (!$useNetworkAddress) {
                $whereCondition .= ' AND `be_users`.`tx_backendiplogin_last_login_ip` = ' .
                    $t3db->fullQuoteStr($loginIpAddress, "be_users");
            }
        }
        if ($username) {
            $whereCondition .= ' AND `be_users`.`username` = ' .
                $t3db->fullQuoteStr($username, "be_users");
        }

        $dbRes = $t3db->exec_SELECTgetRows(
            "*",
            "be_users",
            $whereCondition,
            "",
            "`be_users`.`admin` DESC, `be_users`.`lastlogin` DESC"
        );
        return $dbRes;
    }

    /**
     * refresh the saved ip information for the corresponding database user
     *
     * @param string|int $uid
     * @param string $ipAddress
     * @param string $ipNetworkAddress
     * @return bool
     */
    public static function updateIpInformation($uid, $ipAddress, $ipNetworkAddress)
    {
        /** @var DatabaseConnection $t3db */
        $t3db = $GLOBALS['TYPO3_DB'];
        $res = $t3db->exec_UPDATEquery(
            "be_users bu",
            "bu.uid = " . (int)$uid,
            array(
                "tx_backendiplogin_last_login_ip" => $ipAddress,
                "tx_backendiplogin_last_login_ip_network" => $ipNetworkAddress
            )
        );
        return $res;
    }
}