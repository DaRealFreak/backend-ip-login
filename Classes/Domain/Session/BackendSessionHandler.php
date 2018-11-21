<?php

namespace SKeuper\BackendIpLogin\Domain\Session;

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

use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Extbase\Persistence\Repository;

class BackendSessionHandler extends Repository
{
    /**
     * @var string
     */
    protected $storageKey = 'tx_backendiplogin';

    /**
     * @param string $storageKey
     */
    public function setStorageKey(string $storageKey): void
    {
        $this->storageKey = $storageKey;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function store(string $key, $value): void
    {
        /** @var AbstractUserAuthentication $backendUser */
        $backendUser = $GLOBALS['BE_USER'];
        $data = $backendUser->getSessionData($this->storageKey);
        $data[$key] = $value;
        $backendUser->setAndSaveSessionData($this->storageKey, $data);
    }

    /**
     * @param string $key
     */
    public function delete(string $key): void
    {
        /** @var AbstractUserAuthentication $backendUser */
        $backendUser = $GLOBALS['BE_USER'];
        $data = $backendUser->getSessionData($this->storageKey);
        unset($data[$key]);
        $backendUser->setAndSaveSessionData($this->storageKey, $data);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $backendUser = $GLOBALS['BE_USER'];
        $data = $backendUser->getSessionData($this->storageKey);
        return isset($data[$key]) ? $data[$key] : null;
    }

}