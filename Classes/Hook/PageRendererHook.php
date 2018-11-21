<?php

namespace SKeuper\BackendIpLogin\Hook;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017-2018 Steffen Keuper <steffen.keuper@web.de>
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


use SKeuper\BackendIpLogin\Domain\Repository\BackendUserRepository;
use SKeuper\BackendIpLogin\Domain\Session\BackendSessionHandler;
use SKeuper\BackendIpLogin\Utility\ConfigurationUtility;
use SKeuper\BackendIpLogin\Utility\IpUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class for using the Hook defined in the PageRenderer class
 *
 * Class PageRendererHook
 * @package SKeuper\BackendIpLogin\Classes\Hook
 */
class PageRendererHook
{
    use \SKeuper\BackendIpLogin\Component\HookRegisterComponent;

    /*
     * you can define your hooks here, library -> hook -> function name
     */
    const associations = [
        "t3lib/class.t3lib_pagerenderer.php" => [
            "render-preProcess" => [
                "pageRendererPreProcessHook"
            ]
        ]
    ];

    /**
     * The pageRendererPreProcess hook.
     * Here we check the TYPO3 version and inject our additional HTML/Javascript code to modify the login page
     *
     * @param $parameters
     * @param $Obj
     */
    public function pageRendererPreProcessHook(array $parameters, PageRenderer &$Obj)
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // FixMe other extensions injecting js code inline into the footer breaks this check
        if (TYPO3_MODE == 'BE' && !$GLOBALS['BE_USER']->user
            && !$parameters["jsFooterInline"]
            && $backendUsers = BackendUserRepository::getBackendUsers(
                GeneralUtility::getIndpEnv('REMOTE_ADDR'),
                IpUtility::getNetworkAddress()
            )
        ) {
            $cssFiles = [];
            $jsFiles = [];
            $typo3Version = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
            if ($typo3Version >= 7000000 && $typo3Version < 10000000) {
                $cssFiles = [
                    "EXT:backend_ip_login/Resources/Public/css/login.css",
                ];
                if (ConfigurationUtility::getConfigurationKey("option.displayAccounts")) {
                    $jsCode = @file_get_contents(GeneralUtility::getFileAbsFileName('EXT:backend_ip_login/Resources/Public/js/list_accounts.js'));
                    foreach (array_reverse($backendUsers) as $backendUser) {
                        $jsCode .= sprintf("userform.insertBefore(htmlToElement('%s'), userform.firstChild);",
                            '<button type="button" class="btn btn-block btn-login btn-autologin">' . $backendUser['username'] . '</button>'
                        );
                    }
                } else {
                    $jsFiles[] = 'EXT:backend_ip_login/Resources/Public/js/auto_login.js';
                }
            } else {
                # unknown number, don't take any action in the template
                $jsCode = "";
            }

            /** @var PageRenderer $pageRenderer */
            $pageRenderer = $objectManager->get(PageRenderer::class);
            /*
             * old way to inject js code, deprecated since TYPO3 8.x
             * TYPO3\CMS\Backend\Template\DocumentTemplate->preStartPageHook
             * $Obj->extJScode .= $jsCode;
             */
            $pageRenderer->addJsFooterInlineCode("backend auto login", $jsCode);

            foreach ($cssFiles as $cssFile) {
                $pageRenderer->addCssFile($cssFile);
            }
            foreach ($jsFiles as $jsFile) {
                $pageRenderer->addJsFooterFile($jsFile);
            }
        }

        // save the ip address and network address on successful login
        /** @var BackendSessionHandler $backendSessionHandler */
        $backendSessionHandler = $objectManager->get(BackendSessionHandler::class);
        if ($GLOBALS['BE_USER']->user && !$backendSessionHandler->get("saved_ip")) {
            $this->executePostLoginHook();
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

    /**
     * Execute PostLoginHook for possible manipulation
     */
    private function executePostLoginHook(): void
    {
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postLoginSuccessProcessing'])) {
            $_params = [];
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postLoginSuccessProcessing'] as $hook) {
                GeneralUtility::callUserFunction($hook, $_params, $this);
            }
        }
    }
}