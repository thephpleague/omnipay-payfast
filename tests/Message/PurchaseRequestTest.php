<?php

namespace Omnipay\PayFast\Message;

use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testSignature()
    {
        $this->request->initialize(
            array(
                'amount' => '12.00',
                'description' => 'Test Product',
                'transactionId' => 123,
                'merchantId' => 'foo',
                'merchantKey' => 'bar',
                'returnUrl' => 'https://www.example.com/return',
                'cancelUrl' => 'https://www.example.com/cancel',
            )
        );

        $data = $this->request->getData();
        $this->assertSame('812a071d77d0120073fd53ee7e45aa9b', $data['signature']);
    }

    public function testSignatureWithPassphrase()
    {
        $this->request->initialize(
            array(
                'amount' => '12.00',
                'description' => 'Test Product',
                'transactionId' => 123,
                'merchantId' => '10004554',
                'merchantKey' => '93zkeljp6j9ao',
                'returnUrl' => 'https://www.example.com/return',
                'notifyUrl' => 'https://www.example.com/notify',
                'cancelUrl' => 'https://www.example.com/cancel',
            )
        );
        $this->request->setPassphrase('ihnKRspB5IZ5bpOzLKbVArpQfiGVuWh');

        $data = $this->request->getData();
        $this->assertSame('c98b49c6d0914fa8bd13cc8974e2d29e', $data['signature']);
    }

    public function testPurchase()
    {
        $this->request->setAmount('12.00')->setDescription('Test Product');

        $response = $this->request->send();

        $this->assertInstanceOf('Omnipay\PayFast\Message\PurchaseResponse', $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getCode());

        $this->assertSame('https://www.payfast.co.za/eng/process', $response->getRedirectUrl());
        $this->assertSame('POST', $response->getRedirectMethod());
        $this->assertArrayHasKey('signature', $response->getData());
    }
}
