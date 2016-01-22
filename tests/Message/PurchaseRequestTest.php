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
                'merchantId' => '123',
                'merchantKey' => 'bar',
                'returnUrl' => 'https://www.example.com/return',
                'cancelUrl' => 'https://www.example.com/cancel',
            )
        );

        $data = $this->request->getData();
        $this->assertSame('032684ceb17276430c4263544fa4cf71', $data['signature']);
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
