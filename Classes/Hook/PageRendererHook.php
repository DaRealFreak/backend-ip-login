<?php

namespace SKeuper\BackendIpLogin\Hook;

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
use Psr\Http\Message\ServerRequestInterface;
use SKeuper\BackendIpLogin\Domain\Repository\BackendUserRepository;
use SKeuper\BackendIpLogin\Domain\Session\BackendSessionHandler;
use SKeuper\BackendIpLogin\Security\ContextValidation;
use SKeuper\BackendIpLogin\Utility\ConfigurationUtility;
use SKeuper\BackendIpLogin\Utility\IpUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class for using the Hook defined in the PageRenderer class
 */
class PageRendererHook
{
    /**
     * The pageRendererPreProcess hook.
     * Here we check the TYPO3 version and inject our additional HTML/Javascript code to modify the login page
     *
     * @param array $parameters
     * @param PageRenderer $pageRenderer
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws Exception
     */
    public function pageRendererPreProcessHook(array $parameters, PageRenderer $pageRenderer): void
    {
        $isBackend = ($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
            && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend();

        // only apply hook in the backend
        if (!$isBackend) {
            return;
        }

        // check security context if the hook should be applied
        if (!ContextValidation::validateContext()) {
            return;
        }

        if (!($GLOBALS['BE_USER']->user['uid'] ?? false)
            && $backendUsers = BackendUserRepository::getBackendUsers(
                GeneralUtility::getIndpEnv('REMOTE_ADDR'),
                IpUtility::getNetworkAddress()
            )
        ) {
            $jsFiles = [];
            $cssFiles = [
                "EXT:backend_ip_login/Resources/Public/Css/login.css",
            ];

            if (ConfigurationUtility::getConfigurationKey("option.displayAccounts")) {
                $userHtml = '<div id="backend-ip-login-accounts">';
                foreach (array_reverse($backendUsers) as $backendUser) {
                    $userHtml .= '<div class="backend-ip-login-account" data-username="' . $backendUser['username'] . '"></div>';
                }
                $userHtml .= '</div>';

                // add HTML to generate the account list with the loaded Javascript files
                // since inline Javascript is not allowed in TYPO3 12 anymore by default
                $pageRenderer->addBodyContent($userHtml);

                // load scripts for the account list
                $jsFiles[] = 'EXT:backend_ip_login/Resources/Public/JavaScript/list_accounts.js';
                $jsFiles[] = 'EXT:backend_ip_login/Resources/Public/JavaScript/scroll_top.js';
            } else {
                $jsFiles[] = 'EXT:backend_ip_login/Resources/Public/JavaScript/auto_login.js';
            }

            foreach ($cssFiles as $cssFile) {
                $pageRenderer->addCssFile($cssFile);
            }

            foreach ($jsFiles as $jsFile) {
                $pageRenderer->addJsFooterFile($jsFile);
            }
        }

        // save the ip address and network address on successful login
        /** @var BackendSessionHandler $backendSessionHandler */
        $backendSessionHandler = GeneralUtility::makeInstance(BackendSessionHandler::class);
        if ($GLOBALS['BE_USER']->user['uid'] ?? false && !$backendSessionHandler->get("saved_ip")) {
            $allowLocalNetwork = boolval(ConfigurationUtility::getConfigurationKey("option.allowLocalNetwork"));
            // don't update the ip information if accessed from the local network
            if (!($allowLocalNetwork && IpUtility::isLocalNetworkAddress())) {
                BackendUserRepository::updateIpInformation(
                    $GLOBALS['BE_USER']->user['uid'],
                    GeneralUtility::getIndpEnv('REMOTE_ADDR'),
                    IpUtility::getNetworkAddress()
                );
            }
            $backendSessionHandler->store("saved_ip", true);
        }
    }
}
