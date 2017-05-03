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

namespace SKeuper\BackendIpLogin\Hook;

use SKeuper\BackendIpLogin\Utility\DatabaseUtility;
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
    const associations = array(
        "t3lib/class.t3lib_pagerenderer.php" => array(
            "render-preProcess" => array(
                "pageRendererPreProcessHook"
            )
        )
    );

    /**
     * @param $parameters
     * @param $Obj
     */
    function pageRendererPreProcessHook($parameters, &$Obj)
    {
        // FixMe:
        // other extensions injecting js code inline into the footer breaks this check
        if ($_SERVER["SCRIPT_NAME"] === "/typo3/index.php" && !$GLOBALS['BE_USER']->user && !$parameters["jsFooterInline"] && DatabaseUtility::existBackendUser()) {
            $typo3Version = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
            if ($typo3Version >= 4000000 and $typo3Version < 6000000) {
                # TYPO3 4.x
                $jsCode = "Ext.select('#t3-login-submit').elements[0].click();";
            } elseif ($typo3Version >= 6000000 and $typo3Version < 7000000) {
                # TYPO3 6.x
                $jsCode = "var lb = Ext.select('#logout-button input').elements[0];lb.setAttribute('disabled','disabled');lb.value = '" . IpUtility::getIP() . "';";
            } elseif ($typo3Version >= 7000000 and $typo3Version < 9000000) {
                # TYPO3 7.x & 8.x added a javascript check for username and password form field, easiest method to
                # disable the check is removing the fields
                $jsCode = "$('#t3-login-username-section').remove();$('#t3-login-password-section').remove();$('#t3-login-submit').click();";
            } else {
                # unknown number, don't take any action in the template
                $jsCode = "";
            }

            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

            /** @var PageRenderer $pageRenderer */
            $pageRenderer = $objectManager->get('TYPO3\CMS\Core\Page\PageRenderer');
            $pageRenderer->addJsFooterInlineCode("backend auto login", $jsCode);

            /*
             * old way to inject js code, deprecated since TYPO3 8.x
             * TYPO3\CMS\Backend\Template\DocumentTemplate->preStartPageHook
             * $Obj->extJScode .= $jsCode;
             */
        }
    }
}