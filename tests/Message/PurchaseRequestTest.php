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
        $data = array(
            'amount' => '12.00',
            'item_description' => 'Test Product',
            'm_payment_id' => 123,
            'merchant_id' => 'foo',
            'merchant_key' => 'bar',
            'return_url' => 'https://www.example.com/return',
            'cancel_url' => 'https://www.example.com/cancel',
        );

        $this->request->initialize($data);

        $data = $this->request->getData();
        $this->assertSame($this->generateSignature($data), $data['signature']);
    }

    protected function generateSignature($data)
    {
        // Strip any slashes in data
        $pfData = [];
        foreach( $data as $key => $val ) {
            $pfData[$key] = stripslashes( $val );
        }

        // Dump the submitted variables and calculate security signature
        $pfParamString = '';
        foreach( $pfData as $key => $val ) {
            if( $key != 'signature' ) {
                $pfParamString .= $key .'='. urlencode( $val ) .'&';
            }
        }

        // Remove the last '&' from the parameter string
        $pfParamString = substr($pfParamString, 0, -1);
        $pfTempParamString = $pfParamString;

        $signature = md5( $pfTempParamString );

        return md5( $pfTempParamString );
    }

    public function testPurchase()
    {
        $this->request->setAmount('12.00')->setItemDescription('Test Product');

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
