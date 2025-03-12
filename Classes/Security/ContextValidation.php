<?php

namespace SKeuper\BackendIpLogin\Security;

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

use SKeuper\BackendIpLogin\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Core\Environment;

class ContextValidation
{
    /**
     * validates the security settings for the current context
     *
     * @param bool $includeMfaCheck
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function validateContext(bool $includeMfaCheck = false): bool
    {
        $disabledInProductionContext = ConfigurationUtility::getConfigurationKey("option.disableInProductionContext");
        if ($disabledInProductionContext && Environment::getContext()->isProduction()) {
            // disable in production context
            return false;
        }

        $limitToDdevContext = ConfigurationUtility::getConfigurationKey("option.limitToDdevContext");
        if ($limitToDdevContext && getenv('IS_DDEV_PROJECT') !== 'true') {
            // limit to ddev context
            return false;
        }

        if ($includeMfaCheck) {
            $disableMultiFactorAuth = ConfigurationUtility::getConfigurationKey("option.disableMultiFactorAuth");
            if (!$disableMultiFactorAuth) {
                // do not disable the MFA check, user still has to pass the MFA
                return false;
            }
        }

        return true;
    }
}