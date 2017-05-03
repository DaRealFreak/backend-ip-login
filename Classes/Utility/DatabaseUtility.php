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

namespace SKeuper\BackendIpLogin\Utility;

use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Class DatabaseUtility
 * @package SKeuper\BackendIpLogin\Utility
 */
class DatabaseUtility
{

    public static function existBackendUser()
    {
        /** @var DatabaseConnection $t3db */
        $t3db = $GLOBALS['TYPO3_DB'];
        // ToDo
        // differentiate between choosing from network address or ip address
        $loginNetworkAddress = IpUtility::getIpNetworkAddress();
        $whereCondition = '`pid` IN (0) 
            AND (`be_users`.`deleted` = 0) 
            AND (`be_users`.`disable` = 0) 
            AND (`be_users`.`starttime` <= 1493392140) 
            AND ((`be_users`.`endtime` = 0) OR (`be_users`.`endtime` > 1493392140)) 
            AND (`be_users`.`pid` = 0) AND `be_users`.`tx_backendiplogin_last_login_ip_network` = ' .
            $t3db->fullQuoteStr($loginNetworkAddress, "be_users");

        $dbRes = $t3db->exec_SELECTgetSingleRow(
            "*",
            "be_users",
            $whereCondition,
            "`be_users`.`admin` DESC, `be_users`.`lastlogin` DESC"
        );
        return !empty($dbRes);
    }

}