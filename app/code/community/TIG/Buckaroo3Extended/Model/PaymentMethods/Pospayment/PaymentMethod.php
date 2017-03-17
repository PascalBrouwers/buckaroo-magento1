<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_Buckaroo3Extended_Model_PaymentMethods_Pospayment_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
{
    const POSPAYMENT_XHEADER = 'X-Buckaroo-Ecrid';

    public $allowedCurrencies = array(
        'EUR',
    );

    protected $_code = 'buckaroo3extended_pospayment';

    protected $_canOrder                = true;
    protected $_canRefund               = false;
    protected $_canRefundInvoicePartial = false;

    /**
     * POSPayment may only be used when the ecrid x-header is set and optionally if the User-Agent matches
     *
     * {@inheritdoc}
     */
    public function isAvailable($quote = null)
    {
        $request = Mage::app()->getRequest();
        $xHeader = $request->getHeader(self::POSPAYMENT_XHEADER);

        if (strlen($xHeader) <= 0) {
            return false;
        }

        $storeId = Mage::app()->getStore()->getId();
        $userAgent = $request->getHeader('User-Agent');
        $userAgentConfiguration = trim(Mage::getStoreConfig('buckaroo/' . $this->_code . '/user_agent', $storeId));

        if (strlen($userAgentConfiguration) > 0 && $userAgent != $userAgentConfiguration) {
            return false;
        }

        return parent::isAvailable($quote);
    }
}
