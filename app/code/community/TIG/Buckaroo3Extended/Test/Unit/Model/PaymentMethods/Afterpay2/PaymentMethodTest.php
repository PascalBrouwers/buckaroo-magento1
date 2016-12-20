<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_Buckaroo3Extended_Test_Unit_Model_PaymentMethods_Afterpay2_PaymentMethodTest extends TIG_Buckaroo3Extended_Test_Framework_TIG_Test_TestCase
{
    /** @var null|TIG_Buckaroo3Extended_Model_PaymentMethods_Afterpay2_PaymentMethod */
    protected $_instance = null;

    protected function _getInstance()
    {
        if ($this->_instance === null) {
            $this->_instance = $this
                ->getMockBuilder('TIG_Buckaroo3Extended_Model_PaymentMethods_Afterpay2_PaymentMethod')
                ->setMethods(array('getConfigPaymentAction', 'getInfoInstance'))
                ->getMock();
        }

        return $this->_instance;
    }

    public function testCanOrder()
    {
        $instance = $this->_getInstance();
        $result = $instance->canCapture();

        $this->assertTrue($result);
    }

    /**
     * @return array
     */
    public function canCaptureTestProvider()
    {
        return array(
            array(
                Mage_Payment_Model_Method_Abstract::ACTION_ORDER,
                false
            ),
            array(
                Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE,
                true
            ),
            array(
                Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE,
                true
            )
        );
    }

    /**
     * @param $paymentAction
     * @param $expected
     *
     * @dataProvider canCaptureTestProvider
     */
    public function testCanCapture($paymentAction, $expected)
    {
        $instance = $this->_getInstance();
        $instance->expects($this->once())
            ->method('getConfigPaymentAction')
            ->will($this->returnValue($paymentAction));

        $result = $instance->canCapture();

        $this->assertEquals($expected, $result);
    }

    public function testCapture()
    {
        $this->markTestIncomplete('TODO: Create "working" capture test case');
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Capture action is not available.
     */
    public function testShouldThrowAnExceptionIfCantCapture()
    {
        $instance = $this->_getInstance();
        $instance->expects($this->once())
            ->method('getConfigPaymentAction')
            ->will($this->returnValue(Mage_Payment_Model_Method_Abstract::ACTION_ORDER));

        $instance->capture(new Varien_Object(), 0);
    }

    public function testValidate()
    {
        $mockOrderAddress = $this->getMockBuilder('Mage_Sales_Model_Order_Address')
            ->setMethods(array('getCountryId'))
            ->getMock();
        $mockOrderAddress->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue('NL'));

        $mockOrder = $this->getMockBuilder('Mage_Sales_Model_Order')->setMethods(array('getBillingAddress'))->getMock();
        $mockOrder->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($mockOrderAddress));

        $mockPaymentInfo = $this->getMockBuilder('Mage_Sales_Model_Order_Payment')
            ->setMethods(array('getOrder'))
            ->getMock();
        $mockPaymentInfo->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($mockOrder));

        $instance = $this->_getInstance();
        $instance->expects($this->any())
            ->method('getInfoInstance')
            ->will($this->returnValue($mockPaymentInfo));

        $postData = array(
            $instance->getCode() . '_bpe_accept'                  => 'checked',
            $instance->getCode() . '_bpe_customer_account_number' => 'NL32INGB0000012345',
            $instance->getCode() . '_BPE_Customergender'          => 1,
            $instance->getCode() . '_bpe_customer_phone_number'   => '0513744112',
            $instance->getCode() . '_BPE_BusinessSelect'          => 1,
            'payment' => array(
                $instance->getCode() => array(
                    'year' => 1990,
                    'month' => 01,
                    'day' => 01
                )
            )
        );

        $request = Mage::app()->getRequest();
        $request->setPost($postData);

        $result = $instance->validate();

        $this->assertInstanceOf('TIG_Buckaroo3Extended_Model_PaymentMethods_Afterpay2_PaymentMethod', $result);
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Please accept the terms and conditions.
     */
    public function testShouldThrowAnExceptionIfNotAcceptedTos()
    {
        $instance = $this->_getInstance();
        $instance->validate();
    }
}
