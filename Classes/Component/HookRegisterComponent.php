<?php
namespace SKeuper\BackendIpLogin\Component;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016-2018 Steffen keuper <steffen.keuper@web.de>,
 *           Ruben Pascal Abel <ruben.p.abel@gmail.com>
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

use SKeuper\BackendIpLogin\Utility\ReflectionUtility;

/**
 * HookRegisterComponent Component
 */
trait HookRegisterComponent
{

    /**
     * Registers a hook into the library, either specific by arguments or
     * by the constant associations in the class using the trait
     *
     * @param string $library
     * @param string $hook
     * @param $function
     */
    public static function register($library = '', $hook = '', $function = '')
    {
        if ($library && $hook && $function) {
            # dirty but can't use the trait within a function
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$library][$hook][] = __CLASS__ . "->" . $function;
        } else {
            try {
                $classConstants = ReflectionUtility::getConstants(__CLASS__);
            } catch (\ReflectionException $e) {
                $classConstants = [];
            }
            if (array_key_exists('associations', $classConstants)) {
                $associations = $classConstants['associations'];
                foreach ($associations as $library => $hooks) {
                    foreach ($hooks as $hook => $functions) {
                        if (is_string($functions)) {
                            // some hooks only want classes and check for the function themself
                            // so in this case we don't have any functions and it is [0] => $hook in the array
                            $hook = $functions;
                            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$library][$hook][] = __CLASS__;
                        } elseif (is_array($functions)) {
                            foreach ($functions as $function) {
                                self::register($library, $hook, $function);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Unregisters the hooks from the libraries
     *
     * @param string $library
     * @param string $hook
     * @param string $function
     */
    public static function unregister($library = '', $hook = '', $function = '')
    {
        if ($library && $hook && $function) {
            # dirty but can't use the trait within a function
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$library][$hook] as $index => $registeredFunction) {
                if ($registeredFunction == __CLASS__ . "->" . $function) {
                    unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$library][$hook][$index]);
                }
            }
        } else {
            try {
                $classConstants = ReflectionUtility::getConstants(__CLASS__);
            } catch (\ReflectionException $e) {
                $classConstants = [];
            }
            if (array_key_exists('associations', $classConstants)) {
                $associations = $classConstants['associations'];
                foreach ($associations as $library => $hooks) {
                    foreach ($hooks as $hook => $functions) {
                        foreach ($functions as $function) {
                            self::unregister($library, $hook, $function);
                        }
                    }
                }
            }
        }
    }

}