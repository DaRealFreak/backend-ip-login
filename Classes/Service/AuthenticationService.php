<?php

namespace SKeuper\BackendIpLogin\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017-2023 Steffen Keuper <steffen.keuper@web.de>
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
use SKeuper\BackendIpLogin\Domain\Repository\BackendUserRepository;
use SKeuper\BackendIpLogin\Security\ContextValidation;
use SKeuper\BackendIpLogin\Utility\ConfigurationUtility;
use SKeuper\BackendIpLogin\Utility\IpUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AuthenticationService extends \TYPO3\CMS\Core\Authentication\AuthenticationService
{
    /**
     * if the username and password is empty try to select the user based on the
     * ip address or network address, else use the original function
     *
     * @return bool|array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws Exception
     */
    public function getUser()
    {
        // check the security settings if the extension functionality should be active
        if (!ContextValidation::validateContext()) {
            return false;
        }

        if ($this->login['status'] !== 'login') {
            return false;
        }

        $displayAccounts = ConfigurationUtility::getConfigurationKey("option.displayAccounts");

        // use case for the auto login, since we automatically continue from the backend with an empty username and password
        if ((!$displayAccounts && (string)$this->login['uident_text'] === '' && (string)$this->login['uname'] === '') ||
            ($displayAccounts && (string)$this->login['uname'] !== '' && (string)$this->login['uident_text'] === '')
        ) {
            if ($backendUsers = BackendUserRepository::getBackendUsers(
                GeneralUtility::getIndpEnv('REMOTE_ADDR'),
                IpUtility::getNetworkAddress(),
                $this->login['uname'])
            ) {
                return $backendUsers[0];
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
     * @return int
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function authUser(array $user): int
    {
        // check the security settings if the extension functionality should be active
        if (!ContextValidation::validateContext()) {
            return 100;
        }

        $displayAccounts = ConfigurationUtility::getConfigurationKey("option.displayAccounts");

        // use case for the auto login, since we automatically continue from the backend username and password is empty
        if ((!$displayAccounts && (string)$this->login['uident_text'] === '' && (string)$this->login['uname'] === '') ||
            ($displayAccounts && (string)$this->login['uname'] !== '' && (string)$this->login['uident_text'] === '')
        ) {
            if (self::isAllowedByIP($user)) {
                return 200;
            } else {
                return 100;
            }
        } else {
            return 100;
        }
    }

    /**
     * function to check current extension configuration
     * and cross-validates the user based on the ip address or network address
     * to check if the user is allowed to log-in based on his network information alone
     *
     * @param array $user
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isAllowedByIP(array $user): bool
    {
        $useNetworkAddress = boolval(ConfigurationUtility::getConfigurationKey("configuration.useNetworkAddress"));
        $allowLocalNetwork = boolval(ConfigurationUtility::getConfigurationKey("option.allowLocalNetwork"));
        if (($useNetworkAddress && ($user['tx_backendiplogin_last_login_ip_network'] ?? '') === IpUtility::getNetworkAddress())
            || (!$useNetworkAddress && ($user['tx_backendiplogin_last_login_ip'] ?? '') === GeneralUtility::getIndpEnv('REMOTE_ADDR'))
            || ($allowLocalNetwork && IpUtility::isLocalNetworkAddress())
        ) {
            return true;
        }
        return false;
    }

}
