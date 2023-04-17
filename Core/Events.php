<?php
/*
 * GREYHOUND OXID Connect module for OXID eShop
 *
 * MIT License
 *
 * Copyright (c) 2019-2023 GREYHOUND Software
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Greyhound\Connect\Core;

use OxidEsales\Eshop\Core\Registry as oxRegistry;

/**
 * Event handler class.
 *
 * @package ghoxid2greyhoundconnect
 * @subpackage Core
 *
 * @author GREYHOUND Software GmbH &amp; Co. KG <develop@greyhound-software.com>
 * @copyright 2019-2023 GREYHOUND Software GmbH &amp; Co. KG
 * @link greyhound-software.com
 */
class Events
{
    /**
     * This function is executed every time the module gets activated in the shop.
     */
    public static function onActivate()
    {
        $sApiKey = oxRegistry::getConfig()->getShopConfVar('sGhApiKey', null, 'module:ghoxid2greyhoundconnect');

        if (!$sApiKey) {
            $sApiKey = oxRegistry::getUtilsObject()->generateUId();
            oxRegistry::getConfig()->saveShopConfVar('str', 'sGhApiKey', $sApiKey, null, 'module:ghoxid2greyhoundconnect');
        }
    }
}
