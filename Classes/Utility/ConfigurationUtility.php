<?php

namespace SKeuper\BackendIpLogin\Utility;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class ConfigurationUtility
{
    const EXTKEY = "backend_ip_login";

    /**
     * return the value of the requested key if set
     *
     * @param $key
     * @return mixed|NULL
     */
    public static function getConfigurationKey($key)
    {
        $typo3Version = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
        if ($typo3Version >= 9000000) {
            /** @var array $extConf */
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
            /** @noinspection PhpUndefinedClassInspection */
            $extConf = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get(self::EXTKEY);
            $items = explode('.', $key);
            foreach ($items as $keyPart) {
                if (!array_key_exists($keyPart, $extConf)) {
                    return null;
                }
                if (end($items) == $keyPart) {
                    return $extConf[$keyPart];
                }
                $extConf = $extConf[$keyPart];
            }
            return null;
        } else {
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            /** @var array $extensionConfiguration */
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
            /** @noinspection PhpUndefinedClassInspection */
            $extensionConfiguration = $objectManager->get(\TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility::class)->getCurrentConfiguration(self::EXTKEY);
            if (array_key_exists($key, $extensionConfiguration)) {
                return $extensionConfiguration[$key]['value'];
            } else {
                return NULL;
            }
        }
    }
}