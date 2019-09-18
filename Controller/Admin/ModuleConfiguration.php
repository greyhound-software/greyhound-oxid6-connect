<?php
/*
 * GREYHOUND OXID Connect module for OXID eShop
 *
 * MIT License
 *
 * Copyright (c) 2019 GREYHOUND Software
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

namespace Greyhound\Connect\Controller\Admin;

use OxidEsales\Eshop\Core\Registry as oxRegistry;

/**
 * This controller extends the shop admin module configuration controller to show the api url for the GREYHOUND OXID Connect module.
 *
 * @package ghoxid2greyhoundconnect
 * @subpackage Controller
 *
 * @author GREYHOUND Software GmbH &amp; Co. KG <develop@greyhound-software.com>
 * @copyright 2019 GREYHOUND Software GmbH &amp; Co. KG
 * @link greyhound-software.com
 */
class ModuleConfiguration extends ModuleConfiguration_parent
{
    /**
     * Executes parent::render() and adds a value for sGhApiUrl to the view data if the current module is ghoxid2greyhoundconnect.
     *
     * @return string
     */
    public function render()
    {
        $sResult = parent::render();

        try {
            if ($this->_sModuleId == 'ghoxid2greyhoundconnect') {
                if (isset($this->_aViewData) && isset($this->_aViewData['confstrs']) && is_array($this->_aViewData['confstrs'])) {
                    $this->_aViewData['confstrs']['sGhApiUrl'] = oxRegistry::getConfig()->getSslShopUrl() . 'index.php?cl=ghOxid2GreyhoundConnectApi';
                }
            }
        } catch (\Exception $exception) {
        }

        return $sResult;
    }
}
