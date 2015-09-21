<?php

namespace Omnipay\PayFast\Message;

use Omnipay\Common\Message\AbstractRequest;

/**
 * PayFast Purchase Request
 */
class PurchaseRequest extends AbstractRequest
{
    protected $liveEndpoint = 'https://www.payfast.co.za/eng';
    protected $testEndpoint = 'https://sandbox.payfast.co.za/eng';

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getMerchantKey()
    {
        return $this->getParameter('merchantKey');
    }

    public function setMerchantKey($value)
    {
        return $this->setParameter('merchantKey', $value);
    }

    public function getPdtKey()
    {
        return $this->getParameter('pdtKey');
    }

    public function setPdtKey($value)
    {
        return $this->setParameter('pdtKey', $value);
    }

    public function getItnPassphrase()
    {
        return $this->getParameter('itnPassphrase');
    }

    public function setItnPassphrase($value)
    {
        return $this->setParameter('itnPassphrase', $value);
    }

    public function getItemDescription()
    {
        return $this->getParameter('item_description');
    }

    public function setItemDescription($value)
    {
        return $this->setParameter('item_description', $value);
    }

    public function getCustomStr1()
    {
        return $this->getParameter('customStr1');
    }

    public function setCustomStr1($value)
    {
        return $this->setParameter('customStr1', $value);
    }

    public function getCustomStr2()
    {
        return $this->getParameter('customStr2');
    }

    public function setCustomStr2($value)
    {
        return $this->setParameter('customStr2', $value);
    }

    public function getCustomStr3()
    {
        return $this->getParameter('customStr3');
    }

    public function setCustomStr3($value)
    {
        return $this->setParameter('customStr3', $value);
    }

    public function getCustomStr4()
    {
        return $this->getParameter('customStr4');
    }

    public function setCustomStr4($value)
    {
        return $this->setParameter('customStr4', $value);
    }

    public function getCustomStr5()
    {
        return $this->getParameter('customStr5');
    }

    public function setCustomStr5($value)
    {
        return $this->setParameter('customStr5', $value);
    }

    public function getCustomInt1()
    {
        return $this->getParameter('customInt1');
    }

    public function setCustomInt1($value)
    {
        return $this->setParameter('customInt1', $value);
    }

    public function getCustomInt2()
    {
        return $this->getParameter('customInt2');
    }

    public function setCustomInt2($value)
    {
        return $this->setParameter('customInt2', $value);
    }

    public function getCustomInt3()
    {
        return $this->getParameter('customInt3');
    }

    public function setCustomInt3($value)
    {
        return $this->setParameter('customInt3', $value);
    }

    public function getCustomInt4()
    {
        return $this->getParameter('customInt4');
    }

    public function setCustomInt4($value)
    {
        return $this->setParameter('customInt4', $value);
    }

    public function getCustomInt5()
    {
        return $this->getParameter('customInt5');
    }

    public function setCustomInt5($value)
    {
        return $this->setParameter('customInt5', $value);
    }

    public function getEmailConfirmation()
    {
        return $this->getParameter('emailConfirmation');
    }

    public function setEmailConfirmation($value)
    {
        return $this->setParameter('emailConfirmation', $value);
    }

    public function getConfirmationAddress()
    {
        return $this->getParameter('confirmationAddress');
    }

    public function setConfirmationAddress($value)
    {
        return $this->setParameter('confirmationAddress', $value);
    }

    public function getData()
    {
        $this->validate('amount');

        $data = [];

        // Merchant Details
        $data['merchant_id'] = $this->getMerchantId();
        $data['merchant_key'] = $this->getMerchantKey();
        $data['return_url'] = $this->getReturnUrl();
        $data['cancel_url'] = $this->getCancelUrl();
        $data['notify_url'] = $this->getNotifyUrl();

        // Payer Details (optional)
        if ($this->getCard()) {
            $data['name_first'] = $this->getCard()->getFirstName();
            $data['name_last'] = $this->getCard()->getLastName();
            $data['email_address'] = $this->getCard()->getEmail();
        }

        // Transaction Details
        $data['m_payment_id'] = $this->getTransactionId(); // 100 char
        $data['amount'] = $this->getAmount();
        $data['item_name'] = $this->getDescription(); // 100 char
        $data['item_description'] = $this->getItemDescription(); // 255 char
        $data['custom_int1'] = $this->getCustomInt1();
        $data['custom_int2'] = $this->getCustomInt2();
        $data['custom_int3'] = $this->getCustomInt3();
        $data['custom_int4'] = $this->getCustomInt4();
        $data['custom_int5'] = $this->getCustomInt5();
        $data['custom_str1'] = $this->getCustomStr1(); // 255 char
        $data['custom_str2'] = $this->getCustomStr2(); // 255 char
        $data['custom_str3'] = $this->getCustomStr3(); // 255 char
        $data['custom_str4'] = $this->getCustomStr4(); // 255 char
        $data['custom_str5'] = $this->getCustomStr5(); // 255 char

        // Transaction Options(optional)
        $data['email_confirmation'] = $this->getEmailConfirmation();
        $data['confirmation_address'] = $this->getConfirmationAddress(); // 100 char

        $data['signature'] = $this->generateSignature($data);

        return $data;
    }

    protected function generateSignature($data)
    {
        $getString = '';
        foreach ($data as $key => $val) {
            if (!empty($val)) {
                $getString .= $key . '=' . urlencode(trim($val)) .'&';
            }
        }

        $getString = substr($getString, 0, -1);

        $passPhrase = $this->getItnPassphrase();
        if (!empty($passPhrase)) {
            $getString .= '&passphrase=' . urlencode($passPhrase);
        }
        $signature = md5($getString);

        return $signature;
    }

    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data, $this->getEndpoint() . '/process');
    }

    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }
}
