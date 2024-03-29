<?php

namespace SKeuper\BackendIpLogin\Domain\Model;

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

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class BackendUser extends BackendUserAuthentication
{
    /**
     * ip of the last successful login
     *
     * @var string
     */
    protected string $lastLoginIp = "";

    /**
     * network ip of the last successful login
     *
     * @var string
     */
    protected string $lastLoginIpNetwork = "";

    /**
     * @return string
     */
    public function getLastLoginIp(): string
    {
        return $this->lastLoginIp;
    }

    /**
     * @param string $lastLoginIp
     */
    public function setLastLoginIp(string $lastLoginIp): void
    {
        $this->lastLoginIp = $lastLoginIp;
    }

    /**
     * @return string
     */
    public function getLastLoginIpNetwork(): string
    {
        return $this->lastLoginIpNetwork;
    }

    /**
     * @param string $lastLoginIpNetwork
     */
    public function setLastLoginIpNetwork(string $lastLoginIpNetwork): void
    {
        $this->lastLoginIpNetwork = $lastLoginIpNetwork;
    }

}