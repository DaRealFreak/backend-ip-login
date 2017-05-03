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

namespace SKeuper\BackendIpLogin\Utility;

/**
 * Class IpUtility
 * @package SKeuper\BackendIpLogin\Utility
 */
class IpUtility
{
    // ToDo:
    // extract this to settings
    const ipNmask = "255.255.255.0";

    /**
     * check if the user is from the local network
     *
     * @return bool
     */
    static public function isLocalNetworkAddress() {
        return boolval(preg_match('/(192.168.1.[\d]+|127.0.0.1)/', self::getIP()));
    }

    /**
     * returns the ip based on the most common keys
     *
     * @return string
     */
    static public function getIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * get the network address based on the network mask
     *
     * @param $ipAddress
     * @return int
     */
    static public function getIpNetworkAddress($ipAddress="") {
        if (!$ipAddress) {
            $ipAddress = self::getIP();
        }
        // convert the ip addresses from string to long
        $ipAddressLong = ip2long($ipAddress);
        $ipNmaskLong = ip2long(self::ipNmask);

        //calculate network address
        $ipNetworkAddress = $ipAddressLong & $ipNmaskLong;
        return long2ip($ipNetworkAddress);
    }
}