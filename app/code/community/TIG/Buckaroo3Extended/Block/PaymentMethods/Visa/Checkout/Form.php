<?php
class TIG_Buckaroo3Extended_Block_PaymentMethods_Visa_Checkout_Form extends Mage_Payment_Block_Form
{
    public function __construct()
    {
		$this->setTemplate('buckaroo3extended/visa/checkout/form.phtml');
        parent::_construct();
    }
}