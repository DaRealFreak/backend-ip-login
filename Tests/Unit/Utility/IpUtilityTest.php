<?php

declare(strict_types=1);

namespace SKeuper\BackendIpLogin\Tests\Unit\Utility;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SKeuper\BackendIpLogin\Utility\IpUtility;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * Pure unit tests for IpUtility. No TYPO3 bootstrap needed — only autoloader.
 *
 * getNetworkAddress() is tested with explicit mask overrides so the
 * ExtensionConfiguration fallback path is not exercised.
 */
class IpUtilityTest extends TestCase
{
    /**
     * @var mixed
     */
    private $previousRequest;

    protected function setUp(): void
    {
        $this->previousRequest = $GLOBALS['TYPO3_REQUEST'] ?? null;
    }

    protected function tearDown(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $this->previousRequest;
    }

    private function setClientIp(string $ip): void
    {
        $serverParams = [
            'REMOTE_ADDR' => $ip,
            'HTTP_HOST' => 'test',
            'SERVER_NAME' => 'test',
            'HTTPS' => 'on',
            'SERVER_PORT' => 443,
        ];
        $request = (new ServerRequest('https://test/', 'GET', null, [], $serverParams))
            ->withAttribute('normalizedParams', new NormalizedParams($serverParams, [], '/', '/tmp'));
        $GLOBALS['TYPO3_REQUEST'] = $request;
    }

    /**
     * @dataProvider ipv4LocalProvider
     */
    #[DataProvider('ipv4LocalProvider')]
    public function testIsLocalNetworkAddressIpv4(string $ip, bool $expected): void
    {
        $this->setClientIp($ip);
        self::assertSame($expected, IpUtility::isLocalNetworkAddress());
    }

    public static function ipv4LocalProvider(): array
    {
        return [
            'rfc1918 10/8'        => ['10.1.2.3', true],
            'rfc1918 172.16/12'   => ['172.17.0.5', true],
            'rfc1918 192.168/16'  => ['192.168.1.10', true],
            'loopback 127/8'      => ['127.0.0.1', true],
            'link-local 169.254' => ['169.254.1.1', true],
            'public google dns'   => ['8.8.8.8', false],
            'outside 172.16/12'   => ['172.32.0.1', false],
        ];
    }

    /**
     * @dataProvider ipv6LocalProvider
     */
    #[DataProvider('ipv6LocalProvider')]
    public function testIsLocalNetworkAddressIpv6(string $ip, bool $expected): void
    {
        $this->setClientIp($ip);
        self::assertSame($expected, IpUtility::isLocalNetworkAddress());
    }

    public static function ipv6LocalProvider(): array
    {
        return [
            'loopback ::1'        => ['::1', true],
            'ula fc00::1'         => ['fc00::1', true],
            'ula fd12::'          => ['fd12:3456:789a::1', true],
            'link-local fe80::'   => ['fe80::1234', true],
            'documentation 2001:db8' => ['2001:db8::1', false],
            'public cloudflare'   => ['2606:4700:4700::1111', false],
        ];
    }

    /**
     * @dataProvider ipv4NetworkProvider
     */
    #[DataProvider('ipv4NetworkProvider')]
    public function testGetNetworkAddressIpv4(string $ip, string $mask, string $expected): void
    {
        $this->setClientIp('1.1.1.1');
        self::assertSame($expected, IpUtility::getNetworkAddress($ip, $mask));
    }

    public static function ipv4NetworkProvider(): array
    {
        return [
            'dotted /24'           => ['192.168.1.42', '255.255.255.0', '192.168.1.0'],
            'dotted /16'           => ['10.20.30.40', '255.255.0.0', '10.20.0.0'],
            'dotted /24 loopback'  => ['127.0.0.99', '255.255.255.0', '127.0.0.0'],
            'numeric prefix 16'    => ['10.20.30.40', '16', '10.20.0.0'],
            'numeric prefix 8'     => ['10.20.30.40', '8', '10.0.0.0'],
        ];
    }

    /**
     * @dataProvider ipv6NetworkProvider
     */
    #[DataProvider('ipv6NetworkProvider')]
    public function testGetNetworkAddressIpv6(string $ip, string $prefix, string $expected): void
    {
        $this->setClientIp('1.1.1.1');
        self::assertSame($expected, IpUtility::getNetworkAddress($ip, $prefix));
    }

    public static function ipv6NetworkProvider(): array
    {
        return [
            '/64 documentation' => ['2001:db8:abcd:0012::1234', '64', '2001:db8:abcd:12::'],
            '/64 ula'           => ['fc00:1234:5678:9abc::5', '64', 'fc00:1234:5678:9abc::'],
            '/128 host'         => ['::1', '128', '::1'],
            '/32 truncation'    => ['2001:db8:1234::', '32', '2001:db8::'],
            '/48 truncation'    => ['2001:db8:1234:5678::', '48', '2001:db8:1234::'],
        ];
    }

    /**
     * @dataProvider clientIpProvider
     */
    #[DataProvider('clientIpProvider')]
    public function testGetClientIpCanonicalizes(string $rawIp, string $expected): void
    {
        $this->setClientIp($rawIp);
        self::assertSame($expected, IpUtility::getClientIp());
    }

    public static function clientIpProvider(): array
    {
        return [
            'ipv6 short'           => ['::0001', '::1'],
            'ipv6 long form'       => ['2001:0db8:0000:0000:0000:0000:0000:0001', '2001:db8::1'],
            'ipv4 unchanged'       => ['192.168.1.1', '192.168.1.1'],
        ];
    }

    public function testGetClientIpReturnsEmptyWithoutRequest(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = null;
        self::assertSame('', IpUtility::getClientIp());
    }
}
