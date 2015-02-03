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
            'description' => 'Test Product',
            'transactionId' => 123,
            'merchantId' => 'foo',
            'merchantKey' => 'bar',
            'returnUrl' => 'https://www.example.com/return',
            'cancelUrl' => 'https://www.example.com/cancel',
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

        // If a passphrase has been set in the PayFast Settings, then it needs to be included in the signature string.
        $passPhrase = 'XXXXX'; // You need to get this from a constant or stored in your website
        if( !empty( $passPhrase ) )
        {
            $pfTempParamString .= '&passphrase='.urlencode( $passPhrase );
        }
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
