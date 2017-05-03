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

namespace SKeuper\BackendIpLogin\Service;

use SKeuper\BackendIpLogin\Utility\IpUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;

class AuthenticationService extends \TYPO3\CMS\Sv\AuthenticationService
{
    /**
     * if the username and password is empty try to select the user based on the
     * ip address or network address, else use the original function
     *
     * @return bool|array
     */
    public function getUser()
    {
        if ($this->login['status'] !== 'login') {
            return false;
        }

        // ToDo:
        // add local network option and case

        // use case for the auto login, since we automatically continue from the backend username and password is empty
        if ((string)$this->login['uident_text'] === '' && (string)$this->login['uname'] === '') {
            /** @var DatabaseConnection $t3db */
            $t3db = $GLOBALS['TYPO3_DB'];
            $loginNetworkAddress = IpUtility::getIpNetworkAddress();
            $whereClause = $this->db_user['check_pid_clause'] . ' AND ' . $this->db_user['enable_clause'];
            $whereClause .= ' AND `be_users`.`tx_backendiplogin_last_login_ip_network` = ' .
                $t3db->fullQuoteStr($loginNetworkAddress, "be_users");
            // ToDo
            // I got no idea what we should do on multiple accounts
            // so far it prioritizes admin users and picks the first one based on last login timestamp
            $dbRes = $t3db->exec_SELECTgetSingleRow(
                '*',
                $this->db_user['table'],
                $whereClause,
                '',
                '`be_users`.`admin` DESC, `be_users`.`lastlogin` DESC'
            );

            if ($dbRes) {
                return $dbRes;
            } else {
                return false;
            }
        } else {
            $user = parent::getUser();
        }
        return $user;
    }

    /**
     * Authenticate a user (Check various conditions for the user that might invalidate its authentication, eg. password match, domain, IP, etc.)
     *
     * @param array $user Data of user.
     * @return boolean
     */
    public function authUser(array $user)
    {
        // use case for the auto login, since we automatically continue from the backend username and password is empty
        if ((string)$this->login['uident_text'] === '' && (string)$this->login['uname'] === '') {
            // ToDo
            // differentiate between choosing from network address or ip address
            $loginIp = IpUtility::getIP();
            $loginNetworkAddress = IpUtility::getIpNetworkAddress();
            return 200;
        } else {
            return 100;
        }
    }

}