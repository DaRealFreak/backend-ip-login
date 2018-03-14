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

/**
 * Class IpUtility
 * @package SKeuper\BackendIpLogin\Utility
 */
class IpUtility
{

    /**
     * check if the user is from the local network
     *
     * @return bool
     */
    static public function isLocalNetworkAddress()
    {
        // private IP ranges; see RFC 6890 or https://en.wikipedia.org/wiki/Private_network#Private_IPv4_address_spaces
        // ip ranges used for private usage according to https://tools.ietf.org/html/rfc1918
        $localNetworkRules = array(
            array(
                // 10/8
                "NETWORK_ADDRESS" => "10.0.0.0",
                "NETWORK_MASK" => "255.0.0.0"
            ),
            array(
                // 192.168/16
                "NETWORK_ADDRESS" => "192.168.0.0",
                "NETWORK_MASK" => "255.255.0.0"
            ),
            array(
                // 172.16/12
                "NETWORK_ADDRESS" => "172.16.0.0",
                "NETWORK_MASK" => "255.240.0.0"
            )
        );

        // prefer outer proxy over internal ip address
        $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        if (!$clientIp) {
            return False;
        }

        foreach ($localNetworkRules as $localNetworkRule) {
            $networkAddress = self::getNetworkAddress($_SERVER['HTTP_X_FORWARDED_FOR'], $localNetworkRule['NETWORK_MASK']);
            if ($networkAddress == $localNetworkRule['NETWORK_ADDRESS']) {
                return True;
            }
        }
        return False;
    }

    /**
     * calculate the network address based on the given ip address and the network mask
     *
     * @param string $ipAddress
     * @param string $ipNetworkMask
     * @return int
     */
    static public function getNetworkAddress($ipAddress = "", $ipNetworkMask = "")
    {
        if (!$ipAddress) {
            $ipAddress = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }
        if (!$ipNetworkMask) {
            $ipNetworkMask = ConfigurationUtility::getConfigurationKey("configuration.networkMask");
        }
        // convert the ip addresses from string to long
        $ipAddressLong = ip2long($ipAddress);
        $ipNetworkMaskLong = ip2long($ipNetworkMask);

        //calculate network address
        $ipNetworkAddress = $ipAddressLong & $ipNetworkMaskLong;
        return long2ip($ipNetworkAddress);
    }
}