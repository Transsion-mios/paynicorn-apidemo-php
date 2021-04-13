<?php
/**
 * Created by PhpStorm
 * User: qingyue.zhang2@transsion.com
 * Date: 2020/12/21
 * Time: 17:42
 * Description: file todo
 */
require_once '../src/paynicorn/PaynicornClient.php';

// test url
$gateway = 'https://api.paynicorn.com/trade';
$appKey = 'PUT_YOUR_APP_KEY_HERE';
$appSecret = 'PUT_YOUR_MERCHANT_MD5_SECRET_KEY_HERE';

$payClient = new PaynicornClient($gateway, $appKey, $appSecret);
$orderId = time() . randNumber();

// test pay
$result = $payClient->pay("100", 
    "BR", "BRL", $orderId, "This is the order description", "http://www.baidu.com/");

logInfo('pay', $result);
// result content demo
// "{"code":"0000","message":"success","txnId":"32012210000010134","status":"-1","webUrl":"https://h5-test.paynicorn.com/#/index?t=J05T69m379_300002123021001147n7041a7hc"}"
$data = json_decode($result['content'], true);

// test refund
$result = $payClient->refund($data['txnId'], $orderId . randNumber());
logInfo('refund', $result);


// test payOut
$result = $payClient->payOut("100", "BR", $orderId, "BRL", "9999999", "tes"
    , "bank_account", "username", "9999", "ASD");
logInfo('payout', $result);


// test authPay
$result = $payClient->authPay("100", "BR", $orderId, "BRL", "9999999", "tes");
logInfo('authpay', $result);


// test query
$result = $payClient->query($orderId, "payment");
logInfo('query', $result);

/**
 * @return int
 */
function randNumber()
{
    return rand(100, 999);
}

/**
 * @param $func
 * @param $data
 */
function logInfo($func, $data)
{
    echo date('Y-m-d H:i:s') . "\t$func\t:\t" . json_encode($data) . PHP_EOL;
}





