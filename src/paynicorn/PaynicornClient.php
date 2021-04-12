<?php
/**
 * Created by PhpStorm
 * User: qingyue.zhang2@transsion.com
 * Date: 2020/12/21
 * Time: 17:38
 * Description: file todo
 */


/**
 * Class PaynicornClient
 */
class PaynicornClient
{

    const PAY_URI = '/v3/transaction/pay';
    const REFUND_URI = '/v3/transaction/refund';
    const AUTH_PAY_URI = '/v3/transaction/authpay';
    const PAY_OUT_URI = '/v3/transaction/payout';
    const QUERY_URI = '/v3/transaction/query';

    private $gatewayUrl;
    private $appSecret;
    private $appKey;

    /**
     * PaynicornClient constructor.
     * @param $gatewayUrl
     * @param $appKey
     * @param $appSecret
     */
    public function __construct($gatewayUrl, $appKey, $appSecret)
    {
        $this->gatewayUrl = $gatewayUrl;
        $this->appSecret = $appSecret;
        $this->appKey = $appKey;
    }

    /**
     * @param $account
     * @param $amount
     * @param $bankCode
     * @param $countryCode
     * @param $currency
     * @param $name
     * @param $orderId
     * @param $payOutType
     * @return bool|string|string[]
     */
    public function pay($amount, $countryCode, $currency, $name, $orderId, $payMethod, $cpFrontPage)
    {
        $payData = [
            "cpFrontPage" => $cpFrontPage,
            "amount" => $amount,
            "countryCode" => $countryCode,
            "currency" => $currency,
            "name" => $name,
            "orderId" => $orderId,
            "payMethod" => $payMethod,
        ];
        try {
            $response = $this->curl($this->gatewayUrl . self::PAY_URI, $this->aloneSignBody($payData));
            return $this->decodeResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @param $payTxnId
     * @param $refundId
     * @return bool|mixed|string[]
     */
    public function refund($payTxnId, $refundId)
    {
        $refundData = [
            'payTxnId' => $payTxnId,
            'refundId' => $refundId,
        ];
        try {
            $response = $this->curl($this->gatewayUrl . self::REFUND_URI, $this->aloneSignBody($refundData));
            return $this->decodeResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @param $amount
     * @param $countryCode
     * @param $orderId
     * @param $currency
     * @param $userId
     * @param $authCode
     * @return bool|mixed|string[]
     */
    public function authPay($amount, $countryCode, $orderId, $currency, $userId, $authCode)
    {
        $authPayData = [
            'amount' => $amount,
            'countryCode' => $countryCode,
            'orderId' => $orderId,
            'currency' => $currency,
            'userId' => $userId,
            'authCode' => $authCode,
        ];
        try {
            $response = $this->curl($this->gatewayUrl . self::AUTH_PAY_URI, $this->aloneSignBody($authPayData));
            return $this->decodeResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }


    /**
     * @param $amount
     * @param $countryCode
     * @param $orderId
     * @param $currency
     * @param $userId
     * @param $authCode
     * @param $payoutType
     * @param $name
     * @param $account
     * @param $bankCode
     * @return bool|mixed|string[]
     */
    public function payOut($amount, $countryCode, $orderId, $currency, $userId, $authCode, $payoutType, $name, $account, $bankCode)
    {
        $payOutData = [
            'amount' => $amount,
            'countryCode' => $countryCode,
            'orderId' => $orderId,
            'currency' => $currency,
            'userId' => $userId,
            'authCode' => $authCode,
            'payoutType' => $payoutType,
            'name' => $name,
            'account' => $account,
            'bankCode' => $bankCode,
        ];

        try {
            $response = $this->curl($this->gatewayUrl . self::PAY_OUT_URI, $this->aloneSignBody($payOutData));
            return $this->decodeResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }


    /**
     * @param $orderId
     * @param $txnType
     * @return bool|mixed|string[]
     */
    public function query($orderId, $txnType)
    {
        $queryData = [
            'orderId' => $orderId,
            'txnType' => $txnType,
        ];
        try {
            $response = $this->curl($this->gatewayUrl . self::QUERY_URI, $this->aloneSignBody($queryData));
            return $this->decodeResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }


    /**
     * @param $data
     * @return array
     */
    protected function aloneSignBody($data)
    {
        $data['requestTime'] = $this->getMillisecond();
        $base64Content = base64_encode(json_encode($data));
        return [
            'appKey' => $this->appKey,
            'content' => $base64Content,
            'sign' => md5($base64Content . $this->appSecret),
        ];
    }

    /**
     * @param $msg
     * @return string[]
     */
    protected function errorResponse($msg)
    {
        return [
            'responseCode' => '10000',
            'responseMessage' => $msg,
            'content' => '',
        ];
    }

    /**
     * @param $body
     * @return bool|mixed|string[]
     */
    protected function decodeResponse($body)
    {
        $data = json_decode($body, true);
        if (!$data) {
            return $this->errorResponse('error response');
        }
        if ($data['responseCode'] != '000000') {
            $data = json_decode($data['responseMessage'], true);
        }
        $sign = md5($data['content'] . $this->appSecret);
        if ($data['sign'] == $sign) {
            $data['content'] = base64_decode($data['content']);
            return $data;
        }
        return false;
    }

    /**
     * @return float
     */
    protected function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }


    /**
     * @param $url
     * @param null $body
     * @return bool|string
     * @throws Exception
     */
    protected function curl($url, $body = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                $result = $this->decodeResponse($response);
                throw new Exception($result['content'], $httpStatusCode);
            }
        }
        curl_close($ch);
        return $response;
    }
}