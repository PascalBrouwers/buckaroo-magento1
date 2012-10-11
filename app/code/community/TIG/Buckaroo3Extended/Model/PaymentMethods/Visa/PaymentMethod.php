<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Visa_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    protected $_payment;
    
    public function setPayment($payment)
    {
        $this->_payment = $payment;
    }
    
    public function getPayment()
    {
        return $this->_payment;
    }
    
    public $allowedCurrencies = array(
		'EUR',
		'GBP',
		'USD',
		'CAD',
		'SHR',
		'NOK',
		'SEK',
		'DKK',
        'ARS',
        'BRL',
        'HRK',
        'LTL',
        'TRY',
        'TRL',
        'AUD',
        'CNY',
        'LVL',
        'MXN',
        'MXP',
        'PLN',
        'CHF',
	);

    protected $_code = 'buckaroo3extended_visa';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_visa_checkout_form';
    
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    protected $_canSaveCc 				= false;

    public function getAllowedCurrencies()
    {
        return $this->allowedCurrencies;
    }

    public function setAllowedCurrencies($allowedCurrencies)
    {
        $this->allowedCurrencies = $allowedCurrencies;
    }

    public function getOrderPlaceRedirectUrl()
    {
    	return Mage::getUrl('buckaroo3extended/checkout/checkout', array('_secure' => true, 'method' => $this->_code));
    }
    
    public function refund(Varien_Object $payment, $amount)
    {
        if (!$this->canRefund()) {
            Mage::throwException($this->_getHelper()->__('Refund action is not available.'));
        }
        
        $refundRequest = Mage::getModel(
        	'buckaroo3extended/refund_request_abstract', 
            array(
            	'payment' => $payment, 
            	'amount' => $amount
            )
        );
        $payment = $refundRequest->sendRefundRequest();
        
        $this->setPayment($payment);
        
        return $this;
    }

    public function isAvailable($quote = null)
    {
        if (!TIG_Buckaroo3Extended_Model_Request_Availability::canUseBuckaroo($quote)) {
    		return false;
    	}

    	//check if the country specified in the billing address is allowed to use this payment method
    	if (Mage::getStoreConfig('buckaroo/buckaroo3extended_visa/allowspecific', Mage::app()->getStore()->getStoreId()) == 1
    		&& $quote->getBillingAddress()->getCountry())
    	{
    		$allowedCountries = explode(',',Mage::getStoreConfig('buckaroo/buckaroo3extended_visa/specificcountry', Mage::app()->getStore()->getStoreId()));
    		$country = $quote->getBillingAddress()->getCountry();

    		if (!in_array($country,$allowedCountries)) {
    			return false;
    		}
    	}

    	//check if the module is set to enabled
    	if (!Mage::getStoreConfig('buckaroo/buckaroo3extended_visa/active', Mage::app()->getStore()->getStoreId())) {
    		return false;
    	}

    	//limit by ip
    	if (mage::getStoreConfig('dev/restrict/allow_ips') && Mage::getStoreConfig('buckaroo/buckaroo3extended_visa/limit_by_ip'))
    	{
    		$allowedIp = explode(',', mage::getStoreConfig('dev/restrict/allow_ips'));
    		if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIp))
    		{
    			return false;
    		}
    	}

        // get current currency code
        $currency = Mage::app()->getStore()->getBaseCurrencyCode();

        // currency is not available for this module
        if (!in_array($currency, $this->allowedCurrencies))
        {
            return false;
        }

        return parent::isAvailable($quote);
    }
}