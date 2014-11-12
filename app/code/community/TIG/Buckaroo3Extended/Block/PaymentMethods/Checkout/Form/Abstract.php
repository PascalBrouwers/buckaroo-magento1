<?php
class TIG_Buckaroo3Extended_Block_PaymentMethods_Checkout_Form_Abstract extends Mage_Payment_Block_Form
{
    /**
     * construct method
     */
    protected function _construct()
    {
        $session = Mage::getSingleton('checkout/session');
        $this->setSession($session);
        $this->setCustomer(Mage::getSingleton('customer/session')->getCustomer());
        $this->setAddress($session->getQuote()->getBillingAddress());
        $this->setQuote($session->getQuote());

        return parent::_construct();
    }

    /**
     * @param bool $useSpan
     * @return string
     */
    public function getMethodLabelAfterHtml($useSpan = true)
    {
        if(Mage::helper('buckaroo3extended')->getIsKlarnaEnabled()) {
            return '';
        }

        $code = $this->getMethod()->getCode();

        $paymentFeeModel = Mage::getModel('buckaroo3extended/paymentFee_quote_address_total_fee');

        $paymentFee = $paymentFeeModel->getPaymentFeeBeforeSelect($this->getQuote(), $code);

        if($paymentFee < 1){
            return '';
        }

        $fee = str_replace(',', '.', $paymentFee);

        if (strpos($fee, '%') === false) {
            $fee = Mage::helper('core')->currency($fee, true, false);
        }

        $feeText      = '';

        if ($useSpan) {
            $feeText .= '<span class="buckaroo_fee '
                      . $code
                      . '">';
        }

        $feeText     .= '(+ ' . $fee . ')';

        if ($useSpan) {
            $feeText .= '</span>';
        }

        return $feeText;
    }

    /**
     * @return string
     */
    public function getName()
    {
        $name = $this->getSession()->getData($this->getMethodCode() . '_BPE_Customername');
        if (!$name) {
            $address = $this->getAddress();
            $firstname = $this->getFirstname();
            $lastname = $this->getLastname();

            $name = $firstname . ' ' . $lastname;
        }

        return $name;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        $firstname = $this->getSession()->getData($this->getMethodCode() . '_BPE_Customerfirstname');

        if (!$firstname) {
            $firstname = $this->getAddress()->getFirstname();
        }

        if (!$firstname && $this->getCustomer()) {
            $firstname = $this->getCustomer()->getFirstname();
        }

        return $firstname;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        $lastname = $this->getSession()->getData($this->getMethodCode() . '_BPE_Customerlastname');

        if (!$lastname) {
            $lastname = $this->getAddress()->getLastname();
        }

        if (!$lastname && $this->getCustomer()) {
            $lastname = $this->getCustomer()->getLastname();
        }

        return $lastname;
    }

    /**
     * @return int
     */
    public function getGender()
    {
        $gender = (int) $this->getSession()->getData($this->getMethodCode() . '_BPE_Customergender');
        if (!$gender) {
        	$customerId = $this->getAddress()->getCustomerId();
			$customer = Mage::getModel('customer/customer')->load($customerId);
            $gender = (int) $customer->getGender();
        }

        return $gender;
    }

    /**
     * @return array|string
     */
    public function getDob()
    {
        $dob = array(
            false,
            false,
            false,
        );
        if (!is_null($this->getSession()->getData($this->getMethodCode() . '_customerbirthdate[day]'))) {
            $dob = array(
                $this->getSession()->getData($this->getMethodCode() . '_customerbirthdate[day]'),
                $this->getSession()->getData($this->getMethodCode() . '_customerbirthdate[month]'),
                $this->getSession()->getData($this->getMethodCode() . '_customerbirthdate[year]'),
            );
        } else {
            $customerId = $this->getAddress()->getCustomerId();
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $customerDob = $customer->getDob();
            if (!$customerDob) {
                return $dob;
            }

            $dob = Mage::getModel('core/date')->date('d,m,Y', $customerDob);
            $dob = explode(',', $dob);
        }

        return $dob;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        $email = $this->getSession()->getData($this->getMethodCode() . '_BPE_Customeremail');

        if (!$email) {
            $this->getAddress()->getEmail();
        }

        if (!$email && $this->getCustomer()) {
            $email = $this->getCustomer()->getEmail();
        }

        return $email;
    }

    /**
     * @return mixed
     */
    public function getBankAccount()
    {
        $account = $this->getSession()->getData($this->getMethodCode() . '_bpe_customer_account_number');

        return $account;
    }

    /**
     * @return null|string
     */
    public function getPhoneNumber()
    {
        $phoneNumber = $this->getSession()->getData($this->getMethodCode() . '_bpe_customer_phone_number');

        if (!$phoneNumber) {
            $phoneNumber = $this->getAddress()->getTelephone();
        }

        if (!$phoneNumber || $phoneNumber == '-') {
            $billingAddress = $this->getCustomer()->getDefaultBillingAddress();
            if ($billingAddress) {
                $phoneNumber = $billingAddress->getTelephone();
            }
        }

        if ($phoneNumber == '-') {
            return null;
        }

        return $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getBillingCountry()
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $this->getQuote();

        return $quote->getBillingAddress()->getCountry();
    }
}
