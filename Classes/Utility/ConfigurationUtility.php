<?php

namespace SKeuper\BackendIpLogin\Utility;

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

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationUtility
{
    const EXTKEY = "backend_ip_login";

    /**
     * return the value of the requested key if set
     *
     * @param string $key
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getConfigurationKey(string $key): ?string
    {
        /** @var array $extConf */
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(self::EXTKEY);
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
    }
}