<?php

namespace OroB2B\Bundle\PaymentBundle\Tests\Unit\Method\View;

use OroB2B\Bundle\PaymentBundle\Method\View\PayflowGatewayView;
use OroB2B\Bundle\PaymentBundle\DependencyInjection\Configuration;

class PayflowGatewayViewTest extends AbstractPayflowGatewayViewTest
{
    /**
     * @return PayflowGatewayView
     */
    protected function getMethodView()
    {
        return new PayflowGatewayView(
            $this->formFactory,
            $this->configManager,
            $this->paymentTransactionProvider
        );
    }

    /** {@inheritdoc} */
    protected function getZeroAmountKey()
    {
        return Configuration::PAYFLOW_GATEWAY_ZERO_AMOUNT_AUTHORIZATION_KEY;
    }

    /** {@inheritdoc} */
    protected function getAllowedCCTypesKey()
    {
        return Configuration::PAYFLOW_GATEWAY_ALLOWED_CC_TYPES_KEY;
    }

    /** {@inheritdoc} */
    protected function getRequireCvvEntryKey()
    {
        return Configuration::PAYFLOW_GATEWAY_REQUIRE_CVV_KEY;
    }

    /** {@inheritdoc} */
    protected function getAuthForRequiredAmountKey()
    {
        return Configuration::PAYFLOW_GATEWAY_AUTHORIZATION_FOR_REQUIRED_AMOUNT_KEY;
    }

    public function testGetOrder()
    {
        $order = '100';
        $this->setConfig($this->once(), Configuration::PAYFLOW_GATEWAY_SORT_ORDER_KEY, $order);

        $this->assertSame((int)$order, $this->methodView->getOrder());
    }

    public function testGetPaymentMethodType()
    {
        $this->assertEquals('payflow_gateway', $this->methodView->getPaymentMethodType());
    }

    public function testGetLabel()
    {
        $this->setConfig($this->once(), Configuration::PAYFLOW_GATEWAY_LABEL_KEY, 'testValue');
        $this->assertEquals('testValue', $this->methodView->getLabel());
    }

    public function testGetShortLabel()
    {
        $this->setConfig($this->once(), Configuration::PAYFLOW_GATEWAY_SHORT_LABEL_KEY, 'testValue');
        $this->assertEquals('testValue', $this->methodView->getShortLabel());
    }
}