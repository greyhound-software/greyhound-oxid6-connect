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

namespace Greyhound\Connect\Controller;

use stdClass;
use OxidEsales\Eshop\Core\Registry as oxRegistry;
use OxidEsales\Eshop\Core\DatabaseProvider;
use Greyhound\Connect\Core\Exception\ApiException;

/**
 * This controller provides an API for the GREYHOUND OXID Connect.
 *
 * @package ghoxid2greyhoundconnect
 * @subpackage Controller
 *
 * @author GREYHOUND Software GmbH &amp; Co. KG <develop@greyhound-software.com>
 * @copyright 2019-2023 GREYHOUND Software GmbH &amp; Co. KG
 * @link greyhound-software.com
 */
class ApiController extends \OxidEsales\Eshop\Application\Controller\FrontendController
{
    /**
     * This method processes the API request and then exits.
     * It does not return a template name, it aborts execution after processing
     * the request.
     */
    public function render()
    {
        $this->_ghProcessRequest();
        exit(0);
    }

    /**
     * Creates a new api exception object.
     *
     * @param string  $message message
     * @param integer $code    error code
     * @return Greyhound\Connect\Core\Exception\ApiException
     */
    protected function _ghNewApiException($message, $code = 0)
    {
        $exception = oxNew(ApiException::class);
        $exception->setMessage($message);
        $exception->setCode($code);

        return $exception;
    }

    /**
     * Returns the response to an api request.
     *
     * @throws Greyhound\Connect\Core\Exception\ApiException if an error occurred during the request
     *
     * @param object $oRequest request object
     * @return mixed
     */
    protected function _ghGetRequestResponse($oRequest)
    {
        switch ($oRequest->method) {
            case 'searchCustomersAndOrders':
                return $this->_ghSearchCustomersAndOrders($oRequest->params);

            case 'getOrder':
                return $this->_ghGetOrder($oRequest->params);

            case 'getPaymentMethods':
                return $this->_ghGetPaymentMethods($oRequest->params);

            case 'getShippingMethods':
                return $this->_ghGetShippingMethods($oRequest->params);

            case 'getCountries':
                return $this->_ghGetCountries($oRequest->params);

            case 'getStates':
                return $this->_ghGetStates($oRequest->params);

            case 'getShops':
                return $this->_ghGetShops($oRequest->params);

            case 'getActiveShop':
                return $this->_ghGetActiveShop($oRequest->params);

            default:
                throw $this->_ghNewApiException('Invalid method', 406);
        }
    }

    /**
     * Processes an API request.
     *
     * @throws Greyhound\Connect\Core\Exception\ApiException if an error occurred during the request
     */
    protected function _ghProcessRequest()
    {
        try {
            $oRequest = $this->_ghGetRequest();

            $this->_ghSendResponse($this->_ghGetRequestResponse($oRequest));
        } catch (\Exception $oException) {
            $this->_ghSendError($oException);
        }
    }

    /**
     * Throws an exception if an insecure connection is used and this has not been explicitly allowed in the module config.
     *
     * @throws Greyhound\Connect\Core\Exception\ApiException if an insecure connection is used and not allowed
     *
     * @param bool $blAllowNonSsl true: allow insecure connection, false: require ssl connection
     */
    protected function _ghCheckSsl($blAllowNonSsl)
    {
        if (!oxRegistry::getConfig()->isSsl() && !$blAllowNonSsl) {
            throw $this->_ghNewApiException('Non-SSL connections are not allowed', 403);
        }
    }

    /**
     * Throws an exception if a request is not properly authorized (i.e. the api key is missing or doesn't match).
     *
     * @throws Greyhound\Connect\Core\Exception\ApiException if authorization failed
     *
     * @param object $oRequest request object
     * @param string $sApiKey  api key
     */
    protected function _ghCheckAuth($oRequest, $sApiKey)
    {
        if (!property_exists($oRequest, 'auth')) {
            throw $this->_ghNewApiException('Missing api key', 401);
        } elseif (strlen($oRequest->auth) < 1) {
            throw $this->_ghNewApiException('Empty api key is not allowed', 401);
        } elseif (!$sApiKey || $oRequest->auth != $sApiKey) {
            throw $this->_ghNewApiException('Authentication failed (invalid api key)', 401);
        }
    }

    /**
     * Returns the JSON-RPC request.
     *
     * @throws Greyhound\Connect\Core\Exception\ApiException if the request could not be parsed or the authentication is invalid
     *
     * @return object
     */
    protected function _ghGetRequest()
    {
        $oConfig = oxRegistry::getConfig();

        $sApiKey = $oConfig->getShopConfVar('sGhApiKey', null, 'module:ghoxid2greyhoundconnect');

        if ($sApiKey) {
            $blAllowNonSsl = $oConfig->getShopConfVar('blGhApiAllowNonSsl', null, 'module:ghoxid2greyhoundconnect');
        } else {
            // Backwards compatibility (old versions of this module didn't use the 'module:' prefix):
            $sApiKey = $oConfig->getShopConfVar('sGhApiKey', null, 'ghoxid2greyhoundconnect');
            $blAllowNonSsl = $oConfig->getShopConfVar('blGhApiAllowNonSsl', null, 'ghoxid2greyhoundconnect');
        }

        $this->_ghCheckSsl($blAllowNonSsl);

        $sRequest = $oConfig->getRequestParameter('request', true);

        if (strlen($sRequest) < 1) {
            throw $this->_ghNewApiException('Empty request', 400);
        }

        $oRequest = json_decode($sRequest);

        if (!is_object($oRequest)) {
            throw $this->_ghNewApiException('Invalid request', 400);
        }

        $this->_ghCheckAuth($oRequest, $sApiKey);

        if (!isset($oRequest->method) || !$oRequest->method) {
            throw $this->_ghNewApiException('Invalid method', 406);
        }

        if (!isset($oRequest->params)) {
            $oRequest->params = array();
        } elseif (!is_object($oRequest->params)) {
            throw $this->_ghNewApiException('Invalid params', 400);
        } else {
            $oRequest->params = (array)$oRequest->params;// convert params to array
        }

        return $oRequest;
    }

    /**
     * Outputs the response to the API request (in JSON format).
     *
     * @param mixed $mResponse API response
     */
    protected function _ghSendResponse($mResponse)
    {
        $oResponse = new stdClass();
        $oResponse->version = '1.1';
        $oResponse->error = null;
        $oResponse->result = $mResponse;

        print(json_encode($oResponse));
    }

    /**
     * Outputs an error response to the API request (in JSON format).
     *
     * @param Exception $oException Exception
     */
    protected function _ghSendError($oException)
    {
        $oResponse = new stdClass();
        $oResponse->version = '1.1';
        $oResponse->error = new stdClass();
        $oResponse->error->name = 'Exception';
        $oResponse->error->code = $oException->getCode();
        $oResponse->error->message = $oException->getMessage();

        print(json_encode($oResponse));
    }

    /**
     * Checks whether requests should be limited to the current shop.
     *
     * @return bool true if all requests should be limited to the current shop, false if data from all shops should be returned
     */
    protected function _ghLimitToShopId()
    {
        $oConfig = oxRegistry::getConfig();
        $sShopId = $oConfig->getShopId();

        // "oxbaseshop" has no sub-shops, otherwise check setting of main-shop:
        if ($sShopId == 'oxbaseshop' || ($sShopId == '1' && oxRegistry::getConfig()->getShopConfVar('blGhApiIncludeSubshops', null, 'module:ghoxid2greyhoundconnect'))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns the database query condition for searching for customers and orders.
     *
     * @param string  $sSearchCondition search condition
     * @param bool    $blDigitsOnly     search terms contain only digits
     * @param integer $iMaxLength       maximum search term length
     * @param string  $sSearchType      search type
     * @return string
     */
    protected function _ghGetSearchCustomersAndOrdersQueryCondition($sSearchCondition, $blDigitsOnly, $iMaxLength, $sSearchType)
    {
        switch ($sSearchType) {
            case 'orderid':
                return 'oxid ' . $sSearchCondition;

            case 'customerid':
                return 'oxuserid ' . $sSearchCondition;

            case 'email':
                return 'oxbillemail ' . $sSearchCondition;

            case 'orderno':
                return 'oxordernr ' . $sSearchCondition;

            default:
                if ($blDigitsOnly) {
                    $sCustomersCondition = $this->_ghSearchCustomersCondition($sSearchCondition);

                    if ($sCustomersCondition) {
                        $sCustomersCondition = ' OR oxuserid ' . $sCustomersCondition;
                    } else {
                        $sCustomersCondition = '';
                    }

                    if ($iMaxLength == 5) {
                        return 'oxordernr ' . $sSearchCondition . ' OR oxbillnr ' . $sSearchCondition . ' OR oxbillzip ' . $sSearchCondition . ' OR oxdelzip ' . $sSearchCondition . $sCustomersCondition;
                    } else {
                        return 'oxordernr ' . $sSearchCondition . ' OR oxbillnr ' . $sSearchCondition . $sCustomersCondition;
                    }
                } else {
                    return 'oxbillcity ' . $sSearchCondition . ' OR oxdelcity ' . $sSearchCondition . ' OR oxbilllname ' . $sSearchCondition . ' OR oxdellname ' . $sSearchCondition;
                }
        }
    }

    /**
     * Returns the database query for searching for customers and orders.
     *
     * @throws Greyhound\Connect\Core\Exception\ApiException if the search params aren't valid
     *
     * @param array $aParams request params
     * @return string
     */
    protected function _ghGetSearchCustomersAndOrdersQuery($aParams)
    {
        $blDigitsOnly = true;
        $iMaxLength = 0;

        $aSearchTerm = $aParams['searchTerm'];
        $sSearchType = $aParams['searchType'];
        $oDb = DatabaseProvider::getDb();

        if (empty($aSearchTerm)) {
            throw $this->_ghNewApiException('Empty search term');
        }

        if (!is_array($aSearchTerm)) {
            $aSearchTerm = array($aSearchTerm);
        }

        $aSearchConditions = [];

        foreach ($aSearchTerm as $sSearchTerm) {
            $sSearchTerm = trim($sSearchTerm);
            $iMaxLength = max($iMaxLength, strlen($sSearchTerm));
            $blDigitsOnly = $blDigitsOnly && !preg_match('/[^0-9]/', $sSearchTerm);
            $aSearchConditions[] = $oDb->quote($sSearchTerm);
        }

        if (count($aSearchConditions) == 1) {
            $sSearchCondition = '= ' . $aSearchConditions[0];
        } else {
            $sSearchCondition = 'IN (' . implode(', ', $aSearchConditions) . ')';
        }

        $sQueryCondition = $this->_ghGetSearchCustomersAndOrdersQueryCondition($sSearchCondition, $blDigitsOnly, $iMaxLength, $sSearchType);

        if ($this->_ghLimitToShopId()) {
            $sQueryCondition = 'oxshopid = ' . $oDb->quote(oxRegistry::getConfig()->getShopId()) . ' AND (' . $sQueryCondition . ')';
        }

        return 'SELECT * FROM oxorder WHERE ' . $sQueryCondition;
    }

    /**
     * Searches for customers and returns their ids.
     *
     * @throws Greyhound\Connect\Core\Exception\ApiException if an error occurs during the search or if the params aren't valid
     *
     * @param string $sSearchCondition search condition
     * @return string
     */
    protected function _ghSearchCustomersCondition($sSearchCondition)
    {
        $aResult = array();
        $oDb = DatabaseProvider::getDb();
        $sQueryCondition = 'oxcustnr ' . $sSearchCondition;

        if ($this->_ghLimitToShopId()) {
            $sQueryCondition = 'oxshopid = ' . $oDb->quote(oxRegistry::getConfig()->getShopId()) . ' AND (' . $sQueryCondition . ')';
        }

        $oUsers = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class);
        $oUsers->init(\OxidEsales\Eshop\Application\Model\User::class);
        $oUsers->selectString('SELECT oxid FROM oxuser WHERE ' . $sQueryCondition);
        $aUserIds = [];

        foreach ($oUsers as $oUser) {
            $sUserId = $oUser->oxuser__oxid->value;

            if ($sUserId) {
                $aUserIds[] = $oDb->quote($sUserId);
            }
        }

        if (count($aUserIds) > 1) {
            return ' IN (' . implode( ', ', $aUserIds ) . ')';
        } else if (count($aUserIds) == 1) {
            return ' = ' . $aUserIds[0];
        } else {
            return '';
        }
    }

    /**
     * Searches for customers and orders.
     *
     * @throws Greyhound\Connect\Core\Exception\ApiException if an error occurs during the search or if the params aren't valid
     *
     * @param array $aParams request params
     * @return array
     */
    protected function _ghSearchCustomersAndOrders($aParams)
    {
        $aResult = array();

        $sQuery = $this->_ghGetSearchCustomersAndOrdersQuery($aParams);

        $oOrders = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class);
        $oOrders->init(\OxidEsales\Eshop\Application\Model\Order::class);
        $oOrders->selectString($sQuery);

        foreach ($oOrders as $oOrder) {
            $oData = $this->_ghOrderToData($oOrder);

            if ($oData) {
                $aResult[] = $oData;
            }
        }

        return $aResult;
    }

    /**
     * Returns data of an order.
     *
     * @param array $aParams request params
     * @return object
     */
    protected function _ghGetOrder($aParams)
    {
        $sOrderId = $aParams['orderId'];

        if (!$sOrderId) {
            return null;
        }

        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);

        if (!$oOrder->load($sOrderId)) {
            return null;
        }

        if ($this->_ghLimitToShopId() && $oOrder->oxorder__oxshopid->value != oxRegistry::getConfig()->getShopId()) {
            return null;
        }

        $oData = $this->_ghOrderToData($oOrder);

        return $oData;
    }

    /**
     * Returns an API data object for an order.
     *
     * @param oxOrder $oOrder order object
     * @return object
     */
    protected function _ghOrderToData($oOrder)
    {
        $oData = new stdClass();
        $oData->ID = $oOrder->getId();
        $oData->ShopID = $oOrder->oxorder__oxshopid->value;
        $oData->CustomerID = $oOrder->oxorder__oxuserid->value;
        $oData->Date = $oOrder->oxorder__oxoderdate->rawValue;
        $oData->Date = $this->_ghFormatDate($oOrder->oxorder__oxorderdate->value);
        $oData->Cancelled = $oOrder->oxorder__oxstorno->value;
        $oData->State = $oOrder->oxorder__oxtransstatus->value;
        $oData->OrderNo = $oOrder->oxorder__oxordernr->value;
        $oData->OrderTotal = $oOrder->oxorder__oxtotalordersum->value;
        $oData->OrderTotalNet = $oOrder->oxorder__oxtotalnetsum->value;
        $oData->OrderTotalGross = $oOrder->oxorder__oxtotalbrutsum->value;
        $oData->Currency = $oOrder->oxorder__oxcurrency->value;
        $oData->CurrencyRate = $oOrder->oxorder__oxcurrate->value;
        $oData->NetMode = $oOrder->oxorder__oxisnettomode->value;
        $oData->Vat = $oOrder->oxorder__oxartvat1->value;
        $oData->VatTotal = $oOrder->oxorder__oxartvatprice1->value;
        $oData->Vat2 = $oOrder->oxorder__oxartvat2->value;
        $oData->Vat2Total = $oOrder->oxorder__oxartvatprice2->value;
        $oData->Discount = $oOrder->oxorder__oxdiscount->value;
        $oData->VoucherDiscount = $oOrder->oxorder__oxvoucherdiscount->value;
        $oData->BillNo = $oOrder->oxorder__oxbillnr->value;
        $oData->BillDate = $oOrder->oxorder__oxbilldate->value;
        $oData->InvoiceNo = $oOrder->oxorder__oxinvoicenr->value;
        $oData->Remark = $oOrder->oxorder__oxremark->value;

        // Billing address:
        $oData->BillingAddress = new stdClass();
        $oData->BillingAddress->Company = $oOrder->oxorder__oxbillcompany->value;
        $oData->BillingAddress->Email= $oOrder->oxorder__oxbillemail->value;
        $oData->BillingAddress->FirstName = $oOrder->oxorder__oxbillfname->value;
        $oData->BillingAddress->LastName = $oOrder->oxorder__oxbilllname->value;
        $oData->BillingAddress->Street = $oOrder->oxorder__oxbillstreet->value;
        $oData->BillingAddress->StreetNo = $oOrder->oxorder__oxbillstreetnr->value;
        $oData->BillingAddress->AddInfo = $oOrder->oxorder__oxbilladdinfo->value;
        $oData->BillingAddress->City = $oOrder->oxorder__oxbillcity->value;
        $oData->BillingAddress->ZipCode = $oOrder->oxorder__oxbillzip->value;
        $oData->BillingAddress->VatID = $oOrder->oxorder__oxbillustid->value;
        $oData->BillingAddress->CountryID = $oOrder->oxorder__oxbillcountryid->value;
        $oData->BillingAddress->StateID = $oOrder->oxorder__oxbillstateid->value;
        $oData->BillingAddress->Phone = $oOrder->oxorder__oxbillfon->value;
        $oData->BillingAddress->Fax = $oOrder->oxorder__oxbillfax->value;
        $oData->BillingAddress->Salutation = $oOrder->oxorder__oxbillsal->value;
        $oData->BillingAddress->CustomerNo = $oOrder->getOrderUser()->oxuser__oxcustnr->value;

        // Delivery address:
        $oData->ShippingAddress = new stdClass();
        $oData->ShippingAddress->Company = $oOrder->oxorder__oxdelcompany->value;
        $oData->ShippingAddress->FirstName = $oOrder->oxorder__oxdelfname->value;
        $oData->ShippingAddress->LastName = $oOrder->oxorder__oxdellname->value;
        $oData->ShippingAddress->Street = $oOrder->oxorder__oxdelstreet->value;
        $oData->ShippingAddress->StreetNo = $oOrder->oxorder__oxdelstreetnr->value;
        $oData->ShippingAddress->AddInfo = $oOrder->oxorder__oxdeladdinfo->value;
        $oData->ShippingAddress->City = $oOrder->oxorder__oxdelcity->value;
        $oData->ShippingAddress->ZipCode = $oOrder->oxorder__oxdelzip->value;
        $oData->ShippingAddress->CountryID = $oOrder->oxorder__oxdelcountryid->value;
        $oData->ShippingAddress->StateID = $oOrder->oxorder__oxdelstateid->value;
        $oData->ShippingAddress->Phone = $oOrder->oxorder__oxdelfon->value;
        $oData->ShippingAddress->Fax = $oOrder->oxorder__oxdelfax->value;
        $oData->ShippingAddress->Salutation = $oOrder->oxorder__oxdelsal->value;

        $blEmptyShippingAddress = true;

        foreach (get_object_vars($oData->ShippingAddress) as $sValue) {
            if ($sValue) {
                $blEmptyShippingAddress = false;
                break;
            }
        }

        if ($blEmptyShippingAddress) {
            $oData->ShippingAddress = null;
        }

        // Payment:
        $oData->Payment = new stdClass();
        $oData->Payment->ID = $oOrder->oxorder__oxpaymenttype->value;
        $oData->Payment->Date = $this->_ghFormatDate($oOrder->oxorder__oxpaid->value);
        $oData->Payment->Cost = $oOrder->oxorder__oxpaycost->value;
        $oData->Payment->Vat = $oOrder->oxorder__oxpayvat->value;
        $oData->Payment->TransactionID = $oOrder->oxorder__oxtransid->value;

        // Shipping:
        $oData->Shipping = new stdClass();
        $oData->Shipping->ID = $oOrder->oxorder__oxdeltype->value;
        $oData->Shipping->Cost = $oOrder->oxorder__oxdelcost->value;
        $oData->Shipping->Vat = $oOrder->oxorder__oxdelvat->value;
        $oData->Shipping->Date = $this->_ghFormatDate($oOrder->oxorder__oxsenddate->value);
        $oData->Shipping->TrackingCode = $oOrder->oxorder__oxtrackcode->value;

        // Wrapping:
        $oData->Wrapping = new stdClass();
        $oData->Wrapping->Cost = $oOrder->oxorder__oxwrapcost->value;
        $oData->Wrapping->Vat = $oOrder->oxorder__oxwrapvat->value;

        // Gift card:
        $oData->GiftCard = new stdClass();
        $oData->GiftCard->Cost = $oOrder->oxorder__oxgiftcardcost->value;
        $oData->GiftCard->Vat = $oOrder->oxorder__oxgiftcardvat->value;
        $oData->GiftCard->ID = $oOrder->oxorder__oxcardid->value;
        $oData->GiftCard->Text = $oOrder->oxorder__oxcardtext->value;

        // Order products:
        $oData->OrderItems = array();

        foreach ($oOrder->getOrderArticles() as $oOrderProduct) {
            $oItem = new stdClass();
            $oItem->ID = $oOrderProduct->oxorderarticles__oxid->value;
            $oItem->ProductID = $oOrderProduct->oxorderarticles__oxartid->value;
            $oItem->Quantity = $oOrderProduct->oxorderarticles__oxamount->value;
            $oItem->ItemNo = $oOrderProduct->oxorderarticles__oxartnum->value;
            $oItem->Title = $oOrderProduct->oxorderarticles__oxtitle->value;
            $oItem->ShortDescription = $oOrderProduct->oxorderarticles__oxshortdesc->value;
            $oItem->Total = $oOrderProduct->oxorderarticles__oxbrutprice->value ? $oOrderProduct->oxorderarticles__oxbrutprice->value : $oOrderProduct->oxorderarticles__oxnetprice->value;
            $oItem->Price = $oOrderProduct->oxorderarticles__oxprice->value;
            $oItem->Vat = $oOrderProduct->oxorderarticles__oxvat->value;
            $oItem->VatTotal = $oOrderProduct->oxorderarticles__oxvatprice->value;
            $oItem->Cancelled = $oOrderProduct->oxorderarticles__oxstorno->value;
            $oItem->Bundle = $oOrderProduct->oxorderarticles__oxisbundle->value;
            $oItem->Variant = $oOrderProduct->oxorderarticles__oxselvariant->value;
            $oItem->PersParams = $oOrderProduct->getPersParams();

            $oData->OrderItems[] = $oItem;
        }

        return $oData;
    }

    /**
     * Returns the payment methods of the shop.
     *
     * @param array $aParams request params
     * @return array
     */
    protected function _ghGetPaymentMethods($aParams)
    {
        $aResult = array();

        $oList = oxNew(\OxidEsales\Eshop\Application\Model\PaymentList::class);
        $oList->selectString('SELECT * FROM oxpayments');

        foreach ($oList as $oPayment) {
            $oData = new stdClass();
            $oData->ID = $oPayment->oxpayments__oxid->value;
            $oData->Active = $oPayment->oxpayments__oxactive->value;
            $oData->Title = $oPayment->oxpayments__oxdesc->value;

            $aResult[ $oData->ID ] = $oData;
        }

        return $aResult;
    }

    /**
     * Returns the delivery methods of the shop.
     *
     * @param array $aParams request params
     * @return array
     */
    protected function _ghGetShippingMethods($aParams)
    {
        $aResult = array();

        $oList = oxNew(\OxidEsales\Eshop\Application\Model\DeliverySetList::class);
        $oList->selectString('SELECT * FROM oxdeliveryset');

        foreach ($oList as $oDeliverySet) {
            $oData = new stdClass();
            $oData->ID = $oDeliverySet->oxdeliveryset__oxid->value;
            $oData->ShopID = $oDeliverySet->oxdeliveryset__oxshopid->value;
            $oData->Active = $oDeliverySet->oxdeliveryset__oxactive->value;
            $oData->Title = $oDeliverySet->oxdeliveryset__oxtitle->value;

            $aResult[ $oData->ID ] = $oData;
        }

        return $aResult;
    }

    /**
     * Returns the countries of the shop.
     *
     * @param array $aParams request params
     * @return array
     */
    protected function _ghGetCountries($aParams)
    {
        $aResult = array();

        $oList = oxNew(\OxidEsales\Eshop\Application\Model\CountryList::class);
        $oList->selectString('SELECT * FROM oxcountry');

        foreach ($oList as $oCountry) {
            $oData = new stdClass();
            $oData->ID = $oCountry->oxcountry__oxid->value;
            $oData->Active = $oCountry->oxcountry__oxactive->value;
            $oData->Title = $oCountry->oxcountry__oxtitle->value;
            $oData->ISO2 = $oCountry->oxcountry__oxisoalpha2->value;
            $oData->ISO3 = $oCountry->oxcountry__oxisoalpha3->value;

            $aResult[ $oData->ID ] = $oData;
        }

        return $aResult;
    }

    /**
     * Returns the states of the shop.
     *
     * @param array $aParams request params
     * @return array
     */
    protected function _ghGetStates($aParams)
    {
        $aResult = array();

        $oList = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class);
        $oList->init(\OxidEsales\Eshop\Application\Model\State::class);
        $oList->selectString('SELECT * FROM oxstates');

        foreach ($oList as $oState) {
            $oData = new stdClass();
            $oData->ID = $oState->oxstates__oxid->value;
            $oData->CountryID = $oState->oxstates__oxcountryid->value;
            $oData->Title = $oState->oxstates__oxtitle->value;
            $oData->ISO2 = $oState->oxstates__oxisoalpha2->value;

            $aResult[ $oData->ID ] = $oData;
        }

        return $aResult;
    }

    /**
     * Returns the shop / subshops.
     *
     * @param array $aParams request params
     * @return array
     */
    protected function _ghGetShops($aParams)
    {
        $aResult = array();
        $oConfig = oxRegistry::getConfig();
        $sShopId = $oConfig->getShopId();

        $oList = oxNew(\OxidEsales\Eshop\Application\Model\ShopList::class);
        $oList->selectString('SELECT * FROM oxshops');

        foreach ($oList as $oShop) {
            $oData = new stdClass();
            $oData->ID = $oShop->oxshops__oxid->value;
            $oData->Title = $oShop->oxshops__oxname->value;
            $oData->Edition = $oShop->oxshops__oxedition->value;
            $oData->Version = $oShop->oxshops__oxversion->value;

            // temporarily set shop id and fetch urls from config:
            $oConfig->setShopId($oShop->oxshops__oxid->value);
            $oData->Revision = $oConfig->getRevision();
            $oData->Url = $oConfig->getShopUrl(null, false);
            $oData->SslUrl = $oConfig->getSslShopUrl(null);

            $aResult[ $oData->ID ] = $oData;
        }

        // restore shop id:
        $oConfig->setShopId($sShopId);

        return $aResult;
    }

    /**
     * Returns the current shop / subshop.
     *
     * @param array $aParams request params
     * @return object
     */
    protected function _ghGetActiveShop($aParams)
    {
        $oConfig = oxRegistry::getConfig();
        $oShop = $oConfig->getActiveShop();

        $oData = new stdClass();
        $oData->ID = $oShop->oxshops__oxid->value;
        $oData->Title = $oShop->oxshops__oxname->value;
        $oData->Edition = $oShop->oxshops__oxedition->value;
        $oData->Version = $oShop->oxshops__oxversion->value;
        $oData->Revision = $oConfig->getRevision();
        $oData->Url = $oConfig->getShopUrl(null, false);
        $oData->SslUrl = $oConfig->getSslShopUrl(null);

        return $oData;
    }

    /**
     * Formatiert ein Datum in ISO Format (YYYY-MM-DD HH:MM:SS).
     *
     * @param string $date date
     * @return string
     */
    protected function _ghFormatDate($date)
    {
        if ($date && $date != '-' && $date != '0000-00-00' && $date != '0000-00-00 00:00:00') {
            return date('Y-m-d H:i:s', strtotime($date));
        } else {
            return null;
        }
    }
}
