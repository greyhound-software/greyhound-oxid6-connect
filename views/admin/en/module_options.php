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

$aLang = array(
    'charset'                                 => 'UTF-8',
    'SHOP_MODULE_GROUP_api'                   => 'Schnittstelle zum GREYHOUND Addon',
    'SHOP_MODULE_sGhApiKey'                   => 'API Key',
    'HELP_SHOP_MODULE_sGhApiKey'              => 'Please specify an arbitrary password. You need to enter this password in GREYHOUND in the GREYHOUND OXID Connect addon settings, so that the addon can access the shop data. If the password is empty then the GREYHOUND addon will not be able to access the shop data.',
    'SHOP_MODULE_sGhApiUrl'                   => 'API URL',
    'HELP_SHOP_MODULE_sGhApiUrl'              => 'This value is generated automatically and cannot be edited. It needs to be entered into the addon settings in GREYHOUND.',
    'SHOP_MODULE_blGhApiAllowNonSsl'          => 'Allow insecure connections',
    'HELP_SHOP_MODULE_blGhApiAllowNonSsl'     => 'If this option is activated then the api will accept requests via insecure connections, otherwise it will only accept requests via ssl connections. This option should only be activated for debugging purposes. In a live system insecure connections should not be accepted because customer and order data may be transmitted.',
    'SHOP_MODULE_blGhApiIncludeSubshops'      => 'Provide sub-shop data through the main-shop api',
    'HELP_SHOP_MODULE_blGhApiIncludeSubshops' => 'If this option is activated then data from the sub-shops will be included in requests to the main-shop api. In that case, only the main-shop api connection needs to be configured in GREYHOUND in the addon settings.',
);
