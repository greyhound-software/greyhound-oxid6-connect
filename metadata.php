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

$sMetadataVersion = '2.0';

$aModule = array(
    'id' => 'ghoxid2greyhoundconnect',
    'version' => '2.0.5',
    'title' => array(
        'en' => 'GREYHOUND OXID Connect Module',
        'de' => 'GREYHOUND OXID Connect Modul'
    ),
    'description' => array(
        'en' => 'Support module for the GREYHOUND OXID Connect addon',
        'de' => 'Modul zur Unterstützung des GREYHOUND OXID Connect Addons'
    ),
    'thumbnail' => 'module-thumbnail.jpg',
    'url' => 'http://greyhound-software.com',
    'email' => 'develop@greyhound-software.com',
    'author' => 'GREYHOUND Software GmbH & Co. KG',
    'events' => array(
        'onActivate' => '\Greyhound\Connect\Core\Events::onActivate'
    ),
    'settings' => [
        array('group' => 'api', 'name' => 'sGhApiKey', 'position' => 10, 'type' => 'str', 'value' => ''),
        array('group' => 'api', 'name' => 'sGhApiUrl', 'position' => 15, 'type' => 'str', 'value' => ''),
        array('group' => 'api', 'name' => 'blGhApiAllowNonSsl', 'position' => 20, 'type' => 'bool', 'value' => '0'),
        array('group' => 'api', 'name' => 'blGhApiIncludeSubshops', 'position' => 30, 'type' => 'bool', 'value' => '0')
    ],
    'extend' => array(
        \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class => \Greyhound\Connect\Controller\Admin\ModuleConfiguration::class
    ),
    'controllers' => array(
        'ghoxid2greyhoundconnectapi' => \Greyhound\Connect\Controller\ApiController::class
    )
);
