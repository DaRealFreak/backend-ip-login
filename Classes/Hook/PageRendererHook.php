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
    public function pageRendererPreProcessHook($parameters, &$Obj)
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
            if ($typo3Version >= 4000000 and $typo3Version < 6000000) {
                // 4.x is not supported anymore, probably only adding it if I personally need it
                # TYPO3 4.x
                $jsCode = "Ext.select('#t3-login-submit').elements[0].click();";
            } elseif ($typo3Version >= 6000000 and $typo3Version < 7000000) {
                // 6.x is not supported anymore, probably only adding it if I personally need it
                # TYPO3 6.x
                $jsCode = "var lb = Ext.select('#logout-button input').elements[0];lb.setAttribute('disabled','disabled');lb.value = '" . GeneralUtility::getIndpEnv('REMOTE_ADDR') . "';";
            } elseif ($typo3Version >= 7000000 and $typo3Version < 10000000) {
                # TYPO3 7.x & 8.x added a javascript check for username and password form field, easiest method to
                # disable the check is removing the fields
                $cssFiles = [
                    "EXT:backend_ip_login/Resources/Public/css/login.css",
                ];
                if (ConfigurationUtility::getConfigurationKey("option.displayAccounts")) {
                    $jsCode = @file_get_contents(PATH_site . "typo3conf/ext/backend_ip_login/Resources/Public/js/login.js");
                    foreach (array_reverse($backendUsers) as $backendUser) {
                        $jsCode .= sprintf("userform.prepend('%s');",
                            '<button type="button" class="btn btn-block btn-login">' . $backendUser['username'] . '</button>'
                        );
                    }
                } else {
                    $jsCode = "$('#t3-login-username-section').remove();$('#t3-login-password-section').remove();$('#t3-login-submit').click();";
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
    private function executePostLoginHook()
    {
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postLoginSuccessProcessing'])) {
            $_params = [];
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postLoginSuccessProcessing'] as $hook) {
                GeneralUtility::callUserFunction($hook, $_params, $this);
            }
        }
    }
}