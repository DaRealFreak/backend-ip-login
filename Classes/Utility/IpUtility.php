<?php

namespace SKeuper\BackendIpLogin\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017-2026 Steffen Keuper <steffen.keuper@web.de>
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

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Http\NormalizedParams;

class IpUtility
{
    /**
     * IPv4 private/loopback/link-local ranges (RFC 1918, RFC 5735, RFC 3927).
     */
    private const IPV4_LOCAL_RULES = [
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '127.0.0.0/8',
        '169.254.0.0/16',
    ];

    /**
     * IPv6 loopback, unique local addresses, link-local (RFC 4291, RFC 4193).
     */
    private const IPV6_LOCAL_RULES = [
        '::1/128',
        'fc00::/7',
        'fe80::/10',
    ];

    /**
     * Resolve client IP from the current PSR-7 request via NormalizedParams, which honors trustedProxies / X-Forwarded-For.
     * Returns '' when no request is in scope (CLI, early bootstrap).
     * IPv6 addresses are canonicalized so that equality comparisons work across representations.
     */
    public static function getClientIp(): string
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return '';
        }
        $normalizedParams = $request->getAttribute('normalizedParams');
        if (!$normalizedParams instanceof NormalizedParams) {
            return '';
        }
        return self::canonicalize($normalizedParams->getRemoteAddress());
    }

    /**
     * Check whether the client IP is from a private/loopback/link-local range,
     * for either IPv4 or IPv6.
     *
     * @return bool
     */
    public static function isLocalNetworkAddress(): bool
    {
        $clientIp = self::getClientIp();
        if ($clientIp === '') {
            return false;
        }

        $rules = self::isIpv6($clientIp) ? self::IPV6_LOCAL_RULES : self::IPV4_LOCAL_RULES;
        foreach ($rules as $cidr) {
            if (self::ipInCidr($clientIp, $cidr)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Calculate the network address of an IP using either a configured IPv4 mask
     * (dotted-quad, e.g., 255.255.255.0) or a configured IPv6 prefix length.
     *
     * @param string $ipAddress IPv4 or IPv6 address. Empty -> use the client IP.
     * @param string $ipNetworkMask Override: IPv4 dotted-quad or numeric prefix length. Empty -> use config.
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getNetworkAddress(string $ipAddress = '', string $ipNetworkMask = ''): string
    {
        if ($ipAddress === '') {
            $ipAddress = self::getClientIp();
        }
        if ($ipAddress === '') {
            return '';
        }

        $prefix = self::isIpv6($ipAddress)
            ? self::resolveIpv6Prefix($ipNetworkMask)
            : self::resolveIpv4Prefix($ipNetworkMask);

        return self::applyPrefix($ipAddress, $prefix);
    }

    /**
     * Test whether $ip falls inside $cidr (e.g. "10.0.0.0/8" or "fc00::/7").
     *
     * @param string $ip
     * @param string $cidr
     * @return bool
     */
    private static function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $prefix] = explode('/', $cidr, 2);
        return self::applyPrefix($ip, (int)$prefix) === self::canonicalize($subnet);
    }

    /**
     * Mask $ip down to its $prefix-bit network address and return the canonical form.
     *
     * @param string $ip
     * @param int $prefix
     * @return string
     */
    private static function applyPrefix(string $ip, int $prefix): string
    {
        $packed = @inet_pton($ip);
        if ($packed === false) {
            return '';
        }

        $bytes = strlen($packed);
        $maxBits = $bytes * 8;
        $prefix = max(0, min($maxBits, $prefix));

        $fullBytes = intdiv($prefix, 8);
        $remainder = $prefix % 8;

        $mask = str_repeat("\xff", $fullBytes);
        if ($remainder !== 0 && $fullBytes < $bytes) {
            $mask .= chr(0xff & (0xff << (8 - $remainder)));
        }

        $mask = str_pad($mask, $bytes, "\x00");

        return inet_ntop($packed & $mask);
    }

    /**
     * Convert an IP address to its canonical form (e.g., IPv4 to dotted-quad).
     *
     * @param string $ip
     * @return string
     */
    private static function canonicalize(string $ip): string
    {
        $packed = @inet_pton($ip);
        return $packed === false ? $ip : inet_ntop($packed);
    }

    /**
     * @param string $ip
     * @return bool
     */
    private static function isIpv6(string $ip): bool
    {
        return (bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * Resolve the network mask for IPv4 addresses.
     *
     * @param string $override
     * @return int
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    private static function resolveIpv4Prefix(string $override): int
    {
        $mask = $override !== ''
            ? $override
            : (string)ConfigurationUtility::getConfigurationKey('configuration.networkMask');
        if (ctype_digit($mask)) {
            return (int)$mask;
        }

        $packed = @inet_pton($mask);
        if ($packed === false || strlen($packed) !== 4) {
            return 32;
        }

        $bits = '';
        foreach (str_split($packed) as $byte) {
            $bits .= sprintf('%08b', ord($byte));
        }

        return strlen(rtrim($bits, '0'));
    }

    /**
     * Calculate the network address based on the given ip address and the network mask.
     *
     * @param string $override
     * @return int
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    private static function resolveIpv6Prefix(string $override): int
    {
        if ($override !== '' && ctype_digit($override)) {
            return (int)$override;
        }
        $configured = (string)ConfigurationUtility::getConfigurationKey('configuration.networkPrefixV6');
        return ctype_digit($configured) ? (int)$configured : 64;
    }
}
